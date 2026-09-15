<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知を新しい順に一覧表示する。
     */
    public function index(): View
    {
        $notifications = Auth::user()->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にする。所有者本人でなければ403。
     */
    public function read(string $notification): RedirectResponse
    {
        $target = DatabaseNotification::findOrFail($notification);

        if ((int) $target->notifiable_id !== Auth::id()) {
            abort(403);
        }

        $target->markAsRead();

        return back();
    }
}
