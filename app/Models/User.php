<?php
namespace App\Models;

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
        'admin_role_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }
}
