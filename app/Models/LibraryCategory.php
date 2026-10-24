<?php

namespace App\Models;


use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class LibraryCategory extends Model
{
    use BelongsToSchool;

    protected $table = "library_categories";
    
    protected $fillable = [
        "school_id", "name", "slug", "description", "color", "is_active"
    ];
    
    protected $casts = [
        "is_active" => "boolean"
    ];
    
    public function books()
    {
        return $this->hasMany(LibraryBook::class, "category_id");
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
