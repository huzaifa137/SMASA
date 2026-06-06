<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySubject extends Model
{
    protected $table = "library_subjects";
    
    protected $fillable = [
        "school_id", "name", "description", "is_active"
    ];
    
    protected $casts = [
        "is_active" => "boolean"
    ];
    
    public function books()
    {
        return $this->hasMany(LibraryBook::class, "subject_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where("school_id", $schoolId);
    }
}
