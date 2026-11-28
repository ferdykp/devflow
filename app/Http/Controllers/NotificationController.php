<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();
        $destination = data_get($item->data, 'url');

        $isRelativePath = is_string($destination)
            && str_starts_with($destination, '/')
            && ! str_starts_with($destination, '//')
            && ! str_starts_with($destination, '/\\')
            && parse_url($destination, PHP_URL_HOST) === null;

        return redirect($isRelativePath ? $destination : route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function dismiss(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $request->user()->notifications()->findOrFail($notification)->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Notifikasi dihapus.']);
        }

        return back()->with('success', 'Notifikasi dihapus dari pusat notifikasi.');
    }
}
