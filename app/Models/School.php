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