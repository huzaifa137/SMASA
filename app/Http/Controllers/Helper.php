<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentBasic;
use App\Models\TermDate;
use App\Models\User;
use DB;
use Session;

class Helper extends Controller
{
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

    public static function schoolIDFromHouseID($house_id)
    {
        $schoolID = DB::table('schools')
            ->where('registration_code', $house_id)
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
}
