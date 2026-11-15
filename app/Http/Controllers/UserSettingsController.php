<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('settings.index', [
            'timezone' => $request->user()->timezone ?: 'Asia/Jakarta',
            'preferences' => $request->user()->preferences ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['timezone' => ['required', 'timezone']]);
        $request->user()->update($data);

        return back()->with('success', 'Zona waktu diperbarui.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($request->user())],
        ]);
        $request->user()->fill($data)->save();

        return back()->with('success', 'Profil diperbarui.');
    }

    public function welcome(Request $request): View
    {
        $user = $request->user();
        $workspaces = $user->workspaces()->withCount('projects')->get()
            ->merge($user->joinedWorkspaces()->withCount('projects')->get())
            ->unique('id')->sortBy('name')->values();

        return view('onboarding.welcome', compact('workspaces'));
    }

    public function completeWelcome(Request $request): RedirectResponse
    {
        $preferences = $request->user()->preferences ?? [];
        $preferences['onboarding']['welcome_completed'] = true;
        $request->user()->update(['preferences' => $preferences]);

        return redirect()->route('onboarding.setup');
    }

    public function setup(Request $request): View
    {
        return view('onboarding.setup', [
            'timezone' => $request->user()->timezone ?: 'Asia/Jakarta',
            'preferences' => $request->user()->preferences ?? [],
        ]);
    }

    public function saveSetup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'timezone' => ['required', 'timezone'],
            'working_days' => ['nullable', 'array', 'max:7'],
            'working_days.*' => ['integer', 'between:1,7'],
            'workday_start' => ['required', 'date_format:H:i'],
            'workday_end' => ['required', 'date_format:H:i', 'after:workday_start'],
            'learning_aim' => ['nullable', 'string', 'max:200'],
        ]);
        $preferences = $request->user()->preferences ?? [];
        $preferences['work_schedule'] = [
            'days' => array_values(array_unique($data['working_days'] ?? [])),
            'start' => $data['workday_start'],
            'end' => $data['workday_end'],
        ];
        $preferences['learning_aim'] = $data['learning_aim'] ?? null;
        $preferences['onboarding']['welcome_completed'] = true;
        $preferences['onboarding']['setup_completed'] = true;
        $request->user()->update(['timezone' => $data['timezone'], 'preferences' => $preferences]);

        return redirect()->route('dashboard')->with('success', 'Pengaturan awal tersimpan. Kamu bisa mengubahnya kapan saja.');
    }

    public function updateWorkSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'working_days' => ['nullable', 'array', 'max:7'],
            'working_days.*' => ['integer', 'between:1,7'],
            'workday_start' => ['required', 'date_format:H:i'],
            'workday_end' => ['required', 'date_format:H:i', 'after:workday_start'],
            'learning_aim' => ['nullable', 'string', 'max:200'],
        ]);
        $preferences = $request->user()->preferences ?? [];
        $preferences['work_schedule'] = [
            'days' => array_values(array_unique($data['working_days'] ?? [])),
            'start' => $data['workday_start'],
            'end' => $data['workday_end'],
        ];
        $preferences['learning_aim'] = $data['learning_aim'] ?? null;
        $request->user()->update(['preferences' => $preferences]);

        return back()->with('success', 'Jadwal kerja dan tujuan belajar disimpan.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'task_reminders' => ['nullable', 'boolean'],
            'routine_reminders' => ['nullable', 'boolean'],
            'learning_reminders' => ['nullable', 'boolean'],
            'weekly_review_reminder' => ['nullable', 'boolean'],
            'reminder_time' => ['required', 'date_format:H:i'],
            'reminder_channel' => ['required', Rule::in(['in_app', 'email'])],
        ]);
        $preferences = $request->user()->preferences ?? [];
        $preferences['notifications'] = [
            'task_reminders' => (bool) ($data['task_reminders'] ?? false),
            'routine_reminders' => (bool) ($data['routine_reminders'] ?? false),
            'learning_reminders' => (bool) ($data['learning_reminders'] ?? false),
            'weekly_review_reminder' => (bool) ($data['weekly_review_reminder'] ?? false),
            'reminder_time' => $data['reminder_time'],
            'reminder_channel' => $data['reminder_channel'],
        ];
        $request->user()->update(['preferences' => $preferences]);

        return back()->with('success', 'Preferensi pengingat disimpan.');
    }

    public function integrations(Request $request): View
    {
        return view('settings.integrations', [
            'githubConfigured' => filled(config('services.github.client_id')),
            'aiConfigured' => filled(config('devflow_ai.api_key')),
            'preferences' => $request->user()->preferences ?? [],
        ]);
    }

    public function notificationCenter(Request $request): View
    {
        return view('settings.notifications', ['preferences' => $request->user()->preferences ?? []]);
    }
}
