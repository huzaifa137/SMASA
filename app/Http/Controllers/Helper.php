<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentBasic;
use App\Models\TermDate;
use App\Models\Examination;
use App\Models\ExaminationClass;
use App\Models\ExaminationMark;
use App\Models\User;
use DB;
use App\Models\Teacher;
use Session;
use App\Models\Stream;


class Helper extends Controller
{

    // TechSateAdmins access ability (URPF)

    public static function isAdminAllowed()
    {
        // Helper::isAdminAllowed()
        // if (!Helper::isAdminAllowed())

        $adminId = session('LoggedAdmin');

        $admin = null;

        if ($adminId) {
            $admin = User::find($adminId);
        }

        return (
            data_get($admin, 'attached_company_role') == self::getcompanyAdministratorsId()
        );
    }

    public static function isLoggedAdmin()
    {
        // Helper::isLoggedAdmin()
        // if (!Helper::isLoggedAdmin())

        return session()->has('LoggedAdmin');
    }

    // TechSateAdmins and School Admins Teacher access ability (URPF)
    public static function isTechSateAdminOrSchoolAdminsAlone()
    {
        // Helper::isTechSateAdminOrSchoolAdminsAlone()
        // if (!Helper::isTechSateAdminOrSchoolAdminsAlone())

        $teacherId = session('LoggedTeacher');
        $adminId = session('LoggedAdmin');

        $teacher = null;
        $admin = null;

        if ($teacherId) {
            $teacher = Teacher::find($teacherId);
        }

        if ($adminId) {
            $admin = User::find($adminId);
        }

        return (
            data_get($teacher, 'teacher_role') == self::getcschoolAdministratorsId() ||
            data_get($admin, 'attached_company_role') == self::getcompanyAdministratorsId()
        );
    }

    // TechSateAdmins and School Admins and Sales Representatives Teacher access ability (URPF)
    public static function isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives()
    {
        // Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives()
        // if (!Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives())
        //  abort(
        //         403,
        //         'Unauthorized Access. Contact TechSate Software Company Limited.'
        //     );

        $teacherId = session('LoggedTeacher');
        $adminId = session('LoggedAdmin');

        $teacher = null;
        $admin = null;

        if ($teacherId) {
            $teacher = Teacher::find($teacherId);
        }

        if ($adminId) {
            $admin = User::find($adminId);
        }

        return (
            data_get($teacher, 'teacher_role') == self::getcschoolAdministratorsId() || data_get($admin, 'attached_company_role') == self::getcompanySalesRepresentativesId() ||
            data_get($admin, 'attached_company_role') == self::getcompanyAdministratorsId()
        );
    }

    // Assigned Teacher access ability (URPF)
    public static function isAssignedClassTeacher($classId, $streamId)
    {
        // Helper::isTechSateAdminOrSchoolAdminsAlone($senior, $stream)
        // if (!Helper::isTechSateAdminOrSchoolAdminsAlone())
        return (
            self::isAdminAllowed() ||
            self::canClassTeacherAssignedManageStudents($classId, $streamId)
        );
    }

    public static function canClassTeacherAssignedManageStudents($classId, $streamId)
    {
        $teacherId = session('LoggedTeacher');

        if (!$teacherId) {
            return false;
        }

        $stream = Stream::where('class_id', $classId)
            ->where('stream_id', $streamId)
            ->first();

        if (!$stream) {
            return false;
        }

        return $stream->class_teacher == $teacherId;
    }

    public static function authorizeAdminsOnly()
    {
        if (!self::isAdminAllowed()) {

            abort(
                403,
                'Unauthorized Access. Contact TechSate Software Company Limited.'
            );
        }
    }

    public static function authorizeTechSateAdminOrSchoolAdmins()
    {
        if (!self::isTechSateAdminOrSchoolAdminsAlone()) {

            abort(
                403,
                'Unauthorized Access. Contact TechSate Software Company Limited.'
            );
        }
    }

    public static function authorizeAssignedClassTeacher($classId, $streamId)
    {
        if (!self::isAssignedClassTeacher($classId, $streamId)) {

            abort(
                403,
                'You are not allowed to manage this class stream. Contact TechSate Software Company Limited.'
            );
        }
    }


    public static function getcompanyAdministratorsId()
    {
        return DB::table('roles')
            ->where('name', 'Administrators')
            ->where('scope', 'system')
            ->value('id');
    }

    public static function getcompanySalesRepresentativesId()
    {
        return DB::table('roles')
            ->where('name', 'Sales Representative')
            ->where('scope', 'system')
            ->value('id');
    }

    public static function getcschoolAdministratorsId()
    {
        return DB::table('roles')
            ->where('name', 'School Administrators')
            ->where('scope', 'school')
            ->value('id');
    }

    public static function getnormalSchoolTeachers()
    {
        return DB::table('roles')
            ->where('name', 'School Teachers')
            ->where('scope', 'school')
            ->value('id');
    }

    public static function loggedTeacherId()
    {
        return session('LoggedTeacher');
    }

    public static function maleClassStudents($classId)
    {
        $maleClassStudents = DB::table('students')
            ->where('school_id', Session('LoggedSchool'))
            ->where('senior', $classId)
            ->where('gender', 'Male')
            ->count();

        return $maleClassStudents;
    }

    public static function femaleClassStudents($classId)
    {
        $femaleClassStudents = DB::table('students')
            ->where('school_id', Session('LoggedSchool'))
            ->where('senior', $classId)
            ->where('gender', 'Female')
            ->count();

        return $femaleClassStudents;
    }

    public static function totalClassStudent($classId)
    {

        $totalClassStudent = self::femaleClassStudents($classId) + self::maleClassStudents($classId);

        return $totalClassStudent;
    }


    public static function maleClassStreamStudents($classId, $stream_id)
    {
        $maleClassStudents = DB::table('students')
            ->where('senior', $classId)
            ->where('gender', 'Male')
            ->where('stream', $stream_id)
            ->count();

        return $maleClassStudents;
    }

    public static function femaleClassStreamStudents($classId, $stream_id)
    {
        $femaleClassStudents = DB::table('students')
            ->where('senior', $classId)
            ->where('gender', 'Female')
            ->where('stream', $stream_id)
            ->count();

        return $femaleClassStudents;
    }

    public static function totalClassStreamStudent($classId, $stream_id)
    {

        $totalClassStreamStudents = self::maleClassStreamStudents($classId, $stream_id) + self::femaleClassStreamStudents($classId, $stream_id);

        return $totalClassStreamStudents;
    }

    public static function schoolName($school_id)
    {
        $schoolName = DB::table('houses')
            ->where('Number', $school_id)
            ->value('House');

        return $schoolName;
    }

    public static function requireSchool()
    {
        if (!Session::has('LoggedSchool')) {
            redirect()->route('school.dashboard')->send();
            exit;
        }

        return Session::get('LoggedSchool');
    }

    public static function schoolProducts()
    {
        if (!Session::has('LoggedSchool')) {
            redirect()->route('school.dashboard')->send();
            exit;
        }

        $school_product = DB::table('schools')->where('id', Session('LoggedSchool'))->value('school_product');

        return $school_product;
    }

    public static function schoolIDFromHouseRegistrationCode($house_id)
    {
        // using registration code
        $schoolID = DB::table('schools')
            ->where('registration_code', $house_id)
            ->value('id');

        return $schoolID;
    }

    public static function schoolIDFromHouseID($house_id)
    {
        $Number = DB::table('houses')
            ->where('ID', $house_id)
            ->value('Number');

        $schoolID = DB::table('schools')
            ->where('registration_code', $Number)
            ->value('id');

        return $schoolID;
    }

    public static function houseIdFromSchoolId($school_id)
    {
        $registrationCode = DB::table('schools')
            ->where('id', $school_id)
            ->value('registration_code');

        $schoolID = DB::table('houses')
            ->where('Number', $registrationCode)
            ->value('id');

        return $schoolID;
    }

    public static function schoolNameByHouseID($house_id)
    {
        $schoolName = DB::table('houses')
            ->where('ID', $house_id)
            ->value('House');

        return $schoolName;
    }

    public static function schoolNameBySchoolID($school_id)
    {
        $schoolName = DB::table('schools')
            ->where('id', $school_id)
            ->value('name');

        return $schoolName;
    }

    public static function schoolPhoneBySchoolID($school_id)
    {
        $schoolPhone = DB::table('schools')
            ->where('id', $school_id)
            ->value('phone');

        return $schoolPhone;
    }

    public static function schoolNumber($house_id)
    {
        $Number = DB::table('houses')
            ->where('ID', $house_id)
            ->value('Number');

        return $Number;
    }

    public static function ar_schoolName($school_id)
    {
        $schoolName = DB::table('houses')
            ->where('id', $school_id)
            ->value('House');

        return $schoolName;
    }

    public static function activeIndividualLoggedIn()
    {
        if (!Session('LoggedSchool')) {
            $passwordStatus = User::where('id', Session::get('LoggedStudent'))->first();

            return $passwordStatus;
        }

        return false;
    }

    public static function subjectName($subject_id)
    {
        $schoolName = DB::table('houses')
            ->where('Number', $subject_id)
            ->value('House');

        return $schoolName;
    }

    public static function user_id()
    {
        return $user = Session::get('LoggedAdmin');
    }

    public static function logged_admin_user()
    {
        if (Session::has('LoggedAdmin')) {
            return User::where('id', Session::get('LoggedAdmin'))
                ->value('name');
        }

        if (Session::has('LoggedStudent')) {
            return User::where('id', Session::get('LoggedStudent'))
                ->value('name');
        }

        return 'Guest';
    }

    public static function student_username($user = '')
    {
        $user = (int) $user;

        return DB::table('users')
            ->where('id', $user)
            ->where('user_role', 1)
            ->value('id');
    }

    public static function get_teacher_name($teacher_id)
    {
        $teacher_id = (int) $teacher_id;

        return DB::table('teachers')
            ->where('id', $teacher_id)
            ->value('firstname') ?? 'No Record Found';
    }

    /**
     * Full "Surname Firstname" for a teacher, used on report cards.
     * Returns null (not a placeholder string) when there's no teacher,
     * so callers can decide their own fallback (e.g. '—').
     */
    public static function teacherFullName($teacherId): ?string
    {
        if (empty($teacherId)) {
            return null;
        }

        $teacher = DB::table('teachers')->where('id', $teacherId)->first();

        if (!$teacher) {
            return null;
        }

        return trim(($teacher->surname ?? '') . ' ' . ($teacher->firstname ?? '')) ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Early Years Grading Helpers (Nursery / Kindergarten / Pre-Primary)
    |--------------------------------------------------------------------------
    */

    /**
     * Master-code IDs that represent early-years categories
     * (NURSERY_BABY_CLASS, NURSERY_MIDDLE_CLASS, NURSERY_TOP_CLASS).
     */
    public static function earlyYearsMasterCodes(): array
    {
        return config('constants.early_years.master_codes', [35, 36, 37]);
    }

    /**
     * The 3 system comment presets: marks (1-3), label, remark.
     */
    public static function earlyYearsPresets(): array
    {
        return config('constants.early_years.presets', []);
    }

    public static function earlyYearsMaxMark(): int
    {
        return (int) config('constants.early_years.max_mark', 3);
    }

    /**
     * True if a given subject (master_datas.md_id) belongs to one of the
     * early-years categories, meaning it's graded 1-3 with system
     * comments rather than a numeric mark out of the exam's total_marks.
     */
    public static function isEarlyYearsSubject($subjectId): bool
    {
        if (empty($subjectId)) {
            return false;
        }

        $masterCodeId = DB::table('master_datas')
            ->where('md_id', (string) $subjectId)
            ->value('md_master_code_id');

        return in_array((int) $masterCodeId, self::earlyYearsMasterCodes(), true);
    }

    /**
     * Find the preset (marks/label/remark) matching a given 1-3 mark.
     */
    public static function earlyYearsPresetForMark($marks): ?array
    {
        foreach (self::earlyYearsPresets() as $preset) {
            if ((int) $preset['marks'] === (int) $marks) {
                return $preset;
            }
        }

        return null;
    }

    /**
     * Overall remark for an early-years subject average (0-3 scale).
     * Rounds to the nearest preset (1/2/3) rather than requiring an
     * exact match, since the average of several subjects rarely lands
     * on a whole number.
     */
    public static function earlyYearsRemarkForAverage(float $average): string
    {
        $presets = self::earlyYearsPresets();
        if (empty($presets)) {
            return '—';
        }

        $nearestMarks = max(1, min(3, (int) round($average)));

        foreach ($presets as $preset) {
            if ((int) $preset['marks'] === $nearestMarks) {
                return $preset['remark'];
            }
        }

        return $presets[array_key_last($presets)]['remark'];
    }

    /*
    |--------------------------------------------------------------------------
    | Passlip Customisation Persistence (per class)
    |--------------------------------------------------------------------------
    | Lets one or more classes (e.g. Baby Class + Middle Class, or a whole
    | Nursery section) share a saved show/hide profile for their report
    | cards, instead of it resetting to defaults every time the page reloads.
    */

    /**
     * Saved toggle/accent settings for a class, or [] if none saved yet
     * (callers should merge this with hard defaults).
     */
    public static function getPassslipSettings($schoolId, $classId): array
    {
        if (empty($classId) || empty($schoolId)) {
            return [];
        }

        $row = DB::table('passslip_settings')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->first();

        if (!$row) {
            return [];
        }

        $decoded = json_decode($row->settings, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Save the same settings JSON against one or more classes.
     * Idempotent — safe to call repeatedly (updateOrInsert per class).
     */
    public static function savePassslipSettings($schoolId, array $classIds, array $settings): void
    {
        foreach ($classIds as $classId) {
            if (empty($classId)) {
                continue;
            }

            DB::table('passslip_settings')->updateOrInsert(
                ['school_id' => $schoolId, 'class_id' => $classId],
                ['settings' => json_encode($settings), 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public static function category_name($user = '')
    {
        $user = (int) $user;
        $admin = DB::table('users')->where('id', '=', $user)->where('user_role', '!=', 1)->first();

        return $user = @$admin->firstname . ' ' . @$admin->lastname;
    }

    public static function language_name($user = '')
    {
        $user = (int) $user;
        $admin = DB::table('users')->where('id', '=', $user)->where('user_role', '!=', 1)->first();

        return $user = @$admin->firstname . ' ' . @$admin->lastname;
    }

    public static function active_user()
    {

        $admin = DB::table('users')->where('id', '=', Session('LoggedAdmin'))->first();

        return $user = @$admin->firstname . ' ' . @$admin->lastname;
    }

    public static function item_md_name($md_id)
    {
        $md_name = DB::table('master_datas')
            ->where('md_id', $md_id)
            ->value('md_name');

        return $md_name;
    }

    public static function item_md_id($md_name)
    {
        $md_id = DB::table('master_datas')
            ->where('md_name', $md_name)
            ->value('md_id');

        return $md_id;
    }

    public static function studentStream($studentId)
    {
        $studentStream = DB::table('students')
            ->where('id', $studentId)
            ->value('stream');

        return $studentStream;
    }

    public static function course_information($course_id)
    {
        $courseName = DB::table('courses')
            ->where('id', $course_id)
            ->value('title');

        return $courseName;
    }

    public static function DropMasterData($code_id = '', $selected = '', $id = '', $part = 2, $disabled = 0)
    {

        if (!$code_id) {
            $select = DB::table('master_datas')->get();
        } else {
            $select = DB::table('master_datas')->where('md_master_code_id', $code_id)->orderBy('md_name', 'asc')->get();
        }

        $disabled = ($disabled) ? 'disabled' : '';

        $string = '';
        $string .= '<select name="' . $id . '" id="' . $id . '" class="form-control select2" ' . $disabled . '>';
        $string .= '<option value=""> -- Select -- </option>';
        foreach ($select as $row) {
            if ($part == 1) {
                if ($row->md_id == $selected) {
                    $string .= '<option selected value="' . $row->md_id . '">' . $row->md_name . '</option>';
                } else {
                    $string .= '<option value="' . $row->md_id . '">' . $row->md_name . '</option>';
                }
            } elseif ($part == 2) {
                if ($row->md_id == $selected) {
                    $string .= '<option selected value="' . $row->md_id . '">' . $row->md_name . ' (' . $row->md_code . ')</option>';
                } else {
                    $string .= '<option value="' . $row->md_id . '">' . $row->md_name . ' (' . $row->md_code . ')</option>';
                }
            }
        }

        $string .= '</select>';

        return $string;
    }

    public static function DropMasterDataAsc($code_id = '', $selected = '', $id = '', $part = 2, $disabled = 0)
    {

        if (!$code_id) {
            $select = DB::table('master_datas')->get();
        } else {
            $select = DB::table('master_datas')->where('md_master_code_id', $code_id)->orderBy('md_id', 'asc')->get();
        }

        $disabled = ($disabled) ? 'disabled' : '';

        $string = '';
        $string .= '<select name="' . $id . '" id="' . $id . '" class="form-control" ' . $disabled . '>';
        $string .= '<option value=""> -- Select -- </option>';
        foreach ($select as $row) {
            if ($part == 1) {
                if ($row->md_id == $selected) {
                    $string .= '<option selected value="' . $row->md_id . '">' . $row->md_name . '</option>';
                } else {
                    $string .= '<option value="' . $row->md_id . '">' . $row->md_name . '</option>';
                }
            } elseif ($part == 2) {
                if ($row->md_id == $selected) {
                    $string .= '<option selected value="' . $row->md_id . '">' . $row->md_name . ' (' . $row->md_code . ')</option>';
                } else {
                    $string .= '<option value="' . $row->md_id . '">' . $row->md_name . ' (' . $row->md_code . ')</option>';
                }
            }
        }

        $string .= '</select>';

        return $string;
    }

    public static function MasterRecord($md_master_code_id, $md_id)
    {

        $md_id = (string) $md_id;

        $masterRecord = DB::table('master_datas')
            ->where('md_master_code_id', $md_master_code_id)
            ->where('md_id', operator: $md_id)
            ->value('md_name');

        return $masterRecord;
    }

    public static function MasterRecordMdId($md_id)
    {
        $md_id = (string) $md_id;
        $masterRecord = DB::table('master_datas')
            ->where('md_id', operator: $md_id)
            ->value('md_name');

        return $masterRecord;
    }

    public static function recordMdname($md_id)
    {
        $recordName = DB::table('master_datas')
            ->where('md_id', operator: $md_id)
            ->value('md_name');

        return $recordName;
    }

    public static function MasterRecordMerge($item1, $item2)
    {
        $items = [$item1, $item2];

        $records = DB::table('master_datas')
            ->whereIn('md_master_code_id', $items)
            ->get();

        return $records;
    }

    public static function MasterRecordMultiple(...$items)
    {
        $records = DB::table('master_datas')
            ->whereIn('md_master_code_id', $items)
            ->get();

        return $records;
    }

    public static function MasterDataRecords($item1)
    {
        $items = [$item1];

        $records = DB::table('master_datas')
            ->whereIn('md_master_code_id', $items)
            ->get();

        return $records;
    }

    public static function fetchAllSubjects()
    {

        $Technical_Subjects = config('constants.options.TECHNICAL_SUBJECTS');
        $Mathematics = config('constants.options.MATHEMATICS');
        $Languages = config('constants.options.LANGUAGES');
        $Sciences = config('constants.options.SCIENCES');
        $Humanities = config('constants.options.HUMANITIES');

        $items = [$Technical_Subjects, $Mathematics, $Languages, $Sciences, $Humanities];

        $records = DB::table('master_datas')
            ->whereIn('md_master_code_id', $items)
            ->get();

        return $records;
    }

    public static function MasterRecords($md_master_code_id)
    {
        $records = DB::table('master_datas')
            ->where('md_master_code_id', $md_master_code_id)
            ->get();

        return $records;
    }

    public static function schoolStudentsCount($school_id)
    {
        return Student::where('school_id', $school_id)->count();
    }

    public static function db_item_from_column($db_table, $item_id, $item_column)
    {
        $specificItem = DB::table($db_table)
            ->where('id', $item_id)
            ->value($item_column);

        return $specificItem;
    }

    public static function school_student_fullName($user = '')
    {
        $user = (int) $user;

        return DB::table('students')
            ->where('id', $user)
            ->select(DB::raw("CONCAT(firstname, ' ', lastname) as full_name"))
            ->value('full_name');
    }

    public static function current_logged_school($school_id)
    {
        if (is_object($school_id) && isset($school_id->school_id)) {
            $school_id = $school_id->school_id;
        }

        if (is_array($school_id) && isset($school_id['school_id'])) {
            $school_id = $school_id['school_id'];
        }

        return DB::table('schools')
            ->where('id', $school_id)
            ->value('name') ?? 'Unknown School';
    }

    public static function uploadedSchoolExam($school_id, $exam_type)
    {
        return DB::table('exams')
            ->where('school_id', $school_id)
            ->where('academic_year', Helper::active_year())
            ->where('exam_type', $exam_type)
            ->exists();
    }

    public static function active_year()
    {
        $activeYear = AcademicYear::where('is_active', 1)
            ->orderBy('id', 'desc')
            ->value('name');

        return $activeYear ?? 'No Active Year Set';
    }

    public static function fetchActiveYearName($academic_year_id)
    {
        $activeYearName = AcademicYear::where('id', $academic_year_id)->value('name');

        return $activeYearName;
    }

    public static function activeAcademicYear()
    {
        return TermDate::where('school_id', Session('LoggedSchool'))
            ->where('is_active', 1)
            ->value('academic_year_id');
    }

    public static function schoolActiveYearName()
    {
        return DB::table('academic_years')->where('id', self::activeAcademicYear())
            ->value('name');
    }

    public static function activeTerm()
    {
        return TermDate::where('school_id', Session('LoggedSchool'))
            ->where('is_active', 1)
            ->value('term');
    }

    public static function schoolActiveTermName()
    {
        return self::recordMdname(self::activeTerm());
    }

    public static function systemActiveYear()
    {
        return AcademicYear::where('is_active', 1)
            ->value('name');
    }

    public static function activeUploadingIdaadYear()
    {
        $activeUploadingYear = DB::table('annual_examinations')
            ->where('examination_name', 'Idaad')
            ->where('is_active', true)
            ->value('year');

        return $activeUploadingYear ?? 'Upload Year Not Set';
    }

    public static function activeUploadingThanawiYear()
    {
        $activeUploadingYear = DB::table('annual_examinations')
            ->where('examination_name', 'Thanawi')
            ->where('is_active', true)
            ->value('year');

        return $activeUploadingYear ?? 'Upload Year Not Set';
    }

    public static function getStudentName($studentId)
    {

        $Student_Name = DB::table('students_basic')
            ->where('Student_ID', $studentId)
            ->value('Student_Name');

        return $Student_Name;
    }

    public static function parseStudentId($studentId, $type = null)
    {
        $parts = explode('-', $studentId);

        if (count($parts) !== 5) {
            return null;
        }

        $schoolId = "{$parts[0]}-{$parts[1]}";
        $studentIdOnly = "{$parts[2]}-{$parts[3]}";
        $year = $parts[4];

        $Student_Name = StudentBasic::where('Student_ID', $studentId)->value('Student_Name');
        $Student_School = StudentBasic::where('Student_ID', $studentId)->value('House');

        return match ($type) {
            'school' => $Student_School,
            'student' => $Student_Name,
            'year' => $year,
            default => [
                'school' => $Student_School,
                'student' => $Student_Name,
                'year' => $year,
            ]
        };
    }

    public static function schoolStatus($House_Number)
    {
        $schoolStatus = DB::table('schools')
            ->where('registration_code', $House_Number)
            ->value('school_status');

        return $schoolStatus;
    }

    /**
     * Get the school_status for the currently logged-in school (by school ID).
     * Status codes: 10 = Active, 1 = Pending Activation, 0 = Banned, 8 = Locked, 9 = Suspended
     */
    public static function currentSchoolStatus(): ?int
    {
        $schoolId = Session('LoggedSchool');
        if (!$schoolId) return null;

        return DB::table('schools')
            ->where('id', $schoolId)
            ->value('school_status');
    }

    public static function getHelperMarksEntryProgress()
    {
        $schoolId = Session('LoggedSchool');
        $teacherId = Session('LoggedTeacher');

        // Get all examinations with marks_entry status
        $examsWithMarksEntry = Examination::where('school_id', $schoolId)
            ->where('status', 'marks_entry')
            ->orderBy('marks_entry_deadline', 'asc')
            ->get();

        $examProgress = [];

        foreach ($examsWithMarksEntry as $exam) {
            // Get all class-subject combinations for this exam where teacher is assigned
            $examClasses = ExaminationClass::where('examination_id', $exam->id)
                ->where('school_id', $schoolId)
                ->get();

            // Get subjects assigned to this teacher for these classes
            $teacherSubjects = DB::table('class_subjects')
                ->where('school_id', $schoolId)
                ->where(function ($q) use ($teacherId) {
                    $q->where('subject_teacher_1', $teacherId)
                        ->orWhere('subject_teacher_2', $teacherId);
                })
                ->whereIn('class_id', $examClasses->pluck('class_id'))
                ->get();

            $totalSubjects = $teacherSubjects->count();
            $submittedSubjects = 0;
            $subjectProgress = [];
            $hasPendingMarks = false;

            foreach ($teacherSubjects as $subject) {
                // Count students in this class-stream
                $studentCount = DB::table('students')
                    ->where('school_id', $schoolId)
                    ->where('senior', $subject->class_id)
                    ->where('stream', $subject->stream_id)
                    ->count();

                // Count marks entered for this subject
                $enteredMarks = ExaminationMark::where('examination_id', $exam->id)
                    ->where('subject_id', $subject->subject_id)
                    ->where('class_id', $subject->class_id)
                    ->where('stream_id', $subject->stream_id)
                    ->where('school_id', $schoolId)
                    ->whereNotNull('marks_obtained')
                    ->count();

                $progressPercent = $studentCount > 0 ? round(($enteredMarks / $studentCount) * 100) : 0;

                if ($progressPercent == 100) {
                    $submittedSubjects++;
                } else {
                    $hasPendingMarks = true;
                }

                $subjectProgress[] = (object) [
                    'subject_id' => $subject->subject_id,
                    'subject_name' => Helper::recordMdname($subject->subject_id),
                    'class_name' => Helper::recordMdname($subject->class_id),
                    'stream' => $subject->stream_id,
                    'total_students' => $studentCount,
                    'entered_marks' => $enteredMarks,
                    'progress' => $progressPercent,
                    'class_subject_id' => $subject->id
                ];
            }

            // Calculate overall progress for the exam
            $overallProgress = $totalSubjects > 0 ? round(($submittedSubjects / $totalSubjects) * 100) : 0;

            // ── NEW: skip this exam entirely if teacher has no assigned subjects ──
            if ($totalSubjects === 0) {
                continue;
            }

            // Calculate deadline status
            $deadline = \Carbon\Carbon::parse($exam->marks_entry_deadline);
            $daysLeft = now()->diffInDays($deadline, false);
            $isDeadlinePassed = $daysLeft < 0;

            if (!$isDeadlinePassed || ($isDeadlinePassed && $hasPendingMarks)) {
                $urgency = $daysLeft <= 2 ? 'urgent' : ($daysLeft <= 5 ? 'warning' : 'normal');

                $examProgress[] = (object) [
                    'exam' => $exam,
                    'total_subjects' => $totalSubjects,
                    'submitted_subjects' => $submittedSubjects,
                    'overall_progress' => $overallProgress,
                    'subject_progress' => $subjectProgress,
                    'days_left' => max(0, $daysLeft),
                    'is_deadline_passed' => $isDeadlinePassed,
                    'urgency' => $urgency,
                    'deadline' => $deadline,
                    'has_pending_marks' => $hasPendingMarks
                ];
            }
        }
        return $examProgress;
    }

    public static function schoolProduct(int $schoolId): ?string
    {
        return DB::table('schools')
            ->where('id', $schoolId)
            ->value('school_product');
    }

    /**
     * Resolve which slip template(s) to use for this school.
     *
     * Returns one of:
     *   'arabic'  – full Arabic RTL  (Idaad And Thanawi  |  Primary Theology)
     *   'english' – standard English (Primary Secular)
     *   'both'    – school has both theology & secular classes
     *
     * For the 'both' case the controller must also check the class's
     * subject_type ('primary_theology' | 'primary_secular') to decide
     * which template applies to each individual class/student.
     */
    public static function schoolSlipType(int $schoolId): string
    {
        $product = (string) self::schoolProduct($schoolId);

        return match ($product) {
            '1', 'Idaad And Thanawi' => 'arabic',   // md_id 1
            '219', 'Primary Theology' => 'arabic',   // md_id 219
            '231', 'Primary Secular' => 'english',  // md_id 231
            '289', 'Both Primary Theology and Secular' => 'both',  // md_id 289
            default => 'english',
        };
    }

    /**
     * For a specific class_id + school, return whether its subjects
     * are theology-based (Arabic) or secular (English).
     * Reads class_subjects.subject_type for the given class.
     *
     * Returns 'arabic' | 'english'
     */
    public static function classSlipType(int $classId, int $schoolId): string
    {
        $type = DB::table('class_subjects')
            ->where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->value('subject_type');

        // 'primary_theology' → Arabic,  'primary_secular' → English
        return ($type === 'primary_theology') ? 'arabic' : 'english';
    }

    /**
     * Return the Arabic school name stored in schools.school_name_arabic.
     */
    public static function schoolNameArabic(int $schoolId): string
    {
        return DB::table('schools')
            ->where('id', $schoolId)
            ->value('school_name_arabic') ?? '';
    }

    public static function toArabicNumberDate($value)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($western, $arabic, $value);
    }

    public static function toArabicLettersCountriesAndWordsPackage($text)
    {
        $dictionary = [

            // Countries
            'UGANDA' => 'أوغندا',
            'KENYA' => 'كينيا',
            'TANZANIA' => 'تنزانيا',
            'RWANDA' => 'رواندا',
            'BURUNDI' => 'بوروندي',
            'SOUTH SUDAN' => 'جنوب السودان',

            // Cities
            'KAMPALA' => 'كمبالا',
            'JINJA' => 'جينجا',
            'MASAKA' => 'مساكا',
            'MBALE' => 'مبالي',

            // Nationalities
            'UGANDAN' => 'أوغندي',
            'KENYAN' => 'كيني',

            // Gender
            'MALE' => 'ذكر',
            'FEMALE' => 'أنثى',
        ];

        $upper = strtoupper(trim($text));

        // Exact dictionary match
        if (isset($dictionary[$upper])) {
            return $dictionary[$upper];
        }

        // Fallback transliteration
        $special = [
            'TH' => 'ث',
            'SH' => 'ش',
            'CH' => 'تش',
            'PH' => 'ف',
            'KH' => 'خ',
            'GH' => 'غ'
        ];

        $text = str_ireplace(
            array_keys($special),
            array_values($special),
            strtoupper($text)
        );

        $map = [
            'A' => 'ا',
            'B' => 'ب',
            'C' => 'ك',
            'D' => 'د',
            'E' => 'ي',
            'F' => 'ف',
            'G' => 'ج',
            'H' => 'ه',
            'I' => 'ي',
            'J' => 'ج',
            'K' => 'ك',
            'L' => 'ل',
            'M' => 'م',
            'N' => 'ن',
            'O' => 'و',
            'P' => 'ب',
            'Q' => 'ق',
            'R' => 'ر',
            'S' => 'س',
            'T' => 'ت',
            'U' => 'و',
            'V' => 'ف',
            'W' => 'و',
            'X' => 'كس',
            'Y' => 'ي',
            'Z' => 'ز',

            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
        ];

        return strtr($text, $map);
    }

    // Optional: Get student initials for avatar fallback
    public static function getStudentInitials($student)
    {

        $firstname = Student::where('id', $student)->value('firstname');
        $lastname = Student::where('id', $student)->value('lastname');


        $first = substr($firstname ?? 'S', 0, 1);
        $last = substr($lastname ?? 'T', 0, 1);
        return strtoupper($first . $last);
    }

    // student Image

    public static function getStudentPhotoUrl($studentID)
    {
        $student_photo_id = $studentID;
        if (empty($student_photo_id)) {
            return null;
        }

        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $path = 'uploads/studentPhotos/' . $student_photo_id . '.' . $ext;
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return null;

        // Applicability in the blade

        // @php
        //     $photoUrl = Helper::getStudentPhotoUrl($student);
        //     $initials = Helper::getStudentInitials($student);
        // @endphp

        // <div class="preview-item">
        //     @if($photoUrl)
        //         <img src="{{ $photoUrl }}" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
        //     @else
        //         <div style="width:24px;height:24px;border-radius:50%;background:var(--bl);color:var(--b);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;">
        //             {{ $initials }}
        //         </div>
        //     @endif
        //     <span><strong>{{ $student->firstname }} {{ $student->lastname }}</strong></span>
        // </div>
    }
}