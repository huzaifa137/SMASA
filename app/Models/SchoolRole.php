<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolRole extends Model
{
    protected $table = "school_roles";
    
    protected $fillable = ["school_id", "name", "description", "is_active"];

    protected $casts = [
        "is_active" => "boolean"
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function moduleAccess()
    {
        return $this->hasMany(RoleModuleAccess::class, "school_role_id");
    }

    public function featureAccess()
    {
        return $this->hasMany(RoleFeatureAccess::class, "school_role_id");
    }

    public function teachers()
    {
        return $this->hasMany(TeacherSchoolRole::class, "school_role_id");
    }

    /**
     * Check if this role has access to a module
     */
    public function canAccessModule($moduleKey): bool
    {
        return $this->moduleAccess()
            ->whereHas("module", fn($q) => $q->where("key", $moduleKey))
            ->where("can_access", true)
            ->exists();
    }

    /**
     * Check if this role has access to a specific feature
     */
    public function canAccessFeature($featureKey): bool
    {
        return $this->featureAccess()
            ->whereHas("feature", fn($q) => $q->where("key", $featureKey))
            ->where("can_access", true)
            ->exists();
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
