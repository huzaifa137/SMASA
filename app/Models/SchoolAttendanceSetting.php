<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Per-school gate hours for Card Scan. Every school can define its own
 * "late after" and "closing" times; schools that never opened the settings
 * page keep working with DEFAULTS.
 */
class SchoolAttendanceSetting extends Model
{
    public const DEFAULTS = [
        'student_late_time' => '08:00:00',
        'teacher_late_time' => '08:00:00',
        'grace_minutes' => 0,
        'student_closing_time' => '16:00:00',
        'teacher_closing_time' => '16:00:00',
        'min_minutes_before_signout' => 10,
        'notify_parent_on_arrival' => true,
        'notify_parent_on_signout' => true,
    ];

    protected $fillable = [
        'school_id',
        'student_late_time',
        'teacher_late_time',
        'grace_minutes',
        'student_closing_time',
        'teacher_closing_time',
        'min_minutes_before_signout',
        'notify_parent_on_arrival',
        'notify_parent_on_signout',
    ];

    protected $casts = [
        'grace_minutes' => 'integer',
        'min_minutes_before_signout' => 'integer',
        'notify_parent_on_arrival' => 'boolean',
        'notify_parent_on_signout' => 'boolean',
    ];

    /**
     * The school's settings, or an unsaved instance holding the defaults.
     * Never throws: if the table is missing (migration not run yet) scanning
     * must keep working with the defaults.
     */
    public static function forSchool($schoolId): self
    {
        try {
            return static::firstOrNew(['school_id' => $schoolId], self::DEFAULTS);
        } catch (QueryException $e) {
            return new static(array_merge(self::DEFAULTS, ['school_id' => $schoolId]));
        }
    }

    // ── helpers ────────────────────────────────────────────────────

    public function lateTimeFor(string $personType): string
    {
        return $this->normalise(
            $personType === 'teacher' ? $this->teacher_late_time : $this->student_late_time,
            self::DEFAULTS['student_late_time']
        );
    }

    public function closingTimeFor(string $personType): string
    {
        return $this->normalise(
            $personType === 'teacher' ? $this->teacher_closing_time : $this->student_closing_time,
            self::DEFAULTS['student_closing_time']
        );
    }

    /**
     * Decide whether an arrival at $now is early / on time / late.
     * "Late" means strictly after (late_time + grace), same as the old 08:00 rule.
     * Minutes in the label are measured from the school's late time itself, so
     * with a 5 min grace an 08:06 arrival at an 08:00 school reads "Late by 6 min".
     */
    public function arrivalVerdict(string $personType, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $lateTime = $this->lateTimeFor($personType);
        $base = Carbon::today()->setTimeFromTimeString($lateTime);
        $cutoff = Carbon::today()->setTimeFromTimeString($lateTime)->addMinutes((int) $this->grace_minutes);

        $result = ['cutoff' => $cutoff->format('H:i:s')];

        // Strictly after the cutoff (late time + grace) → late
        if ($now->timestamp > $cutoff->timestamp) {
            $mins = max(1, intdiv($now->timestamp - $base->timestamp, 60));
            return $result + [
                'status' => 'late',
                'punctuality' => 'late',
                'minutes' => $mins,
                'label' => 'Late by ' . self::humanMinutes($mins),
            ];
        }

        // After the late time but still inside the grace period
        if ($now->timestamp > $base->timestamp) {
            return $result + [
                'status' => 'present',
                'punctuality' => 'on_time',
                'minutes' => 0,
                'label' => 'On time (within grace period)',
            ];
        }

        $mins = intdiv($base->timestamp - $now->timestamp, 60);
        return $result + [
            'status' => 'present',
            'punctuality' => $mins >= 1 ? 'early' : 'on_time',
            'minutes' => $mins,
            'label' => $mins >= 1 ? 'Early by ' . self::humanMinutes($mins) : 'On time',
        ];
    }

    /** Is a sign-out at $now before the school's closing time? */
    public function departureVerdict(string $personType, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $closing = Carbon::today()->setTimeFromTimeString($this->closingTimeFor($personType));

        $secs = $closing->timestamp - $now->timestamp;

        if ($secs > 0) {
            $mins = max(1, intdiv($secs, 60));
            return [
                'status' => 'early',
                'minutes' => $mins,
                'label' => 'Left early – ' . self::humanMinutes($mins) . ' before closing',
                'closing' => $closing->format('H:i:s'),
            ];
        }

        return [
            'status' => 'on_time',
            'minutes' => 0,
            'label' => 'Signed out at/after closing time',
            'closing' => $closing->format('H:i:s'),
        ];
    }

    public static function humanMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $m ? "{$h} h {$m} min" : "{$h} h";
    }

    /** Accept "08:00" or "08:00:00" (or null) and always return "H:i:s". */
    private function normalise($value, string $fallback): string
    {
        $value = $value ?: $fallback;
        return strlen($value) === 5 ? $value . ':00' : $value;
    }
}
