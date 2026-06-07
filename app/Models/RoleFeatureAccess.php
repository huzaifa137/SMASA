<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleFeatureAccess extends Model
{
    protected $table = "role_feature_access";
    
    protected $fillable = ["school_role_id", "feature_id", "can_access"];

    protected $casts = [
        "can_access" => "boolean"
    ];

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }

    public function feature()
    {
        return $this->belongsTo(ModuleFeature::class, "feature_id");
    }
}
