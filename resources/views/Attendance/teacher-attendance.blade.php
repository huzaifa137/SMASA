@extends('layouts-side-bar.master')
@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
   :root {
   --brand: #5351e4;
   --brand-light: #2C29CA;
   --brand-dark: #2C29CA;
   --brand-muted: rgba(83, 81, 228, 0.08);
   --success: #10b981;
   --success-muted: rgba(16, 185, 129, 0.1);
   --warning: #f59e0b;
   --warning-muted: rgba(245, 158, 11, 0.1);
   --danger: #ef4444;
   --danger-muted: rgba(239, 68, 68, 0.1);
   --info: #3b82f6;
   --info-muted: rgba(59, 130, 246, 0.1);
   --purple: #8b5cf6;
   --purple-muted: rgba(139, 92, 246, 0.1);
   --gray: #64748b;
   --gray-muted: rgba(100, 116, 139, 0.1);
   --text-primary: #1e293b;
   --text-secondary: #475569;
   --text-muted: #94a3b8;
   --border-light: #e2e8f0;
   --bg-surface: #f8fafc;
   }
   body {
   background: #f1f5f9;
   }
   /* Modern Glass Header */
   .hero-section {
   background: linear-gradient(135deg, #5351e4 0%, #2C29CA 100%);
   border-radius: 28px;
   padding: 2rem 2rem;
   margin-bottom: 2rem;
   position: relative;
   overflow: hidden;
   box-shadow: 0 20px 35px -12px rgba(83, 81, 228, 0.3);
   }
   .hero-section::before {
   content: '';
   position: absolute;
   top: -30%;
   right: -10%;
   width: 250px;
   height: 250px;
   background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
   border-radius: 50%;
   }
   .hero-section::after {
   content: '';
   position: absolute;
   bottom: -20%;
   left: -5%;
   width: 200px;
   height: 200px;
   background: radial-gradient(circle, rgba(108, 63, 197, 0.15) 0%, transparent 70%);
   border-radius: 50%;
   }
   /* Stats Grid */
   .stats-grid {
   display: grid;
   grid-template-columns: repeat(6, 1fr);
   gap: 1rem;
   margin-bottom: 1.5rem;
   }
   .stat-card-modern {
   background: white;
   border-radius: 20px;
   padding: 1rem 0.75rem;
   text-align: center;
   transition: all 0.3s ease;
   border: 1px solid rgba(83, 81, 228, 0.08);
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
   }
   .stat-card-modern:hover {
   transform: translateY(-2px);
   box-shadow: 0 8px 20px rgba(83, 81, 228, 0.1);
   border-color: rgba(83, 81, 228, 0.15);
   }
   .stat-value {
   font-size: 1.8rem;
   font-weight: 800;
   line-height: 1.2;
   }
   .stat-label {
   font-size: 0.7rem;
   color: var(--text-muted);
   margin-top: 0.25rem;
   font-weight: 500;
   }
   /* Toolbar */
   .toolbar-modern {
   background: white;
   border-radius: 20px;
   padding: 1rem 1.25rem;
   margin-bottom: 1.5rem;
   display: flex;
   gap: 1rem;
   align-items: center;
   flex-wrap: wrap;
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
   border: 1px solid rgba(83, 81, 228, 0.08);
   }
   .search-wrapper {
   flex: 1;
   min-width: 220px;
   position: relative;
   }
   .search-wrapper i {
   position: absolute;
   left: 12px;
   top: 50%;
   transform: translateY(-50%);
   color: var(--text-muted);
   font-size: 0.85rem;
   }
   .search-wrapper input {
   width: 100%;
   padding: 0.6rem 0.75rem 0.6rem 2.2rem;
   border: 1px solid var(--border-light);
   border-radius: 14px;
   font-size: 0.85rem;
   transition: all 0.2s ease;
   }
   .search-wrapper input:focus {
   outline: none;
   border-color: var(--brand);
   box-shadow: 0 0 0 3px var(--brand-muted);
   }
   .filter-select {
   padding: 0.6rem 1rem;
   border: 1px solid var(--border-light);
   border-radius: 14px;
   font-size: 0.85rem;
   background: white;
   cursor: pointer;
   transition: all 0.2s ease;
   }
   .filter-select:focus {
   outline: none;
   border-color: var(--brand);
   }
   .btn-mark-all {
   background: linear-gradient(135deg, var(--brand), var(--brand-light));
   color: white;
   border: none;
   border-radius: 14px;
   padding: 0.6rem 1.2rem;
   font-size: 0.8rem;
   font-weight: 600;
   cursor: pointer;
   transition: all 0.2s ease;
   display: inline-flex;
   align-items: center;
   gap: 0.5rem;
   }
   .btn-mark-all:hover {
   transform: translateY(-2px);
   box-shadow: 0 6px 14px rgba(83, 81, 228, 0.3);
   }
   .auto-save-indicator {
   display: flex;
   align-items: center;
   gap: 0.5rem;
   font-size: 0.75rem;
   color: var(--text-muted);
   background: var(--brand-muted);
   padding: 0.4rem 0.9rem;
   border-radius: 99px;
   }
   .auto-save-dot {
   width: 8px;
   height: 8px;
   border-radius: 50%;
   background: var(--success);
   display: inline-block;
   }
   .auto-saving .auto-save-dot {
   background: var(--warning);
   animation: pulse 1s infinite;
   }
   @keyframes pulse {
   0%, 100% { opacity: 1; }
   50% { opacity: 0.4; }
   }
   /* Teacher Grid */
   .teacher-grid {
   display: grid;
   grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
   gap: 1.25rem;
   margin-bottom: 6rem;
   }
   /* Teacher Card */
   .teacher-card-modern {
   background: white;
   border-radius: 24px;
   overflow: hidden;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
   border: 1px solid rgba(83, 81, 228, 0.08);
   position: relative;
   }
   .teacher-card-modern:hover {
   transform: translateY(-4px);
   box-shadow: 0 12px 24px rgba(83, 81, 228, 0.12);
   border-color: rgba(83, 81, 228, 0.2);
   }
   /* Status border accents */
   .teacher-card-modern.status-present { border-left: 4px solid var(--success); }
   .teacher-card-modern.status-absent { border-left: 4px solid var(--danger); }
   .teacher-card-modern.status-late { border-left: 4px solid var(--warning); }
   .teacher-card-modern.status-on_leave { border-left: 4px solid var(--info); }
   .teacher-card-modern.status-half_day { border-left: 4px solid var(--purple); }
   .teacher-card-modern.status-excused { border-left: 4px solid var(--gray); }
   /* Card Content */
   .card-content {
   padding: 1.25rem;
   }
   .teacher-header {
   display: flex;
   align-items: center;
   gap: 1rem;
   margin-bottom: 1rem;
   }
   .teacher-avatar {
   width: 52px;
   height: 52px;
   border-radius: 20px;
   background: linear-gradient(135deg, var(--brand), var(--brand-light));
   color: white;
   font-size: 1.1rem;
   font-weight: 700;
   display: flex;
   align-items: center;
   justify-content: center;
   flex-shrink: 0;
   box-shadow: 0 4px 12px rgba(83, 81, 228, 0.2);
   }
   .teacher-info {
   flex: 1;
   }
   .teacher-name {
   font-size: 1rem;
   font-weight: 700;
   color: var(--text-primary);
   margin-bottom: 0.2rem;
   }
   .teacher-dept {
   font-size: 0.7rem;
   color: var(--text-muted);
   display: flex;
   align-items: center;
   gap: 0.5rem;
   }
   .save-badge {
   font-size: 0.65rem;
   color: var(--success);
   display: flex;
   align-items: center;
   gap: 0.3rem;
   }
   /* Status Buttons */
   .status-buttons {
   display: flex;
   gap: 0.5rem;
   margin-bottom: 1rem;
   flex-wrap: wrap;
   }
   .status-btn {
   flex: 1;
   min-width: 55px;
   padding: 0.5rem 0.25rem;
   border: 1.5px solid var(--border-light);
   border-radius: 12px;
   font-size: 0.7rem;
   font-weight: 600;
   text-align: center;
   cursor: pointer;
   background: white;
   transition: all 0.2s ease;
   color: var(--text-secondary);
   }
   .status-btn:hover {
   transform: translateY(-1px);
   }
   .status-btn.active-P { background: var(--success); color: white; border-color: var(--success); }
   .status-btn.active-A { background: var(--danger); color: white; border-color: var(--danger); }
   .status-btn.active-L { background: var(--warning); color: white; border-color: var(--warning); }
   .status-btn.active-OL { background: var(--info); color: white; border-color: var(--info); }
   .status-btn.active-HD { background: var(--purple); color: white; border-color: var(--purple); }
   .status-btn.active-EX { background: var(--gray); color: white; border-color: var(--gray); }
   .status-btn[data-v="present"]:hover { background: var(--success-muted); color: var(--success); border-color: var(--success); }
   .status-btn[data-v="absent"]:hover { background: var(--danger-muted); color: var(--danger); border-color: var(--danger); }
   .status-btn[data-v="late"]:hover { background: var(--warning-muted); color: var(--warning); border-color: var(--warning); }
   .status-btn[data-v="on_leave"]:hover { background: var(--info-muted); color: var(--info); border-color: var(--info); }
   .status-btn[data-v="half_day"]:hover { background: var(--purple-muted); color: var(--purple); border-color: var(--purple); }
   .status-btn[data-v="excused"]:hover { background: var(--gray-muted); color: var(--gray); border-color: var(--gray); }
   /* Time Inputs */
   .time-inputs {
   display: flex;
   gap: 0.75rem;
   margin-bottom: 0.75rem;
   }
   .time-group {
   flex: 1;
   }
   .time-group label {
   font-size: 0.65rem;
   font-weight: 600;
   color: var(--text-muted);
   text-transform: uppercase;
   display: block;
   margin-bottom: 0.25rem;
   }
   .time-group input {
   width: 100%;
   border: 1px solid var(--border-light);
   border-radius: 12px;
   padding: 0.5rem 0.75rem;
   font-size: 0.8rem;
   transition: all 0.2s ease;
   }
   .time-group input:focus {
   outline: none;
   border-color: var(--brand);
   box-shadow: 0 0 0 3px var(--brand-muted);
   }
   /* Leave Type */
   .leave-type-row {
   margin-top: 0.75rem;
   padding-top: 0.5rem;
   border-top: 1px dashed var(--border-light);
   }
   .leave-type-row label {
   font-size: 0.65rem;
   font-weight: 600;
   color: var(--text-muted);
   text-transform: uppercase;
   display: block;
   margin-bottom: 0.25rem;
   }
   .leave-type-row select {
   width: 100%;
   border: 1px solid var(--border-light);
   border-radius: 12px;
   padding: 0.5rem 0.75rem;
   font-size: 0.8rem;
   background: white;
   cursor: pointer;
   }
   .leave-type-row select:focus {
   outline: none;
   border-color: var(--brand);
   }
   /* Bottom Save Bar */
   .save-bar {
   position: fixed;
   bottom: 0;
   left: 0;
   right: 0;
   background: white;
   border-top: 1px solid var(--border-light);
   padding: 0.9rem 2rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   flex-wrap: wrap;
   gap: 1rem;
   z-index: 100;
   box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.05);
   }
   .save-info {
   display: flex;
   align-items: center;
   gap: 0.5rem;
   font-size: 0.8rem;
   color: var(--text-muted);
   }
   .btn-bulk-save {
   background: linear-gradient(135deg, var(--brand), var(--brand-light));
   color: white;
   border: none;
   border-radius: 14px;
   padding: 0.7rem 2rem;
   font-size: 0.85rem;
   font-weight: 600;
   cursor: pointer;
   transition: all 0.2s ease;
   display: inline-flex;
   align-items: center;
   gap: 0.5rem;
   }
   .btn-bulk-save:hover {
   transform: translateY(-2px);
   box-shadow: 0 6px 20px rgba(83, 81, 228, 0.35);
   }
   /* Toast Notification */
   .toast-notification {
   position: fixed;
   top: 80px;
   right: 20px;
   background: var(--text-primary);
   color: white;
   padding: 0.75rem 1.25rem;
   border-radius: 14px;
   font-size: 0.8rem;
   font-weight: 500;
   z-index: 10000;
   transform: translateX(120%);
   transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
   box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
   }
   .toast-notification.show {
   transform: translateX(0);
   }
   .toast-notification.success {
   border-left: 4px solid var(--success);
   }
   .toast-notification.error {
   border-left: 4px solid var(--danger);
   }
   /* Header Buttons */
   .header-buttons {
   display: flex;
   gap: 0.75rem;
   flex-wrap: wrap;
   }
   .btn-glass {
   background: rgba(255, 255, 255, 0.15);
   backdrop-filter: blur(8px);
   color: white;
   border: 1px solid rgba(255, 255, 255, 0.3);
   border-radius: 14px;
   padding: 0.5rem 1.2rem;
   font-size: 0.8rem;
   font-weight: 600;
   text-decoration: none;
   display: inline-flex;
   align-items: center;
   gap: 0.5rem;
   transition: all 0.2s ease;
   }
   .btn-glass:hover {
   background: white;
   color: var(--brand);
   border-color: white;
   }
   .date-picker-glass {
   background: rgba(255, 255, 255, 0.15);
   border: 1px solid rgba(255, 255, 255, 0.3);
   border-radius: 14px;
   padding: 0.5rem 1rem;
   color: white;
   font-size: 0.85rem;
   }
   .date-picker-glass::-webkit-calendar-picker-indicator {
   filter: invert(1);
   cursor: pointer;
   }
   /* Responsive */
   @media (max-width: 1100px) {
   .stats-grid {
   grid-template-columns: repeat(3, 1fr);
   }
   }
   @media (max-width: 768px) {
   .stats-grid {
   grid-template-columns: repeat(2, 1fr);
   }
   .teacher-grid {
   grid-template-columns: 1fr;
   }
   .save-bar {
   flex-direction: column;
   text-align: center;
   }
   }
</style>
@endsection
@section('content')
<div class="side-app" style="padding: 1.5rem;">
   {{-- Hero Section --}}
   <div class="hero-section">
      <div class="row align-items-center">
         <div class="col-lg-7">
            <div class="mb-4">
               <span class="badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 0.5rem 1rem; border-radius: 99px; font-size: 1rem; color: #FFF; display: inline-block;">
               <i class="fas fa-calendar-alt me-2"></i> {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
               </span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: white; margin-bottom: 0.5rem;">
               <i class="fas fa-chalkboard-user me-3"></i> Teacher Attendance
            </h1>
            <p style="font-size: 0.95rem; color: rgba(255,255,255,0.85); margin-bottom: 0;">
               Record and manage staff attendance for today
            </p>
         </div>
         <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <div class="header-buttons" style="display: flex; gap: 1rem; justify-content: flex-end; align-items: center;">
               <input type="date" id="datePicker" value="{{ $date }}"
                  onchange="window.location.href='{{ route('attendance.teachers') }}?date='+this.value"
                  class="date-picker-glass"
                  style="padding: 0.6rem 1rem; font-size: 1rem; border-radius: 8px; border: none; background: rgba(255,255,255,0.2); color: #FFF; cursor: pointer;">
               <a href="{{ route('attendance.teachers.report') }}" class="btn-glass"
                  style="padding: 0.6rem 1.5rem; font-size: 1rem; border-radius: 8px; background: rgba(255,255,255,0.2); color: #FFF; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
               <i class="fas fa-chart-bar"></i> Report
               </a>
               <a href="{{ route('attendance.dashboard') }}" class="btn-glass"
                  style="padding: 0.6rem 1.5rem; font-size: 1rem; border-radius: 8px; background: rgba(255,255,255,0.9); color: #2C29CA; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; font-weight: 600;">
               <i class="fas fa-arrow-left"></i> Dashboard
               </a>
            </div>
         </div>
      </div>
   </div>
   {{-- Stats Cards --}}
   <div class="stats-grid">
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--text-primary);">{{ $stats['total'] }}</div>
         <div class="stat-label">Total Staff</div>
      </div>
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--success);">{{ $stats['present'] }}</div>
         <div class="stat-label">Present</div>
      </div>
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--danger);">{{ $stats['absent'] }}</div>
         <div class="stat-label">Absent</div>
      </div>
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--warning);">{{ $stats['late'] }}</div>
         <div class="stat-label">Late</div>
      </div>
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--info);">{{ $stats['on_leave'] }}</div>
         <div class="stat-label">On Leave</div>
      </div>
      <div class="stat-card-modern">
         <div class="stat-value" style="color: var(--text-muted);">{{ $stats['not_marked'] }}</div>
         <div class="stat-label">Not Marked</div>
      </div>
   </div>
   {{-- Toolbar --}}
   <div class="toolbar-modern">
      <div class="search-wrapper">
         <i class="fas fa-search"></i>
         <input type="text" id="searchTeacher" placeholder="Search teacher name..." onkeyup="filterTeachers()">
      </div>
      <select id="statusFilter" class="filter-select" onchange="filterTeachers()">
         <option value="">All Statuses</option>
         <option value="present">Present</option>
         <option value="absent">Absent</option>
         <option value="late">Late</option>
         <option value="on_leave">On Leave</option>
         <option value="half_day">Half Day</option>
         <option value="not_marked">Not Marked</option>
      </select>
      <div class="auto-save-indicator" id="autoSaveStatus">
         <span class="auto-save-dot"></span>
         <span>Auto-save enabled</span>
      </div>
      <button class="btn-mark-all" onclick="markAllPresent()" style="background: linear-gradient(135deg, #10b981, #059669);">
      <i class="fas fa-check-double"></i> All Present
      </button>
      <button class="btn-mark-all" onclick="markAllAbsent()" style="background: linear-gradient(135deg, #ef4444, #dc2626); margin-left: 0.5rem;">
      <i class="fas fa-times-circle"></i> All Absent
      </button>
      <button class="btn-mark-all" onclick="resetAll()" style="background: #64748b; margin-left: 0.5rem;">
      <i class="fas fa-undo-alt"></i> Reset
      </button>
   </div>
   {{-- Teacher Grid --}}
   <div class="teacher-grid" id="teacherGrid">
      @foreach($teachers as $teacher)
      @php
      $att = $existing->get($teacher->id);
      $status = $att ? $att->status : '';
      $arrival = $att ? ($att->arrival_time ?? '') : '';
      $departure = $att ? ($att->departure_time ?? '') : '';
      $leaveType = $att ? ($att->leave_type ?? '') : '';
      $initials = strtoupper(substr($teacher->firstname ?? '?', 0, 1)) . strtoupper(substr($teacher->surname ?? '', 0, 1));
      @endphp
      <div class="teacher-card-modern {{ $status ? 'status-'.$status : '' }}"
         data-teacher-id="{{ $teacher->id }}"
         data-name="{{ strtolower($teacher->firstname . ' ' . $teacher->surname) }}"
         data-status="{{ $status ?: 'not_marked' }}"
         id="tc-{{ $teacher->id }}">
         <div class="card-content">
            <div class="teacher-header">
               <div class="teacher-avatar">{{ $initials }}</div>
               <div class="teacher-info">
                  <div class="teacher-name">{{ $teacher->firstname }} {{ $teacher->surname }}</div>
                  <div class="teacher-dept">
                     <i class="fas fa-phone-alt" style="font-size: 0.6rem;"></i>
                     {{ $teacher->phonenumber ?? 'No phone' }}
                  </div>
               </div>
               <div class="save-badge" id="saved-{{ $teacher->id }}" style="display: {{ $att ? 'flex' : 'none' }}">
                  <i class="fas fa-check-circle"></i> Saved
               </div>
            </div>
            <div class="status-buttons">
               @foreach([
               ['v'=>'present', 'l'=>'Present', 'cls'=>'P'],
               ['v'=>'absent', 'l'=>'Absent', 'cls'=>'A'],
               ['v'=>'late', 'l'=>'Late', 'cls'=>'L'],
               ['v'=>'on_leave', 'l'=>'Leave', 'cls'=>'OL'],
               ['v'=>'half_day', 'l'=>'Half', 'cls'=>'HD'],
               ['v'=>'excused', 'l'=>'Excused', 'cls'=>'EX'],
               ] as $s)
               <button type="button"
                  class="status-btn {{ $status === $s['v'] ? 'active-'.$s['cls'] : '' }}"
                  data-v="{{ $s['v'] }}"
                  data-cls="{{ $s['cls'] }}"
                  onclick="selectStatus({{ $teacher->id }}, '{{ $s['v'] }}', '{{ $s['cls'] }}', this)">
               {{ $s['l'] }}
               </button>
               @endforeach
            </div>
            <div class="time-inputs">
               <div class="time-group">
                  <label><i class="fas fa-clock"></i> Arrival</label>
                  <input type="time" id="arr-{{ $teacher->id }}" value="{{ $arrival }}" onchange="autoSave({{ $teacher->id }})">
               </div>
               <div class="time-group">
                  <label><i class="fas fa-hourglass-end"></i> Departure</label>
                  <input type="time" id="dep-{{ $teacher->id }}" value="{{ $departure }}" onchange="autoSave({{ $teacher->id }})">
               </div>
            </div>
            <div class="leave-type-row" id="leave-row-{{ $teacher->id }}" style="{{ $status === 'on_leave' ? '' : 'display:none' }}">
               <label><i class="fas fa-umbrella-beach"></i> Leave Type</label>
               <select id="leave-{{ $teacher->id }}" onchange="autoSave({{ $teacher->id }})">
                  <option value="">Select type...</option>
                  <option value="sick" {{ $leaveType === 'sick' ? 'selected' : '' }}>🏥 Sick Leave</option>
                  <option value="annual" {{ $leaveType === 'annual' ? 'selected' : '' }}>🌴 Annual Leave</option>
                  <option value="official" {{ $leaveType === 'official' ? 'selected' : '' }}>📋 Official Duty</option>
                  <option value="maternity" {{ $leaveType === 'maternity' ? 'selected' : '' }}>👶 Maternity Leave</option>
                  <option value="other" {{ $leaveType === 'other' ? 'selected' : '' }}>📝 Other</option>
               </select>
            </div>
            <div style="margin-top: 1rem;">
               <button class="btn-save-modern" 
                  onclick="saveTeacherAttendance(this, {{ $teacher->id }})"
                  style="width: 100%; justify-content: center; border: none; cursor: pointer; border-radius: 60px; padding: 0.65rem 1.5rem; font-size: 0.8rem; font-weight: 700; color: white; background: linear-gradient(135deg, var(--brand), var(--brand-light)); display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(83,81,228,0.2);">
               <i class="fas fa-cloud-upload-alt"></i>
               <span>Save Attendance</span>
               </button>
            </div>
         </div>
      </div>
      @endforeach
      {{-- REPLACE the existing 
      <form>
      bulk block with this --}}
      <div class="save-actions-stats mt-4"
     id="saveAllContainer"
     style="text-align: left;">

    <button class="btn-save-modern"
        id="saveAllBtn"
        style="
            justify-content: center;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 60px;
            padding: 0.85rem 2rem;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 12px rgba(16,185,129,0.25);
        ">

        <i class="fas fa-cloud-upload-alt"></i>

        <span>Save All Teacher Attendance</span>

        <span id="pendingCount"
            style="
                background: rgba(255,255,255,0.2);
                border-radius: 40px;
                padding: 0.35rem 1rem;
                font-size: 0.7rem;
                font-weight: 600;
                margin-left: 0.5rem;
            ">
            0 pending
        </span>

    </button>
</div>
   </div>
</div>
</div>
</div>
</div>
{{-- Toast Notification --}}
<div class="toast-notification" id="taToast"></div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   // ── State stores ──────────────────────────────────────────────────
   let teacherMap     = {};   // current in-memory state
   let originalMap    = {};   // state as loaded from DB
   
   // ── Bootstrap from server-rendered data ──────────────────────────
   function storeOriginalState() {
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           const id = card.dataset.teacherId;
           if (!id) return;
   
           const status    = card.dataset.status === 'not_marked' ? '' : (card.dataset.status || '');
           const arrival   = document.getElementById('arr-' + id)?.value  || '';
           const departure = document.getElementById('dep-' + id)?.value  || '';
           const leaveType = document.getElementById('leave-' + id)?.value || '';
   
           originalMap[id] = { status, arrival, departure, leaveType };
           teacherMap[id]  = { status, arrival, departure, leaveType };
       });
   
       console.log('Teacher original state stored:', originalMap);
   }
   
   // ── Change detection ──────────────────────────────────────────────
   function checkForChanges() {
       let changedCount = 0;
   
       for (let id in teacherMap) {
           const orig = originalMap[id];
           const curr = teacherMap[id];
           if (!orig) continue;
   
           const changed = orig.status    !== curr.status    ||
                           orig.arrival   !== curr.arrival   ||
                           orig.departure !== curr.departure  ||
                           orig.leaveType !== curr.leaveType;
   
           const card = document.querySelector(`.teacher-card-modern[data-teacher-id="${id}"]`);
           if (changed) {
               changedCount++;
               if (card && !card.classList.contains('has-changes')) {
                   card.classList.add('has-changes');
               }
           } else {
               if (card) card.classList.remove('has-changes');
           }
       }
   
       // Update pending badge
       const badge = document.getElementById('pendingCount');
       if (badge) {
           if (changedCount > 0) {
               badge.textContent = `${changedCount} pending`;
               badge.style.background = 'rgba(245, 158, 11, 0.3)';
               badge.style.color = '#f59e0b';
           } else {
               badge.textContent = 'All saved';
               badge.style.background = 'rgba(16, 185, 129, 0.2)';
               badge.style.color = '#FFF';
           }
       }
   
       return changedCount;
   }
   
   // ── Status selection ──────────────────────────────────────────────
   function selectStatus(id, status, cls, btn) {
       const card = document.getElementById('tc-' + id);
       const strId = String(id);
   
       // Deactivate all pills
       card.querySelectorAll('.status-btn').forEach(b => {
           b.classList.remove('active-P', 'active-A', 'active-L', 'active-OL', 'active-HD', 'active-EX');
       });
       btn.classList.add('active-' + cls);
   
       // Update card border class
       ['status-present','status-absent','status-late','status-on_leave','status-half_day','status-excused']
           .forEach(sc => card.classList.remove(sc));
       if (status) card.classList.add('status-' + status);
   
       card.dataset.status = status;
   
       // Update state map
       if (!teacherMap[strId]) teacherMap[strId] = {};
       teacherMap[strId].status = status;
   
       // Show/hide leave type row
       const leaveRow = document.getElementById('leave-row-' + id);
       if (leaveRow) leaveRow.style.display = status === 'on_leave' ? 'flex' : 'none';
   
       // Default arrival time for late
       if (status === 'late') {
           const arrInput = document.getElementById('arr-' + id);
           if (arrInput && !arrInput.value) {
               arrInput.value = new Date().toTimeString().slice(0, 5);
               teacherMap[strId].arrival = arrInput.value;
           }
       }
   
       checkForChanges();
   }
   
   // ── Input change handlers ─────────────────────────────────────────
   function setupInputListeners() {
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           const id = card.dataset.teacherId;
           if (!id) return;
   
           const arrInput  = document.getElementById('arr-' + id);
           const depInput  = document.getElementById('dep-' + id);
           const leaveInput = document.getElementById('leave-' + id);
   
           if (arrInput) arrInput.addEventListener('change', () => {
               teacherMap[id].arrival = arrInput.value;
               checkForChanges();
           });
   
           if (depInput) depInput.addEventListener('change', () => {
               teacherMap[id].departure = depInput.value;
               checkForChanges();
           });
   
           if (leaveInput) leaveInput.addEventListener('change', () => {
               teacherMap[id].leaveType = leaveInput.value;
               checkForChanges();
           });
       });
   }
   
   // ── Individual save ───────────────────────────────────────────────
   async function saveTeacherAttendance(btn, teacherId) {
       const id  = String(teacherId);
       const curr = teacherMap[id];
       const orig = originalMap[id];
   
       if (!curr || !curr.status) {
           showToast('Please select a status first', 'error');
           return;
       }
   
       const isChanged = !orig ||
                         orig.status    !== curr.status    ||
                         orig.arrival   !== curr.arrival   ||
                         orig.departure !== curr.departure  ||
                         orig.leaveType !== curr.leaveType;
   
       if (!isChanged) {
           showToast('No changes to save for this teacher', 'info');
           return;
       }
   
       btn.disabled = true;
       const origHTML = btn.innerHTML;
       btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
   
       try {
           const resp = await fetch('{{ route('attendance.teachers.save') }}', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
               body: JSON.stringify({
                   teacher_id:      teacherId,
                   attendance_date: '{{ $date }}',
                   status:          curr.status,
                   arrival_time:    curr.arrival   || null,
                   departure_time:  curr.departure || null,
                   leave_type:      curr.leaveType || null,
               })
           });
   
           const data = await resp.json();
   
           if (data.success) {
               showToast('✓ Saved!', 'success');
               btn.innerHTML = '<i class="fas fa-check-circle"></i> Saved!';
   
               // Update original to match saved state
               originalMap[id] = { ...curr };
   
               // Remove change indicator
               const card = document.querySelector(`.teacher-card-modern[data-teacher-id="${id}"]`);
               if (card) card.classList.remove('has-changes');
   
               // Show saved badge
               const savedBadge = document.getElementById('saved-' + teacherId);
               if (savedBadge) {
                   savedBadge.style.display = 'flex';
                   savedBadge.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
               }
   
               checkForChanges();
               setTimeout(() => { btn.innerHTML = origHTML; }, 2000);
           } else {
               throw new Error(data.message || 'Save failed');
           }
       } catch(e) {
           showToast('✗ ' + e.message, 'error');
           btn.innerHTML = origHTML;
       } finally {
           btn.disabled = false;
       }
   }
   
   // ── Save All ──────────────────────────────────────────────────────
   async function saveAllTeachers() {
       const saveAllBtn = document.getElementById('saveAllBtn');
       if (!saveAllBtn) return;
   
       const origHTML = saveAllBtn.innerHTML;
   
       // Collect changed records
       const changed = [];
       for (let id in teacherMap) {
           const orig = originalMap[id];
           const curr = teacherMap[id];
   
           if (!curr.status) continue; // skip unmarked
   
           const isChanged = !orig ||
                             orig.status    !== curr.status    ||
                             orig.arrival   !== curr.arrival   ||
                             orig.departure !== curr.departure  ||
                             orig.leaveType !== curr.leaveType;
   
           if (isChanged) {
               changed.push({ id, ...curr });
           }
       }
   
       if (changed.length === 0) {
           showToast('No changes to save', 'info');
           return;
       }
   
       const result = await Swal.fire({
           title: 'Save Changes?',
           text: `Save attendance for ${changed.length} teacher(s)?`,
           icon: 'warning',
           showCancelButton: true,
           confirmButtonText: 'Yes, Save All',
           cancelButtonText: 'Cancel',
           confirmButtonColor: '#10b981',
           cancelButtonColor: '#ef4444'
       });
   
       if (!result.isConfirmed) return;
   
       saveAllBtn.disabled = true;
       saveAllBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Saving ${changed.length} records...`;
   
       let successCount = 0;
       let failCount    = 0;
   
       // Save each changed record (sequential to avoid overwhelming server)
       for (const rec of changed) {
           try {
               const resp = await fetch('{{ route('attendance.teachers.save') }}', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': '{{ csrf_token() }}'
                   },
                   body: JSON.stringify({
                       teacher_id:      parseInt(rec.id),
                       attendance_date: '{{ $date }}',
                       status:          rec.status,
                       arrival_time:    rec.arrival   || null,
                       departure_time:  rec.departure || null,
                       leave_type:      rec.leaveType || null,
                   })
               });
   
               const data = await resp.json();
   
               if (data.success) {
                   successCount++;
                   // Update original map
                   originalMap[rec.id] = {
                       status:    rec.status,
                       arrival:   rec.arrival,
                       departure: rec.departure,
                       leaveType: rec.leaveType
                   };
   
                   // Update UI
                   const card = document.querySelector(`.teacher-card-modern[data-teacher-id="${rec.id}"]`);
                   if (card) card.classList.remove('has-changes');
   
                   const savedBadge = document.getElementById('saved-' + rec.id);
                   if (savedBadge) {
                       savedBadge.style.display = 'flex';
                       savedBadge.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
                   }
               } else {
                   failCount++;
               }
           } catch(e) {
               failCount++;
           }
       }
   
       checkForChanges();
   
       if (failCount === 0) {
           showToast(`✓ All ${successCount} records saved!`, 'success');
           saveAllBtn.innerHTML = '<i class="fas fa-check-circle"></i> All Saved!';
       } else {
           showToast(`✓ ${successCount} saved, ✗ ${failCount} failed`, 'error');
           saveAllBtn.innerHTML = origHTML;
       }
   
       saveAllBtn.disabled = false;
       setTimeout(() => { saveAllBtn.innerHTML = origHTML; }, 2500);
   }
   
   // ── Bulk mark all present ─────────────────────────────────────────
   function markAllPresent() {
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           if (card.style.display === 'none') return;
           const id  = parseInt(card.dataset.teacherId);
           const btn = card.querySelector('[data-v="present"]');
           if (btn) selectStatus(id, 'present', 'P', btn);
       });
   }
   
   // ── Bulk mark all absent ──────────────────────────────────────────
   function markAllAbsent() {
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           if (card.style.display === 'none') return;
           const id  = parseInt(card.dataset.teacherId);
           const btn = card.querySelector('[data-v="absent"]');
           if (btn) selectStatus(id, 'absent', 'A', btn);
       });
   }
   
   // ── Reset to original DB state ────────────────────────────────────
   function resetAll() {
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           if (card.style.display === 'none') return;
           const id = card.dataset.teacherId;
           if (!id) return;
   
           const orig = originalMap[id];
           if (!orig) return;
   
           const origStatus = orig.status || '';
   
           // Reset card border
           ['status-present','status-absent','status-late','status-on_leave','status-half_day','status-excused']
               .forEach(sc => card.classList.remove(sc));
           if (origStatus) card.classList.add('status-' + origStatus);
           card.dataset.status = origStatus || 'not_marked';
   
           // Reset status buttons
           card.querySelectorAll('.status-btn').forEach(b => {
               b.classList.remove('active-P','active-A','active-L','active-OL','active-HD','active-EX');
           });
   
           if (origStatus) {
               const clsMap = {
                   present: 'P', absent: 'A', late: 'L',
                   on_leave: 'OL', half_day: 'HD', excused: 'EX'
               };
               const activeCls = clsMap[origStatus];
               if (activeCls) {
                   const btn = card.querySelector(`[data-v="${origStatus}"]`);
                   if (btn) btn.classList.add('active-' + activeCls);
               }
           }
   
           // Reset time/leave inputs
           const arrInput   = document.getElementById('arr-'   + id);
           const depInput   = document.getElementById('dep-'   + id);
           const leaveInput = document.getElementById('leave-' + id);
           if (arrInput)   arrInput.value   = orig.arrival   || '';
           if (depInput)   depInput.value   = orig.departure || '';
           if (leaveInput) leaveInput.value = orig.leaveType || '';
   
           // Show/hide leave row
           const leaveRow = document.getElementById('leave-row-' + id);
           if (leaveRow) leaveRow.style.display = origStatus === 'on_leave' ? 'flex' : 'none';
   
           // Restore teacherMap
           teacherMap[id] = { ...orig };
       });
   
       checkForChanges();
       showToast('Reset to original saved state', 'info');
   }
   
   // ── Search & filter ───────────────────────────────────────────────
   function filterTeachers() {
       const query        = document.getElementById('searchTeacher').value.toLowerCase();
       const statusFilter = document.getElementById('statusFilter').value;
   
       document.querySelectorAll('.teacher-card-modern').forEach(card => {
           const nameMatch   = card.dataset.name.includes(query);
           const statusMatch = !statusFilter || card.dataset.status === statusFilter;
           card.style.display = nameMatch && statusMatch ? '' : 'none';
       });
   }
   
   // ── Toast ─────────────────────────────────────────────────────────
   function showToast(message, type = 'success') {
       const toast = document.getElementById('taToast');
       if (!toast) return;
       toast.textContent = message;
       toast.className = `toast-notification show ${type}`;
       clearTimeout(window._taTimer);
       window._taTimer = setTimeout(() => toast.classList.remove('show'), 3000);
   }
   
   // ── CSS for has-changes indicator ─────────────────────────────────
   const taStyle = document.createElement('style');
   taStyle.textContent = `
       .teacher-card-modern.has-changes {
           border: 2px solid #f59e0b !important;
           animation: teacherGlow 1.5s ease-in-out infinite;
       }
       @keyframes teacherGlow {
           0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
           50%       { box-shadow: 0 0 0 4px rgba(245,158,11,0.2); }
       }
       .teacher-card-modern.has-changes::after {
           content: '⚠️';
           position: absolute;
           top: 10px; right: 10px;
           font-size: 1.1rem;
           z-index: 10;
           background: white;
           border-radius: 50%;
           width: 26px; height: 26px;
           display: flex; align-items: center; justify-content: center;
           box-shadow: 0 2px 8px rgba(0,0,0,0.1);
       }
   `;
   document.head.appendChild(taStyle);
   
   // ── Init ──────────────────────────────────────────────────────────
   document.addEventListener('DOMContentLoaded', function () {
       storeOriginalState();
       setupInputListeners();
       checkForChanges();
   
       document.getElementById('saveAllBtn')?.addEventListener('click', saveAllTeachers);
   
       // Warn before leaving with unsaved changes
       window.addEventListener('beforeunload', (e) => {
           if (checkForChanges() > 0) {
               e.preventDefault();
               e.returnValue = 'You have unsaved teacher attendance changes.';
           }
       });
   });
   
   // Make globally available
   window.selectStatus    = selectStatus;
   window.saveTeacherAttendance = saveTeacherAttendance;
   window.markAllPresent  = markAllPresent;
   window.markAllAbsent   = markAllAbsent;
   window.resetAll        = resetAll;
   window.filterTeachers  = filterTeachers;
</script>