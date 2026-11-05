<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\SmasaNotification;
use App\Models\SmasaNotificationRecipient;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    // ─── Admin Panel ────────────────────────────────────────────────

    /**
     * Notification dashboard — list all sent notifications for this school.
     */
    public function index()
    {
        PermissionHelper::denyUnlessFeature('view_notifications');

        $school_id = session('LoggedSchool');

        $notifications = SmasaNotification::where('school_id', $school_id)
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total' => SmasaNotification::where('school_id', $school_id)->count(),
            'today' => SmasaNotification::where('school_id', $school_id)
                ->where('created_at', '>=', today())->where('created_at', '<', today()->addDay())->count(),
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
        PermissionHelper::denyUnlessFeature('create_notification');

        $school_id = session('LoggedSchool');
        $types = SmasaNotification::typeConfig();

        // Specific recipients are searched on demand (see recipientSearch()),
        // not pre-loaded: a school can have thousands of students.
        return view('notifications.create', compact('types'));
    }

    /**
     * Send a manually composed notification.
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canFeature('send_notification')) {
            return redirect()->route('notifications.index')
                ->with('error', 'Unauthorized. You do not have permission to send notifications.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => ['required', 'string', Rule::in(array_keys(SmasaNotification::typeConfig()))],
            'recipient_group' => ['required', 'string', Rule::in([
                'all', 'all_teachers', 'all_students', 'all_admins', 'all_staff',
                'specific_teachers', 'specific_students',
            ])],
            'recipient_ids' => 'nullable|array|max:500',
            'recipient_ids.*' => 'integer',
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
            case 'all_staff':
                NotificationService::sendToSchoolStaff($data, $school_id);
                break;

            case 'specific_teachers':
                // Only ids that really belong to THIS school are accepted.
                $recipients = $this->ownIds(Teacher::class, $request->recipient_ids, $school_id)
                    ->map(fn($id) => ['type' => 'teacher', 'id' => $id, 'school_id' => $school_id])
                    ->toArray();
                NotificationService::send($data, $recipients);
                break;

            case 'specific_students':
                $recipients = $this->ownIds(Student::class, $request->recipient_ids, $school_id)
                    ->map(fn($id) => ['type' => 'student', 'id' => $id, 'school_id' => $school_id])
                    ->toArray();
                NotificationService::send($data, $recipients);
                break;
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification sent successfully!');
    }

    /** Keep only the given ids that belong to $schoolId (one query). */
    private function ownIds(string $model, $ids, $schoolId)
    {
        $ids = collect($ids ?? [])->map(fn($i) => (int) $i)->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $model::query()
            ->where('school_id', $schoolId)
            ->whereIn('id', $ids)
            ->pluck('id');
    }

    /**
     * AJAX: search-as-you-type recipients for the "specific teachers / students"
     * pickers. Returns at most 30 matches of the current school.
     */
    public function recipientSearch(Request $request)
    {
        PermissionHelper::denyUnlessFeature('create_notification');

        $schoolId = session('LoggedSchool');
        abort_unless($schoolId, 403);

        $type = $request->query('type');
        abort_unless(in_array($type, ['teacher', 'student'], true), 422);

        $term = mb_substr(trim((string) $request->query('q', '')), 0, 60);
        $like = '%' . addcslashes($term, '\\%_') . '%';
        $limit = 30;

        if ($type === 'teacher') {
            $rows = Teacher::query()
                ->where('school_id', $schoolId)
                ->when($term !== '', fn($q) => $q->whereRaw("CONCAT_WS(' ', firstname, surname) LIKE ?", [$like]))
                ->orderBy('firstname')->orderBy('id')
                ->limit($limit + 1)
                ->get(['id', 'firstname', 'surname']);

            $items = $rows->map(fn($t) => [
                'id' => $t->id,
                'name' => trim($t->firstname . ' ' . $t->surname),
                'detail' => '',
            ]);
        } else {
            $rows = Student::query()
                ->where('school_id', $schoolId)
                ->when($term !== '', function ($q) use ($like) {
                    $q->where(function ($w) use ($like) {
                        $w->whereRaw("CONCAT_WS(' ', firstname, lastname) LIKE ?", [$like])
                            ->orWhere('admission_number', 'like', $like);
                    });
                })
                ->orderBy('firstname')->orderBy('id')
                ->limit($limit + 1)
                ->get(['id', 'firstname', 'lastname', 'admission_number']);

            $items = $rows->map(fn($s) => [
                'id' => $s->id,
                'name' => trim($s->firstname . ' ' . $s->lastname),
                'detail' => (string) $s->admission_number,
            ]);
        }

        return response()->json([
            'results' => $items->take($limit)->values(),
            'more' => $items->count() > $limit,
        ]);
    }

    /**
     * View a notification detail + delivery stats.
     */
    public function show(int $id)
    {
        PermissionHelper::denyUnlessFeature('view_notifications');

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
        if (!PermissionHelper::canFeature('delete_notification')) {
            return redirect()->route('notifications.index')
                ->with('error', 'Unauthorized. You do not have permission to delete notifications.');
        }

        $school_id = session('LoggedSchool');
        SmasaNotification::where('school_id', $school_id)->findOrFail($id)->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted.');
    }


    /**
     * Who is reading the bell: [recipient_type, recipient_id, school_id].
     * School staff are 'teacher' recipients of THEIR school only; platform
     * admins (users table) are 'admin'. Teacher ids and user ids are different
     * id spaces and must never be mixed under one type.
     */
    private function recipientIdentity(): ?array
    {
        $schoolId = session('LoggedSchool') ? (int) session('LoggedSchool') : null;

        if ($adminId = session('LoggedAdmin')) {
            return ['admin', (int) $adminId, null];
        }

        if (($teacherId = session('LoggedTeacher')) && $schoolId) {
            return ['teacher', (int) $teacherId, $schoolId];
        }

        return null;
    }

    // ─── Recipient-facing (Admin viewing their own notifications) ───

    /**
     * My notifications page (for the logged-in admin).
     */
    public function myNotifications()
    {
        PermissionHelper::denyUnlessFeature('view_my_notifications');

        abort_unless($me = $this->recipientIdentity(), 403);
        [$type, $id, $schoolId] = $me;
        $items = NotificationService::forRecipient($type, $id, 20, $schoolId);
        $unreadCount = NotificationService::unreadCount($type, $id, $schoolId);

        return view('notifications.my_notifications', compact('items', 'unreadCount'));
    }

    // ─── AJAX Endpoints ─────────────────────────────────────────────

    /**
     * GET /notifications/unread-count  (AJAX - for bell badge)
     */
    public function unreadCount()
    {
        if (!PermissionHelper::canFeature('view_my_notifications')) {
            return response()->json(['count' => 0]);
        }

        if (!$me = $this->recipientIdentity()) {
            return response()->json(['count' => 0]);
        }
        $count = NotificationService::unreadCount($me[0], $me[1], $me[2]);

        return response()->json(['count' => $count]);
    }

    /**
     * GET /notifications/dropdown  (AJAX - for bell dropdown preview)
     */
    public function dropdown()
    {
        if (!PermissionHelper::canFeature('view_my_notifications')) {
            return response()->json(['notifications' => []]);
        }

        if (!$me = $this->recipientIdentity()) {
            return response()->json(['notifications' => []]);
        }
        [$type, $id, $schoolId] = $me;

        $items = SmasaNotificationRecipient::with('notification')
            ->where('recipient_type', $type)
            ->where('recipient_id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->filter(fn($item) => $item->notification);

        return response()->json([
            'notifications' => $items->values()->map(fn($item) => [
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
        if (!PermissionHelper::canFeature('mark_notification_read')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (!$me = $this->recipientIdentity()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        NotificationService::markRead($me[0], $me[1], $id, $me[2]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /notifications/read-all  (AJAX - mark all as read)
     */
    public function markAllRead()
    {
        if (!PermissionHelper::canFeature('mark_all_read')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (!$me = $this->recipientIdentity()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        NotificationService::markAllRead($me[0], $me[1], $me[2]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /notifications/push/subscribe
     * Called by the JS service worker registration snippet.
     */
    public function savePushSubscription(Request $request)
    {
        if (!PermissionHelper::canFeature('push_notifications')) {
            return response()->json([
                'error' => 'Unauthorized. You do not have permission to enable push notifications.'
            ], 403);
        }

        Log::info('Push subscription request received', [
            'admin_session'   => session()->has('LoggedAdmin'),
            'teacher_session' => session()->has('LoggedTeacher'),
            'endpoint'        => filled($request->endpoint),
            'public_key'      => filled($request->key),
            'auth_token'      => filled($request->token),
        ]);

        if (!filled($request->endpoint)) {
            return response()->json(['error' => 'Missing push endpoint from the browser.'], 422);
        }

        $subscriber = $this->resolvePushSubscriber();

        if (!$subscriber) {
            Log::warning('Push subscription failed: unauthenticated user');

            return response()->json([
                'error' => 'Unauthenticated user.',
            ], 401);
        }

        try {
            $subscriber->updatePushSubscription(
                endpoint: $request->endpoint,
                key: $request->key,
                token: $request->token,
                contentEncoding: $request->contentEncoding ?? 'aesgcm'
            );

            Log::info('Push subscription saved successfully', [
                'model' => class_basename($subscriber),
                'id'    => $subscriber->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription saved successfully.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to save push subscription', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to save push subscription.',
            ], 500);
        }
    }

    /**
     * POST /notifications/push/unsubscribe
     * Removes a push subscription (browser-side unsubscribe already done by caller).
     */
    public function deletePushSubscription(Request $request)
    {
        if (!PermissionHelper::canFeature('push_notifications')) {
            return response()->json([
                'error' => 'Unauthorized. You do not have permission to change push notifications.'
            ], 403);
        }

        $subscriber = $this->resolvePushSubscriber();

        if (!$subscriber) {
            return response()->json(['error' => 'Unauthenticated user.'], 401);
        }

        if (!$request->endpoint) {
            return response()->json(['error' => 'Missing endpoint.'], 422);
        }

        try {
            $subscriber->deletePushSubscription($request->endpoint);

            Log::info('Push subscription removed', [
                'model' => class_basename($subscriber),
                'id'    => $subscriber->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription removed.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to delete push subscription', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to remove push subscription.',
            ], 500);
        }
    }

    /**
     * POST /notifications/push/status
     * Lets the ON/OFF button check whether THIS browser's subscription is
     * actually stored on the server (browser and server can drift apart).
     */
    public function pushStatus(Request $request)
    {
        $subscriber = $this->resolvePushSubscriber();

        if (!$subscriber) {
            return response()->json(['error' => 'Unauthenticated user.'], 401);
        }

        $registered = filled($request->endpoint)
            && $subscriber->pushSubscriptions()->where('endpoint', $request->endpoint)->exists();

        return response()->json([
            'registered' => $registered,
            'devices' => $subscriber->pushSubscriptions()->count(),
        ]);
    }

    /**
     * Resolve the currently authenticated push notification subscriber.
     */
    private function resolvePushSubscriber()
    {
        if ($adminId = session('LoggedAdmin')) {
            return User::find($adminId);
        }

        if ($teacherId = session('LoggedTeacher')) {
            return Teacher::find($teacherId);
        }

        return null;
    }
}