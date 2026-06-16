<?php

namespace App\Http\Controllers;

use App\Models\SmasaNotification;
use App\Models\SmasaNotificationRecipient;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // ─── Admin Panel ────────────────────────────────────────────────

    /**
     * Notification dashboard — list all sent notifications for this school.
     */
    public function index()
    {
        $school_id = session('LoggedSchool');

        $notifications = SmasaNotification::where('school_id', $school_id)
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total' => SmasaNotification::where('school_id', $school_id)->count(),
            'today' => SmasaNotification::where('school_id', $school_id)
                ->whereDate('created_at', today())->count(),
            'unread' => SmasaNotificationRecipient::where('school_id', $school_id)
                ->where('is_read', false)->count(),
            'byType' => SmasaNotification::where('school_id', $school_id)
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')->pluck('total', 'type'),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Show the compose/send form.
     */
    public function create()
    {
        $school_id = session('LoggedSchool');
        $types = SmasaNotification::typeConfig();

        // Recipient group options
        $teachers = DB::table('teachers')->where('school_id', $school_id)
            ->selectRaw("id, TRIM(CONCAT(firstname, ' ', surname)) as name")
            ->orderBy('firstname')->get();

        $students = DB::table('students')->where('school_id', $school_id)
            ->selectRaw("id, TRIM(CONCAT(firstname, ' ', lastname)) as name")
            ->orderBy('firstname')->get();

        $admins = DB::table('users')->where('user_role', 'admin')
            ->select('id', 'name')->get();

        return view('notifications.create', compact('types', 'teachers', 'students', 'admins'));
    }

    /**
     * Send a manually composed notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string',
            'recipient_group' => 'required|string',
            'recipient_ids' => 'nullable|array',
            'url' => 'nullable|url|max:500',
        ]);

        $school_id = session('LoggedSchool');
        $admin_id = session('LoggedAdmin') ?? session('LoggedTeacher');
        $group = $request->recipient_group;

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'url' => $request->url,
            'module' => 'manual',
            'school_id' => $school_id,
            'triggered_by' => $admin_id,
        ];

        switch ($group) {
            case 'all':
                NotificationService::sendToEveryone($data, $school_id);
                break;

            case 'all_teachers':
                NotificationService::sendToAllTeachers($data, $school_id);
                break;

            case 'all_students':
                NotificationService::sendToAllStudents($data, $school_id);
                break;

            case 'all_admins':
                NotificationService::sendToAllAdmins($data, $school_id);
                break;

            case 'specific_teachers':
                $recipients = collect($request->recipient_ids ?? [])
                    ->map(fn($id) => ['type' => 'teacher', 'id' => $id, 'school_id' => $school_id])
                    ->toArray();
                NotificationService::send($data, $recipients);
                break;

            case 'specific_students':
                $recipients = collect($request->recipient_ids ?? [])
                    ->map(fn($id) => ['type' => 'student', 'id' => $id, 'school_id' => $school_id])
                    ->toArray();
                NotificationService::send($data, $recipients);
                break;
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification sent successfully!');
    }

    /**
     * View a notification detail + delivery stats.
     */
    public function show(int $id)
    {
        $school_id = session('LoggedSchool');
        $notification = SmasaNotification::where('school_id', $school_id)->findOrFail($id);

        $deliveryStats = [
            'total' => $notification->recipients()->count(),
            'read' => $notification->recipients()->where('is_read', true)->count(),
            'unread' => $notification->recipients()->where('is_read', false)->count(),
        ];

        $recipients = $notification->recipients()
            ->orderBy('is_read')
            ->paginate(20);

        return view('notifications.show', compact('notification', 'deliveryStats', 'recipients'));
    }

    /**
     * Delete a notification.
     */
    public function destroy(int $id)
    {
        $school_id = session('LoggedSchool');
        SmasaNotification::where('school_id', $school_id)->findOrFail($id)->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted.');
    }

    // ─── Recipient-facing (Admin viewing their own notifications) ───

    /**
     * My notifications page (for the logged-in admin).
     */
    public function myNotifications()
    {
        $adminId = session('LoggedAdmin') ?? session('LoggedTeacher');
        $items = NotificationService::forRecipient('admin', $adminId, 20);
        $unreadCount = NotificationService::unreadCount('admin', $adminId);

        return view('notifications.my_notifications', compact('items', 'unreadCount'));
    }

    // ─── AJAX Endpoints ─────────────────────────────────────────────

    /**
     * GET /notifications/unread-count  (AJAX - for bell badge)
     */
    public function unreadCount()
    {
        $adminId = session('LoggedAdmin') ?? session('LoggedTeacher');
        $count = NotificationService::unreadCount('admin', $adminId);

        return response()->json(['count' => $count]);
    }

    /**
     * GET /notifications/dropdown  (AJAX - for bell dropdown preview)
     */
    public function dropdown()
    {
        $adminId = session('LoggedAdmin') ?? session('LoggedTeacher');

        $items = SmasaNotificationRecipient::with('notification')
            ->where('recipient_type', 'admin')
            ->where('recipient_id', $adminId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'notifications' => $items->map(fn($item) => [
                'id' => $item->notification->id,
                'title' => $item->notification->title,
                'body' => \Str::limit($item->notification->body, 80),
                'type' => $item->notification->type,
                'icon' => $item->notification->icon,
                'color' => $item->notification->color,
                'url' => $item->notification->url,
                'is_read' => $item->is_read,
                'time' => $item->notification->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * POST /notifications/{id}/read  (AJAX - mark one as read)
     */
    public function markRead(int $id)
    {
        $adminId = session('LoggedAdmin') ?? session('LoggedTeacher');
        NotificationService::markRead('admin', $adminId, $id);

        return response()->json(['success' => true]);
    }

    /**
     * POST /notifications/read-all  (AJAX - mark all as read)
     */
    public function markAllRead()
    {
        $adminId = session('LoggedAdmin') ?? session('LoggedTeacher');
        NotificationService::markAllRead('admin', $adminId);

        return response()->json(['success' => true]);
    }
}