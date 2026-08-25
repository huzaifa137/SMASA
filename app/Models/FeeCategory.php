<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeeCategory extends Model
{
    protected $fillable = [
        'school_id', 'name', 'slug', 'color', 'icon',
        'description', 'is_external', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function structureItems()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    public function paymentItems()
    {
        return $this->hasMany(FeePaymentItem::class);
    }

    /**
     * The categories every school starts with out of the box. Schools can
     * rename, deactivate, or add their own on top of these — this list only
     * seeds a school's very first visit to fee-structures/payments.
     */
    public static function defaults(): array
    {
        return [
            ['name' => 'Tuition',   'icon' => 'fa-chalkboard-teacher', 'color' => '#2f2ccb', 'is_external' => false],
            ['name' => 'Boarding',  'icon' => 'fa-bed',                'color' => '#7c3aed', 'is_external' => false],
            ['name' => 'Activity',  'icon' => 'fa-futbol',             'color' => '#059669', 'is_external' => false],
            ['name' => 'Library',   'icon' => 'fa-book',               'color' => '#0891b2', 'is_external' => false],
            ['name' => 'Sports',    'icon' => 'fa-running',            'color' => '#d97706', 'is_external' => false],
            ['name' => 'Medical',   'icon' => 'fa-briefcase-medical',  'color' => '#dc2626', 'is_external' => false],
            ['name' => 'Exams',     'icon' => 'fa-file-signature',     'color' => '#4338ca', 'is_external' => false],
            ['name' => 'Tour',      'icon' => 'fa-bus',                'color' => '#0d9488', 'is_external' => false],
            ['name' => 'Uniform',   'icon' => 'fa-tshirt',             'color' => '#9333ea', 'is_external' => false],
            ['name' => 'Transport', 'icon' => 'fa-shuttle-van',        'color' => '#65a30d', 'is_external' => false],
            ['name' => 'Damages / Incidentals', 'icon' => 'fa-tools',  'color' => '#b45309', 'is_external' => true],
            ['name' => 'Other',     'icon' => 'fa-ellipsis-h',         'color' => '#64748b', 'is_external' => false],
        ];
    }

    /**
     * Active categories for a school, seeding the defaults on first use so
     * every school has a sensible starting set they can then customise.
     */
    public static function forSchool(int $schoolId)
    {
        $exists = self::where('school_id', $schoolId)->exists();

        if (!$exists) {
            foreach (self::defaults() as $i => $cat) {
                self::create(array_merge($cat, [
                    'school_id'  => $schoolId,
                    'slug'       => Str::slug($cat['name']),
                    'sort_order' => $i,
                ]));
            }
        }

        return self::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected static function booted()
    {
        static::saving(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
