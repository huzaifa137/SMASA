@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @include('Admin.sales-team._hero', ['title' => 'My performance', 'subtitle' => 'Your schools, targets and collections', 'icon' => 'fa-user-check'])
    <div class="st-card"><div class="st-empty"><i class="fas fa-user-slash"></i>
        You have not been set up as a sales representative yet.<br>Ask an administrator to add you under Sales Team → Representatives.
        @if($canTeam)<br><a href="{{ route('sales.reps') }}">Go to Representatives</a>@endif</div></div>
@endsection
