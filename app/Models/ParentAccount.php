<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * A parent/guardian's login identity, keyed by phone number rather than
 * a school_id — the same number can belong to a parent with children at
 * more than one school, and they should only ever need one account.
 *
 * "Children" is not a real Eloquent relationship because students aren't
 * linked to a parent by foreign key anywhere in this schema — the only
 * link that exists is Student.primary_contact matching this phone number.
 * That's why children() below is a query, not a hasMany().
 */
class ParentAccount extends Model
{
    protected $fillable = [
        'phone',
        'password',
        'must_change_password',
        'last_login_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /** The default password issued the first time a parent logs in. */
    public const DEFAULT_PASSWORD = '1234';

    public function children()
    {
        return Student::where('primary_contact', $this->phone)->get();
    }

    public function checkPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }

    public static function findOrProvisionByPhone(string $phone): ?self
    {
        $account = static::where('phone', $phone)->first();
        if ($account) {
            return $account;
        }

        // Only auto-create an account if that phone number is actually on
        // file as a student's primary contact — otherwise anyone could
        // "log in" with a made-up number and get a fresh blank account.
        $hasChild = Student::where('primary_contact', $phone)->exists();
        if (!$hasChild) {
            return null;
        }

        return static::create([
            'phone' => $phone,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'must_change_password' => true,
        ]);
    }
}
