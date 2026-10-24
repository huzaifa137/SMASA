{{-- resources/views/GeneralSchools/no-access.blade.php --}}
@extends('layouts-side-bar.master')
@section('content')
    <div class="container py-5" style="max-width:640px;">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm" style="border-radius:16px;border:0;">
            <div class="card-body text-center p-5">
                <div style="font-size:3rem;color:#f59e0b;"><i class="fas fa-lock"></i></div>
                <h3 class="mt-3 mb-2">No pages assigned yet</h3>
                <p class="text-muted mb-1">
                    Your role (<strong>{{ $roleName }}</strong>) does not give you access to any page yet.
                </p>
                <p class="text-muted">
                    Please ask your school administrator to grant you access under
                    <em>User Rights &amp; Permissions</em>.
                </p>
                <a href="{{ url('/logout') }}" class="btn btn-outline-secondary mt-3">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </a>
            </div>
        </div>
    </div>
@endsection
