<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use Illuminate\Http\Request;
use App\Models\ClassAllocation;
use App\Models\StudentBasic;
use App\Http\Controllers\Helper;
use App\Models\Grading;
use App\Models\MasterData;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SchoolPassword;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class SchoolsController extends Controller
{

    public function schoolDashboard()
    {
        return view('GeneralSchools.schoolsdashboard');
    }


    public function processGrading(Request $request)
    {

        $request->validate([
            'year' => 'required',
            'category' => 'required',
            'school_number' => 'nullable',
        ]);

        $year = $request->year;
        $category = $request->category;
        $schoolNumber = $request->school_number;
        $level = $request->level ?? 'A';

        // Build query for students
        $studentsQuery = ClassAllocation::select('Student_ID')
            ->where('Student_ID', 'LIKE', "%-$category-%")
            ->where('Student_ID', 'LIKE', "%-$year")
            ->distinct();

        if ($schoolNumber) {
            $studentsQuery->where('Student_ID', 'LIKE', "$schoolNumber-%");
        }

        $students = $studentsQuery->pluck('Student_ID');

        // Get subjects for this category
        $subjectIds = $this->getSubjectIdsForCategory($category);

        // Get total possible marks (each subject out of 100)
        $totalPossibleMarks = count($subjectIds) * 100;

        // Get all marks for these students and subjects
        $marks = Mark::whereIn('student_id', $students)
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->groupBy('student_id');

        // Calculate results for each student
        $results = [];
        foreach ($students as $studentId) {
            $studentMarks = $marks->get($studentId, collect());

            $totalMarks = $studentMarks->sum('mark');
            $subjectsAttempted = $studentMarks->count();

            // Calculate percentage based on total possible marks for category
            $percentage = $totalPossibleMarks > 0
                ? round(($totalMarks / $totalPossibleMarks) * 100, 2)
                : 0;

            // Get grade (D1, D2, C3, C4, F)
            $gradeModel = Grading::getGrade($percentage, 'Marks', $level);

            // Get classification (FIRST CLASS, SECOND CLASS UPPER, etc.)
            $classificationModel = Grading::getGrade($percentage, 'Points', $level);

            // Build marks details with subject names using the helper
            $marksDetails = [];
            foreach ($studentMarks as $mark) {
                $marksDetails[] = [
                    'subject_id' => $mark->subject_id,
                    'mark' => $mark->mark,
                    'subject_name' => Helper::item_md_name($mark->subject_id),
                ];
            }

            $results[$studentId] = [
                'student_id' => $studentId,
                'total_marks' => $totalMarks,
                'total_possible' => $totalPossibleMarks,
                'subjects_attempted' => $subjectsAttempted,
                'total_subjects' => count($subjectIds),
                'percentage' => $percentage,
                'grade' => $gradeModel->Grade ?? 'N/A',
                'grade_comment' => $gradeModel->Comment ?? '',
                'classification' => $classificationModel->Grade ?? 'N/A',
                'classification_comment' => $classificationModel->Comment ?? '',
                'level' => $level,
                'marks_details' => $marksDetails,
            ];
        }

        uasort($results, function ($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        $statistics = $this->calculateStatistics($results, $level);

        $schoolName = $schoolNumber ? Helper::schoolName($schoolNumber) : 'All Schools';

        return view('itemGrading.grading-results', compact(
            'results',
            'year',
            'category',
            'schoolNumber',
            'schoolName',
            'statistics',
            'level',
            'totalPossibleMarks'
        ));
    }
    private function getSubjectIdsForCategory($category)
    {
        $masterCodeId = ($category == 'TH')
            ? config('constants.options.ThanawiPapers')
            : config('constants.options.IdaadPapers');

        return MasterData::where('md_master_code_id', $masterCodeId)
            ->pluck('md_id')
            ->toArray();
    }


    private function calculateStatistics($results, $level = 'A')
    {
        $count = count($results);

        if ($count == 0) {
            return [
                'count' => 0,
                'average' => 0,
                'highest' => 0,
                'lowest' => 0,
                'grade_distribution' => [],
                'class_distribution' => [],
            ];
        }

        $percentages = array_column($results, 'percentage');

        $grades = Grading::marks($level)->get();
        $gradeDistribution = [];
        foreach ($grades as $grade) {
            $gradeDistribution[$grade->Grade] = 0;
        }

        $classDistribution = [];
        $classes = Grading::points($level)->get();
        foreach ($classes as $class) {
            $classDistribution[$class->Grade] = 0;
        }

        foreach ($results as $result) {
            if (isset($gradeDistribution[$result['grade']])) {
                $gradeDistribution[$result['grade']]++;
            }
            if (isset($classDistribution[$result['classification']])) {
                $classDistribution[$result['classification']]++;
            }
        }

        return [
            'count' => $count,
            'average' => round(array_sum($percentages) / $count, 2),
            'highest' => max($percentages),
            'lowest' => min($percentages),
            'grade_distribution' => $gradeDistribution,
            'class_distribution' => $classDistribution,
        ];
    }

    public function getSchoolRanking(Request $request)
    {
        $request->validate([
            'year' => 'required',
            'category' => 'required',
            'level' => 'nullable|in:A,O'
        ]);

        $year = $request->year;
        $category = $request->category;
        $level = $request->level ?? 'A';

        // Get all students for this year and category
        $studentsQuery = ClassAllocation::select('Student_ID')
            ->where('Student_ID', 'LIKE', "%-$category-%")
            ->where('Student_ID', 'LIKE', "%-$year");

        if ($request->school_number) {
            $studentsQuery->where('Student_ID', 'LIKE', $request->school_number . '-%');
        }

        $students = $studentsQuery->pluck('Student_ID');

        // Get results for these students
        $results = StudentResult::whereIn('student_id', $students)
            ->where('year', $year)
            ->where('category', $category)
            ->where('level', $level)
            ->get();

        // Group by school and calculate statistics
        $schoolStats = [];
        foreach ($results as $result) {
            $schoolNumber = explode('-', $result->student_id)[0];

            if (!isset($schoolStats[$schoolNumber])) {
                $schoolStats[$schoolNumber] = [
                    'school_code' => $schoolNumber,
                    'school_name' => Helper::schoolName($schoolNumber) ?? "School {$schoolNumber}",
                    'total_students' => 0,
                    'total_marks' => 0,
                    'average_percentage' => 0,
                    'grades' => [],
                    'classifications' => [],
                    'students' => []
                ];
            }

            $schoolStats[$schoolNumber]['total_students']++;
            $schoolStats[$schoolNumber]['total_marks'] += $result->percentage;
            $schoolStats[$schoolNumber]['grades'][$result->grade] =
                ($schoolStats[$schoolNumber]['grades'][$result->grade] ?? 0) + 1;
            $schoolStats[$schoolNumber]['classifications'][$result->classification] =
                ($schoolStats[$schoolNumber]['classifications'][$result->classification] ?? 0) + 1;
            $schoolStats[$schoolNumber]['students'][] = [
                'id' => $result->student_id,
                'percentage' => $result->percentage,
                'grade' => $result->grade,
                'classification' => $result->classification
            ];
        }

        // Calculate averages and sort
        foreach ($schoolStats as &$stats) {
            $stats['average_percentage'] = $stats['total_students'] > 0
                ? round($stats['total_marks'] / $stats['total_students'], 2)
                : 0;

            // Calculate pass rate (percentage of students with grade >= C4 or classification not FAIL)
            $passed = 0;
            foreach ($stats['students'] as $student) {
                if (!in_array($student['classification'], ['FAIL', 'F'])) {
                    $passed++;
                }
            }
            $stats['pass_rate'] = $stats['total_students'] > 0
                ? round(($passed / $stats['total_students']) * 100, 2)
                : 0;
        }

        // Sort by average percentage descending
        usort($schoolStats, function ($a, $b) {
            return $b['average_percentage'] <=> $a['average_percentage'];
        });

        // Add rankings
        foreach ($schoolStats as $index => &$stats) {
            $stats['rank'] = $index + 1;
        }

        // Get previous year data for comparison
        $prevYearData = $this->getPreviousYearComparison($year, $category, $level, array_keys($schoolStats));

        return response()->json([
            'success' => true,
            'data' => $schoolStats,
            'previous_year' => $prevYearData,
            'summary' => [
                'total_schools' => count($schoolStats),
                'total_students' => $results->count(),
                'average_across_schools' => count($schoolStats) > 0
                    ? round(array_sum(array_column($schoolStats, 'average_percentage')) / count($schoolStats), 2)
                    : 0,
                'top_school' => $schoolStats[0]['school_name'] ?? 'N/A',
                'top_school_score' => $schoolStats[0]['average_percentage'] ?? 0
            ]
        ]);
    }

    public function getStudentRanking(Request $request)
    {
        $request->validate([
            'year' => 'required',
            'category' => 'required',
            'limit' => 'nullable|integer|min:1|max:500'
        ]);

        $year = $request->year;
        $category = $request->category;
        $level = $request->level ?? 'A';
        $limit = $request->limit ?? 100;
        $schoolNumber = $request->school_number;

        // Build query
        $query = StudentResult::where('year', $year)
            ->where('category', $category)
            ->where('level', $level);

        if ($schoolNumber) {
            $query->where('school_number', $schoolNumber);
        }

        // Get top students
        $topStudents = $query->orderBy('percentage', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item, $index) {
                $schoolNumber = explode('-', $item->student_id)[0];
                return [
                    'rank' => $index + 1,
                    'student_id' => $item->student_id,
                    'school' => Helper::schoolName($schoolNumber) ?? $schoolNumber,
                    'percentage' => $item->percentage,
                    'grade' => $item->grade,
                    'classification' => $item->classification,
                    'total_marks' => $item->total_marks
                ];
            });

        // Get bottom students
        $bottomStudents = StudentResult::where('year', $year)
            ->where('category', $category)
            ->where('level', $level)
            ->where('percentage', '>', 0)
            ->orderBy('percentage', 'asc')
            ->limit(min(50, $limit))
            ->get()
            ->map(function ($item, $index) {
                $schoolNumber = explode('-', $item->student_id)[0];
                return [
                    'rank' => $index + 1,
                    'student_id' => $item->student_id,
                    'school' => Helper::schoolName($schoolNumber) ?? $schoolNumber,
                    'percentage' => $item->percentage,
                    'grade' => $item->grade,
                    'classification' => $item->classification,
                    'total_marks' => $item->total_marks
                ];
            });

        // Get statistics
        $stats = [
            'total_students' => StudentResult::where('year', $year)
                ->where('category', $category)
                ->where('level', $level)
                ->count(),
            'average_percentage' => StudentResult::where('year', $year)
                ->where('category', $category)
                ->where('level', $level)
                ->avg('percentage'),
            'highest_score' => StudentResult::where('year', $year)
                ->where('category', $category)
                ->where('level', $level)
                ->max('percentage'),
            'lowest_score' => StudentResult::where('year', $year)
                ->where('category', $category)
                ->where('level', $level)
                ->min('percentage')
        ];

        return response()->json([
            'success' => true,
            'top_students' => $topStudents,
            'bottom_students' => $bottomStudents,
            'statistics' => $stats
        ]);
    }

    public function getSubjectAnalysis(Request $request)
    {
        $request->validate([
            'year' => 'required',
            'category' => 'required'
        ]);

        $year = $request->year;
        $category = $request->category;
        $schoolNumber = $request->school_number;

        // Get subjects for this category
        $subjectIds = $this->getSubjectIdsForCategory($category);

        // Get all students for this year/category
        $studentsQuery = ClassAllocation::select('Student_ID')
            ->where('Student_ID', 'LIKE', "%-$category-%")
            ->where('Student_ID', 'LIKE', "%-$year");

        if ($schoolNumber) {
            $studentsQuery->where('Student_ID', 'LIKE', $schoolNumber . '-%');
        }

        $students = $studentsQuery->pluck('Student_ID');

        // Get marks for all subjects
        $marks = Mark::whereIn('student_id', $students)
            ->whereIn('subject_id', $subjectIds)
            ->with('subject')
            ->get()
            ->groupBy('subject_id');

        $subjectAnalysis = [];
        foreach ($subjectIds as $subjectId) {
            $subjectMarks = $marks->get($subjectId, collect());

            if ($subjectMarks->isEmpty()) {
                continue;
            }

            $marksValues = $subjectMarks->pluck('mark')->toArray();

            $analysis = [
                'subject_id' => $subjectId,
                'subject_name' => $subjectMarks->first()->subject->md_name ?? 'Unknown',
                'total_students' => $subjectMarks->count(),
                'average_mark' => round($subjectMarks->avg('mark'), 2),
                'highest_mark' => max($marksValues),
                'lowest_mark' => min($marksValues),
                'median_mark' => $this->calculateMedian($marksValues),
                'std_deviation' => $this->calculateStdDev($marksValues),
                'pass_count' => $subjectMarks->where('mark', '>=', 50)->count(),
                'fail_count' => $subjectMarks->where('mark', '<', 50)->count(),
                'pass_rate' => round(($subjectMarks->where('mark', '>=', 50)->count() / $subjectMarks->count()) * 100, 2),
                'grade_distribution' => $this->getMarkGradeDistribution($subjectMarks->pluck('mark')->toArray())
            ];

            $subjectAnalysis[] = $analysis;
        }

        // Sort by average mark descending
        usort($subjectAnalysis, function ($a, $b) {
            return $b['average_mark'] <=> $a['average_mark'];
        });

        // Get best and worst subjects
        $bestSubjects = array_slice($subjectAnalysis, 0, 5);
        $worstSubjects = array_slice(array_reverse($subjectAnalysis), 0, 5);

        return response()->json([
            'success' => true,
            'all_subjects' => $subjectAnalysis,
            'best_subjects' => $bestSubjects,
            'worst_subjects' => $worstSubjects,
            'summary' => [
                'total_subjects' => count($subjectAnalysis),
                'overall_average' => count($subjectAnalysis) > 0
                    ? round(array_sum(array_column($subjectAnalysis, 'average_mark')) / count($subjectAnalysis), 2)
                    : 0,
                'best_subject' => $bestSubjects[0]['subject_name'] ?? 'N/A',
                'best_subject_score' => $bestSubjects[0]['average_mark'] ?? 0,
                'worst_subject' => $worstSubjects[0]['subject_name'] ?? 'N/A',
                'worst_subject_score' => $worstSubjects[0]['average_mark'] ?? 0
            ]
        ]);
    }

    public function getYearComparison(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'years' => 'required|array|min:2|max:5'
        ]);

        $category = $request->category;
        $years = $request->years;
        $level = $request->level ?? 'A';
        $schoolNumber = $request->school_number;

        $comparison = [];
        $trends = [];

        foreach ($years as $year) {
            $query = StudentResult::where('year', $year)
                ->where('category', $category)
                ->where('level', $level);

            if ($schoolNumber) {
                $query->where('school_number', $schoolNumber);
            }

            $results = $query->get();

            $yearData = [
                'year' => $year,
                'total_students' => $results->count(),
                'average_percentage' => $results->avg('percentage') ?? 0,
                'highest_percentage' => $results->max('percentage') ?? 0,
                'lowest_percentage' => $results->min('percentage') ?? 0,
                'grade_distribution' => $results->groupBy('grade')->map->count(),
                'classification_distribution' => $results->groupBy('classification')->map->count(),
                'pass_rate' => $results->whereNotIn('classification', ['FAIL', 'F'])->count() / max($results->count(), 1) * 100
            ];

            $comparison[] = $yearData;
        }

        // Calculate trends
        for ($i = 1; $i < count($comparison); $i++) {
            $current = $comparison[$i];
            $previous = $comparison[$i - 1];

            $trends[] = [
                'from_year' => $previous['year'],
                'to_year' => $current['year'],
                'average_change' => round($current['average_percentage'] - $previous['average_percentage'], 2),
                'student_count_change' => $current['total_students'] - $previous['total_students'],
                'pass_rate_change' => round($current['pass_rate'] - $previous['pass_rate'], 2)
            ];
        }

        return response()->json([
            'success' => true,
            'comparison' => $comparison,
            'trends' => $trends,
            'summary' => [
                'best_year' => collect($comparison)->sortByDesc('average_percentage')->first()['year'] ?? null,
                'best_year_avg' => collect($comparison)->max('average_percentage') ?? 0,
                'worst_year' => collect($comparison)->sortBy('average_percentage')->first()['year'] ?? null,
                'worst_year_avg' => collect($comparison)->min('average_percentage') ?? 0,
                'overall_trend' => $this->calculateOverallTrend($comparison)
            ]
        ]);
    }

    public function generateResultsPDF(Request $request)
    {
        try {
            $year = $request->year;
            $category = $request->category;
            $schoolNumber = $request->school_number;
            $level = $request->level;
            $schoolName = $request->school_name;
            $results = json_decode($request->results_data, true);

            // Calculate statistics for PDF
            $statistics = $this->calculateStatisticsTotal($results);

            // Prepare data for PDF view
            $data = [
                'year' => $year,
                'category' => $category,
                'schoolNumber' => $schoolNumber,
                'level' => $level,
                'schoolName' => $schoolName,
                'results' => $results,
                'statistics' => $statistics,
                'generated_date' => now()->format('F d, Y H:i:s'),
                'total_students' => count($results)
            ];

            // Load the PDF view
            $pdf = Pdf::loadView('itemGrading.pdf.grading-results', $data);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'landscape');

            // Generate filename
            $filename = "Grading_Results_{$schoolName}_{$category}_{$year}_" . now()->format('Y-m-d') . ".pdf";

            // Download the PDF
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateStatisticsTotal($results)
    {
        $count = count($results);
        $percentages = array_column($results, 'percentage');
        $totalPercentage = array_sum($percentages);

        // Grade distribution
        $gradeDistribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0
        ];

        // Classification distribution
        $classDistribution = [
            'FIRST CLASS' => 0,
            'SECOND CLASS UPPER' => 0,
            'SECOND CLASS LOWER' => 0,
            'THIRD CLASS' => 0,
            'FAIL' => 0
        ];

        foreach ($results as $result) {
            $percentage = $result['percentage'];
            $classification = $result['classification'] ?? 'FAIL';

            // Grade distribution
            if ($percentage >= 80)
                $gradeDistribution['A']++;
            elseif ($percentage >= 70)
                $gradeDistribution['B']++;
            elseif ($percentage >= 60)
                $gradeDistribution['C']++;
            elseif ($percentage >= 50)
                $gradeDistribution['D']++;
            else
                $gradeDistribution['F']++;

            // Classification distribution
            if (isset($classDistribution[$classification])) {
                $classDistribution[$classification]++;
            }
        }

        return [
            'count' => $count,
            'average' => $count > 0 ? round($totalPercentage / $count, 2) : 0,
            'highest' => $count > 0 ? max($percentages) : 0,
            'lowest' => $count > 0 ? min($percentages) : 0,
            'grade_distribution' => $gradeDistribution,
            'class_distribution' => $classDistribution
        ];
    }

    public function exportAllPasswordsPDF(Request $request)
    {

        try {
            // Fetch all passwords with school information
            $passwords = SchoolPassword::with('school')
                ->orderBy('school_id')
                ->get()
                ->map(function ($item, $index) {
                    return [
                        'sr_no' => $index + 1,
                        'id' => $item->id,
                        'school_id' => $item->school_id,
                        'school_name' => $item->school
                            ? $item->school->House
                            : 'N/A',
                        'password_plain' => $item->password_plain,
                        'created_at' => $item->created_at ? $item->created_at->format('Y-m-d') : 'N/A',
                    ];
                });

            if ($passwords->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No passwords found to export'
                ], 404);
            }

            $data = [
                'passwords' => $passwords,
                'total_records' => count($passwords),
                'export_date' => now()->format('Y-m-d H:i:s'),
                'exported_by' => auth()->user()->name ?? 'System',
                'title' => 'School Passwords Export',
                'company_name' => config('app.name', 'School Management System')
            ];

            // Clean any output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Generate PDF
            $pdf = Pdf::loadView('itemGrading.pdf.school-passwords', $data);
            $pdf->setPaper('A4', 'landscape');

            // Set options for better compatibility
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false
            ]);

            $filename = 'school_passwords_' . date('Y-m-d_His') . '.pdf';

            // Return with proper headers
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public'
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            Log::error('PDF Export Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }


}
