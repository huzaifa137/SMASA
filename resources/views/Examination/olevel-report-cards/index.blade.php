<?php use App\Http\Controllers\Helper; use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .rc-hero { background: linear-gradient(135deg,#0a0a0f 0%,#14143a 40%,#1e1b8a 75%,#2C29CA 100%); border-radius: 1.75rem; padding: 1.5rem 2rem 2rem; margin-bottom: 1.5rem; }
        .rc-hero .hero-badge { background: rgba(44,41,202,.25); border: 1px solid rgba(107,105,232,.5); color: #c7c5ff; padding: .3rem .9rem; border-radius: 999px; font-size: .65rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .rc-hero .hero-title { font-size: 1.4rem; font-weight: 800; color: #fff; margin: .25rem 0; }
        .rc-hero .hero-subtitle { color: rgba(255,255,255,.68); font-size: .85rem; max-width: 760px; }
        .rc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 1.1rem; }
        .rc-card { background: #fff; border-radius: 1.1rem; box-shadow: 0 4px 24px rgba(44,41,202,.08); padding: 1.2rem 1.3rem; display: flex; flex-direction: column; gap: .7rem; }
        .rc-card h5 { margin: 0; font-weight: 800; font-size: 1rem; color: #1a1a3a; }
        .rc-meta { font-size: .75rem; color: #7a7a9a; }
        .rc-chip { display: inline-block; background: #eef0ff; color: #2C29CA; border-radius: 999px; padding: .15rem .6rem; font-size: .68rem; font-weight: 700; margin: 0 .25rem .25rem 0; }
        .rc-comp { display: flex; justify-content: space-between; align-items: center; font-size: .8rem; padding: .35rem .6rem; border-radius: .6rem; background: #f6f7ff; }
        .rc-comp b { color: #2C29CA; }
        .rc-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: auto; }
        .rc-empty { text-align: center; background: #fff; border-radius: 1.25rem; padding: 3rem 1rem; color: #7a7a9a; }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-3">
        <div class="rc-hero">
            <span class="hero-badge"><i class="fas fa-graduation-cap me-1"></i> Secondary · Senior 1–4</span>
            <div class="hero-title">O-Level Report Cards</div>
            <div class="hero-subtitle">
                Build a report card from any NLSC assessments, from a standard examination, or from both together —
                each part rescaled to the weight you choose (for example assessments out of 20 plus an examination out of 80).
            </div>
            @if(PermissionHelper::canFeature('create_olevel_report_card'))
                <a href="{{ route('olevel-report-cards.create') }}" class="btn btn-light mt-3" style="border-radius:.7rem; font-weight:700;">
                    <i class="fas fa-plus me-1"></i> New Report Card
                </a>
            @endif
        </div>

        @if($cards->isEmpty())
            <div class="rc-empty">
                <i class="fas fa-file-alt fa-2x mb-2"></i>
                <div class="fw-bold">No report cards yet</div>
                <div style="font-size:.85rem;">Create one to combine assessments and examinations onto a single report.</div>
            </div>
        @else
            <div class="rc-grid">
                @foreach($cards as $card)
                    <div class="rc-card" id="card-{{ $card->id }}">
                        <div>
                            <h5>{{ $card->exam_name }}</h5>
                            <div class="rc-meta">{{ \App\Support\Term::label($card->term) }} · {{ $card->academic_year }} · {{ $card->exam_code }}</div>
                        </div>
                        <div>
                            @foreach($card->class_labels as $label)
                                <span class="rc-chip">{{ $label }}</span>
                            @endforeach
                        </div>
                        <div class="d-grid gap-1">
                            @foreach($card->reportCardComponents as $c)
                                <div class="rc-comp">
                                    <span>
                                        <i class="fas {{ $c->type === 'exam' ? 'fa-file-signature' : 'fa-tasks' }} me-1"></i>
                                        {{ $c->label }}
                                    </span>
                                    <b>/ {{ rtrim(rtrim(number_format($c->weight, 2), '0'), '.') }}</b>
                                </div>
                            @endforeach
                        </div>
                        <div class="rc-actions">
                            @if(PermissionHelper::canFeature('view_report_cards'))
                                <a href="{{ route('examination.passslips.index', $card->id) }}" class="btn btn-primary btn-sm" style="border-radius:.6rem;">
                                    <i class="fas fa-print me-1"></i> Generate Report Cards
                                </a>
                            @endif
                            @if(PermissionHelper::canFeature('edit_olevel_report_card'))
                                <a href="{{ route('olevel-report-cards.edit', $card->id) }}" class="btn btn-outline-primary btn-sm" style="border-radius:.6rem;">
                                    <i class="fas fa-pen me-1"></i> Edit
                                </a>
                            @endif
                            @if(PermissionHelper::canFeature('delete_olevel_report_card'))
                                <button type="button" class="btn btn-outline-danger btn-sm" style="border-radius:.6rem;"
                                    onclick="deleteCard({{ $card->id }}, @js($card->exam_name))">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@section('js')
    <script>
        function deleteCard(id, name) {
            Swal.fire({
                title: 'Delete this report card?',
                text: name + ' will be removed. The assessments and examinations it used are not affected.',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, delete',
            }).then(r => {
                if (!r.isConfirmed) return;
                fetch(`{{ url('examinations/olevel-report-cards') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                }).then(res => res.json()).then(res => {
                    if (res.success) { document.getElementById('card-' + id)?.remove(); }
                    else { Swal.fire('Error', res.message || 'Could not delete.', 'error'); }
                }).catch(() => Swal.fire('Error', 'Could not delete.', 'error'));
            });
        }
    </script>
@endsection
