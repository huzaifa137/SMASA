@extends('parents.layout')

@section('title', $student->firstname . ' — Results')

@section('content')
    @include('parents.partials.child-nav', ['active' => 'results'])

    <div class="pp-card">
        <div style="font-weight:700;margin-bottom:1rem;"><i class="fas fa-file-lines me-1" style="color:var(--brand);"></i> Released Exam Results</div>

        @if ($exams->isEmpty())
            <div class="pp-empty">
                <i class="fas fa-inbox"></i>
                <h6>No results released yet</h6>
                <p class="mb-0">Once the school releases results for {{ $student->firstname }}'s class, they'll show up here.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="pp-table">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Term</th>
                            <th>Academic Year</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($exams as $exam)
                            <tr>
                                <td>
                                    <strong>{{ $exam->exam_name }}</strong>
                                    <div style="font-size:0.72rem;color:var(--gray-500);">{{ $exam->exam_code }}</div>
                                </td>
                                <td>{{ $exam->term }}</td>
                                <td>{{ $exam->academic_year }}</td>
                                <td style="text-align:right;">
                                    <a href="{{ route('parents.result.view', [$student->id, $exam->id]) }}" class="pp-btn pp-btn-primary">
                                        <i class="fas fa-eye"></i> View Report
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
