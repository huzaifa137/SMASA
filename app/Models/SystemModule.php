<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $table = "system_modules";
    
    protected $fillable = ["key", "name", "icon", "route", "description", "sort_order", "is_active"];

    protected $casts = [
        "is_active" => "boolean"
    ];

    public function features()
    {
        return $this->hasMany(ModuleFeature::class, "module_id")->orderBy("sort_order");
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleModuleAccess::class, "module_id");
    }
    
    public function scopeActive($query)
    {
        return $query->where("is_active", true);
    }
}
