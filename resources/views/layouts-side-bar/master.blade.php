<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
	<!-- Meta data -->
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
	<meta content="SMASA" name="description">
	<meta name="keywords"
		content="SMASA, school management system, student information system, online school platform, school ERP, digital classroom tools, school attendance tracking, exam management system, timetable scheduling, fees management system, parent-teacher communication, learning management system, education technology, school reporting tools, smart education software, school administration platform" />
	<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

	<meta name="csrf-token" content="{{ csrf_token() }}">
	@include('layouts-side-bar.head')
</head>

<style>
	@media (max-width: 768px) {
		/*
		 * The base stylesheet (sidemenu.css) gives .app-content a hardcoded
		 * margin-top: 50px to clear the fixed .app-header, assuming a fixed
		 * 50px header height. On mobile our header wraps onto multiple lines
		 * (school dropdown, notifications, badge, settings) and is taller
		 * than 50px, so header.blade.php measures the real rendered height
		 * and exposes it as --app-header-h, applied below as padding-top on
		 * .side-app. Without resetting the old margin-top here, both offsets
		 * stack and create a large empty gap above the page content.
		 */
		.app-content {
			margin-top: 0 !important;
		}

		/*
		 * IMPORTANT: scoped to the DIRECT child of .app-content, not a bare
		 * ".side-app" selector. Many page views (73 of 153 at last count —
		 * everything under Examination, Attendance, Exam, itemGrading, etc.)
		 * mistakenly wrap their own @section('content') in an extra,
		 * redundant <div class="side-app">, duplicating the one this master
		 * layout already provides. A bare ".side-app" selector would apply
		 * this dynamic header-height offset to BOTH the real outer wrapper
		 * and any such nested duplicate, stacking the gap a second time.
		 * Scoping to the direct child means only the genuine outer wrapper
		 * gets the offset; a stray nested duplicate just falls back to the
		 * small static padding in sidemenu.css, which is harmless.
		 */
		.app-content > .side-app {
			padding-top: var(--app-header-h, 1px) !important;
		}
	}
</style>

<body class="app sidebar-mini light-mode default-sidebar">
	<!---Global-loader-->
	<div id="global-loader">
		<img src="{{URL::asset('assets/images/svgs/loader.svg')}}" alt="loader">
	</div>

	<div class="page">
		<div class="page-main">
			@include('layouts-side-bar.side-menu')
			<div class="app-content main-content">
				<div class="side-app">
					@include('layouts-side-bar.header')
					@yield('page-header')
					@yield('content')
					@include('layouts-side-bar.footer')
				</div><!-- End Page -->
				@include('layouts-side-bar.footer-scripts')
				@if(session('LoggedAdmin') || session('LoggedTeacher'))
					<script src="{{ asset('js/push-init.js') }}"></script>
				@endif
</body>

</html>