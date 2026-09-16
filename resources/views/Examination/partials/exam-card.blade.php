<div class="exam-card status-{{ $exam->status }}" data-id="{{ $exam->id }}" onclick="viewExamDetails({{ $exam->id }})">
    @php
        // Secondary O-Level (Senior 1-4) only needs a Create Assessment
        // step before marks entry — see NlscAssessmentController's own
        // docblock. School-wide count (every teacher), since this board
        // is the admin's view of the whole exam, not one teacher's.
        $pendingNlscForExam = in_array($exam->status, ['active', 'marks_entry'])
            ? \App\Http\Controllers\Helper::pendingNlscAssessmentsCountForExam($exam->id)
            : 0;
    @endphp
    <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="exam-code-badge">
            <i class="fas fa-qrcode me-1"></i> {{ $exam->exam_code }}
        </span>
        <div class="dropdown">
            <button class="btn btn-sm" style="background: none;" onclick="event.stopPropagation();"
                data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                @if($exam->status === 'draft')
                    <li><a class="dropdown-item" href="#"
                            onclick="event.stopPropagation(); updateExamStatus({{ $exam->id }}, 'active')">
                            <i class="fas fa-play text-success me-2"></i> Activate
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="#"
                            onclick="event.stopPropagation(); deleteExam({{ $exam->id }})">
                            <i class="fas fa-trash me-2"></i> Delete
                        </a></li>
                @elseif($exam->status === 'active')
                    @if($pendingNlscForExam > 0)
                        <li><a class="dropdown-item" href="{{ route('nlsc-assessments.pending', ['examination_id' => $exam->id]) }}"
                                onclick="event.stopPropagation();">
                                <i class="fas fa-clipboard-list text-danger me-2"></i> Create Assessment
                                <span class="badge badge-danger ms-1">{{ $pendingNlscForExam }}</span>
                            </a></li>
                    @endif
                    <li><a class="dropdown-item" href="#"
                            onclick="event.stopPropagation(); updateExamStatus({{ $exam->id }}, 'marks_entry')">
                            <i class="fas fa-pen-alt text-warning me-2"></i> Open Marks Entry
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('examination.marks.entry', $exam->id) }}"
                            onclick="event.stopPropagation();">
                            <i class="fas fa-table text-primary me-2"></i> Enter Marks
                        </a></li>
                @elseif($exam->status === 'marks_entry')
                    @if($pendingNlscForExam > 0)
                        <li><a class="dropdown-item" href="{{ route('nlsc-assessments.pending', ['examination_id' => $exam->id]) }}"
                                onclick="event.stopPropagation();">
                                <i class="fas fa-clipboard-list text-danger me-2"></i> Create Assessment
                                <span class="badge badge-danger ms-1">{{ $pendingNlscForExam }}</span>
                            </a></li>
                    @endif
                    <li><a class="dropdown-item" href="#"
                            onclick="event.stopPropagation(); updateExamStatus({{ $exam->id }}, 'closed')">
                            <i class="fas fa-lock text-danger me-2"></i> Close Exam
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('examination.marks.entry', $exam->id) }}"
                            onclick="event.stopPropagation();">
                            <i class="fas fa-table text-primary me-2"></i> Enter Marks
                        </a></li>
                @elseif($exam->status === 'closed')
                    <li><a class="dropdown-item" href="#"
                            onclick="event.stopPropagation(); updateExamStatus({{ $exam->id }}, 'results_released')">
                            <i class="fas fa-award text-purple me-2"></i> Release Results
                        </a></li>
                @endif

                @if(in_array($exam->status, ['closed', 'results_released']))
    <a href="{{ route('examination.passslips.index', $exam->id) }}"
       class="btn btn-sm fw-semibold"
       style="background: linear-gradient(135deg, #2C29CA, #5351e4);
              color: #fff; border-radius: .6rem; font-size: .75rem;">
        <i class="fas fa-id-card me-1"></i> Pass Slips
    </a>
@endif
            </ul>
        </div>
    </div>

    <h6 class="fw-bold mb-2" style="font-size: 0.95rem;">{{ $exam->exam_name }}</h6>

    @if($pendingNlscForExam > 0)
        <a href="{{ route('nlsc-assessments.pending', ['examination_id' => $exam->id]) }}"
           class="d-inline-flex align-items-center mb-2"
           onclick="event.stopPropagation();"
           style="background: #FEE2E2; color: #DC2626; padding: .25rem .65rem; border-radius: 10px;
                  font-size: .72rem; font-weight: 700; text-decoration: none;">
            <i class="fas fa-clipboard-list me-1"></i> Create Assessment
            <span class="badge badge-danger ms-1">{{ $pendingNlscForExam }}</span>
        </a>
    @endif

    <div class="mb-2">
        <small class="text-muted">
            <i class="fas fa-calendar me-1"></i> {{ $exam->academic_year }}
        </small>
        <br>
        <small class="text-muted">
            <i class="fas fa-tag me-1"></i> {{ $exam->exam_type }} - {{ $exam->term }}
        </small>
    </div>

    <div class="mb-2">
        <small class="text-muted d-block">
            <i class="fas fa-calendar-alt me-1"></i>
            {{ $exam->start_date->format('d M') }} → {{ $exam->end_date->format('d M Y') }}
        </small>
    </div>

    <div class="deadline-timer" data-deadline="{{ $exam->marks_entry_deadline }}">
        <i class="fas fa-clock me-1"></i> Calculating...
    </div>

    <div class="mt-2">
        <div class="progress" style="height: 3px;">
            @php
                $totalDays = $exam->start_date->diffInDays($exam->end_date);
                $elapsedDays = $exam->start_date->diffInDays(now());
                $progress = $totalDays > 0 ? min(100, ($elapsedDays / $totalDays) * 100) : 0;
            @endphp
            <div class="progress-bar"
                style="width: {{ $progress }}%; background: linear-gradient(90deg, var(--accent), var(--purple));">
            </div>
        </div>
    </div>
</div>