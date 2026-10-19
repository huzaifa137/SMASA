<?php use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper; ?>
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .rc-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem;
            padding: 1.75rem 2rem 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .rc-hero::after {
            content: "";
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(107, 105, 232, .35), transparent 70%);
            pointer-events: none;
        }

        .rc-hero .hero-badge {
            background: rgba(44, 41, 202, .25);
            border: 1px solid rgba(107, 105, 232, .5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .rc-hero .hero-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #fff;
            margin: .35rem 0 .25rem;
            position: relative;
            z-index: 1;
        }

        .rc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .72);
            font-size: .85rem;
            max-width: 760px;
            line-height: 1.5;
            position: relative;
            z-index: 1;
        }

        .rc-hero .hero-cta {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1.1rem;
            padding: .55rem 1.1rem .55rem .55rem;
            background: #fff;
            color: #2C29CA;
            border-radius: .65rem;
            font-weight: 700;
            font-size: .85rem;
            text-decoration: none;
            transition: all .15s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .18);
        }

        .rc-hero .hero-cta:hover {
            color: #2C29CA;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25);
        }

        .rc-hero .hero-cta .hero-cta-icon {
            width: 26px;
            height: 26px;
            border-radius: .4rem;
            background: #eef0ff;
            color: #2C29CA;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
        }

        /* ── Grid & cards ── */
        .rc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.1rem;
        }

        .rc-card {
            background: #fff;
            border-radius: 1.1rem;
            box-shadow: 0 4px 24px rgba(44, 41, 202, .08);
            padding: 1.25rem 1.3rem;
            display: flex;
            flex-direction: column;
            gap: .85rem;
            border: 1px solid #eef0ff;
            transition: box-shadow .2s ease, transform .2s ease;
        }

        .rc-card:hover {
            box-shadow: 0 8px 32px rgba(44, 41, 202, .14);
            transform: translateY(-2px);
        }

        .rc-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .65rem;
        }

        .rc-card-title {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            min-width: 0;
        }

        .rc-card-icon {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            background: #eef0ff;
            color: #2C29CA;
            border: 1px solid #d9dbfa;
        }

        .rc-card-title h5 {
            margin: 0;
            font-weight: 800;
            font-size: 1rem;
            color: #1a1a3a;
            line-height: 1.25;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .rc-meta {
            font-size: .72rem;
            color: #7a7a9a;
            display: flex;
            align-items: center;
            gap: .35rem;
            margin-top: .2rem;
            flex-wrap: wrap;
        }

        .rc-meta .rc-meta-dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: #c4c6e0;
            display: inline-block;
        }

        .rc-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .68rem;
            background: #f4f5ff;
            color: #4a4a6a;
            padding: .1rem .4rem;
            border-radius: .3rem;
            border: 1px solid #e4e6ff;
        }

        /* class chips */
        .rc-chips-row {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .rc-chip {
            display: inline-block;
            background: #eef0ff;
            color: #2C29CA;
            border-radius: 999px;
            padding: .18rem .65rem;
            font-size: .68rem;
            font-weight: 700;
            border: 1px solid #d9dbfa;
            white-space: nowrap;
        }

        /* component list */
        .rc-comp-list {
            display: grid;
            gap: .4rem;
        }

        .rc-comp {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .8rem;
            padding: .45rem .7rem;
            border-radius: .55rem;
            background: #f8f9ff;
            border: 1px solid #eef0ff;
        }

        .rc-comp-left {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: 0;
        }

        .rc-comp-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .rc-comp-dot.exam {
            background: #f59e0b;
        }

        .rc-comp-dot.assess {
            background: #2C29CA;
        }

        .rc-comp-label {
            color: #2a2a4a;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rc-comp b {
            color: #2C29CA;
            font-size: .8rem;
            font-weight: 800;
            background: #eef0ff;
            padding: .1rem .5rem;
            border-radius: .35rem;
            flex-shrink: 0;
        }

        /* action buttons */
        .rc-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: .35rem;
            border-top: 1px dashed #eef0ff;
        }

        .rc-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .42rem .8rem .42rem .42rem;
            border-radius: .55rem;
            border: 1.5px solid transparent;
            font-size: .74rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s ease;
            white-space: nowrap;
        }

        .rc-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: .35rem;
            font-size: .65rem;
            flex-shrink: 0;
        }

        /* primary (generate) */
        .rc-btn-primary {
            background: linear-gradient(135deg, #2C29CA 0%, #1e1b8a 100%);
            border-color: #2C29CA;
            color: #fff;
            box-shadow: 0 3px 10px rgba(44, 41, 202, .25);
        }

        .rc-btn-primary .rc-btn-icon {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .rc-btn-primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(44, 41, 202, .35);
        }

        /* edit */
        .rc-btn-edit {
            background: #eef0ff;
            border-color: #c7c5ff;
            color: #2C29CA;
        }

        .rc-btn-edit .rc-btn-icon {
            background: #2C29CA;
            color: #fff;
        }

        .rc-btn-edit:hover {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
        }

        .rc-btn-edit:hover .rc-btn-icon {
            background: rgba(255, 255, 255, .25);
        }

        /* delete */
        .rc-btn-delete {
            background: #fff5f5;
            border-color: #f3c9c9;
            color: #a02020;
        }

        .rc-btn-delete .rc-btn-icon {
            background: #ffe3e3;
            color: #a02020;
        }

        .rc-btn-delete:hover {
            background: #a02020;
            border-color: #a02020;
            color: #fff;
        }

        .rc-btn-delete:hover .rc-btn-icon {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        /* empty state */
        .rc-empty {
            text-align: center;
            background: #fff;
            border-radius: 1.25rem;
            padding: 3rem 1rem;
            color: #7a7a9a;
            border: 1px dashed #d9dbfa;
        }

        .rc-empty-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto .8rem;
            border-radius: 1rem;
            background: #eef0ff;
            color: #2C29CA;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .rc-empty-title {
            font-weight: 800;
            color: #1a1a3a;
            font-size: 1rem;
            margin-bottom: .25rem;
        }

        .rc-empty-sub {
            font-size: .82rem;
        }

        @media (max-width: 575.98px) {
            .rc-hero {
                padding: 1.35rem 1.25rem 1.5rem;
            }

            .rc-hero .hero-title {
                font-size: 1.2rem;
            }

            .rc-btn {
                font-size: .7rem;
                padding: .4rem .7rem .4rem .4rem;
            }

            .rc-btn-icon {
                width: 20px;
                height: 20px;
                font-size: .6rem;
            }
        }

        /* ── Empty state & grid: soft bluish backdrop ── */
        .rc-empty {
            text-align: center;
            background:
                radial-gradient(circle at 50% 0%, #eef1ff 0%, #f6f8ff 45%, #ffffff 100%);
            border-radius: 1.25rem;
            padding: 3rem 1rem;
            color: #7a7a9a;
            border: 1px dashed #c9cdf0;
            box-shadow: inset 0 0 0 1px rgba(44, 41, 202, .03), 0 4px 24px rgba(44, 41, 202, .06);
        }

        .rc-empty-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto .8rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #eef0ff 0%, #dfe3ff 100%);
            color: #2C29CA;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 6px 18px rgba(44, 41, 202, .15);
        }

        .rc-empty-title {
            font-weight: 800;
            color: #1a1a3a;
            font-size: 1rem;
            margin-bottom: .25rem;
        }

        .rc-empty-sub {
            font-size: .82rem;
        }

        /* Soft bluish backdrop behind the grid */
        .rc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.1rem;
            background:
                radial-gradient(circle at 0% 0%, rgba(44, 41, 202, .05) 0%, transparent 45%),
                radial-gradient(circle at 100% 100%, rgba(107, 105, 232, .06) 0%, transparent 50%),
                linear-gradient(180deg, #f6f8ff 0%, #fafbff 100%);
            padding: 1.15rem;
            border-radius: 1.25rem;
            border: 1px solid #eef0ff;
        }

        /* ── Action buttons: equal width across the footer ── */
        .rc-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: nowrap;
            margin-top: auto;
            padding-top: .55rem;
            border-top: 1px dashed #eef0ff;
        }

        .rc-actions>.rc-btn {
            flex: 1 1 0;
            min-width: 0;
            justify-content: center;
            padding-left: .55rem;
            padding-right: .7rem;
        }

        .rc-actions>.rc-btn>span:last-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .rc-actions {
                flex-wrap: wrap;
            }

            .rc-actions>.rc-btn {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 575.98px) {
    /* existing rules stay as they are... */

    /* New Report Card: span full width on small screens */
    .rc-hero .hero-cta {
        display: flex;
        width: 100%;
        justify-content: center;
    }
}
    </style>
@endsection

@section('content')
    <div class="container-fluid py-3">
        <div class="rc-hero">
            <span class="hero-badge"><i class="fas fa-graduation-cap"></i> Secondary · Senior 1–4</span>
            <div class="hero-title">O Level Report Cards</div>
            <div class="hero-subtitle">
                Build a report card from any NLSC assessments, from a standard examination, or from both together —
                each part rescaled to the weight you choose (for example assessments out of 20 plus an examination out of
                80).
            </div>
            @if(PermissionHelper::canFeature('create_olevel_report_card'))
                <a href="{{ route('olevel-report-cards.create') }}" class="hero-cta">
                    <span class="hero-cta-icon"><i class="fas fa-plus"></i></span>
                    <span>New Report Card</span>
                </a>
            @endif
        </div>

        @if($cards->isEmpty())
            <div class="rc-empty">
                <div class="rc-empty-icon"><i class="fas fa-file-alt"></i></div>
                <div class="rc-empty-title">No report cards yet</div>
                <div class="rc-empty-sub">Create one to combine assessments and examinations onto a single report.</div>
            </div>
        @else
            <div class="rc-grid">
                @foreach($cards as $card)
                    <div class="rc-card" id="card-{{ $card->id }}">
                        <div class="rc-card-head">
                            <div class="rc-card-title">
                                <span class="rc-card-icon"><i class="fas fa-file-invoice"></i></span>
                                <div>
                                    <h5>{{ $card->exam_name }}</h5>
                                    <div class="rc-meta">
                                        <i class="fas fa-calendar-alt" style="font-size:.65rem; opacity:.7;"></i>
                                        <span>{{ \App\Support\Term::label($card->term) }}</span>
                                        <span class="rc-meta-dot"></span>
                                        <span>{{ $card->academic_year }}</span>
                                        <span class="rc-meta-dot"></span>
                                        <span class="rc-code">{{ $card->exam_code }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rc-chips-row">
                            @foreach($card->class_labels as $label)
                                <span class="rc-chip">{{ $label }}</span>
                            @endforeach
                        </div>

                        <div class="rc-comp-list">
                            @foreach($card->reportCardComponents as $c)
                                <div class="rc-comp">
                                    <div class="rc-comp-left">
                                        <span class="rc-comp-dot {{ $c->type === 'exam' ? 'exam' : 'assess' }}"></span>
                                        <i class="fas {{ $c->type === 'exam' ? 'fa-file-signature' : 'fa-tasks' }}"
                                            style="font-size:.7rem; color:{{ $c->type === 'exam' ? '#b45309' : '#2C29CA' }};"></i>
                                        <span class="rc-comp-label">{{ $c->label }}</span>
                                    </div>
                                    <b>/ {{ rtrim(rtrim(number_format($c->weight, 2), '0'), '.') }}</b>
                                </div>
                            @endforeach
                        </div>

                        <div class="rc-actions">
                            @if(PermissionHelper::canFeature('view_report_cards'))
                                <a href="{{ route('examination.passslips.index', $card->id) }}" class="rc-btn rc-btn-primary">
                                    <span class="rc-btn-icon"><i class="fas fa-print"></i></span>
                                    <span>Generate</span>
                                </a>
                            @endif
                            @if(PermissionHelper::canFeature('edit_olevel_report_card'))
                                <a href="{{ route('olevel-report-cards.edit', $card->id) }}" class="rc-btn rc-btn-edit">
                                    <span class="rc-btn-icon"><i class="fas fa-pen"></i></span>
                                    <span>Edit</span>
                                </a>
                            @endif
                            @if(PermissionHelper::canFeature('delete_olevel_report_card'))
                                <button type="button" class="rc-btn rc-btn-delete"
                                    onclick="deleteCard({{ $card->id }}, @js($card->exam_name))">
                                    <span class="rc-btn-icon"><i class="fas fa-trash"></i></span>
                                    <span>Delete</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
    </div>
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
                    if (res.success) {
                        const el = document.getElementById('card-' + id);
                        if (el) {
                            el.style.transition = 'opacity .2s ease, transform .2s ease';
                            el.style.opacity = '0';
                            el.style.transform = 'scale(.96)';
                            setTimeout(() => el.remove(), 200);
                        }
                    }
                    else { Swal.fire('Error', res.message || 'Could not delete.', 'error'); }
                }).catch(() => Swal.fire('Error', 'Could not delete.', 'error'));
            });
        }
    </script>
@endsection