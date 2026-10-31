<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Public, rate-limited school lookup used by the login and forgot-password
 * pages. The school list is NOT rendered into the page: schools are only
 * returned when the visitor types at least MIN_CHARS characters, and at most
 * LIMIT matches come back.
 *
 * Returned "id" is houses.ID — exactly the value the old <select> posted as
 * school_id, so the login / reset back-end is unchanged.
 */
class SchoolSearchController extends Controller
{
    private const MIN_CHARS = 2;
    private const LIMIT = 8;

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < self::MIN_CHARS) {
            return response()->json(['data' => []]);
        }

        // Escape LIKE wildcards so "%" / "_" typed by a visitor are literal.
        $like = '%' . addcslashes($q, '\\%_') . '%';

        $rows = DB::table('houses as h')
            ->leftJoin('schools as s', 's.registration_code', '=', 'h.Number')
            ->leftJoin('school_profiles as p', 'p.school_id', '=', 's.id')
            // Same visibility rule the old dropdown used.
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('schools as live')
                    ->whereColumn('live.registration_code', 'h.Number')
                    ->where('live.school_status', 1);
            })
            ->where('h.House', 'like', $like)
            ->orderByRaw('CASE WHEN h.House LIKE ? THEN 0 ELSE 1 END', [addcslashes($q, '\\%_') . '%'])
            ->orderBy('h.House')
            ->limit(self::LIMIT)
            ->get(['h.ID as id', 'h.House as name', 'p.logo as logo']);

        $placeholder = asset('assets/images/brand/uplogolight.png');

        $data = $rows->map(function ($row) use ($placeholder) {
            return [
                'id'   => $row->id,
                'name' => $row->name,
                'logo' => $this->logoUrl($row->logo) ?: $placeholder,
            ];
        })->values();

        return response()->json(['data' => $data, 'placeholder' => $placeholder]);
    }

    /** Public URL of an uploaded logo, or null when none / file missing. */
    private function logoUrl(?string $logo): ?string
    {
        if (!$logo) {
            return null;
        }

        $name = ltrim($logo, '/');

        foreach (['uploads/logos/', 'uploads/school_logos/', '', 'storage/'] as $dir) {
            $relative = $dir . $name;
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }
}
