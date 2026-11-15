<?php

namespace App\Http\Controllers;

use App\Models\TaskHistoryEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(Request $request): View
    {
        $goals = $request->user()->personalGoals()->with([
            'milestones', 'progressEntries' => fn ($query) => $query->latest('recorded_on')->limit(8),
            'tasks' => fn ($query) => $query->whereNull('project_id')->orderBy('due_date'),
            'routines:id,title,personal_goal_id,status', 'learningPaths:id,title,personal_goal_id,status',
        ])->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'paused' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
            ->orderBy('target_date')->get();

        return view('goals.index', compact('goals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:4000'],
            'category' => ['nullable', 'string', 'max:100'],
            'horizon' => ['required', Rule::in(['short', 'medium', 'long'])],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress_mode' => ['required', Rule::in(['milestones', 'numeric'])],
            'current_value' => ['required_if:progress_mode,numeric', 'nullable', 'numeric', 'min:0'],
            'target_value' => ['required_if:progress_mode,numeric', 'nullable', 'numeric', 'gt:0'],
            'unit' => ['required_if:progress_mode,numeric', 'nullable', 'string', 'max:40'],
            'review_every_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);
        if (($data['progress_mode'] ?? null) === 'numeric' && (float) $data['current_value'] > (float) $data['target_value']) {
            return back()->withErrors(['current_value' => 'Progress awal tidak boleh melebihi target.'])->withInput();
        }
        $data['status'] = 'active';
        $goal = $request->user()->personalGoals()->create($data);
        if ($goal->progress_mode === 'numeric') {
            $goal->progressEntries()->create(['value' => $goal->current_value, 'recorded_on' => $goal->start_date ?? today(), 'evidence' => 'Nilai awal']);
        }

        return back()->with('success', 'Goal berhasil dibuat.');
    }

    public function update(Request $request, int $goal): RedirectResponse
    {
        $goalModel = $request->user()->personalGoals()->findOrFail($goal);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['active', 'paused', 'completed', 'archived'])],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'horizon' => ['sometimes', Rule::in(['short', 'medium', 'long'])],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'target_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'progress_mode' => ['sometimes', Rule::in(['milestones', 'numeric'])],
            'target_value' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:40'],
            'current_value' => ['sometimes', 'numeric', 'min:0'],
            'evidence' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'next_review_on' => ['sometimes', 'nullable', 'date'],
        ]);
        if (isset($data['target_value'], $data['current_value']) && (float) $data['current_value'] > (float) $data['target_value']) {
            return back()->withErrors(['current_value' => 'Nilai progress tidak boleh melebihi target.'])->withInput();
        }
        if (isset($data['target_value']) && ! array_key_exists('current_value', $data) && (float) $goalModel->current_value > (float) $data['target_value']) {
            return back()->withErrors(['target_value' => 'Target baru tidak boleh lebih kecil daripada nilai progress saat ini.'])->withInput();
        }
        if (isset($data['progress_mode']) && $data['progress_mode'] === 'milestones') {
            $data['current_value'] = null;
            $data['target_value'] = null;
            $data['unit'] = null;
        }
        DB::transaction(function () use ($goalModel, $data, $request): void {
            $lockedGoal = $request->user()->personalGoals()->lockForUpdate()->findOrFail($goalModel->id);
            $metadata = array_diff_key($data, array_flip(['current_value', 'evidence', 'status', 'next_review_on']));
            $lockedGoal->fill($metadata);
            if (array_key_exists('current_value', $data)) {
                if ($lockedGoal->progress_mode !== 'numeric' || (float) $data['current_value'] > (float) $lockedGoal->target_value) {
                    abort(422, 'Nilai progress harus berada di antara nol dan target goal.');
                }
                $lockedGoal->progressEntries()->create([
                    'value' => $data['current_value'],
                    'recorded_on' => today($request->user()->timezone ?: 'Asia/Jakarta')->toDateString(),
                    'evidence' => $data['evidence'] ?? null,
                ]);
                $lockedGoal->current_value = $data['current_value'];
            }
            if (array_key_exists('status', $data)) {
                $lockedGoal->status = $data['status'];
            }
            if (array_key_exists('next_review_on', $data)) {
                $lockedGoal->next_review_on = $data['next_review_on'];
            }
            $lockedGoal->save();
        });

        return back()->with('success', 'Goal diperbarui.');
    }

    public function storeMilestone(Request $request, int $goal): RedirectResponse
    {
        $goalModel = $request->user()->personalGoals()->findOrFail($goal);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_date' => ['nullable', 'date'],
        ]);
        $goalModel->milestones()->create($data + ['sort_order' => (int) $goalModel->milestones()->max('sort_order') + 1]);

        return back()->with('success', 'Milestone ditambahkan.');
    }

    public function updateMilestone(Request $request, int $goal, int $milestone): RedirectResponse
    {
        $goalModel = $request->user()->personalGoals()->findOrFail($goal);
        $milestoneModel = $goalModel->milestones()->findOrFail($milestone);
        $data = $request->validate([
            'completed' => ['sometimes', 'boolean'],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'target_date' => ['sometimes', 'nullable', 'date'],
        ]);
        $completed = array_key_exists('completed', $data) ? (bool) $data['completed'] : null;
        unset($data['completed']);
        if ($completed !== null) {
            $data['completed_at'] = $completed ? now() : null;
        }
        $milestoneModel->update($data);

        return back()->with('success', $completed === null ? 'Milestone diperbarui.' : ($completed ? 'Milestone dicatat tercapai.' : 'Milestone dibuka kembali.'));
    }

    public function storeTask(Request $request, int $goal): RedirectResponse
    {
        $goalModel = $request->user()->personalGoals()->findOrFail($goal);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
        ]);
        $task = $request->user()->personalTasks()->create($data + ['personal_goal_id' => $goalModel->id]);
        TaskHistoryEntry::query()->create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'action' => 'created', 'to_status' => 'todo']);

        return back()->with('success', 'Tugas goal ditambahkan ke daftar tugas dan Hari Ini.');
    }
}
