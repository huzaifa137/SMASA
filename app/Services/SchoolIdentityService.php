<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SchoolIdentityService
 * ─────────────────────────────────────────────────────────────────────────
 * A school's NAME lives in three tables, and different screens read
 * different ones:
 *
 *   schools.name (+ school_name_arabic)   receipts, finance PDFs, ID cards,
 *                                          report-card renderer, headers
 *   houses.House (+ House_AR)              the login dropdown, password
 *                                          reset, parent portal, super-admin
 *                                          "All Schools" list
 *   school_profiles.name                   Helper::schoolNameBySchoolID()
 *                                          (report cards, pass slips,
 *                                          exports) and the profile form
 *
 * The admin "Edit School" screen used to write schools only (its houses
 * update referenced columns that don't exist and the error was swallowed),
 * and the school portal's profile screen wrote school_profiles only — so
 * after a rename each screen showed a different name. Every rename now goes
 * through syncName(), which writes all three in one transaction.
 *
 * houses.Number == schools.registration_code is the link between the first
 * two tables. It is never changed here (school_passwords references it).
 */
class SchoolIdentityService
{
    /**
     * Writes $name (and, when given, the Arabic name) to every table that
     * stores it.
     *
     * @param string|null $arabic null = leave the Arabic name alone, '' = clear it
     * @return array<string,string> what was done per table
     */
    public function syncName(School $school, string $name, ?string $arabic = null): array
    {
        $name = trim($name);

        if ($name === '') {
            return [];
        }

        $done = [];

        DB::transaction(function () use ($school, $name, $arabic, &$done) {
            // 1. schools
            $schoolUpdate = ['name' => $name, 'updated_at' => now()];
            if ($arabic !== null) {
                $schoolUpdate['school_name_arabic'] = $arabic === '' ? null : $arabic;
            }
            DB::table('schools')->where('id', $school->id)->update($schoolUpdate);
            $done['schools'] = 'updated';

            // 2. houses (login dropdown etc.), matched on the registration code
            if (!empty($school->registration_code)) {
                $house = DB::table('houses')->where('Number', $school->registration_code)->first();

                if ($house) {
                    $houseUpdate = ['House' => $name];
                    if ($arabic !== null) {
                        $houseUpdate['House_AR'] = $arabic;
                    }
                    DB::table('houses')->where('Number', $school->registration_code)->update($houseUpdate);
                    $done['houses'] = 'updated';
                } else {
                    DB::table('houses')->insert([
                        'House'            => $name,
                        'House_AR'         => $arabic ?? '',
                        'Number'           => $school->registration_code,
                        'Location'         => 'Kampala',
                        'RegistrationDate' => now(),
                        'Head'             => 0,
                        'ContactPerson'    => 0,
                    ]);
                    $done['houses'] = 'created';
                }
            }

            // 3. school_profiles (only if the school already has one)
            if (Schema::hasTable('school_profiles')
                && DB::table('school_profiles')->where('school_id', $school->id)->exists()) {
                DB::table('school_profiles')
                    ->where('school_id', $school->id)
                    ->update(['name' => $name, 'updated_at' => now()]);
                $done['school_profiles'] = 'updated';
            }
        });

        return $done;
    }

    /**
     * Every school whose three copies of the name disagree, with the name
     * that should win: the school-profile name when it was saved at or after
     * the last change to schools, otherwise the schools name. (Whichever
     * screen was used last is the one the user meant.)
     *
     * @return array<int,array<string,mixed>>
     */
    public function findDrift(): array
    {
        $rows = [];

        $schools = DB::table('schools')->get();
        $houses = DB::table('houses')->get()->keyBy('Number');
        $profiles = DB::table('school_profiles')->get()->keyBy('school_id');

        foreach ($schools as $school) {
            $house = $houses->get($school->registration_code);
            $profile = $profiles->get($school->id);

            $schoolName = trim((string) $school->name);
            $houseName = $house ? trim((string) $house->House) : null;
            $profileName = $profile ? trim((string) $profile->name) : '';

            $profileWins = $profileName !== ''
                && $profile
                && strtotime((string) $profile->updated_at) >= strtotime((string) $school->updated_at);

            $canonical = $profileWins ? $profileName : $schoolName;

            $arabicSchool = trim((string) ($school->school_name_arabic ?? ''));
            $arabicHouse = $house ? trim((string) $house->House_AR) : '';
            $canonicalArabic = $arabicSchool !== '' ? $arabicSchool : $arabicHouse;

            $nameDrift = $schoolName !== $canonical
                || ($house && $houseName !== $canonical)
                || ($profileName !== '' && $profileName !== $canonical)
                || !$house;

            $arabicDrift = $house && $canonicalArabic !== '' && ($arabicSchool !== $canonicalArabic || $arabicHouse !== $canonicalArabic);

            if ($nameDrift || $arabicDrift) {
                $rows[] = [
                    'school_id'        => $school->id,
                    'schools.name'     => $schoolName,
                    'houses.House'     => $houseName ?? '(no house row)',
                    'profile.name'     => $profileName !== '' ? $profileName : '(none)',
                    'canonical'        => $canonical,
                    'canonical_arabic' => $canonicalArabic,
                ];
            }
        }

        return $rows;
    }
}
