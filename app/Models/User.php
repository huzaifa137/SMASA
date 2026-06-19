<?php
namespace App\Models;

use App\Models\Role;
use App\Models\School;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;  // ← ADD THIS

class User extends Authenticatable
{
    use Notifiable, HasPushSubscriptions;  // ← ADD HasPushSubscriptions HERE

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'temp_otp',
        'user_role',
        'user_status',
        'procurement_approval_status',
        'firstname',
        'lastname',
        'gender',
        'phonenumber',
        'user_id',
        'account_status',
        'supervisor',
        'title',
        'user_supervisor',
        'user_title',
        'procurement_year',
        'user_reference',
        'user_last_active',
        'user_signature',
        'passport_number',
        'profile_id',
        'country',
        'is_active',
        'attached_company_role',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user_school')
            ->withPivot('school_id')->withTimestamps();
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'role_user_school')
            ->withPivot('role_id')->withTimestamps();
    }

    public function getRolesForSchool($schoolId)
    {
        return $this->roles()->wherePivot('school_id', $schoolId)->get();
    }

    public function hasPermission($permissionName, $schoolId)
    {
        return $this->roles()
            ->wherePivot('school_id', $schoolId)
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })->exists();
    }
}