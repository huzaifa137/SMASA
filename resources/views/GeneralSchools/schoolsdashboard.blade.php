{{-- resources/views/school/dashboard.blade.php --}}
@extends('layouts-side-bar.master')

@section('content')

@php
    // ─── Dummy Data for School Dashboard ───────────────────────────────────
    // School Information
    $schoolName       = $schoolName       ?? 'Al-Noor Academy';
    $schoolCode       = $schoolCode       ?? 'ANA-001';
    $schoolCategory   = $schoolCategory   ?? 'Thanawi (TH)';
    $schoolLogo       = $schoolLogo       ?? null;
    $academicYear     = $academicYear     ?? '2024/2025';
    $currentTerm      = $currentTerm      ?? 'Term 2';
    
    // Student Statistics
    $totalStudents    = $totalStudents    ?? 1_842;
    $totalTeachers    = $totalTeachers    ?? 86;
    $totalClasses     = $totalClasses     ?? 42;
    $totalStaff       = $totalStaff       ?? 124;
    
    // Academic Performance
    $overallPassRate  = $overallPassRate  ?? 87.4;
    $averageGrade     = $averageGrade     ?? 'B+';
    $topPerformer     = $topPerformer     ?? 'Fatima Al-Mansoori';
    $topScore         = $topScore         ?? 98.5;
    $improvement      = $improvement      ?? '+4.2%';
    
    // Attendance
    $avgAttendance    = $avgAttendance    ?? 94.2;
    $avgPunctuality   = $avgPunctuality   ?? 89.7;
    
    // Recent Exams
    $recentExams = $recentExams ?? [
        ['name'=>'Mathematics Final',      'date'=>'Mar 15, 2025', 'avg_score'=>78.5, 'passed'=>142, 'total'=>185, 'status'=>'published'],
        ['name'=>'Science Mid-Term',       'date'=>'Mar 10, 2025', 'avg_score'=>82.3, 'passed'=>167, 'total'=>185, 'status'=>'published'],
        ['name'=>'English Literature',     'date'=>'Mar 05, 2025', 'avg_score'=>85.1, 'passed'=>172, 'total'=>185, 'status'=>'published'],
        ['name'=>'Islamic Studies',        'date'=>'Feb 28, 2025', 'avg_score'=>91.2, 'passed'=>179, 'total'=>185, 'status'=>'published'],
        ['name'=>'Arabic Language',        'date'=>'Feb 20, 2025', 'avg_score'=>79.8, 'passed'=>158, 'total'=>185, 'status'=>'grading'],
        ['name'=>'Physics (Advanced)',     'date'=>'Feb 15, 2025', 'avg_score'=>74.6, 'passed'=>124, 'total'=>156, 'status'=>'grading'],
    ];
    
    // Class Performance
    $classPerformance = $classPerformance ?? [
        ['class'=>'Grade 12A', 'students'=>42, 'avg_score'=>89.2, 'rank'=>1, 'trend'=>'up'],
        ['class'=>'Grade 12B', 'students'=>38, 'avg_score'=>87.5, 'rank'=>2, 'trend'=>'up'],
        ['class'=>'Grade 11A', 'students'=>44, 'avg_score'=>85.3, 'rank'=>3, 'trend'=>'down'],
        ['class'=>'Grade 11B', 'students'=>41, 'avg_score'=>84.1, 'rank'=>4, 'trend'=>'stable'],
        ['class'=>'Grade 10A', 'students'=>46, 'avg_score'=>82.7, 'rank'=>5, 'trend'=>'up'],
        ['class'=>'Grade 10B', 'students'=>43, 'avg_score'=>81.9, 'rank'=>6, 'trend'=>'stable'],
    ];
    
    // Monthly Trends
    $monthlyTrends = $monthlyTrends ?? [
        ['month'=>'Sep', 'attendance'=>92, 'avg_score'=>81, 'events'=>3],
        ['month'=>'Oct', 'attendance'=>93, 'avg_score'=>83, 'events'=>4],
        ['month'=>'Nov', 'attendance'=>94, 'avg_score'=>84, 'events'=>5],
        ['month'=>'Dec', 'attendance'=>95, 'avg_score'=>86, 'events'=>6],
        ['month'=>'Jan', 'attendance'=>94, 'avg_score'=>87, 'events'=>4],
        ['month'=>'Feb', 'attendance'=>94, 'avg_score'=>88, 'events'=>5],
        ['month'=>'Mar', 'attendance'=>95, 'avg_score'=>89, 'events'=>7],
    ];
    
    // Upcoming Events
    $upcomingEvents = $upcomingEvents ?? [
        ['title'=>'Parent-Teacher Meeting',      'date'=>'Mar 25, 2025', 'type'=>'meeting', 'time'=>'09:00 AM'],
        ['title'=>'Mid-Term Examinations',       'date'=>'Apr 02, 2025', 'type'=>'exam',    'time'=>'08:00 AM'],
        ['title'=>'Sports Day',                  'date'=>'Apr 10, 2025', 'type'=>'event',   'time'=>'08:30 AM'],
        ['title'=>'Teacher Training Workshop',   'date'=>'Apr 15, 2025', 'type'=>'training','time'=>'10:00 AM'],
        ['title'=>'End of Term Assembly',        'date'=>'Apr 20, 2025', 'type'=>'event',   'time'=>'09:00 AM'],
    ];
    
    // Recent Announcements
    $announcements = $announcements ?? [
        ['title'=>'Term 2 Results Published',     'date'=>'Mar 18, 2025', 'priority'=>'high',   'content'=>'All exam results are now available in the portal.'],
        ['title'=>'School Holiday Announcement',  'date'=>'Mar 15, 2025', 'priority'=>'normal', 'content'=>'School will remain closed on March 21-22.'],
        ['title'=>'New Staff Member',             'date'=>'Mar 10, 2025', 'priority'=>'low',    'content'=>'Welcome Mr. Ahmed as new Physics teacher.'],
        ['title'=>'PTA Meeting Rescheduled',      'date'=>'Mar 08, 2025', 'priority'=>'high',   'content'=>'Meeting moved to March 26 at 5:00 PM.'],
    ];
    
    // Subject Performance
    $subjectPerformance = $subjectPerformance ?? [
        ['subject'=>'Islamic Studies',   'avg_score'=>91.2, 'pass_rate'=>96.8, 'students'=>185, 'trend'=>'up'],
        ['subject'=>'English',           'avg_score'=>85.1, 'pass_rate'=>92.4, 'students'=>185, 'trend'=>'up'],
        ['subject'=>'Mathematics',       'avg_score'=>78.5, 'pass_rate'=>84.3, 'students'=>185, 'trend'=>'down'],
        ['subject'=>'Science',           'avg_score'=>82.3, 'pass_rate'=>90.2, 'students'=>185, 'trend'=>'stable'],
        ['subject'=>'Arabic',            'avg_score'=>79.8, 'pass_rate'=>87.1, 'students'=>185, 'trend'=>'up'],
        ['subject'=>'Physics',           'avg_score'=>74.6, 'pass_rate'=>79.5, 'students'=>156, 'trend'=>'down'],
    ];
    
    // Recent Activities
    $schoolActivities = $schoolActivities ?? [
        ['icon'=>'fa-user-graduate', 'color'=>'#5351e4', 'text'=>'New student enrolled: Omar Hassan (Grade 10A)', 'time'=>'1 hour ago'],
        ['icon'=>'fa-chalkboard-user', 'color'=>'#3b82f6', 'text'=>'Exam results uploaded for Grade 12', 'time'=>'3 hours ago'],
        ['icon'=>'fa-calendar-check', 'color'=>'#e0a020', 'text'=>'Parent meeting scheduled for next week', 'time'=>'5 hours ago'],
        ['icon'=>'fa-file-alt', 'color'=>'#6c3fc5', 'text'=>'Attendance report generated for Term 2', 'time'=>'Yesterday'],
        ['icon'=>'fa-award', 'color'=>'#f59e0b', 'text'=>'Student of the Month: Aisha Al-Mazroui', 'time'=>'Yesterday'],
    ];
    
    // Teacher of the Month
    $teacherOfMonth = $teacherOfMonth ?? [
        'name'=>'Mr. Khalid Al-Rashid',
        'subject'=>'Mathematics',
        'rating'=>4.8,
        'achievement'=>'98% student pass rate',
        'avatar'=>null,
    ];
    
    // Student Leaderboard
    $studentLeaderboard = $studentLeaderboard ?? [
        ['rank'=>1, 'name'=>'Fatima Al-Mansoori', 'class'=>'Grade 12A', 'avg_score'=>98.5, 'trend'=>'up'],
        ['rank'=>2, 'name'=>'Ahmed Al-Blooshi',   'class'=>'Grade 12A', 'avg_score'=>97.2, 'trend'=>'up'],
        ['rank'=>3, 'name'=>'Mariam Al-Hosani',   'class'=>'Grade 12B', 'avg_score'=>96.8, 'trend'=>'stable'],
        ['rank'=>4, 'name'=>'Saeed Al-Mazroui',   'class'=>'Grade 11A', 'avg_score'=>95.4, 'trend'=>'up'],
        ['rank'=>5, 'name'=>'Noura Al-Mansoori',  'class'=>'Grade 11B', 'avg_score'=>94.9, 'trend'=>'down'],
    ];
    
    // Financial Summary
    $financialSummary = $financialSummary ?? [
        'total_fees' => 2_450_000,
        'collected' => 2_124_500,
        'pending' => 325_500,
        'collection_rate' => 86.7,
    ];
    
    // Resources Usage
    $resources = $resources ?? [
        ['name'=>'Library Books', 'used'=>342, 'total'=>500, 'pct'=>68.4],
        ['name'=>'Computer Lab',  'used'=>28,  'total'=>32,  'pct'=>87.5],
        ['name'=>'Science Lab',   'used'=>2,   'total'=>3,   'pct'=>66.7],
        ['name'=>'Sports Equipment','used'=>75, 'total'=>120, 'pct'=>62.5],
    ];
    
    // Alerts
    $schoolAlerts = $schoolAlerts ?? [
        ['type'=>'warning', 'message'=>'Upcoming deadline: Term 2 fees due by Mar 30'],
        ['type'=>'info',    'message'=>'New feature: Online parent portal now available'],
        ['type'=>'success', 'message'=>'Congratulations! School ranking improved to #3 in region'],
    ];
    
    // Current Date Info
    $currentDate = now();
    $daysUntilTermEnd = 32;
    $daysUntilExams = 12;
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap');

    :root {
        --brand: #5351e4;
        --brand-light: #2C29CA;
        --brand-dark: #2C29CA;
        --brand-muted: rgba(40, 124, 68, 0.12);
        --accent: #e0a020;
        --accent-muted: rgba(224, 160, 32, 0.12);
        --purple: #6c3fc5;
        --purple-muted: rgba(108, 63, 197, 0.12);
        --danger: #ef4444;
        --danger-muted: rgba(239, 68, 68, 0.12);
        --info: #3b82f6;
        --info-muted: rgba(59, 130, 246, 0.12);
        --surface: #ffffff;
        --surface-2: #f7f9f7;
        --surface-3: #eef3ef;
        --border: rgba(40, 124, 68, 0.14);
        --text-primary: #0f1f14;
        --text-secondary: #4b6356;
        --text-muted: #8ca898;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.09);
        --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.12);
        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;
        --font: 'Sora', sans-serif;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .school-dashboard {
        font-family: var(--font);
        color: var(--text-primary);
        background: var(--surface-2);
        min-height: 100vh;
        padding: 0 0 40px;
    }

    /* School Header */
    .school-header {
        background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 55%, var(--brand-light) 100%);
        padding: 28px 32px 70px;
        position: relative;
        overflow: hidden;
        margin-top:1.5em;
    }

    .school-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .school-header::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 60px;
        background: var(--surface-2);
        border-radius: 40px 40px 0 0;
    }

    .school-info {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .school-title h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 8px;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .school-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
    }

    .school-meta {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
        display: flex;
        gap: 16px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .school-meta i {
        margin-right: 6px;
    }

    .school-actions {
        display: flex;
        gap: 12px;
    }

    .school-btn {
        padding: 10px 20px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .school-btn-primary {
        background: #fff;
        color: var(--brand-dark);
    }

    .school-btn-outline {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .school-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    /* Page Body */
    .school-body {
        padding: 0 24px;
        margin-top: -46px;
        position: relative;
        z-index: 2;
    }

    /* Welcome Card */
    .welcome-card {
        background: linear-gradient(135deg, #fff 0%, #fef9e8 100%);
        border-radius: var(--radius-lg);
        padding: 24px 28px;
        margin-bottom: 24px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .welcome-text h3 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0 0 6px;
        color: var(--text-primary);
    }

    .welcome-text p {
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin: 0;
    }

    .countdown-badge {
        background: var(--brand-muted);
        padding: 12px 20px;
        border-radius: var(--radius-md);
        text-align: center;
    }

    .countdown-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--brand);
        line-height: 1;
    }

    .countdown-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1100px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .kpi-card {
        background: var(--surface);
        border-radius: var(--radius-md);
        padding: 20px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
        transition: all 0.25s;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 14px;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
        color: var(--text-primary);
    }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-trend {
        font-size: 0.7rem;
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 100px;
    }

    .trend-up {
        background: rgba(40, 124, 68, 0.1);
        color: var(--brand);
    }

    .trend-down {
        background: var(--danger-muted);
        color: var(--danger);
    }

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: var(--brand);
        font-size: 1rem;
    }

    .section-link {
        font-size: 0.75rem;
        color: var(--brand);
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .section-link:hover {
        color: var(--brand-dark);
    }

    /* Cards */
    .card-school {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }

    .card-header-custom {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body-custom {
        padding: 20px;
    }

    /* Grid Layouts */
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1000px) {

        .grid-2,
        .grid-3 {
            grid-template-columns: 1fr;
        }
    }

    /* Tables */
    .school-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    .school-table th {
        text-align: left;
        padding: 10px 12px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .school-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }

    .school-table tr:hover td {
        background: var(--surface-2);
    }

    /* Progress Bars */
    .progress-track {
        height: 6px;
        background: var(--surface-3);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.6s ease;
    }

    /* Rank Badges */
    .rank-badge-sm {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .rank-1 {
        background: linear-gradient(135deg, #ffd700, #f5a623);
        color: #5a3a00;
    }

    .rank-2 {
        background: linear-gradient(135deg, #e8e8e8, #b0b0b0);
        color: #333;
    }

    .rank-3 {
        background: linear-gradient(135deg, #e8ab6e, #c57b3e);
        color: #fff;
    }

    /* Activity Feed */
    .activity-feed {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .activity-time {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    /* Event Cards */
    .event-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .event-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        transition: all 0.2s;
    }

    .event-date {
        width: 50px;
        text-align: center;
        flex-shrink: 0;
    }

    .event-day {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--brand);
        line-height: 1;
    }

    .event-month {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .event-info {
        flex: 1;
    }

    .event-title {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 2px;
    }

    .event-time {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .event-badge {
        padding: 4px 8px;
        border-radius: 100px;
        font-size: 0.65rem;
        font-weight: 600;
    }

    .event-badge.meeting {
        background: var(--info-muted);
        color: var(--info);
    }

    .event-badge.exam {
        background: var(--danger-muted);
        color: var(--danger);
    }

    .event-badge.event {
        background: var(--brand-muted);
        color: var(--brand);
    }

    /* Announcements */
    .announcement-item {
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .announcement-title {
        font-weight: 600;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .priority-high {
        color: var(--danger);
        font-size: 0.7rem;
    }

    .priority-normal {
        color: var(--accent);
    }

    .priority-low {
        color: var(--text-muted);
    }

    .announcement-content {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 4px 0;
    }

    .announcement-date {
        font-size: 0.65rem;
        color: var(--text-muted);
    }

    /* Alert Strip */
    .alert-strip-school {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fffbf0;
        border: 1px solid #f5d76e;
        border-radius: var(--radius-sm);
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 0.8rem;
    }

    /* Teacher Card */
    .teacher-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: linear-gradient(135deg, var(--surface) 0%, var(--surface-2) 100%);
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
    }

    .teacher-avatar {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        font-weight: 700;
    }

    .teacher-info h4 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 4px;
    }

    .teacher-rating {
        color: #f59e0b;
        font-size: 0.75rem;
    }

    /* Leaderboard */
    .leaderboard-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }

    /* Footer */
    .school-footer {
        margin-top: 24px;
        padding: 20px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    /* Animations */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .kpi-card {
        animation: fadeUp 0.4s ease both;
    }

    .kpi-card:nth-child(1) { animation-delay: 0.05s; }
    .kpi-card:nth-child(2) { animation-delay: 0.1s; }
    .kpi-card:nth-child(3) { animation-delay: 0.15s; }
    .kpi-card:nth-child(4) { animation-delay: 0.2s; }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="school-dashboard">

    {{-- School Header --}}
    <div class="school-header">
        <div class="school-info">
            <div>
                <h1>
                    <i class="fas fa-school text-white"></i>
                    <span class="text-white">{{ $schoolName }}</span>
                    <span class="school-badge">{{ $schoolCode }}</span>
                </h1>
                <div class="school-meta">
                    <span><i class="fas fa-layer-group"></i> {{ $schoolCategory }}</span>
                    <span><i class="fas fa-calendar-alt"></i> Academic Year: {{ $academicYear }}</span>
                    <span><i class="fas fa-book-open"></i> {{ $currentTerm }}</span>
                </div>
            </div>
            <div class="school-actions">
                <a href="#" class="school-btn school-btn-primary"><i class="fas fa-print"></i> Report Card</a>
                <a href="#" class="school-btn school-btn-outline"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>
    </div>

    <div class="school-body">

        {{-- Welcome Card with Countdown --}}
        <div class="welcome-card">
            <div class="welcome-text">
                <h3>Welcome back, Principal!</h3>
                <p><i class="fas fa-chart-line me-1"></i> {{ $overallPassRate }}% overall pass rate • {{ $improvement }} vs last term</p>
            </div>
            <div class="countdown-badge">
                <div class="countdown-number">{{ $daysUntilExams }}</div>
                <div class="countdown-label">Days until exams</div>
            </div>
        </div>

        {{-- Alert Strip --}}
        @if(count($schoolAlerts) > 0)
        @foreach($schoolAlerts as $alert)
        <div class="alert-strip-school">
            <i class="fas fa-{{ $alert['type'] === 'warning' ? 'exclamation-triangle' : ($alert['type'] === 'info' ? 'info-circle' : 'check-circle') }}"></i>
            <span>{{ $alert['message'] }}</span>
        </div>
        @endforeach
        @endif

        {{-- KPI Cards --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: var(--brand-muted); color: var(--brand);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="kpi-value">{{ number_format($totalStudents) }}</div>
                <div class="kpi-label">Total Students</div>
                <span class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +124 this year</span>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: var(--info-muted); color: var(--info);">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="kpi-value">{{ number_format($totalTeachers) }}</div>
                <div class="kpi-label">Total Teachers</div>
                <span class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +6 new</span>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: var(--accent-muted); color: #b07d00;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="kpi-value">{{ $overallPassRate }}%</div>
                <div class="kpi-label">Pass Rate</div>
                <span class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $improvement }}</span>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: var(--purple-muted); color: var(--purple);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="kpi-value">{{ $avgAttendance }}%</div>
                <div class="kpi-label">Attendance Rate</div>
                <span class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +2.1%</span>
            </div>
        </div>

        {{-- Secondary KPIs Row --}}
        <div class="grid-3" style="margin-bottom: 24px;">
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-trophy"></i> Top Performer</div>
                    <i class="fas fa-medal" style="color: #f59e0b;"></i>
                </div>
                <div class="card-body-custom">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #fbbf24); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff;">
                            ★
                        </div>
                        <div>
                            <div style="font-size: 1rem; font-weight: 700;">{{ $topPerformer }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Average Score: <strong style="color: var(--brand);">{{ $topScore }}%</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-chart-simple"></i> Average Grade</div>
                </div>
                <div class="card-body-custom" style="text-align: center;">
                    <div style="font-size: 3rem; font-weight: 800; color: var(--brand);">{{ $averageGrade }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">School-wide average</div>
                    <div class="progress-track" style="margin-top: 12px;">
                        <div class="progress-fill" style="width: {{ $overallPassRate }}%; background: var(--brand);"></div>
                    </div>
                </div>
            </div>

            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-dollar-sign"></i> Fee Collection</div>
                </div>
                <div class="card-body-custom">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 0.7rem; color: var(--text-muted);">Collection Rate</span>
                        <span style="font-weight: 700;">{{ $financialSummary['collection_rate'] }}%</span>
                    </div>
                    <div class="progress-track" style="margin-bottom: 16px;">
                        <div class="progress-fill" style="width: {{ $financialSummary['collection_rate'] }}%; background: var(--brand);"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <div style="font-size: 1rem; font-weight: 700;">${{ number_format($financialSummary['collected']) }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Collected</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1rem; font-weight: 700; color: var(--danger);">${{ number_format($financialSummary['pending']) }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid: Performance Table + Leaderboard --}}
        <div class="grid-2">
            {{-- Class Performance Table --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-chart-bar"></i> Class Performance</div>
                    <a href="#" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="school-table">
                        <thead>
                            <tr><th>Class</th><th>Students</th><th>Avg Score</th><th>Rank</th><th>Trend</th></tr>
                        </thead>
                        <tbody>
                            @foreach($classPerformance as $class)
                            <tr>
                                <td style="font-weight: 600;">{{ $class['class'] }}</td>
                                <td>{{ $class['students'] }}</td>
                                <td><strong>{{ $class['avg_score'] }}%</strong></td>
                                <td><span class="rank-badge-sm rank-{{ $class['rank'] <= 3 ? $class['rank'] : 'other' }}">{{ $class['rank'] }}</span></td>
                                <td>
                                    @if($class['trend'] == 'up')
                                        <i class="fas fa-arrow-up" style="color: var(--brand);"></i>
                                    @elseif($class['trend'] == 'down')
                                        <i class="fas fa-arrow-down" style="color: var(--danger);"></i>
                                    @else
                                        <i class="fas fa-minus" style="color: var(--text-muted);"></i>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Student Leaderboard --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-crown"></i> Top Students</div>
                    <a href="#" class="section-link">Full List <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body-custom">
                    @foreach($studentLeaderboard as $student)
                    <div class="leaderboard-item">
                        <span class="rank-badge-sm rank-{{ $student['rank'] <= 3 ? $student['rank'] : 'other' }}">{{ $student['rank'] }}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.85rem;">{{ $student['name'] }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $student['class'] }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: var(--brand);">{{ $student['avg_score'] }}%</div>
                            @if($student['trend'] == 'up')
                                <i class="fas fa-arrow-up" style="color: var(--brand); font-size: 0.7rem;"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Subject Performance --}}
        <div class="card-school" style="margin-bottom: 24px;">
            <div class="card-header-custom">
                <div class="section-title"><i class="fas fa-book"></i> Subject Performance Analysis</div>
                <a href="#" class="section-link">Detailed Report <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="card-body-custom">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                    @foreach($subjectPerformance as $subject)
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-weight: 600; font-size: 0.85rem;">{{ $subject['subject'] }}</span>
                            <span style="font-weight: 700; color: var(--brand);">{{ $subject['avg_score'] }}%</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px;">
                            <span>Pass Rate: {{ $subject['pass_rate'] }}%</span>
                            <span>{{ $subject['students'] }} students</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $subject['avg_score'] }}%; background: {{ $subject['trend'] == 'up' ? 'var(--brand)' : ($subject['trend'] == 'down' ? 'var(--danger)' : 'var(--accent)') }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Recent Exams + Upcoming Events --}}
        <div class="grid-2">
            {{-- Recent Exams --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-file-alt"></i> Recent Examinations</div>
                    <a href="#" class="section-link">All Exams <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="school-table">
                        <thead>
                            <tr><th>Exam Name</th><th>Date</th><th>Avg Score</th><th>Passed/Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentExams as $exam)
                            <tr>
                                <td style="font-weight: 500;">{{ $exam['name'] }}</td>
                                <td style="font-size: 0.75rem;">{{ $exam['date'] }}</td>
                                <td><strong style="color: {{ $exam['avg_score'] >= 75 ? 'var(--brand)' : 'var(--danger)' }};">{{ $exam['avg_score'] }}%</strong></td>
                                <td>{{ $exam['passed'] }}/{{ $exam['total'] }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $exam['status'] == 'published' ? 'rgba(40,124,68,0.1)' : 'rgba(224,160,32,0.1)' }}; color: {{ $exam['status'] == 'published' ? 'var(--brand)' : '#b07d00' }}; padding: 4px 8px; border-radius: 100px; font-size: 0.7rem;">
                                        {{ $exam['status'] == 'published' ? 'Published' : 'Grading' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-calendar-alt"></i> Upcoming Events</div>
                    <a href="#" class="section-link">Calendar <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body-custom">
                    <div class="event-list">
                        @foreach($upcomingEvents as $event)
                        <div class="event-item">
                            <div class="event-date">
                                <div class="event-day">{{ substr($event['date'], 0, 5) }}</div>
                                <div class="event-month">{{ substr($event['date'], -4) }}</div>
                            </div>
                            <div class="event-info">
                                <div class="event-title">{{ $event['title'] }}</div>
                                <div class="event-time"><i class="far fa-clock"></i> {{ $event['time'] }}</div>
                            </div>
                            <span class="event-badge {{ $event['type'] }}">{{ ucfirst($event['type']) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Teacher Spotlight + Recent Activity --}}
        <div class="grid-2">
            {{-- Teacher Spotlight --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-chalkboard-user"></i> Teacher of the Month</div>
                    <i class="fas fa-award" style="color: #f59e0b;"></i>
                </div>
                <div class="card-body-custom">
                    <div class="teacher-card">
                        <div class="teacher-avatar">
                            {{ substr($teacherOfMonth['name'], 0, 1) }}{{ substr(explode(' ', $teacherOfMonth['name'])[1] ?? '', 0, 1) }}
                        </div>
                        <div class="teacher-info">
                            <h4>{{ $teacherOfMonth['name'] }}</h4>
                            <div class="teacher-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="color: {{ $i <= floor($teacherOfMonth['rating']) ? '#f59e0b' : '#e5e7eb' }}; font-size: 0.7rem;"></i>
                                @endfor
                                <span style="font-size: 0.7rem; margin-left: 4px;">{{ $teacherOfMonth['rating'] }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 6px;">
                                <i class="fas fa-chart-line"></i> {{ $teacherOfMonth['achievement'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-bell"></i> Recent Activity</div>
                    <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 0 3px rgba(34,197,94,0.2);"></span>
                </div>
                <div class="card-body-custom">
                    <ul class="activity-feed">
                        @foreach($schoolActivities as $activity)
                        <li class="activity-item">
                            <div class="activity-icon" style="background: {{ $activity['color'] }}1a; color: {{ $activity['color'] }};">
                                <i class="fas {{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">{{ $activity['text'] }}</div>
                                <div class="activity-time"><i class="far fa-clock"></i> {{ $activity['time'] }}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Announcements + Resource Usage --}}
        <div class="grid-2">
            {{-- Announcements --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-bullhorn"></i> Announcements</div>
                    <a href="#" class="section-link">All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body-custom">
                    @foreach($announcements as $announcement)
                    <div class="announcement-item">
                        <div class="announcement-title">
                            <i class="fas fa-circle" style="font-size: 0.5rem; color: {{ $announcement['priority'] == 'high' ? 'var(--danger)' : ($announcement['priority'] == 'normal' ? 'var(--accent)' : 'var(--text-muted)') }};"></i>
                            {{ $announcement['title'] }}
                            @if($announcement['priority'] == 'high')
                                <span class="priority-high">Urgent</span>
                            @endif
                        </div>
                        <div class="announcement-content">{{ $announcement['content'] }}</div>
                        <div class="announcement-date"><i class="far fa-calendar-alt"></i> {{ $announcement['date'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Resource Usage --}}
            <div class="card-school">
                <div class="card-header-custom">
                    <div class="section-title"><i class="fas fa-boxes"></i> Resource Utilization</div>
                </div>
                <div class="card-body-custom">
                    @foreach($resources as $resource)
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
                            <span style="font-weight: 500;">{{ $resource['name'] }}</span>
                            <span>{{ $resource['used'] }}/{{ $resource['total'] }} ({{ $resource['pct'] }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $resource['pct'] }}%; background: {{ $resource['pct'] > 80 ? 'var(--danger)' : 'var(--brand)' }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="school-footer">
        <span><i class="fas fa-shield-alt"></i> {{ $schoolName }} — School Management Portal</span>
        <span><i class="fas fa-clock"></i> Last sync: {{ now()->format('H:i:s') }}</span>
        <span><i class="fas fa-chart-line"></i> Academic Year {{ $academicYear }} | {{ $currentTerm }}</span>
    </div>
</div></div></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars on load
        const progressFills = document.querySelectorAll('.progress-fill');
        progressFills.forEach(el => {
            const targetWidth = el.style.width;
            el.style.width = '0';
            setTimeout(() => {
                el.style.width = targetWidth;
            }, 200);
        });
    });
</script>

@endsection