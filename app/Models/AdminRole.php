<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A role for restricted Admin Portal users. Super administrators are not a
 * role — they are users with users.is_super_admin = true.
 */
class AdminRole extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    /** @return string[] permission keys granted by this role */
    public function permissionKeys(): array
    {
        return DB::table('admin_role_permissions')
            ->where('admin_role_id', $this->id)
            ->pluck('permission')
            ->all();
    }

    /**
     * Replace this role's permissions with $keys (already validated/filtered).
     */
    public function syncPermissions(array $keys): void
    {
        $keys = array_values(array_unique($keys));
        $now = now();

        DB::transaction(function () use ($keys, $now) {
            DB::table('admin_role_permissions')
                ->where('admin_role_id', $this->id)
                ->whereNotIn('permission', $keys)
                ->delete();

            $existing = DB::table('admin_role_permissions')
                ->where('admin_role_id', $this->id)
                ->pluck('permission')
                ->all();

            $rows = [];
            foreach (array_diff($keys, $existing) as $key) {
                $rows[] = [
                    'admin_role_id' => $this->id,
                    'permission' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('admin_role_permissions')->insert($rows);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
