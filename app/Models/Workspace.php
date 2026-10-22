<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_workspace')->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    #[Scope]
    protected function accessibleTo(Builder $query, int $userId): void
    {
        $query->where(fn (Builder $query) => $query->where('user_id', $userId)
            ->orWhereHas('members', fn (Builder $members) => $members->where('users.id', $userId)));
    }

    /** @return Collection<int, User> */
    public function participants(): Collection
    {
        $this->loadMissing(['user', 'members']);

        return (new Collection($this->members->all()))->push($this->user)->unique('id')->sortBy('name')->values();
    }
}
