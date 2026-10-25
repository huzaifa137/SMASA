@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    <div class="st-page">
    @include('Admin.sales-team._hero', [
        'features' => [['fa-user-plus','#34d399','Ask an admin','To add you as a representative'],['fa-school','#818cf8','Then you will see','Your schools and balances'],['fa-bullseye','#fbbf24','And your targets','With live pacing'],['fa-chart-column','#f472b6','And your trend','Month by month']],'title' => 'My performance', 'subtitle' => 'Your schools, targets and collections', 'icon' => 'fa-user-check'])
    <div class="st-card"><div class="st-empty"><i class="fas fa-user-slash"></i>
        You have not been set up as a sales representative yet.<br>Ask an administrator to add you under Sales Team → Representatives.
        @if($canTeam)<br><a href="{{ route('sales.reps') }}">Go to Representatives</a>@endif</div></div>
    </div>
    </div>
    </div>
    </div>
@endsection
