<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'school_type',
        'email',
        'gender',
        'regional_level',
        'school_ownership',
        'boarding_status',
        'name',
        'school_product',
        'registration_code',
        'phone',
        'population',
        'added_by',
        'date_added',
        'school_name_arabic',
        'custom_subjects_enabled',
        'custom_subjects_active',
    ];

    protected $casts = [
        'custom_subjects_enabled' => 'boolean',
        'custom_subjects_active' => 'boolean',
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function customSubjects()
    {
        return $this->hasMany(CustomSubject::class);
    }

    /**
     * ── School Products (merge/split) ───────────────────────────────────
     * A school can now be enrolled under more than one School Product
     * category at once (a "merge"). schools.school_product is kept as the
     * primary/legacy value - see SchoolProductMergeService for how the two
     * stay in sync.
     */
    public function schoolProducts()
    {
        return $this->hasMany(SchoolProduct::class);
    }

    /**
     * The MasterData rows (SCHOOL_PRODUCTS) this school currently belongs
     * to. Empty collection only happens for a school with no product set
     * at all, which shouldn't occur since school_product is required at
     * creation.
     */
    public function products()
    {
        return $this->belongsToMany(
            MasterData::class,
            'school_products',
            'school_id',
            'product_md_id',
            'id',
            'md_id'
        )->withPivot(['is_primary', 'added_by'])->withTimestamps();
    }

    public function productMdIds(): array
    {
        $ids = $this->schoolProducts()->pluck('product_md_id')->all();

        // Defensive fallback for a school whose school_products rows
        // haven't been backfilled yet (shouldn't happen after the
        // migration runs, but keeps this safe either way).
        if (empty($ids) && !empty($this->school_product)) {
            return [(int) $this->school_product];
        }

        return array_map('intval', $ids);
    }

    public function hasMergedProducts(): bool
    {
        return $this->schoolProducts()->count() > 1;
    }

    public function primaryProduct()
    {
        return $this->schoolProducts()->where('is_primary', true)->first()
            ?? $this->schoolProducts()->first();
    }

    /**
     * True once this school is fully switched over to defining its own
     * subject names. False (the default) means it keeps using the shared
     * master subject list exactly as before.
     */
    public function usesCustomSubjects(): bool
    {
        return (bool) $this->custom_subjects_active;
    }

    public function schoolRoles()
    {
        return $this->hasMany(SchoolRole::class, 'school_id');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'school_id');
    }

}