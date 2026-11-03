<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\NotificationRoutingRule;
use App\Models\SchoolRole;
use App\Services\NotificationService;
use App\Support\NotificationEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Notifications → Who Gets Notified.
 *
 * A school decides, for every automatic notification (fee payment received,
 * absentees recorded, library book issued…), whether it goes to the built-in
 * default, to chosen roles and/or chosen people, or to nobody.
 */
class NotificationRoutingController extends Controller
{
    public function index()
    {
        PermissionHelper::denyUnlessFeature('manage_notification_routing');

        $schoolId = $this->schoolId();
        $events = NotificationEvents::all();

        $rules = NotificationRoutingRule::where('school_id', $schoolId)->get()->keyBy('event_key');

        $roles = SchoolRole::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Staff, with their role, and whether their role lets them open the
        // notification bell at all (a chosen person who can't open it would
        // otherwise be notified "into the void").
        $canOpenBell = NotificationService::schoolStaffIds($schoolId, 'view_my_notifications')->flip();

        $staff = DB::table('teachers')
            ->leftJoin('teacher_school_roles as tsr', function ($j) use ($schoolId) {
                $j->on('tsr.teacher_id', '=', 'teachers.id')->where('tsr.school_id', $schoolId);
            })
            ->leftJoin('school_roles as sr', 'sr.id', '=', 'tsr.school_role_id')
            ->where('teachers.school_id', $schoolId)
            ->where(function ($w) {
                $w->whereNull('teachers.account_status')
                    ->orWhereNotIn('teachers.account_status', ['blocked', 'suspended']);
            })
            ->orderBy('teachers.firstname')
            ->get([
                'teachers.id',
                DB::raw("TRIM(CONCAT(COALESCE(teachers.firstname,''), ' ', COALESCE(teachers.surname,''))) as name"),
                'sr.name as role_name',
            ])
            ->map(function ($t) use ($canOpenBell) {
                $t->can_open_bell = $canOpenBell->has((int) $t->id);
                return $t;
            });

        // "Who would get it today" - computed from what is saved, shown beside each event.
        $reach = [];
        $names = $staff->pluck('name', 'id');
        foreach ($events as $key => $event) {
            if ($event['audience'] !== 'staff') {
                continue;
            }
            $rule = $rules->get($key);
            if ($rule && $rule->mode === NotificationRoutingRule::MODE_DISABLED) {
                $reach[$key] = ['count' => 0, 'names' => []];
                continue;
            }
            $ids = NotificationService::staffIdsForEvent($event, $rule, $schoolId);
            $reach[$key] = [
                'count' => $ids->count(),
                'names' => $ids->map(fn($id) => $names[$id] ?? ('#' . $id))->take(8)->values()->all(),
            ];
        }

        $schoolHasRoles = $roles->isNotEmpty();

        return view('notifications.routing', compact('events', 'rules', 'roles', 'staff', 'reach', 'schoolHasRoles'));
    }

    public function update(Request $request)
    {
        if (!PermissionHelper::canFeature('manage_notification_routing')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to change who gets notified.');
        }

        $schoolId = $this->schoolId();
        $events = NotificationEvents::all();
        $input = (array) $request->input('rules', []);

        $validRoleIds = SchoolRole::where('school_id', $schoolId)->pluck('id')->map(fn($i) => (int) $i)->all();
        $validTeacherIds = DB::table('teachers')->where('school_id', $schoolId)->pluck('id')->map(fn($i) => (int) $i)->all();

        foreach ($events as $key => $event) {
            $row = (array) ($input[$key] ?? []);
            $mode = $row['mode'] ?? NotificationRoutingRule::MODE_DEFAULT;

            if (!in_array($mode, [NotificationRoutingRule::MODE_DEFAULT, NotificationRoutingRule::MODE_CUSTOM, NotificationRoutingRule::MODE_DISABLED], true)) {
                $mode = NotificationRoutingRule::MODE_DEFAULT;
            }
            // Student-facing events can only be switched on or off.
            if ($event['audience'] !== 'staff' && $mode === NotificationRoutingRule::MODE_CUSTOM) {
                $mode = NotificationRoutingRule::MODE_DEFAULT;
            }

            $roleIds = array_values(array_intersect(array_map('intval', (array) ($row['role_ids'] ?? [])), $validRoleIds));
            $teacherIds = array_values(array_intersect(array_map('intval', (array) ($row['teacher_ids'] ?? [])), $validTeacherIds));

            if ($mode === NotificationRoutingRule::MODE_CUSTOM && empty($roleIds) && empty($teacherIds)) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "“{$event['label']}” is set to Custom but no role or person is selected. Choose at least one, or switch it to Default / Off."
                );
            }
        }

        DB::transaction(function () use ($events, $input, $schoolId, $validRoleIds, $validTeacherIds) {
            foreach ($events as $key => $event) {
                $row = (array) ($input[$key] ?? []);
                $mode = $row['mode'] ?? NotificationRoutingRule::MODE_DEFAULT;

                if ($mode === NotificationRoutingRule::MODE_DEFAULT
                    || !in_array($mode, [NotificationRoutingRule::MODE_CUSTOM, NotificationRoutingRule::MODE_DISABLED], true)
                    || ($event['audience'] !== 'staff' && $mode === NotificationRoutingRule::MODE_CUSTOM)) {
                    NotificationRoutingRule::where('school_id', $schoolId)->where('event_key', $key)->delete();
                    continue;
                }

                $custom = $mode === NotificationRoutingRule::MODE_CUSTOM;

                NotificationRoutingRule::updateOrCreate(
                    ['school_id' => $schoolId, 'event_key' => $key],
                    [
                        'mode' => $mode,
                        'role_ids' => $custom ? array_values(array_intersect(array_map('intval', (array) ($row['role_ids'] ?? [])), $validRoleIds)) : null,
                        'teacher_ids' => $custom ? array_values(array_intersect(array_map('intval', (array) ($row['teacher_ids'] ?? [])), $validTeacherIds)) : null,
                        'updated_by' => session('LoggedTeacher') ?? session('LoggedAdmin'),
                    ]
                );
            }
        });

        return redirect()->route('notifications.routing')->with('success', 'Notification recipients saved.');
    }

    private function schoolId(): int
    {
        $id = session('LoggedSchool');
        abort_unless($id, 403, 'Open a school first.');

        return (int) $id;
    }
}
