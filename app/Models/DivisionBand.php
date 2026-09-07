<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionBand extends Model
{
    use HasFactory;

    protected $table = 'division_bands';

    protected $fillable = [
        'grading_scheme_id',
        'min_aggregate',
        'max_aggregate',
        'division',
        'remark',
        'sort_order',
    ];

    protected $casts = [
        'min_aggregate' => 'integer',
        'max_aggregate' => 'integer',
        'sort_order'    => 'integer',
    ];

    public function scheme()
    {
        return $this->belongsTo(GradingScheme::class, 'grading_scheme_id');
    }

    /**
     * The standard P.7 PLE aggregate points used by GradingSchemeDefaults'
     * "UCE Standard (100 Marks)" style scheme: D1..F9 mapped to points 1-9.
     * Single source of truth — used both to detect whether a scheme is
     * "PLE-shaped" and to seed its Division Bands, so the seeder, the
     * create-scheme flow, and the `grading:division-bands` command can
     * never drift out of sync with each other.
     */
    public const PLE_POINTS = ['D1' => 1, 'D2' => 2, 'C3' => 3, 'C4' => 4, 'C5' => 5, 'C6' => 6, 'P7' => 7, 'P8' => 8, 'F9' => 9];

    /** The standard Division 1-4 / Ungraded bands for a PLE-shaped scheme. */
    public const STANDARD_BANDS = [
        [4, 12, 'Division 1', 'First Grade'],
        [13, 23, 'Division 2', 'Second Grade'],
        [24, 29, 'Division 3', 'Third Grade'],
        [30, 34, 'Division 4', 'Fourth Grade'],
        [35, 36, 'Ungraded (U)', 'Ungraded'],
    ];

    /**
     * Does this set of grade bands match the standard 9-point PLE shape
     * (D1, D2, C3, C4, C5, C6, P7, P8, F9 with points 1-9 respectively)?
     *
     * @param \Illuminate\Support\Collection $bands the scheme's GradingScale rows
     */
    public static function isPleShaped($bands): bool
    {
        $bandsByGrade = collect($bands)->keyBy(fn ($b) => strtoupper(trim($b->grade)));

        return collect(self::PLE_POINTS)->every(
            fn ($points, $grade) => isset($bandsByGrade[$grade]) && (int) $bandsByGrade[$grade]->points === $points
        );
    }

    /**
     * Attach the standard Division 1-4 / Ungraded bands to a scheme and
     * flip on `ungraded_on_fail`. Caller is responsible for confirming the
     * scheme doesn't already have division bands and (if desired) that it
     * is PLE-shaped — this method just writes the standard rows.
     */
    public static function seedStandardBandsFor(GradingScheme $scheme): void
    {
        foreach (self::STANDARD_BANDS as $i => $band) {
            self::create([
                'grading_scheme_id' => $scheme->id,
                'min_aggregate' => $band[0],
                'max_aggregate' => $band[1],
                'division' => $band[2],
                'remark' => $band[3],
                'sort_order' => $i,
            ]);
        }
        $scheme->update(['ungraded_on_fail' => true]);
    }
}