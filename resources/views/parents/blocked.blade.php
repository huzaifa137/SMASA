@extends('parents.layout')

@section('title', $student->firstname . ' — Unavailable')

@section('content')
    @include('parents.partials.child-nav', ['active' => $section === 'fees' ? 'finance' : $section])

    <div class="pp-card">
        <div class="pp-empty">
            <i class="fas fa-lock"></i>
            <h6>Not available right now</h6>
            <p class="mb-0">{{ $message }}</p>
        </div>
    </div>
@endsection
