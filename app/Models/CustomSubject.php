<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_type',
        'subject_name',
        'subject_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'custom_subject_id');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeOfType($query, $classType)
    {
        return $query->where('class_type', $classType);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
