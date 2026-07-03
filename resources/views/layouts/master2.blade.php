<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- Meta data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta content="SMASA" name="description">
    <meta content="Spruko Technologies Private Limited" name="author">
    <meta name="keywords" content="UP" />
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @include('layouts.custom-head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>


<body class="h-100vh page-style1 light-mode">
    @yield('content')
    @include('layouts.custom-footer-scripts')

    @if(session('LoggedAdmin') || session('LoggedTeacher'))
<script src="{{ asset('js/push-init.js') }}"></script>
@endif
</body>

</html>
