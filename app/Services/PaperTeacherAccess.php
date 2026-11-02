<?php

namespace App\Services;

use App\Models\ClassSubjectPaper;
use Illuminate\Support\Facades\DB;

/**
 * WHO may enter marks for WHICH paper of a class-stream subject.
 *
 * The rule (kept deliberately simple):
 *
 *  - A subject whose papers have NO paper teacher assigned is "unrestricted":
 *    exactly as before, its subject teachers (subject_teacher_1 / _2) enter
 *    marks for every paper.
 *
 *  - As soon as ANY paper of the subject has a paper teacher, the subject is
 *    "restricted":
 *        paper WITH paper teacher(s)  -> only those teachers may enter it
 *        paper WITHOUT paper teachers -> the subject teachers may enter it
 *    So a school can give only the awkward paper (say the Practical) to a
 *    different teacher and leave P1/P2 with the subject teacher.
 *
 *  - Admins who manage Exam Subjects are never restricted.
 *
 * Paper numbers here are the same numbers used by the examination papers
 * (P1, P2 ...), which is how a standard paper meets an examination paper.
 */
class PaperTeacherAccess
{
    /**
     * Constrain a class_subjects query to the rows $teacherId may work on:
     * their subject-teacher rows PLUS rows where they are a paper teacher.
     * Replaces the old `subject_teacher_1 = ? OR subject_teacher_2 = ?` check.
     */
    public static function scopeTeacherSubjects($query, $teacherId, string $cs = 'class_subjects')
    {
        return $query->where(function ($q) use ($teacherId, $cs) {
            $q->where("{$cs}.subject_teacher_1", $teacherId)
                ->orWhere("{$cs}.subject_teacher_2", $teacherId)
                ->orWhereExists(function ($sub) use ($teacherId, $cs) {
                    $sub->select(DB::raw(1))
                        ->from('class_subject_papers as csp')
                        ->whereColumn('csp.school_id', "{$cs}.school_id")
                        ->whereColumn('csp.class_id', "{$cs}.class_id")
                        ->whereColumn('csp.stream_id', "{$cs}.stream_id")
                        ->where(function ($m) use ($cs) {
                            $m->whereColumn('csp.subject_id', "{$cs}.subject_id")
                                ->orWhere(function ($c) use ($cs) {
                                    $c->whereNull('csp.subject_id')
                                        ->whereColumn('csp.custom_subject_id', "{$cs}.custom_subject_id")
                                        ->where(function ($z) use ($cs) {
                                            $z->whereNull("{$cs}.subject_id")->orWhere("{$cs}.subject_id", 0);
                                        });
                                });
                        })
                        ->where(function ($t) use ($teacherId) {
                            $t->where('csp.teacher_1_id', $teacherId)
                                ->orWhere('csp.teacher_2_id', $teacherId);
                        });
                });
        });
    }

    /**
     * The access picture for ONE class-stream subject and ONE acting teacher.
     *
     * @param object $classSubject row with school_id, class_id, stream_id, subject_id, custom_subject_id, subject_teacher_1/2
     * @return object {restricted, isSubjectTeacher, isAdmin, teacherId, assigned: [paper_number => [teacher ids]], labels: [paper_number => label]}
     */
    public static function forActor(object $classSubject, $teacherId, bool $isAdmin = false): object
    {
        $assigned = [];
        $labels = [];

        $rows = ClassSubjectPaper::with('paper')
            ->where('school_id', $classSubject->school_id)
            ->where('class_id', $classSubject->class_id)
            ->where('stream_id', (string) $classSubject->stream_id)
            ->when(!empty($classSubject->subject_id), function ($q) use ($classSubject) {
                $q->where('subject_id', $classSubject->subject_id);
            }, function ($q) use ($classSubject) {
                $q->whereNull('subject_id')->where('custom_subject_id', $classSubject->custom_subject_id ?? 0);
            })
            ->get();

        foreach ($rows as $row) {
            if (!$row->paper) {
                continue;
            }

            $n = (int) $row->paper->paper_number;
            $labels[$n] = $row->paper->display_label;

            $ids = array_map('intval', $row->teacherIds());
            if ($ids) {
                $assigned[$n] = $ids;
            }
        }

        $isSubjectTeacher = $teacherId
            && in_array((string) $teacherId, [(string) ($classSubject->subject_teacher_1 ?? ''), (string) ($classSubject->subject_teacher_2 ?? '')], true);

        return (object) [
            'restricted' => !empty($assigned),
            'isSubjectTeacher' => (bool) $isSubjectTeacher,
            'isAdmin' => $isAdmin,
            'teacherId' => $teacherId ? (int) $teacherId : null,
            'assigned' => $assigned,
            'labels' => $labels,
        ];
    }

    /** May the actor enter marks for / set the maximum of this paper number? */
    public static function canEdit(object $access, int $paperNumber): bool
    {
        if ($access->isAdmin || !$access->restricted) {
            return true;
        }

        if (isset($access->assigned[$paperNumber])) {
            return $access->teacherId !== null && in_array($access->teacherId, $access->assigned[$paperNumber], true);
        }

        // Paper nobody was specifically given: the subject teachers' paper.
        return $access->isSubjectTeacher;
    }

    /**
     * True when the actor may edit this paper ONLY because they are an
     * administrator: the paper was given to other teachers and the actor is
     * not one of them. Administrators keep that right (they must be able to
     * cover for an absent teacher) but the screens lock these papers by default
     * and ask them to unlock on purpose, and the change is audited.
     */
    public static function isAdminOverride(object $access, int $paperNumber): bool
    {
        if (!$access->isAdmin || empty($access->assigned[$paperNumber])) {
            return false;
        }

        return $access->teacherId === null || !in_array($access->teacherId, $access->assigned[$paperNumber], true);
    }

    /** Is the actor named as a paper teacher on at least one paper of the subject? */
    public static function isPaperTeacher(object $access): bool
    {
        if ($access->teacherId === null) {
            return false;
        }

        foreach ($access->assigned as $ids) {
            if (in_array($access->teacherId, $ids, true)) {
                return true;
            }
        }

        return false;
    }

    /** May the actor enter ONE combined score (a subject with no papers set for the exam)? */
    public static function canEnterSingleScore(object $access): bool
    {
        return $access->isAdmin || !$access->restricted || $access->isSubjectTeacher;
    }

    /** Teacher names for a paper, e.g. "Okello James, Nabirye Sarah" (null when nobody specific). */
    public static function namesFor(object $access, int $paperNumber): ?string
    {
        if (empty($access->assigned[$paperNumber])) {
            return null;
        }

        $names = DB::table('teachers')
            ->whereIn('id', $access->assigned[$paperNumber])
            ->get(['surname', 'firstname'])
            ->map(fn($t) => trim(($t->surname ?? '') . ' ' . ($t->firstname ?? '')))
            ->filter()
            ->implode(', ');

        return $names !== '' ? $names : null;
    }
}
