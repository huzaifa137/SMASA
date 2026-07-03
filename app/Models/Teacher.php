<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Teacher extends Model
{
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'school_id',
        'surname',
        'firstname',
        'email',
        'password',
        'othername',
        'initials',
        'phonenumber',
        'registration_number',
        'gender',
        'national_id',
        'address',
        'employee_number',
        'group_teacher',
        'teacher_profile',
        'teacher_role',
        'account_status',
        'status_reason',
        'status_changed_at',
        'status_changed_by',
        'must_change_password',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function salaryStructure()
    {
        return $this->hasOne(TeacherSalaryStructure::class, 'teacher_id');
    }

    public function schoolRoleAssignment()
    {
        return $this->hasOne(TeacherSchoolRole::class, 'teacher_id');
    }
}