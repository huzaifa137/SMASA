<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

/**
 * Type-ahead school search for platform-admin screens.
 *
 * With thousands of schools a dropdown must never be pre-filled with all of
 * them. Screens ask this endpoint for one small page of matches instead.
 * Route: admin.schools.lookup (AdminAuth middleware, JSON only).
 */
class SchoolLookupController extends Controller
{
    private const PER_PAGE = 20;

    public function search(Request $request)
    {
        // AdminAuth also lets school staff in, and this lists EVERY school's
        // name and registration code, so it is for platform admins only.
        abort_unless(session()->has('LoggedAdmin'), 403);

        $term = trim((string) $request->query('q', ''));
        $term = mb_substr($term, 0, 80);
        $page = max(1, (int) $request->query('page', 1));

        $rows = School::query()
            ->select('id', 'name', 'registration_code')
            ->when($term !== '', function ($q) use ($term) {
                // Escape LIKE wildcards typed by the user.
                $like = '%' . addcslashes($term, '\\%_') . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                        ->orWhere('registration_code', 'like', $like);
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->forPage($page, self::PER_PAGE + 1)   // one extra row tells us if there is a next page
            ->get();

        $more = $rows->count() > self::PER_PAGE;

        return response()->json([
            'results' => $rows->take(self::PER_PAGE)->map(fn($s) => [
                'id' => $s->id,
                'text' => $s->name,
                'code' => $s->registration_code,
            ])->values(),
            'more' => $more,
        ]);
    }
}
