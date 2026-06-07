<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModuleAccess extends Model
{
    protected $table = "role_module_access";
    
    protected $fillable = ["school_role_id", "module_id", "can_access"];

    protected $casts = [
        "can_access" => "boolean"
    ];

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }

    public function module()
    {
        return $this->belongsTo(SystemModule::class, "module_id");
    }
}
