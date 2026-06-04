<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->paginate(25);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $since = $request->input('since');

        $query = $user->notifications()->latest();

        if ($since) {
            $query->where('created_at', '>', $since);
        } else {
            $afterId = $request->input('after_id');
            if ($afterId) {
                $anchor = DatabaseNotification::query()->find($afterId);
                if ($anchor) {
                    $query->where('created_at', '>', $anchor->created_at);
                }
            } else {
                $query->limit(30);
            }
        }

        $items = $query->get()->map(fn (DatabaseNotification $n) => $this->format($n));

        return response()->json([
            'notifications' => $items,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'ok' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'unread_count' => 0]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    private function format(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'alert',
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'url' => $data['url'] ?? route('dashboard'),
            'icon' => $data['icon'] ?? 'zmdi-notifications',
            'open_tab' => $data['open_tab'] ?? null,
            'mfg_stage' => $data['mfg_stage'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
            'created_human' => $notification->created_at->diffForHumans(),
        ];
    }
}
