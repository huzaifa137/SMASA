<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleFeature extends Model
{
    protected $table = "module_features";
    
    protected $fillable = ["module_id", "key", "name", "description", "sort_order"];

    public function module()
    {
        return $this->belongsTo(SystemModule::class, "module_id");
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleFeatureAccess::class, "feature_id");
    }
}
