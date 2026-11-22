<?php

use App\Http\Controllers\CommitMessageController;
use App\Http\Controllers\GitHubWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/github', GitHubWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('github.webhook');

Route::post('/commit-message', CommitMessageController::class)
    ->middleware(['throttle:commit-hook', 'auth.commit-hook'])
    ->name('commit-message.suggest');
