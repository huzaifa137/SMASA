<?php

namespace App\Audit;

use Illuminate\Support\Facades\DB;

/**
 * Works out WHO is acting.
 *
 * SMASA does not use Laravel's auth guards; identity lives in the session:
 *   LoggedAdmin   -> users.id           (platform / company administrator)
 *   LoggedTeacher -> teachers.id        (school staff, with LoggedSchool)
 *   LoggedStudent -> users.id           (student login)
 *   ParentId      -> parent_accounts.id (parent portal)
 *   LoggedSchool  -> schools.id         (tenant the session is working in)
 *
 * The result is a plain array so it can be snapshotted onto the audit row.
 */
class ActorResolver
{
    /** @var array<string, mixed> per-process memo so we don't re-query per entry */
    private array $memo = [];

    /**
     * @return array{type:string, id:?int, name:?string, role:?string, school_id:?int, school_name:?string}
     */
    public function resolve(): array
    {
        if (app()->runningInConsole() && !$this->hasSession()) {
            return $this->consoleActor();
        }

        $admin = $this->session('LoggedAdmin');
        $teacher = $this->session('LoggedTeacher');
        $student = $this->session('LoggedStudent');
        $parent = $this->session('ParentId');
        $schoolId = $this->session('LoggedSchool');

        $key = implode('|', [$admin, $teacher, $student, $parent, $schoolId]);
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        if ($admin) {
            $actor = $this->describeUser((int) $admin, 'admin');
        } elseif ($teacher) {
            $actor = $this->describeTeacher((int) $teacher);
        } elseif ($student) {
            $actor = $this->describeUser((int) $student, 'student');
        } elseif ($parent) {
            $actor = [
                'type' => 'parent',
                'id' => (int) $parent,
                'name' => 'Parent ' . $this->session('ParentPhone'),
                'role' => 'Parent / Guardian',
            ];
        } else {
            $actor = ['type' => 'guest', 'id' => null, 'name' => 'Guest', 'role' => null];
        }

        $actor['school_id'] = $schoolId ? (int) $schoolId : null;
        $actor['school_name'] = $schoolId ? $this->schoolName((int) $schoolId) : null;

        return $this->memo[$key] = $actor;
    }

    public function isAuthenticated(array $actor): bool
    {
        return !in_array($actor['type'], ['guest', 'system'], true);
    }

    public function schoolName(int $schoolId): ?string
    {
        $name = $this->session('LoggedSchoolName');
        if ($name && (int) $this->session('LoggedSchool') === $schoolId) {
            return (string) $name;
        }

        return $this->memo['school:' . $schoolId] ??= $this->safe(
            fn () => DB::table('schools')->where('id', $schoolId)->value('name')
        );
    }

    // ── internals ────────────────────────────────────────────────────────

    private function describeUser(int $id, string $type): array
    {
        return $this->memo["user:$id"] ??= (function () use ($id, $type) {
            $user = $this->safe(fn () => DB::table('users')->where('id', $id)->first());

            $name = null;
            $role = $type === 'admin' ? 'Administrator' : 'Student';

            if ($user) {
                $name = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''))
                    ?: ($user->name ?? null)
                    ?: ($user->username ?? null)
                    ?: ($user->email ?? null);

                if ($type === 'admin') {
                    if (!empty($user->is_super_admin)) {
                        $role = 'Super Administrator';
                    } elseif (!empty($user->admin_role_id)) {
                        $role = $this->safe(
                            fn () => DB::table('admin_roles')->where('id', $user->admin_role_id)->value('name')
                        ) ?: $role;
                    } elseif (!empty($user->attached_company_role)) {
                        $role = $this->safe(
                            fn () => DB::table('roles')->where('id', $user->attached_company_role)->value('name')
                        ) ?: $role;
                    }
                }
            }

            return ['type' => $type, 'id' => $id, 'name' => $name ?: "User #$id", 'role' => $role];
        })();
    }

    private function describeTeacher(int $id): array
    {
        return $this->memo["teacher:$id"] ??= (function () use ($id) {
            $t = $this->safe(fn () => DB::table('teachers')->where('id', $id)->first());

            $name = $t ? trim(($t->firstname ?? '') . ' ' . ($t->surname ?? '')) : null;
            $role = 'Teacher';

            if ($t && !empty($t->teacher_role)) {
                $role = $this->safe(
                    fn () => DB::table('roles')->where('id', $t->teacher_role)->value('name')
                ) ?: $role;
            }

            return ['type' => 'teacher', 'id' => $id, 'name' => $name ?: "Teacher #$id", 'role' => $role];
        })();
    }

    private function consoleActor(): array
    {
        $command = app(AuditContext::class)->command;

        return [
            'type' => 'system',
            'id' => null,
            'name' => $command ? "Console: $command" : 'Console',
            'role' => 'System',
            'school_id' => null,
            'school_name' => null,
        ];
    }

    private function hasSession(): bool
    {
        return AuditContext::sessionActive();
    }

    private function session(string $key): mixed
    {
        try {
            if (!app()->bound('session.store')) {
                return null;
            }

            // No isStarted() check on purpose - see AuditContext::sessionActive().
            return app('session.store')->get($key);
        } catch (\Throwable) {
            return null;
        }
    }

    private function safe(callable $fn): mixed
    {
        try {
            $context = app(AuditContext::class);
            $previous = $context->recording;
            $context->recording = true; // keep these lookups out of the write tally
            try {
                return $fn();
            } finally {
                $context->recording = $previous;
            }
        } catch (\Throwable) {
            return null;
        }
    }
}
