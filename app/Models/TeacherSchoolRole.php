<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSchoolRole extends Model
{
    protected $table = "teacher_school_roles";
    
    protected $fillable = ["teacher_id", "school_id", "school_role_id"];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolRole()
    {
        return $this->belongsTo(SchoolRole::class, "school_role_id");
    }
}
