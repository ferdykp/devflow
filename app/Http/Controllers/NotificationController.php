<?php

namespace App\Http\Controllers;

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

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function dismiss(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->delete();

        return back()->with('success', 'Notifikasi dihapus dari pusat notifikasi.');
    }
}
