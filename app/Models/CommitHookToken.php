<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitHookToken extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'token_prefix', 'encrypted_token', 'last_used_at'];

    protected $hidden = ['token_hash', 'encrypted_token'];

    protected function casts(): array
    {
        return [
            'encrypted_token' => 'encrypted',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
