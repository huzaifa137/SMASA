@php
    $statusColors = [
        'draft' => '#94a3b8',
        'active' => '#10b981',
        'marks_entry' => '#f59e0b',
        'closed' => '#ef4444',
        'results_released' => '#6366f1'
    ];
    
    $statusIcons = [
        'draft' => 'pencil-alt',
        'active' => 'play-circle',
        'marks_entry' => 'edit',
        'closed' => 'lock',
        'results_released' => 'trophy'
    ];
    
    $daysUntilDeadline = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($exam->marks_entry_deadline), false);
@endphp

<div class="exam-card status-{{ $exam->status }}" 
     data-id="{{ $exam->id }}" 
     onclick="viewExamDetails({{ $exam->id }})"
     style="border-left-color: {{ $statusColors[$exam->status] }};">
    
    <div class="exam-code">
        <i class="fas fa-hashtag" style="font-size: 0.65rem;"></i> 
        {{ $exam->exam_code }}
    </div>
    
    <div class="exam-name">
        {{ Str::limit($exam->exam_name, 40) }}
    </div>
    
    <div class="exam-meta">
        <span style="display: flex; align-items: center; gap: 0.25rem;">
            <i class="fas fa-calendar" style="font-size: 0.65rem;"></i>
            {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
        </span>
        <span style="display: flex; align-items: center; gap: 0.25rem;">
            <i class="fas fa-{{ $statusIcons[$exam->status] }}" style="font-size: 0.65rem;"></i>
            {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
        </span>
    </div>
    
    @if(in_array($exam->status, ['active', 'marks_entry']))
        <div style="margin-top: 0.5rem;">
            <span class="exam-badge" style="background: {{ $daysUntilDeadline <= 3 ? '#fef3c7' : '#d1fae5' }}; color: {{ $daysUntilDeadline <= 3 ? '#d97706' : '#059669' }};">
                <i class="fas fa-clock"></i>
                @if($daysUntilDeadline > 0)
                    {{ $daysUntilDeadline }} days left
                @elseif($daysUntilDeadline === 0)
                    Due today
                @else
                    Expired
                @endif
            </span>
        </div>
    @endif
</div>