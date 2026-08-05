{{-- Shared tab-switcher between the three report types, for a given exam.
     Expects: $exam, $active ('class-summary' | 'subject-report' | 'grade-analysis') --}}
<div class="rpt-tabs">
    <a href="{{ route('examination.reports.class-summary', $exam->id) }}"
        class="rpt-tab {{ $active === 'class-summary' ? 'active' : '' }}">
        <i class="fas fa-table-cells me-1"></i> Class Summary
    </a>
    <a href="{{ route('examination.reports.subject-report', $exam->id) }}"
        class="rpt-tab {{ $active === 'subject-report' ? 'active' : '' }}">
        <i class="fas fa-book me-1"></i> Subject Report
    </a>
    <a href="{{ route('examination.reports.grade-analysis', $exam->id) }}"
        class="rpt-tab {{ $active === 'grade-analysis' ? 'active' : '' }}">
        <i class="fas fa-chart-pie me-1"></i> Grade Analysis
    </a>
</div>

<style>
    .rpt-tabs {
        display: inline-flex;
        background: #ede9ff;
        border-radius: 0.75rem;
        padding: 0.25rem;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .rpt-tab {
        padding: 0.5rem 1rem;
        border-radius: 0.55rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #4b3fbf;
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .rpt-tab:hover {
        background: rgba(44, 41, 202, 0.08);
        color: #2C29CA;
    }

    .rpt-tab.active {
        background: #fff;
        color: #2C29CA;
        box-shadow: 0 2px 8px rgba(44, 41, 202, 0.15);
    }
</style>