<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<!-- Meta data -->
	<meta content="SMASA" name="description">
<meta name="keywords" content="SMASA, school management system, student information system, online school platform, school ERP, digital classroom tools, school attendance tracking, exam management system, timetable scheduling, fee management system, parent-teacher communication, learning management system, education technology, school reporting tools, smart education software, school administration platform" />

        <meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
		@include('layouts-side-bar.custom-head')
		
	</head>
		
	<body class="h-100vh page-style1 light-mode default-sidebar">	    
		@yield('content')		
		@include('layouts-side-bar.custom-footer-scripts')	
		@if(session('LoggedAdmin') || session('LoggedTeacher'))
<script src="{{ asset('js/push-init.js') }}"></script>
@endif
	</body>
</html>