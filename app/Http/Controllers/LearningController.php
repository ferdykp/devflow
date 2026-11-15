<?php

namespace App\Http\Controllers;

use App\Models\LearningAssessment;
use App\Models\LearningLesson;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $paths = $user->learningPaths()->with([
            'goal:id,title',
            'modules.lessons.materials',
            'modules.lessons.assessments' => fn ($query) => $query->where('owner_id', $user->id)->latest('assessed_on')->limit(3),
            'modules.lessons.sessions' => fn ($query) => $query->where('owner_id', $user->id)->latest('occurred_on')->limit(3),
        ])->orderBy('status')->orderBy('title')->get();
        $goals = $user->personalGoals()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $reviewQueue = LearningLesson::query()->where('next_review_at', '<=', now())
            ->whereHas('module.path', fn ($path) => $path->where('owner_id', $user->id)->where('status', 'active'))
            ->with('module.path:id,title')->orderBy('next_review_at')->limit(20)->get();
        $history = $user->learningSessions()->with(['path:id,title', 'lesson:id,title'])->latest('occurred_on')->limit(20)->get();

        return view('learning.index', compact('paths', 'goals', 'reviewQueue', 'history'));
    }

    public function storePath(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'personal_goal_id' => ['nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
        ]);
        $request->user()->learningPaths()->create($data + ['status' => 'active']);

        return back()->with('success', 'Jalur belajar dibuat.');
    }

    public function updatePath(Request $request, int $path): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'subject' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:3000'],
            'status' => ['sometimes', Rule::in(['active', 'paused', 'completed', 'archived'])],
            'personal_goal_id' => ['sometimes', 'nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
        ]);
        $pathModel->update($data);

        return back()->with('success', 'Jalur belajar diperbarui.');
    }

    public function storeModule(Request $request, int $path): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:2000']]);
        $pathModel->modules()->create($data + ['sort_order' => (int) $pathModel->modules()->max('sort_order') + 1]);

        return back()->with('success', 'Modul ditambahkan.');
    }

    public function updateModule(Request $request, int $path, int $module): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $moduleModel = $pathModel->modules()->findOrFail($module);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ]);
        $moduleModel->update($data);

        return back()->with('success', 'Modul diperbarui.');
    }

    public function storeLesson(Request $request, int $path): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $data = $request->validate([
            'learning_module_id' => ['required', 'integer', Rule::exists('learning_modules', 'id')->where('learning_path_id', $pathModel->id)],
            'title' => ['required', 'string', 'max:200'],
            'objectives' => ['nullable', 'string', 'max:2000'],
            'materials_markdown' => ['nullable', 'string', 'max:50000'],
            'reference_url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);
        $request->user()->learningPaths()->findOrFail($path)->modules()->findOrFail($data['learning_module_id'])
            ->lessons()->create($data + ['status' => 'not_started']);

        return back()->with('success', 'Pelajaran ditambahkan.');
    }

    public function updateLesson(Request $request, int $path, int $lesson): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $lessonModel = LearningLesson::query()->whereKey($lesson)
            ->whereHas('module', fn ($module) => $module->where('learning_path_id', $pathModel->id))->firstOrFail();
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['not_started', 'content_viewed', 'practice_completed', 'competency_demonstrated'])],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'objectives' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'materials_markdown' => ['sometimes', 'nullable', 'string', 'max:50000'],
            'reference_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'competency_evidence' => ['required_if:status,competency_demonstrated', 'nullable', 'string', 'max:3000'],
            'knowledge_gap' => ['sometimes', 'boolean'],
            'next_review_at' => ['sometimes', 'nullable', 'date'],
        ]);
        if (($data['status'] ?? null) !== 'competency_demonstrated' && ! array_key_exists('competency_evidence', $data)) {
            $data['competency_evidence'] = null;
        }
        if (array_key_exists('status', $data)) {
            $data['status_updated_at'] = now();
        }
        $lessonModel->update($data);

        return back()->with('success', 'Status pelajaran dan bukti diperbarui.');
    }

    public function storeSession(Request $request, int $path, int $lesson): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $lessonModel = LearningLesson::query()->whereKey($lesson)
            ->whereHas('module', fn ($module) => $module->where('learning_path_id', $pathModel->id))->firstOrFail();
        $data = $request->validate([
            'session_type' => ['required', Rule::in(['study', 'practice', 'review'])],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'evidence' => ['nullable', 'string', 'max:2000'],
            'occurred_on' => ['nullable', 'date'],
        ]);
        $request->user()->learningSessions()->create([
            'learning_path_id' => $pathModel->id,
            'learning_lesson_id' => $lessonModel->id,
            'session_type' => $data['session_type'],
            'duration_minutes' => $data['duration_minutes'],
            'evidence' => $data['evidence'] ?? null,
            'occurred_on' => $data['occurred_on'] ?? today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString(),
        ]);

        return back()->with('success', 'Sesi belajar tersimpan. Status kompetensi tetap kamu tentukan sendiri.');
    }

    public function updateSession(Request $request, int $session): RedirectResponse
    {
        $sessionModel = $request->user()->learningSessions()->findOrFail($session);
        $data = $request->validate([
            'session_type' => ['required', Rule::in(['study', 'practice', 'review'])],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'evidence' => ['nullable', 'string', 'max:2000'],
            'occurred_on' => ['required', 'date'],
        ]);
        $sessionModel->update($data);

        return back()->with('success', 'Catatan sesi belajar diperbarui.');
    }

    public function destroySession(Request $request, int $session): RedirectResponse
    {
        $request->user()->learningSessions()->findOrFail($session)->delete();

        return back()->with('success', 'Catatan sesi belajar dihapus.');
    }

    public function storeAssessment(Request $request, int $path, int $lesson): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $lessonModel = LearningLesson::query()->whereKey($lesson)->whereHas('module', fn ($module) => $module->where('learning_path_id', $pathModel->id))->firstOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'maximum_score' => ['nullable', 'numeric', 'gt:0', 'required_with:score'],
            'evidence' => ['nullable', 'string', 'max:2000'],
            'assessed_on' => ['required', 'date'],
        ]);
        if (isset($data['score'], $data['maximum_score']) && $data['score'] > $data['maximum_score']) {
            return back()->withErrors(['score' => 'Nilai tidak boleh melebihi nilai maksimum.'])->withInput();
        }
        $lessonModel->assessments()->create($data + ['owner_id' => $request->user()->id]);

        return back()->with('success', 'Hasil latihan/penilaian dicatat.');
    }

    public function updateAssessment(Request $request, int $assessment): RedirectResponse
    {
        $assessmentModel = LearningAssessment::query()->where('owner_id', $request->user()->id)->findOrFail($assessment);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'maximum_score' => ['nullable', 'numeric', 'gt:0', 'required_with:score'],
            'evidence' => ['nullable', 'string', 'max:2000'],
            'assessed_on' => ['required', 'date'],
        ]);
        if (isset($data['score'], $data['maximum_score']) && $data['score'] > $data['maximum_score']) {
            return back()->withErrors(['score' => 'Nilai tidak boleh melebihi nilai maksimum.'])->withInput();
        }
        $assessmentModel->update($data);

        return back()->with('success', 'Hasil latihan diperbarui.');
    }

    public function destroyAssessment(Request $request, int $assessment): RedirectResponse
    {
        LearningAssessment::query()->where('owner_id', $request->user()->id)->findOrFail($assessment)->delete();

        return back()->with('success', 'Hasil latihan dihapus.');
    }

    public function storeMaterial(Request $request, int $path, int $lesson): RedirectResponse
    {
        $pathModel = $request->user()->learningPaths()->findOrFail($path);
        $lessonModel = LearningLesson::query()->whereKey($lesson)->whereHas('module', fn ($module) => $module->where('learning_path_id', $pathModel->id))->firstOrFail();
        $data = $request->validate(['file' => ['required', 'file', 'mimes:pdf,txt,md,png,jpg,jpeg', 'max:10240']]);
        $file = $data['file'];
        $storagePath = $file->store('learning/'.$request->user()->id.'/'.$lessonModel->id, 'local');
        $lessonModel->materials()->create([
            'owner_id' => $request->user()->id,
            'original_name' => basename($file->getClientOriginalName()),
            'storage_path' => $storagePath,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Lampiran belajar berhasil diunggah.');
    }

    public function downloadMaterial(Request $request, int $material)
    {
        $materialModel = LearningMaterial::query()->where('owner_id', $request->user()->id)
            ->whereHas('lesson.module.path', fn ($path) => $path->where('owner_id', $request->user()->id))
            ->findOrFail($material);
        abort_unless(Storage::disk('local')->exists($materialModel->storage_path), 404);

        return Storage::disk('local')->download($materialModel->storage_path, $materialModel->original_name);
    }
}
