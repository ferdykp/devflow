<?php

namespace App\Http\Controllers;

use App\Models\FocusItem;
use App\Models\Task;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkspaceMemberController extends Controller
{
    public function show(Request $request, int $workspace): View
    {
        $workspace = Workspace::query()->accessibleTo($request->user()->id)->findOrFail($workspace);
        $participants = $workspace->participants();
        $invitations = $workspace->user_id === $request->user()->id
            ? $workspace->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->latest()->get()
            : collect();

        return view('projects.team', compact('workspace', 'participants', 'invitations'));
    }

    public function store(Request $request, int $workspace): RedirectResponse
    {
        $workspace = Workspace::query()->where('user_id', $request->user()->id)->findOrFail($workspace);
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = Str::lower($data['email']);
        $token = Str::random(64);
        DB::transaction(function () use ($workspace, $email, $token): void {
            Workspace::query()->whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            if ($workspace->participants()->contains(fn ($user) => Str::lower($user->email) === $email)) {
                throw ValidationException::withMessages(['email' => 'Pengguna ini sudah menjadi anggota ruang kerja.']);
            }
            $workspace->invitations()->where('email', $email)->whereNull('accepted_at')->delete();
            $workspace->invitations()->create([
                'email' => $email, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7),
            ]);
        });

        return redirect()->route('workspaces.members.show', $workspace)
            ->with('invitation_url', route('invitations.show', $token))
            ->with('success', 'Undangan dibuat. Salin tautannya dan bagikan kepada calon anggota.');
    }

    public function invitation(Request $request, string $token): View|Response
    {
        $invitation = strlen($token) === 64
            ? WorkspaceInvitation::query()->where('token_hash', hash('sha256', $token))->first()
            : null;
        if ($invitation === null || $invitation->expires_at->isPast()) {
            return response()->view('projects.invitation-unavailable', [], $invitation === null ? 404 : 410);
        }
        $invitation->load('workspace');

        return view('projects.invitation', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findInvitation($token);
        DB::transaction(function () use ($invitation, $request): void {
            // Membership changes serialize on the workspace, including invite revocation.
            $workspace = Workspace::query()->whereKey($invitation->workspace_id)->lockForUpdate()->firstOrFail();
            $invitation = $workspace->invitations()->lockForUpdate()->findOrFail($invitation->id);
            abort_if($invitation->expires_at->isPast(), 410, 'Undangan sudah kedaluwarsa.');
            abort_unless(Str::lower($request->user()->email) === $invitation->email, 403, 'Gunakan akun dengan email yang diundang.');
            abort_if($invitation->accepted_at !== null && $workspace->user_id !== $request->user()->id
                && ! $workspace->members()->where('users.id', $request->user()->id)->exists(), 410, 'Undangan ini sudah digunakan. Minta undangan baru kepada pemilik ruang kerja.');
            if ($invitation->accepted_at === null) {
                if ($workspace->user_id !== $request->user()->id) {
                    $workspace->members()->syncWithoutDetaching([$request->user()->id]);
                }
                $invitation->update(['accepted_at' => now()]);
            }
        });

        return redirect()->route('workspaces.members.show', $invitation->workspace_id)->with('success', 'Kamu sudah bergabung dengan ruang kerja.');
    }

    public function destroy(Request $request, int $workspace, int $user): RedirectResponse
    {
        $workspace = Workspace::query()->accessibleTo($request->user()->id)->findOrFail($workspace);
        abort_unless($workspace->user_id === $request->user()->id || $user === $request->user()->id, 403);
        abort_if($workspace->user_id === $user, 422, 'Pemilik ruang kerja tidak dapat dikeluarkan.');
        DB::transaction(function () use ($workspace, $user): void {
            Workspace::query()->whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            $workspace->members()->findOrFail($user);
            $taskIds = Task::query()->whereHas('project', fn ($query) => $query->where('workspace_id', $workspace->id))->select('id');
            FocusItem::query()->where('user_id', $user)->whereIn('task_id', $taskIds)->delete();
            Task::query()->whereHas('project', fn ($query) => $query->where('workspace_id', $workspace->id))
                ->where('assignee_id', $user)->update(['assignee_id' => null]);
            $workspace->members()->detach($user);
        });

        return redirect()->route($user === $request->user()->id ? 'projects.index' : 'workspaces.members.show',
            $user === $request->user()->id ? [] : [$workspace])->with('success', 'Keanggotaan ruang kerja diperbarui.');
    }

    public function revoke(Request $request, int $workspace, int $invitation): RedirectResponse
    {
        $workspace = Workspace::query()->where('user_id', $request->user()->id)->findOrFail($workspace);
        DB::transaction(function () use ($workspace, $invitation): void {
            Workspace::query()->whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            $workspace->invitations()->whereNull('accepted_at')->findOrFail($invitation)->delete();
        });

        return back()->with('success', 'Undangan dibatalkan.');
    }

    private function findInvitation(string $token): WorkspaceInvitation
    {
        abort_unless(strlen($token) === 64, 404);
        $invitation = WorkspaceInvitation::query()->where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($invitation->expires_at->isPast(), 410, 'Undangan sudah kedaluwarsa. Minta undangan baru kepada pemilik ruang kerja.');

        return $invitation;
    }
}
