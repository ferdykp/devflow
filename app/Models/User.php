<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'timezone', 'preferences', 'google_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function joinedWorkspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'user_workspace')->withTimestamps();
    }

    public function personalTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'owner_id')->whereNull('project_id');
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class, 'owner_id');
    }

    public function personalGoals(): HasMany
    {
        return $this->hasMany(PersonalGoal::class, 'owner_id');
    }

    public function personalNotes(): HasMany
    {
        return $this->hasMany(PersonalNote::class, 'owner_id');
    }

    public function learningPaths(): HasMany
    {
        return $this->hasMany(LearningPath::class, 'owner_id');
    }

    public function learningSessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'owner_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'owner_id');
    }

    public function weeklyReviews(): HasMany
    {
        return $this->hasMany(WeeklyReview::class, 'owner_id');
    }

    public function dailyReflections(): HasMany
    {
        return $this->hasMany(DailyReflection::class, 'owner_id');
    }

    public function coachRecommendations(): HasMany
    {
        return $this->hasMany(CoachRecommendation::class, 'owner_id');
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }
}
