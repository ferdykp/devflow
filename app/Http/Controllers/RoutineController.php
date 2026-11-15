<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoutineController extends Controller
{
    public function index(Request $request): View
    {
        $timezone = $request->user()->timezone ?: 'Asia/Jakarta';
        $start = today($timezone)->startOfWeek();
        $end = $start->copy()->endOfWeek();
        $insightStart = today($timezone)->subDays(27);
        $routines = $request->user()->routines()->with([
            'goal:id,title', 'learningPath:id,title',
            'checkIns' => fn ($query) => $query->whereBetween('occurred_on', [$insightStart->toDateString(), today($timezone)->toDateString()])->orderBy('occurred_on'),
        ])->orderBy('status')->orderBy('preferred_time')->get();
        $goals = $request->user()->personalGoals()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $learningPaths = $request->user()->learningPaths()->where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $templates = [
            ['title' => 'Belajar software engineering', 'frequency' => 'weekly', 'days' => [1, 3, 5], 'target' => 3, 'minutes' => 45, 'minimum' => 'Buka materi dan baca 5 menit', 'normal' => 'Pelajari satu topik', 'extended' => 'Belajar dan selesaikan latihan'],
            ['title' => 'Latihan bahasa Inggris', 'frequency' => 'daily', 'days' => [], 'target' => 5, 'minutes' => 20, 'minimum' => 'Pelajari 3 kosakata', 'normal' => 'Latihan 20 menit', 'extended' => 'Latihan dan rekam speaking'],
            ['title' => 'Review malam', 'frequency' => 'daily', 'days' => [], 'target' => 7, 'minutes' => 10, 'minimum' => 'Catat satu hal penting', 'normal' => 'Tinjau hari ini', 'extended' => 'Tinjau hari ini dan rencanakan besok'],
        ];

        return view('routines.index', compact('routines', 'goals', 'learningPaths', 'templates', 'start', 'end'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'frequency' => ['required', Rule::in(['daily', 'weekly'])],
            'days_of_week' => ['nullable', 'array', 'max:7'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'target_per_week' => ['required', 'integer', 'min:1', 'max:7'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'minimum_action' => ['nullable', 'string', 'max:200'],
            'normal_action' => ['nullable', 'string', 'max:200'],
            'extended_action' => ['nullable', 'string', 'max:200'],
            'personal_goal_id' => ['nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
            'learning_path_id' => ['nullable', 'integer', Rule::exists('learning_paths', 'id')->where('owner_id', $request->user()->id)],
        ]);
        $data['days_of_week'] = $data['frequency'] === 'weekly' ? array_values(array_unique($data['days_of_week'] ?? [])) : null;
        if ($data['frequency'] === 'weekly' && $data['days_of_week'] === []) {
            return back()->withErrors(['days_of_week' => 'Pilih setidaknya satu hari untuk rutinitas mingguan.'])->withInput();
        }
        $data['status'] = 'active';
        $request->user()->routines()->create($data);

        return back()->with('success', 'Rutinitas ditambahkan.');
    }

    public function update(Request $request, int $routine): RedirectResponse
    {
        $routineModel = $request->user()->routines()->findOrFail($routine);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['active', 'paused', 'archived'])],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'frequency' => ['sometimes', Rule::in(['daily', 'weekly'])],
            'days_of_week' => ['sometimes', 'nullable', 'array', 'max:7'],
            'days_of_week.*' => ['integer', 'between:1,7'],
            'target_per_week' => ['sometimes', 'required', 'integer', 'min:1', 'max:7'],
            'preferred_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'estimated_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1440'],
            'minimum_action' => ['sometimes', 'nullable', 'string', 'max:200'],
            'normal_action' => ['sometimes', 'nullable', 'string', 'max:200'],
            'extended_action' => ['sometimes', 'nullable', 'string', 'max:200'],
            'personal_goal_id' => ['sometimes', 'nullable', 'integer', Rule::exists('personal_goals', 'id')->where('owner_id', $request->user()->id)],
            'learning_path_id' => ['sometimes', 'nullable', 'integer', Rule::exists('learning_paths', 'id')->where('owner_id', $request->user()->id)],
        ]);
        if (isset($data['frequency'])) {
            $data['days_of_week'] = $data['frequency'] === 'weekly' ? array_values(array_unique($data['days_of_week'] ?? [])) : null;
            if ($data['frequency'] === 'weekly' && $data['days_of_week'] === []) {
                return back()->withErrors(['days_of_week' => 'Pilih setidaknya satu hari untuk rutinitas mingguan.'])->withInput();
            }
        }
        $routineModel->update($data);

        return back()->with('success', 'Status rutinitas diperbarui.');
    }

    public function checkIn(Request $request, int $routine): RedirectResponse
    {
        $routineModel = $request->user()->routines()->findOrFail($routine);
        $data = $request->validate([
            'status' => ['required', Rule::in(['completed', 'skipped'])],
            'activity_level' => ['nullable', Rule::in(['minimum', 'normal', 'extended'])],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        DB::transaction(function () use ($request, $routineModel, $data): void {
            $lockedRoutine = $request->user()->routines()->lockForUpdate()->findOrFail($routineModel->id);
            $lockedRoutine->checkIns()->updateOrCreate(
                ['occurred_on' => today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString()],
                $data,
            );
        });

        return back()->with('success', $data['status'] === 'completed' ? 'Rutinitas dicatat selesai.' : 'Rutinitas dilewati.');
    }
}
