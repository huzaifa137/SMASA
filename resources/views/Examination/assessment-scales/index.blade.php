@extends('layouts-side-bar.master')

@section('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2C29CA;
            --primary-dark: #201ea0;
            --primary-light: #EEEDFC;
            --primary-soft: #F5F4FF;
            --ink: #1B1D28;
            --muted: #6B7280;
            --border: #E6E7EE;
            --surface: #FFFFFF;
            --bg: #F6F7FB;
            --green: #12875A;
            --green-bg: #E6F6EF;
            --amber: #B4770B;
            --amber-bg: #FCF1DC;
            --red: #C4293A;
            --red-bg: #FCEAEC;
            --gray-bg: #F1F2F5;
        }

        * {
            box-sizing: border-box;
        }

        .as-app {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
        }

        .as-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .4rem;
            font-size: .78rem;
            font-weight: 600;
            margin-bottom: .85rem;
        }

        .as-breadcrumb a {
            color: var(--muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .as-breadcrumb a:hover {
            color: var(--primary);
        }

        .as-breadcrumb .crumb-current {
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .as-breadcrumb i.fa-chevron-right {
            font-size: .6rem;
            color: var(--border);
        }

        .as-topbar {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 50%, #DBEAFE 100%);
            border: 1px solid rgba(44, 41, 202, .12);
            border-radius: 1rem;
            padding: 1.25rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(44, 41, 202, .08);
            position: relative;
            overflow: hidden;
        }

        .as-topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
            min-width: 200px;
            position: relative;
            z-index: 1;
        }

        .as-topbar-icon {
            width: 50px;
            height: 50px;
            border-radius: .75rem;
            background: linear-gradient(135deg, var(--primary) 0%, #6366F1 50%, #818CF8 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(44, 41, 202, .3);
        }

        .as-topbar-title {
            font-weight: 800;
            font-size: 1.4rem;
            margin: 0;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #9fa5c0 0%, #2C29CA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .as-topbar-subtitle {
            margin: 0;
            font-size: .85rem;
            color: #4B5563;
            max-width: 56ch;
        }

        .as-topbar-actions {
            display: flex;
            gap: .6rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .btn-as-secondary {
            border: 1px solid rgba(44, 41, 202, .15);
            background: rgba(255, 255, 255, .7);
            color: var(--ink);
            font-weight: 600;
            font-size: .82rem;
            padding: .55rem 1rem;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-as-secondary:hover {
            background: #fff;
            border-color: rgba(44, 41, 202, .3);
            color: var(--primary);
        }

        .btn-as-primary {
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, #6366F1 50%, #818CF8 100%);
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            padding: .55rem 1.1rem;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            box-shadow: 0 4px 12px rgba(44, 41, 202, .25);
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-as-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(44, 41, 202, .35);
        }

        .as-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .as-stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .6rem;
            padding: .85rem 1rem;
        }

        .as-stat .label {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
            margin-bottom: .3rem;
        }

        .as-stat .value {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ink);
        }

        .as-stat.accent .value {
            color: var(--primary);
        }

        .as-split {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.1rem;
            align-items: start;
        }

        .as-list-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .7rem;
            overflow: hidden;
        }

        .as-list-panel-head {
            padding: .8rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
        }

        .as-scale-list {
            list-style: none;
            margin: 0;
            padding: .4rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .scale-list-item {
            display: block;
            width: 100%;
            text-align: left;
            border: 1px solid transparent;
            background: transparent;
            border-radius: .5rem;
            padding: .6rem .7rem;
            margin-bottom: .2rem;
            cursor: pointer;
        }

        .scale-list-item:hover {
            background: var(--bg);
        }

        .scale-list-item.active {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .scale-list-item .name {
            font-weight: 700;
            font-size: .88rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .scale-list-item .name i.fa-star {
            color: var(--amber);
            font-size: .7rem;
        }

        .scale-list-item .sub {
            font-size: .72rem;
            color: var(--muted);
            margin-top: .15rem;
        }

        .mini-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: .3rem;
        }

        .mini-dot.on {
            background: var(--green);
        }

        .mini-dot.off {
            background: var(--muted);
            opacity: .5;
        }

        .as-detail-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: .7rem;
            min-height: 60vh;
        }

        .as-detail {
            display: none;
            padding: 1.4rem 1.5rem;
        }

        .as-detail.active {
            display: block;
        }

        .detail-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .detail-title-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .detail-title-row h4 {
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
            color: var(--ink);
        }

        .badge-pill {
            font-size: .68rem;
            font-weight: 700;
            padding: .2rem .55rem;
            border-radius: 1rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .badge-default {
            background: var(--amber-bg);
            color: var(--amber);
        }

        .badge-inactive {
            background: var(--gray-bg);
            color: var(--muted);
        }

        .badge-linked {
            background: var(--primary-light);
            color: var(--primary);
        }

        .detail-desc {
            color: var(--muted);
            font-size: .87rem;
            margin: .3rem 0 0;
            max-width: 60ch;
        }

        .detail-actions {
            display: flex;
            gap: .45rem;
            flex-shrink: 0;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: .5rem;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            background: var(--bg);
            color: var(--ink);
        }

        .icon-btn.danger:hover {
            background: var(--red-bg);
            color: var(--red);
            border-color: var(--red-bg);
        }

        .detail-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.3rem;
        }

        .detail-stat {
            background: var(--bg);
            border-radius: .5rem;
            padding: .65rem .8rem;
        }

        .detail-stat .label {
            font-size: .68rem;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .detail-stat .value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .section-label {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        .band-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .band-table thead th {
            text-align: left;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--muted);
            font-weight: 700;
            padding: .5rem .6rem;
            border-bottom: 1px solid var(--border);
        }

        .band-table tbody td {
            padding: .55rem .6rem;
            border-bottom: 1px solid var(--border);
            color: var(--ink);
        }

        .band-table tbody tr:last-child td {
            border-bottom: none;
        }

        .grade-chip {
            display: inline-block;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
            font-size: .78rem;
            padding: .18rem .55rem;
            border-radius: .35rem;
        }

        .as-empty-detail {
            padding: 4rem 1rem;
            text-align: center;
            color: var(--muted);
        }

        .as-empty-detail i {
            color: var(--border);
        }

        @media (max-width: 860px) {
            .as-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .as-split {
                grid-template-columns: 1fr;
            }

            .as-scale-list {
                max-height: none;
            }

            .detail-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .preset-row {
            display: grid;
            grid-template-columns: .8fr 1.6fr 1fr auto;
            gap: .5rem;
            align-items: center;
            margin-bottom: .5rem;
        }

        .preset-row input {
            font-size: .82rem;
        }

        .as-swal-confirm {
            background: var(--primary) !important;
            font-weight: 600 !important;
        }
    </style>
@endsection

@section('content')
    <div class="side-app">
        <div class="as-app">

            <div class="as-topbar">
                <div class="as-topbar-left">
                    <div class="as-topbar-icon"><i class="fas fa-comment-dots"></i></div>
                    <div>
                        <h3 class="as-topbar-title"
                            style="color: #fff !important; -webkit-text-fill-color: #fff !important;">
                            Assessment Scales
                        </h3>
                        <p class="as-topbar-subtitle">Define comment/score scales (like the 1–3 Early Years scale) for
                            subjects that aren't graded with normal numeric marks. Attach one to a subject from a class's
                            <em>Attached Subjects</em> page.
                        </p>
                    </div>
                </div>
                <div class="as-topbar-actions">
                    <a href="{{ route('examination.create') }}" class="btn-as-secondary">
                        <i class="fas fa-arrow-left"></i> <span>Back to Create Exam</span>
                    </a>
                    <button type="button" id="btnNewScale" class="btn-as-primary">
                        <i class="fas fa-plus"></i> <span>New Assessment Scale</span>
                    </button>
                </div>
            </div>

            <style>
                /* ── TOP BAR: Premium Dark Gradient (matches Grading Schemes) ── */
                .as-topbar {
                    background: linear-gradient(135deg, #0F0E1A 0%, #1B1D28 40%, #2C29CA 100%);
                    border-radius: 1.25rem;
                    padding: 1.75rem 2.5rem;
                    margin-bottom: 1.5rem;
                    position: relative;
                    overflow: hidden;
                    box-shadow: 0 8px 40px rgba(44, 41, 202, .2);
                    display: flex;
                    flex-wrap: wrap;
                    gap: 1.25rem;
                    justify-content: space-between;
                    align-items: center;
                }

                /* Animated particles background */
                .as-topbar::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-image:
                        radial-gradient(2px 2px at 20px 30px, rgba(255, 255, 255, .1), transparent),
                        radial-gradient(2px 2px at 40px 70px, rgba(255, 255, 255, .08), transparent),
                        radial-gradient(2px 2px at 50px 160px, rgba(255, 255, 255, .12), transparent),
                        radial-gradient(2px 2px at 90px 40px, rgba(255, 255, 255, .06), transparent),
                        radial-gradient(2px 2px at 130px 80px, rgba(255, 255, 255, .1), transparent),
                        radial-gradient(2px 2px at 160px 30px, rgba(255, 255, 255, .08), transparent);
                    background-size: 200px 200px;
                    opacity: 0.5;
                    pointer-events: none;
                    animation: asParticleMove 20s linear infinite;
                }

                @keyframes asParticleMove {
                    0% {
                        transform: translate(0, 0);
                    }

                    100% {
                        transform: translate(-20px, -20px);
                    }
                }

                /* Decorative glow - top right */
                .as-topbar::after {
                    content: '';
                    position: absolute;
                    top: -30%;
                    right: -10%;
                    width: 500px;
                    height: 500px;
                    background: radial-gradient(circle, rgba(99, 102, 241, .15) 0%, transparent 70%);
                    border-radius: 50%;
                    pointer-events: none;
                }

                /* Additional glow - bottom left (using a separate element) */
                .as-topbar .as-topbar-glow-bottom {
                    position: absolute;
                    bottom: -40%;
                    left: -5%;
                    width: 400px;
                    height: 400px;
                    background: radial-gradient(circle, rgba(44, 41, 202, .1) 0%, transparent 70%);
                    border-radius: 50%;
                    pointer-events: none;
                }

                .as-topbar-left {
                    display: flex;
                    align-items: flex-start;
                    gap: 1.25rem;
                    flex: 1;
                    min-width: 200px;
                    position: relative;
                    z-index: 1;
                }

                .as-topbar-icon {
                    width: 54px;
                    height: 54px;
                    border-radius: .75rem;
                    background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
                    color: #fff;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.3rem;
                    box-shadow: 0 4px 16px rgba(99, 102, 241, .3);
                    transition: all .3s ease;
                    flex-shrink: 0;
                }

                .as-topbar-icon:hover {
                    transform: scale(1.08) rotate(-5deg);
                    box-shadow: 0 6px 24px rgba(99, 102, 241, .4);
                }

                .as-topbar-title {
                    font-weight: 800;
                    font-size: 1.5rem;
                    margin: 0;
                    color: #ffffff !important;
                    letter-spacing: -.02em;
                    line-height: 1.2;
                }

                .as-topbar-subtitle {
                    margin: 0;
                    font-size: .85rem;
                    color: rgba(255, 255, 255, .6);
                    line-height: 1.4;
                    max-width: 48ch;
                }

                .as-topbar-actions {
                    display: flex;
                    align-items: center;
                    gap: .6rem;
                    flex-shrink: 0;
                    flex-wrap: wrap;
                    position: relative;
                    z-index: 1;
                }

                .btn-as-secondary {
                    display: inline-flex;
                    align-items: center;
                    gap: .6rem;
                    padding: .6rem 1.2rem;
                    background: rgba(255, 255, 255, .06);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, .08);
                    color: rgba(255, 255, 255, .8);
                    font-weight: 600;
                    font-size: .82rem;
                    border-radius: .6rem;
                    text-decoration: none;
                    transition: all .25s ease;
                    white-space: nowrap;
                }

                .btn-as-secondary:hover {
                    background: rgba(255, 255, 255, .12);
                    color: #ffffff;
                    text-decoration: none;
                    transform: translateY(-2px);
                    border-color: rgba(255, 255, 255, .15);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
                }

                .btn-as-secondary:active {
                    transform: translateY(0);
                }

                .btn-as-primary {
                    display: inline-flex;
                    align-items: center;
                    gap: .6rem;
                    padding: .6rem 1.4rem;
                    background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #4F46E5 100%);
                    color: #ffffff;
                    font-weight: 700;
                    font-size: .82rem;
                    border: none;
                    border-radius: .6rem;
                    text-decoration: none;
                    transition: all .25s ease;
                    box-shadow: 0 4px 20px rgba(99, 102, 241, .3);
                    cursor: pointer;
                    white-space: nowrap;
                }

                .btn-as-primary:hover {
                    transform: translateY(-2px) scale(1.02);
                    box-shadow: 0 6px 28px rgba(99, 102, 241, .4);
                    color: #ffffff;
                }

                .btn-as-primary:active {
                    transform: translateY(0) scale(1);
                }

                .btn-as-secondary span,
                .btn-as-primary span {
                    display: inline;
                }

                /* ── Responsive ────────────────────────────────────────────── */
                @media (max-width: 768px) {
                    .as-topbar {
                        padding: 1.5rem 1.5rem;
                        border-radius: 1rem;
                        flex-direction: column;
                        align-items: stretch;
                    }

                    .as-topbar-left {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: .75rem;
                    }

                    .as-topbar-icon {
                        width: 44px;
                        height: 44px;
                        font-size: 1.1rem;
                    }

                    .as-topbar-title {
                        font-size: 1.25rem;
                    }

                    .as-topbar-subtitle {
                        font-size: .8rem;
                        max-width: 100%;
                    }

                    .as-topbar-actions {
                        width: 100%;
                        flex-direction: column;
                    }

                    .btn-as-secondary,
                    .btn-as-primary {
                        width: 100%;
                        justify-content: center;
                        padding: .6rem 1.2rem;
                    }
                }

                @media (max-width: 480px) {
                    .as-topbar {
                        padding: 1.25rem 1.25rem;
                        border-radius: .85rem;
                    }

                    .as-topbar-title {
                        font-size: 1.1rem;
                    }

                    .btn-as-secondary span,
                    .btn-as-primary span {
                        display: none;
                    }

                    .btn-as-secondary,
                    .btn-as-primary {
                        justify-content: center;
                        min-width: auto;
                    }
                }

                /* ── Professional Table Styles ────────────────────────────── */

                /* Enhanced band-table styling */
                .band-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    font-size: .85rem;
                    background: var(--surface);
                    border-radius: .75rem;
                    overflow: hidden;
                    box-shadow: 0 1px 3px rgba(27, 29, 40, .04);
                }

                /* Table header with gradient */
                .band-table thead {
                    background: #2C29CA;
                }

                .band-table thead th {
                    text-align: left;
                    font-size: .7rem;
                    text-transform: uppercase;
                    letter-spacing: .06em;
                    color: #ffffff;
                    font-weight: 700;
                    padding: .75rem .8rem;
                    border-bottom: 2px solid rgba(255, 255, 255, .15);
                    position: relative;
                }

                .band-table thead th:not(:last-child)::after {
                    content: '';
                    position: absolute;
                    right: 0;
                    top: 25%;
                    height: 50%;
                    width: 1px;
                    background: rgba(255, 255, 255, .15);
                }

                /* Table rows with hover effects */
                .band-table tbody tr {
                    transition: all .2s ease;
                    border-bottom: 1px solid #F3F4F6;
                }

                .band-table tbody tr:hover {
                    background: #F8F7FF;
                    transform: translateX(2px);
                }

                .band-table tbody tr:last-child {
                    border-bottom: none;
                }

                .band-table tbody td {
                    padding: .65rem .8rem;
                    color: var(--ink);
                    font-weight: 500;
                    vertical-align: middle;
                }

                /* Alternating row colors (zebra stripes) */
                .band-table tbody tr:nth-child(even) {
                    background: #FAFAFE;
                }

                .band-table tbody tr:nth-child(even):hover {
                    background: #F5F4FF;
                }

                /* Enhanced grade chips */
                .grade-chip {
                    display: inline-flex;
                    align-items: center;
                    gap: .35rem;
                    background: linear-gradient(135deg, var(--primary-soft) 0%, #EEEDFC 100%);
                    color: var(--primary);
                    font-weight: 700;
                    font-size: .78rem;
                    padding: .25rem .65rem;
                    border-radius: .4rem;
                    border: 1px solid rgba(44, 41, 202, .08);
                    transition: all .2s ease;
                }

                .grade-chip::before {
                    content: '';
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    background: var(--primary);
                    opacity: .3;
                }

                .grade-chip:hover {
                    transform: scale(1.05);
                    box-shadow: 0 2px 8px rgba(44, 41, 202, .15);
                }

                /* Grade chip variants */
                .grade-chip.grade-distinction {
                    background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
                    color: #065F46;
                    border-color: rgba(6, 95, 70, .15);
                }

                .grade-chip.grade-distinction::before {
                    background: #065F46;
                    opacity: .4;
                }

                .grade-chip.grade-credit {
                    background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
                    color: #1E40AF;
                    border-color: rgba(30, 64, 175, .15);
                }

                .grade-chip.grade-credit::before {
                    background: #1E40AF;
                    opacity: .4;
                }

                .grade-chip.grade-pass {
                    background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
                    color: #92400E;
                    border-color: rgba(146, 64, 14, .15);
                }

                .grade-chip.grade-pass::before {
                    background: #92400E;
                    opacity: .4;
                }

                .grade-chip.grade-fail {
                    background: linear-gradient(135deg, #FEE2E2 0%, #FCA5A5 100%);
                    color: #991B1B;
                    border-color: rgba(153, 27, 27, .15);
                }

                .grade-chip.grade-fail::before {
                    background: #991B1B;
                    opacity: .4;
                }

                .band-table tbody td:first-child {
                    font-weight: 600;
                }

                .band-table tbody td:last-child {
                    font-weight: 700;
                    color: var(--primary);
                    text-align: center;
                }

                /* ── Action Buttons ────────────────────────────────────────── */

                /* Edit button - primary color */
                .icon-btn.edit-scale {
                    color: #2C29CA;
                    border-color: rgba(44, 41, 202, 0.2);
                }

                .icon-btn.edit-scale:hover {
                    background: rgba(44, 41, 202, 0.1);
                    color: #2C29CA;
                    border-color: #2C29CA;
                }

                /* Toggle button - green when active, gray when inactive */
                .icon-btn.toggle-scale[data-active="1"] {
                    color: #12875A;
                    border-color: rgba(18, 135, 90, 0.2);
                }

                .icon-btn.toggle-scale[data-active="1"]:hover {
                    background: rgba(18, 135, 90, 0.1);
                    color: #12875A;
                    border-color: #12875A;
                }

                .icon-btn.toggle-scale[data-active="0"] {
                    color: #6B7280;
                    border-color: rgba(107, 114, 128, 0.2);
                }

                .icon-btn.toggle-scale[data-active="0"]:hover {
                    background: rgba(107, 114, 128, 0.1);
                    color: #6B7280;
                    border-color: #6B7280;
                }

                /* Delete button - red */
                .icon-btn.danger.delete-scale {
                    color: #C4293A;
                    border-color: rgba(196, 41, 58, 0.2);
                }

                .icon-btn.danger.delete-scale:hover {
                    background: rgba(196, 41, 58, 0.1);
                    color: #C4293A;
                    border-color: #C4293A;
                }

                /* ── Detail Panel Enhancements ────────────────────────────── */

                .gs-detail-panel {
                    background: var(--surface);
                    border: 1px solid var(--border);
                    border-radius: .8rem;
                    min-height: 70vh;
                    overflow: hidden;
                    box-shadow: 0 2px 8px rgba(27, 29, 40, .04);
                }

                .gs-detail {
                    display: none;
                    padding: 1.5rem 1.75rem;
                }

                .gs-detail.active {
                    display: block;
                    animation: fadeSlideIn .3s ease;
                }

                @keyframes fadeSlideIn {
                    from {
                        opacity: 0;
                        transform: translateY(8px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                /* Section label with icon */
                .section-label {
                    font-size: .75rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: .06em;
                    color: var(--muted);
                    margin-bottom: .75rem;
                    display: flex;
                    align-items: center;
                    gap: .5rem;
                }

                .section-label::before {
                    content: '';
                    width: 3px;
                    height: 16px;
                    background: var(--primary);
                    border-radius: 99px;
                }

                /* Detail stats cards enhancement */
                .detail-stats {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: .75rem;
                    margin-bottom: 1.5rem;
                }

                .detail-stat {
                    background: linear-gradient(135deg, #F8F7FF 0%, #F3F1FF 100%);
                    border-radius: .6rem;
                    padding: .7rem .9rem;
                    border: 1px solid rgba(44, 41, 202, .06);
                    transition: all .2s ease;
                }

                .detail-stat:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(44, 41, 202, .08);
                }

                .detail-stat .label {
                    font-size: .65rem;
                    color: var(--muted);
                    text-transform: uppercase;
                    font-weight: 700;
                    letter-spacing: .04em;
                }

                .detail-stat .value {
                    font-size: 1.1rem;
                    font-weight: 800;
                    color: var(--ink);
                    margin-top: .1rem;
                }

                /* Detail top section */
                .detail-top {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    align-items: flex-start;
                    gap: .75rem;
                    border-bottom: 2px solid #F3F4F6;
                    padding-bottom: 1rem;
                    margin-bottom: 1.25rem;
                }

                .detail-title-row h4 {
                    font-size: 1.2rem;
                    font-weight: 800;
                    margin: 0;
                    color: var(--ink);
                    letter-spacing: -.02em;
                }

                .detail-title-row h4::before {
                    content: '\f5fd';
                    font-family: 'Font Awesome 6 Free';
                    font-weight: 900;
                    margin-right: .5rem;
                    font-size: 1rem;
                    color: var(--primary);
                }

                /* Badge pills enhancement */
                .badge-pill {
                    font-size: .65rem;
                    font-weight: 700;
                    padding: .25rem .65rem;
                    border-radius: 99px;
                    text-transform: uppercase;
                    letter-spacing: .04em;
                    display: inline-flex;
                    align-items: center;
                    gap: .3rem;
                }

                .badge-pill::before {
                    content: '';
                    width: 5px;
                    height: 5px;
                    border-radius: 50%;
                    background: currentColor;
                    opacity: .5;
                }

                .badge-default {
                    background: var(--amber-bg);
                    color: var(--amber);
                    border: 1px solid rgba(180, 119, 11, .15);
                }

                .badge-inactive {
                    background: #F3F4F6;
                    color: #6B7280;
                    border: 1px solid rgba(107, 114, 128, .15);
                }

                .badge-linked {
                    background: var(--primary-light);
                    color: var(--primary);
                    border: 1px solid rgba(44, 41, 202, .15);
                }

                /* ── Responsive Table ────────────────────────────────────────── */
                @media (max-width: 768px) {
                    .band-table {
                        font-size: .78rem;
                    }

                    .band-table thead th,
                    .band-table tbody td {
                        padding: .5rem .6rem;
                    }

                    .detail-stats {
                        grid-template-columns: repeat(2, 1fr);
                    }

                    .detail-top {
                        flex-direction: column;
                        align-items: stretch;
                    }

                    .detail-actions {
                        justify-content: flex-end;
                    }

                    .grade-chip {
                        font-size: .7rem;
                        padding: .2rem .5rem;
                    }
                }

                @media (max-width: 480px) {
                    .band-table {
                        font-size: .7rem;
                    }

                    .band-table thead th,
                    .band-table tbody td {
                        padding: .4rem .4rem;
                    }

                    .detail-stats {
                        grid-template-columns: 1fr 1fr;
                        gap: .5rem;
                    }

                    .detail-stat .value {
                        font-size: .95rem;
                    }

                    .gs-detail {
                        padding: 1rem;
                    }
                }
            </style>

            @php
                $totalScales = $scales->count();
                $activeScales = $scales->where('is_active', true)->count();
                $defaultScale = $scales->firstWhere('is_default', true);
                $subjectsCovered = $scales->sum('class_subjects_count');
            @endphp

            <div class="as-stats">
                <div class="as-stat">
                    <div class="label">Total Scales</div>
                    <div class="value">{{ $totalScales }}</div>
                </div>
                <div class="as-stat">
                    <div class="label">Active</div>
                    <div class="value">{{ $activeScales }} <small class="fw-normal text-muted" style="font-size:.7rem;">/
                            {{ $totalScales }}</small></div>
                </div>
                <div class="as-stat accent">
                    <div class="label">Default Scale</div>
                    <div class="value" style="font-size:.95rem;">{{ $defaultScale->name ?? 'None set' }}</div>
                </div>
                <div class="as-stat">
                    <div class="label">Subjects Using a Scale</div>
                    <div class="value">{{ $subjectsCovered }}</div>
                </div>
            </div>

            @if ($scales->count())
                <div class="as-split">
                    <div class="as-list-panel">
                        <div class="as-list-panel-head">Scales ({{ $totalScales }})</div>
                        <ul class="as-scale-list" id="scaleList">
                            @foreach ($scales as $scale)
                                <li>
                                    <button type="button" class="scale-list-item {{ $loop->first ? 'active' : '' }}"
                                        data-target="scale-detail-{{ $scale->id }}">
                                        <div class="name">
                                            @if ($scale->is_default)
                                                <i class="fas fa-star"></i>
                                            @endif
                                            {{ $scale->name }}
                                        </div>
                                        <div class="sub">
                                            <span class="mini-dot {{ $scale->is_active ? 'on' : 'off' }}"></span>
                                            {{ $scale->is_active ? 'Active' : 'Inactive' }} &bull;
                                            {{ $scale->class_subjects_count }} subject(s)
                                        </div>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="as-detail-panel">
                        @foreach ($scales as $scale)
                                            <div class="as-detail {{ $loop->first ? 'active' : '' }}" id="scale-detail-{{ $scale->id }}">
                                                <div class="detail-top">
                                                    <div>
                                                        <div class="detail-title-row">
                                                            <h4>{{ $scale->name }}</h4>
                                                            @if ($scale->is_default)
                                                                <span class="badge-pill badge-default">Default</span>
                                                            @endif
                                                            @if (!$scale->is_active)
                                                                <span class="badge-pill badge-inactive">Inactive</span>
                                                            @endif
                                                            @if ($scale->usesLinkedGrading())
                                                                <span class="badge-pill badge-linked">Grade:
                                                                    {{ $scale->gradingScheme->name ?? '—' }}</span>
                                                            @endif
                                                            @if ($scale->allow_custom_score)
                                                                <span class="badge-pill badge-linked">Custom scores allowed</span>
                                                            @endif
                                                        </div>
                                                        @if ($scale->description)
                                                            <p class="detail-desc">{{ $scale->description }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="detail-actions">
                                                        <a href="{{ route('examination.assessment-scales.assign-page', $scale->id) }}"
                                                            class="btn btn-sm" style="
                                   text-decoration: none;
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 0.5rem;
                                   padding: 0.45rem 1.1rem;
                                   background: #EEEDFC;
                                   color: #2C29CA;
                                   font-weight: 600;
                                   font-size: 0.8rem;
                                   border: 1.5px solid #2C29CA;
                                   border-radius: 0.5rem;
                                   transition: all 0.25s ease;
                               " onmouseover="this.style.background='#2C29CA'; this.style.color='#ffffff'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(44, 41, 202, 0.25)';"
                                                            onmouseout="this.style.background='#EEEDFC'; this.style.color='#2C29CA'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                                            <i class="fas fa-sitemap"></i>
                                                            <span>Assign to Classes &amp; Subjects</span>
                                                        </a>
                                                        <button type="button" class="icon-btn edit-scale" data-id="{{ $scale->id }}" title="Edit"><i
                                                                class="fas fa-edit"></i></button>
                                                        <button type="button" class="icon-btn toggle-scale" data-id="{{ $scale->id }}"
                                                            data-active="{{ $scale->is_active ? 1 : 0 }}"
                                                            title="{{ $scale->is_active ? 'Deactivate' : 'Activate' }}"><i
                                                                class="fas fa-power-off"></i></button>
                                                        <button type="button" class="icon-btn danger delete-scale" data-id="{{ $scale->id }}"
                                                            title="Delete"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>

                                                <div class="detail-stats">
                                                    <div class="detail-stat">
                                                        <div class="label">Score Range</div>
                                                        <div class="value">
                                                            {{ rtrim(rtrim(number_format($scale->min_score, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format($scale->max_score, 2), '0'), '.') }}
                                                        </div>
                                                    </div>
                                                    <div class="detail-stat">
                                                        <div class="label">Custom Scores</div>
                                                        <div class="value">{{ $scale->allow_custom_score ? 'Allowed' : 'Restricted' }}</div>
                                                    </div>
                                                    <div class="detail-stat">
                                                        <div class="label">Letter Grade</div>
                                                        <div class="value" style="font-size:.85rem;">
                                                            {{ $scale->usesLinkedGrading() ? ($scale->gradingScheme->name ?? 'Linked') : 'None (comment only)' }}
                                                        </div>
                                                    </div>
                                                    <div class="detail-stat">
                                                        <div class="label">Subjects Using</div>
                                                        <div class="value">{{ $scale->class_subjects_count }}</div>
                                                    </div>
                                                </div>

                                                <div class="section-label">System Comments (presets)</div>
                                                <table class="band-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Score / Range</th>
                                                            <th>Label (auto-fills the comment)</th>
                                                            <th>Remark</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($scale->presets as $preset)
                                                            <tr>
                                                                <td><span class="grade-chip">{{ $preset->rangeLabel() }}</span></td>
                                                                <td>{{ $preset->label }}</td>
                                                                <td>{{ $preset->remark ?? '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="as-list-panel">
                    <div class="as-empty-detail">
                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                        No assessment scales yet. Click "New Assessment Scale" to create your first one — e.g. the 1–3 Early
                        Years scale, an A–D effort rating, or an 80–100 reading level.
                    </div>
                </div>
            @endif

        </div>
    </div>
    </div>
    </div>
    </div>

    {{-- ── Create / Edit Modal (hidden template, injected via SweetAlert) ── --}}
    <template id="scaleFormTemplate">
        <form id="scaleForm" style="text-align:left; padding: 0.25rem 0;">
            <!-- Header Section -->
            <div
                style="background: linear-gradient(135deg, #F8F9FF 0%, #EEF2FF 100%); margin: -1.5rem -1.5rem 1.25rem -1.5rem; padding: 1.25rem 1.5rem 1rem 1.5rem; border-radius: 0.5rem 0.5rem 0 0; border-bottom: 2px solid #E0E7FF;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div
                        style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #2C29CA, #6366F1); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; box-shadow: 0 4px 12px rgba(44,41,202,0.25);">
                        <i class="fas fa-ruler-combined"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 700; font-size: 1.1rem; color: #1B1D28;">Scale Details</h4>
                        <p style="margin: 0; font-size: 0.75rem; color: #6B7280;">Configure the assessment scale and its
                            presets</p>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div style="grid-column: 1 / -1;">
                    <label
                        style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; letter-spacing: 0.02em;">
                        Scale Name <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="name" required
                        style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.85rem; border: 1.5px solid #E5E7EB; border-radius: 0.5rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                        placeholder="e.g. Early Years (1-3 Scale)"
                        onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.1)';"
                        onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; letter-spacing: 0.02em;">
                        Description
                    </label>
                    <input type="text" name="description"
                        style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.85rem; border: 1.5px solid #E5E7EB; border-radius: 0.5rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                        placeholder="Optional description"
                        onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.1)';"
                        onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; padding-top: 0.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_default" value="1"
                            style="width: 18px; height: 18px; accent-color: #2C29CA; cursor: pointer; border-radius: 4px;">
                        <label style="font-size: 0.8rem; font-weight: 500; color: #374151; cursor: pointer;">Set as
                            Default</label>
                    </div>
                </div>
            </div>

            <!-- Score Settings -->
            <div
                style="background: #F8FAFC; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 0.75rem; border: 1px solid #E5E7EB;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-sliders-h" style="color: #2C29CA; font-size: 0.8rem;"></i>
                    <span style="font-size: 0.75rem; font-weight: 600; color: #374151; letter-spacing: 0.02em;">Score
                        Range</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.7rem; font-weight: 500; color: #6B7280; margin-bottom: 0.2rem;">Minimum
                            Score <span style="color: #EF4444;">*</span></label>
                        <input type="number" step="0.01" name="min_score" value="1" required
                            style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.85rem; font-weight: 600; border: 1.5px solid #E5E7EB; border-radius: 0.4rem; background: white; transition: all 0.2s; outline: none;"
                            onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                            onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                    </div>
                    <div>
                        <label
                            style="display: block; font-size: 0.7rem; font-weight: 500; color: #6B7280; margin-bottom: 0.2rem;">Maximum
                            Score <span style="color: #EF4444;">*</span></label>
                        <input type="number" step="0.01" name="max_score" value="3" required
                            style="width: 100%; padding: 0.4rem 0.6rem; font-size: 0.85rem; font-weight: 600; border: 1.5px solid #E5E7EB; border-radius: 0.4rem; background: white; transition: all 0.2s; outline: none;"
                            onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                            onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; padding-top: 0.2rem;">
                        <input type="checkbox" name="allow_custom_score" id="allowCustomScoreCheckbox" value="1"
                            style="width: 18px; height: 18px; accent-color: #2C29CA; cursor: pointer; border-radius: 4px;">
                        <div>
                            <label
                                style="font-size: 0.75rem; font-weight: 500; color: #374151; cursor: pointer; display: block;">Custom
                                Scores</label>
                            <span style="font-size: 0.6rem; color: #6B7280;">Allow values outside range</span>
                        </div>
                    </div>
                </div>
                <p style="margin: 0.5rem 0 0; font-size: 0.68rem; color: #6B7280; line-height: 1.4;">
                    Min/Max define the <em>typical</em> range shown to teachers. With Custom Scores on, teachers can still
                    type any value beyond it — useful for outliers (e.g. an absent student) without needing a Letter Grade
                    to make sense of it.
                </p>
            </div>

            <!-- Grading Scheme -->
            <div style="margin-bottom: 0.75rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; letter-spacing: 0.02em;">
                            Letter Grade
                        </label>
                        <select name="grade_mode" id="gradeModeSelect"
                            style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.85rem; border: 1.5px solid #E5E7EB; border-radius: 0.5rem; background: #FAFBFC; transition: all 0.2s; outline: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 12 12%22><path fill=%22%236B7280%22 d=%22M6 8L1 3h10z%22/></svg>'); background-repeat: no-repeat; background-position: right 12px center; padding-right: 2.5rem;"
                            onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.1)';"
                            onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                            <option value="none">None — comment only</option>
                            <option value="linked_grading_scheme" id="linkedGradingOption">Link a Grading Scheme</option>
                        </select>
                    </div>
                    <div id="gradingSchemeWrap" style="display:none;">
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; letter-spacing: 0.02em;">
                            Grading Scheme <span style="color: #EF4444;">*</span>
                        </label>
                        <select name="grading_scheme_id"
                            style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.85rem; border: 1.5px solid #E5E7EB; border-radius: 0.5rem; background: #FAFBFC; transition: all 0.2s; outline: none; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 12 12%22><path fill=%22%236B7280%22 d=%22M6 8L1 3h10z%22/></svg>'); background-repeat: no-repeat; background-position: right 12px center; padding-right: 2.5rem;"
                            onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.1)';"
                            onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
                            <option value="">— Select Scheme —</option>
                            @foreach ($gradingSchemes as $gs)
                                <option value="{{ $gs->id }}">{{ $gs->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="customScoreConflictNotice"
                    style="display:none; margin-top:0.6rem; background:#FCF1DC; border:1px solid #F0D9A0; color:#8A5B0B; font-size:0.72rem; line-height:1.4; padding:0.5rem 0.7rem; border-radius:0.4rem;">
                    <i class="fas fa-triangle-exclamation" style="margin-right:0.3rem;"></i>
                    Custom Scores and a linked Grading Scheme can't be used together — an unbounded score has no fixed
                    ceiling to convert into a percentage. Turning one on switches the other off.
                </div>
            </div>

            <!-- Presets Section -->
            <div style="background: #F8FAFC; border-radius: 0.5rem; padding: 0.75rem 1rem; border: 1px solid #E5E7EB;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-list-ul" style="color: #2C29CA; font-size: 0.8rem;"></i>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #374151; letter-spacing: 0.02em;">System
                            Comments (Presets) <span style="color: #EF4444;">*</span></span>
                    </div>
                    <span
                        style="font-size: 0.6rem; color: #6B7280; background: #E5E7EB; padding: 0.15rem 0.5rem; border-radius: 1rem;">Minimum
                        1 required</span>
                </div>
                <p style="margin: 0 0 0.6rem; font-size: 0.68rem; color: #6B7280; line-height: 1.4;">
                    Leave "To" blank for a single value (e.g. From 3). Fill both to cover a whole band in one row (e.g. From
                    1, To 39 = "Needs Crucial Help") — handy for wide scales like 1-100 so you don't need a row per score.
                </p>

                <div
                    style="display: grid; grid-template-columns: 0.55fr 0.15fr 0.55fr 1.5fr 0.9fr 0.4fr; gap: 0.4rem; padding: 0 0.6rem; margin-bottom: 0.3rem;">
                    <span
                        style="font-size: 0.65rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">From</span>
                    <span></span>
                    <span style="font-size: 0.65rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">To
                        (optional)</span>
                    <span
                        style="font-size: 0.65rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Label</span>
                    <span
                        style="font-size: 0.65rem; font-weight: 700; color: #6B7280; text-transform: uppercase;">Remark</span>
                    <span></span>
                </div>
                <div id="presetRows" style="margin-bottom: 0.5rem;"></div>

                <button type="button" id="addPresetRow"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; font-size: 0.78rem; font-weight: 600; color: #2C29CA; background: #EEEDFC; border: 1.5px dashed #2C29CA; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#E0E7FF'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.background='#EEEDFC'; this.style.transform='none';">
                    <i class="fas fa-plus" style="font-size: 0.7rem;"></i> Add Comment
                </button>
            </div>
        </form>
    </template>

    <template id="presetRowTemplate">
        <div class="preset-row"
            style="display: grid; grid-template-columns: 0.55fr 0.15fr 0.55fr 1.5fr 0.9fr 0.4fr; gap: 0.4rem; align-items: center; margin-bottom: 0.4rem; padding: 0.4rem 0.6rem; background: white; border-radius: 0.4rem; border: 1px solid #E5E7EB; transition: all 0.2s;"
            onmouseover="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 2px 8px rgba(44,41,202,0.08)';"
            onmouseout="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
            <input type="number" step="0.01" class="preset-score preset-min"
                style="width: 100%; padding: 0.3rem 0.5rem; font-size: 0.82rem; font-weight: 600; border: 1.5px solid #E5E7EB; border-radius: 0.3rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                placeholder="1"
                onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
            <span style="text-align:center; font-size:0.75rem; color:#9CA3AF;">–</span>
            <input type="number" step="0.01" class="preset-max"
                style="width: 100%; padding: 0.3rem 0.5rem; font-size: 0.82rem; font-weight: 600; border: 1.5px solid #E5E7EB; border-radius: 0.3rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                placeholder="e.g. 39"
                onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
            <input type="text" class="preset-label"
                style="width: 100%; padding: 0.3rem 0.5rem; font-size: 0.82rem; border: 1.5px solid #E5E7EB; border-radius: 0.3rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                placeholder="Label (e.g. Excellent)"
                onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
            <input type="text" class="preset-remark"
                style="width: 100%; padding: 0.3rem 0.5rem; font-size: 0.82rem; border: 1.5px solid #E5E7EB; border-radius: 0.3rem; background: #FAFBFC; transition: all 0.2s; outline: none;"
                placeholder="Remark"
                onfocus="this.style.borderColor='#2C29CA'; this.style.boxShadow='0 0 0 3px rgba(44,41,202,0.08)';"
                onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none';">
            <button type="button" class="remove-preset-row"
                style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; padding: 0; font-size: 0.75rem; color: #EF4444; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 0.3rem; cursor: pointer; transition: all 0.2s;"
                onmouseover="this.style.background='#FEE2E2'; this.style.transform='scale(1.05)';"
                onmouseout="this.style.background='#FEF2F2'; this.style.transform='none';">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </template>

    <style>
        /* SweetAlert2 modal customization */
        .swal2-popup {
            border-radius: 1rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
        }

        .swal2-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1B1D28 !important;
            padding: 0 !important;
            margin-bottom: 0.5rem !important;
        }

        .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }

        .swal2-actions {
            margin-top: 1.25rem !important;
            gap: 0.5rem !important;
        }

        .swal2-confirm {
            padding: 0.6rem 1.8rem !important;
            font-weight: 600 !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            background: linear-gradient(135deg, #2C29CA, #6366F1) !important;
            box-shadow: 0 4px 14px rgba(44, 41, 202, 0.25) !important;
            transition: all 0.2s !important;
            letter-spacing: 0.01em !important;
        }

        .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(44, 41, 202, 0.35) !important;
        }

        .swal2-cancel {
            padding: 0.6rem 1.8rem !important;
            font-weight: 600 !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            background: #F3F4F6 !important;
            color: #374151 !important;
            border: 1px solid #E5E7EB !important;
            transition: all 0.2s !important;
        }

        .swal2-cancel:hover {
            background: #E5E7EB !important;
            transform: translateY(-2px) !important;
        }

        .swal2-close {
            color: #6B7280 !important;
            transition: all 0.2s !important;
        }

        .swal2-close:hover {
            color: #1B1D28 !important;
            transform: rotate(90deg) !important;
        }
    </style>

    <template id="presetRowTemplate">
        <div class="preset-row">
            <input type="number" step="0.01" class="form-control form-control-sm preset-score" placeholder="1">
            <input type="text" class="form-control form-control-sm preset-label"
                placeholder="Works under Teacher's Guidance">
            <input type="text" class="form-control form-control-sm preset-remark" placeholder="Fair">
            <button type="button" class="btn btn-sm btn-outline-danger remove-preset-row"><i
                    class="fas fa-times"></i></button>
        </div>
    </template>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';

        document.querySelectorAll('.scale-list-item').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.scale-list-item').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.as-detail').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.target)?.classList.add('active');
            });
        });

        function addPresetRow(container, preset = null) {
            const tpl = document.getElementById('presetRowTemplate').content.cloneNode(true);
            const row = tpl.querySelector('.preset-row');
            if (preset) {
                const min = preset.min_score ?? preset.score ?? '';
                const max = preset.max_score ?? preset.score ?? '';
                row.querySelector('.preset-min').value = min;
                // Only show a "To" value when it's an actual range — a
                // single-value preset (min === max) should render as just
                // the one number, matching how it was originally entered.
                row.querySelector('.preset-max').value = (max !== '' && parseFloat(max) !== parseFloat(min)) ? max : '';
                row.querySelector('.preset-label').value = preset.label ?? '';
                row.querySelector('.preset-remark').value = preset.remark ?? '';
            }
            row.querySelector('.remove-preset-row').addEventListener('click', () => row.remove());
            container.appendChild(row);
        }

        function collectPresets(container) {
            const presets = [];
            const skippedRows = [];
            container.querySelectorAll('.preset-row').forEach((row, i) => {
                const minEl = row.querySelector('.preset-min');
                const maxEl = row.querySelector('.preset-max');
                const labelEl = row.querySelector('.preset-label');
                const min = minEl ? minEl.value.trim() : '';
                const max = maxEl ? maxEl.value.trim() : '';
                const label = labelEl ? labelEl.value.trim() : '';

                if (min === '' && !label) {
                    // A genuinely blank row (e.g. one that was added but
                    // never filled in) — silently ignore it rather than
                    // blocking save over an empty row nobody meant to use.
                    return;
                }
                if (min === '' || !label) {
                    skippedRows.push({ row: i + 1, missing: min === '' ? 'From score' : 'Label' });
                    return;
                }
                if (max !== '' && parseFloat(max) < parseFloat(min)) {
                    skippedRows.push({ row: i + 1, missing: null, rangeError: true, min, max });
                    return;
                }

                presets.push({
                    min_score: parseFloat(min),
                    max_score: max !== '' ? parseFloat(max) : parseFloat(min),
                    label,
                    remark: row.querySelector('.preset-remark').value.trim() || null,
                });
            });
            collectPresets.lastSkipped = skippedRows;
            return presets;
        }

        // NOTE: openScaleModal is fully (re)defined further down this file,
        // where it's wired up with a loading-overlay UX. That is the
        // version actually used everywhere below — this file used to also
        // carry an earlier, now-unused definition here, which has been
        // removed to avoid two copies drifting out of sync.

        document.getElementById('btnNewScale').addEventListener('click', () => openScaleModal());

        document.querySelectorAll('.edit-scale').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const scale = window.__scalesById[this.dataset.id];
                openScaleModal(scale);
            });
        });

        document.querySelectorAll('.toggle-scale').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                const nextActive = this.dataset.active === '1' ? 0 : 1;
                $.ajax({
                    url: `{{ url('examinations/assessment-scales') }}/${id}/toggle-active`,
                    method: 'POST',
                    data: { is_active: nextActive },
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function () { window.location.reload(); },
                    error: function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Could not update scale.', 'error'); },
                });
            });
        });

        document.querySelectorAll('.delete-scale').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Delete this assessment scale?',
                    text: 'This cannot be undone. Scales attached to a class subject cannot be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    confirmButtonColor: '#C4293A',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `{{ url('examinations/assessment-scales') }}/${id}`,
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({ icon: 'success', title: 'Deleted', confirmButtonColor: '#2C29CA' })
                                    .then(() => window.location.reload());
                            } else {
                                Swal.fire('Cannot Delete', res.message, 'warning');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Cannot Delete', xhr.responseJSON?.message || 'Could not delete scale.', 'warning');
                        },
                    });
                });
            });
        });
    </script>

    // Add this script after the existing scripts
    <script>
        // Enhanced function with loader for creating new scale
        document.getElementById('btnNewScale').addEventListener('click', function () {
            // Show loading state on button
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;

            // Open modal after a small delay to show loader
            setTimeout(() => {
                openScaleModal();
                // Reset button after modal opens
                this.innerHTML = originalHtml;
                this.disabled = false;
            }, 100);
        });

        // openScaleModal: opens the create/edit form with a loading overlay while
        // the save request is in flight.
        function openScaleModal(existing = null) {
            const isEdit = !!existing;

            // Show loading overlay when saving
            let loadingOverlay = null;

            const showLoader = () => {
                loadingOverlay = document.createElement('div');
                loadingOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                backdrop-filter: blur(4px);
            `;
                loadingOverlay.innerHTML = `
                <div style="
                    background: white;
                    padding: 2rem 3rem;
                    border-radius: 1rem;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                    animation: scaleIn 0.1s ease;
                ">
                    <div style="
                        width: 50px;
                        height: 50px;
                        border: 4px solid #E5E7EB;
                        border-top: 4px solid #2C29CA;
                        border-radius: 50%;
                        margin: 0 auto 1rem auto;
                        animation: spin 1s linear infinite;
                    "></div>
                    <p style="
                        margin: 0;
                        font-family: 'Inter', sans-serif;
                        font-weight: 600;
                        color: #1B1D28;
                        font-size: 1rem;
                    ">${isEdit ? 'Saving changes...' : 'Creating scale...'}</p>
                    <p style="
                        margin: 0.25rem 0 0 0;
                        font-family: 'Inter', sans-serif;
                        font-size: 0.85rem;
                        color: #6B7280;
                    ">Please wait while we process your request</p>
                </div>
            `;
                document.body.appendChild(loadingOverlay);

                // Add keyframe animation
                if (!document.getElementById('loaderStyles')) {
                    const style = document.createElement('style');
                    style.id = 'loaderStyles';
                    style.textContent = `
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    @keyframes scaleIn {
                        from { transform: scale(0.9); opacity: 0; }
                        to { transform: scale(1); opacity: 1; }
                    }
                `;
                    document.head.appendChild(style);
                }
            };

            const hideLoader = () => {
                if (loadingOverlay && loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                    loadingOverlay = null;
                }
            };

            // Original SweetAlert logic with loader integration
            Swal.fire({
                title: isEdit ? 'Edit Assessment Scale' : 'New Assessment Scale',
                html: document.getElementById('scaleFormTemplate').innerHTML,
                width: 700,
                showCancelButton: true,
                confirmButtonText: isEdit ? 'Save Changes' : 'Create Scale',
                confirmButtonColor: '#2C29CA',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'as-swal-confirm',
                    cancelButton: 'as-swal-cancel'
                },
                focusConfirm: false,
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const container = document.getElementById('presetRows');
                    const gradeModeSelect = popup.querySelector('#gradeModeSelect');
                    const gradingSchemeWrap = popup.querySelector('#gradingSchemeWrap');
                    const allowCustomCheckbox = popup.querySelector('#allowCustomScoreCheckbox');
                    const conflictNotice = popup.querySelector('#customScoreConflictNotice');
                    const linkedGradingOption = popup.querySelector('#linkedGradingOption');

                    // Custom (unbounded) scores and a linked, percentage-based
                    // grading scheme can't coexist — see AssessmentScale::
                    // usesLinkedGrading(). Keep the two controls in sync so the
                    // form can never be submitted in that contradictory state.
                    function syncCustomScoreVsGrading(source) {
                        const customOn = allowCustomCheckbox.checked;
                        const linkedSelected = gradeModeSelect.value === 'linked_grading_scheme';

                        if ((source === 'custom' || source === null) && customOn && linkedSelected) {
                            gradeModeSelect.value = 'none';
                            gradingSchemeWrap.style.display = 'none';
                        } else if (source === 'grade' && linkedSelected && customOn) {
                            allowCustomCheckbox.checked = false;
                        }

                        linkedGradingOption.disabled = allowCustomCheckbox.checked;
                        conflictNotice.style.display = allowCustomCheckbox.checked ? '' : 'none';
                        gradingSchemeWrap.style.display = gradeModeSelect.value === 'linked_grading_scheme' ? '' : 'none';
                    }

                    gradeModeSelect.addEventListener('change', function () {
                        gradingSchemeWrap.style.display = this.value === 'linked_grading_scheme' ? '' : 'none';
                        syncCustomScoreVsGrading('grade');
                    });
                    allowCustomCheckbox.addEventListener('change', () => syncCustomScoreVsGrading('custom'));

                    if (isEdit) {
                        popup.querySelector('[name="name"]').value = existing.name;
                        popup.querySelector('[name="description"]').value = existing.description ?? '';
                        popup.querySelector('[name="min_score"]').value = existing.min_score;
                        popup.querySelector('[name="max_score"]').value = existing.max_score;
                        popup.querySelector('[name="allow_custom_score"]').checked = !!existing.allow_custom_score;
                        popup.querySelector('[name="is_default"]').checked = !!existing.is_default;
                        gradeModeSelect.value = existing.grade_mode || 'none';
                        if (existing.grade_mode === 'linked_grading_scheme') {
                            gradingSchemeWrap.style.display = '';
                            popup.querySelector('[name="grading_scheme_id"]').value = existing.grading_scheme_id ?? '';
                        }
                        (existing.presets || []).forEach(p => addPresetRow(container, p));
                    } else {
                        [
                            { score: 1, label: "Works under Teacher's Guidance", remark: 'Fair' },
                            { score: 2, label: 'Works with Minimum Supervision', remark: 'Good' },
                            { score: 3, label: 'Works Independently', remark: 'Excellent' },
                        ].forEach(p => addPresetRow(container, p));
                    }

                    syncCustomScoreVsGrading(null);

                    document.getElementById('addPresetRow').addEventListener('click', () => addPresetRow(container));

                    // Add cancel button style
                    const cancelBtn = document.querySelector('.swal2-cancel');
                    if (cancelBtn) {
                        cancelBtn.style.cssText = `
                        padding: 0.6rem 1.8rem;
                        font-weight: 600;
                        border-radius: 0.5rem;
                        font-size: 0.85rem;
                        background: #F3F4F6;
                        color: #374151;
                        border: 1px solid #E5E7EB;
                        transition: all 0.2s;
                    `;
                    }
                },
                preConfirm: () => {
                    const popup = Swal.getPopup();
                    const name = popup.querySelector('[name="name"]').value.trim();
                    const minScore = popup.querySelector('[name="min_score"]').value;
                    const maxScore = popup.querySelector('[name="max_score"]').value;
                    const gradeMode = popup.querySelector('[name="grade_mode"]').value;
                    const gradingSchemeId = popup.querySelector('[name="grading_scheme_id"]')?.value || null;
                    const allowCustomScore = popup.querySelector('[name="allow_custom_score"]').checked;
                    const presets = collectPresets(document.getElementById('presetRows'));

                    if (!name) {
                        Swal.showValidationMessage('Please enter a scale name.');
                        return false;
                    }
                    if (minScore === '' || maxScore === '' || parseFloat(minScore) >= parseFloat(maxScore)) {
                        Swal.showValidationMessage('Max score must be greater than min score.');
                        return false;
                    }
                    if (gradeMode === 'linked_grading_scheme' && !gradingSchemeId) {
                        Swal.showValidationMessage('Pick a grading scheme, or switch Letter Grade back to "None".');
                        return false;
                    }
                    if (allowCustomScore && gradeMode === 'linked_grading_scheme') {
                        Swal.showValidationMessage('Custom Scores and a linked Grading Scheme can\'t be combined. Turn one off.');
                        return false;
                    }
                    if (presets.length < 1) {
                        const skipped = collectPresets.lastSkipped || [];
                        if (skipped.length) {
                            const first = skipped[0];
                            Swal.showValidationMessage(`Row ${first.row} is missing its ${first.missing} — fill it in, or remove the row with the × button.`);
                        } else {
                            Swal.showValidationMessage('Add at least one system comment (a Score and a Label) below.');
                        }
                        return false;
                    }
                    // Presets only have to sit inside min/max when custom (unbounded)
                    // scores are off — with Custom Scores on, min/max is just the
                    // "typical" range and presets are free to sit outside it.
                    if (!allowCustomScore) {
                        for (const p of presets) {
                            if (p.score < parseFloat(minScore) || p.score > parseFloat(maxScore)) {
                                Swal.showValidationMessage(`Comment score ${p.score} is outside the ${minScore}-${maxScore} range.`);
                                return false;
                            }
                        }
                    }

                    return {
                        name,
                        description: popup.querySelector('[name="description"]').value.trim() || null,
                        min_score: minScore,
                        max_score: maxScore,
                        allow_custom_score: allowCustomScore ? 1 : 0,
                        grade_mode: gradeMode,
                        grading_scheme_id: gradeMode === 'linked_grading_scheme' ? gradingSchemeId : null,
                        is_default: popup.querySelector('[name="is_default"]').checked ? 1 : 0,
                        presets,
                    };
                },
            }).then(result => {
                if (!result.isConfirmed) {
                    hideLoader();
                    return;
                }

                // Show loader before making the request
                showLoader();

                const url = isEdit
                    ? `{{ url('examinations/assessment-scales') }}/${existing.id}/update`
                    : `{{ route('examination.assessment-scales.store') }}`;

                $.ajax({
                    url,
                    method: 'POST',
                    data: JSON.stringify(result.value),
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function (res) {
                        hideLoader();
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: isEdit ? 'Saved!' : 'Created!',
                                text: res.message,
                                confirmButtonColor: '#2C29CA',
                                timer: 1000,
                                timerProgressBar: true
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        hideLoader();
                        const message = xhr.responseJSON?.message
                            || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).map(e => e[0]).join('\n') : 'Something went wrong.');
                        Swal.fire('Error', message, 'error');
                    },
                });
            });
        };

        // Add styles for enhanced loader
        const styleSheet = document.createElement("style");
        styleSheet.textContent = `
        .as-swal-cancel {
            padding: 0.6rem 1.8rem !important;
            font-weight: 600 !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            background: #F3F4F6 !important;
            color: #374151 !important;
            border: 1px solid #E5E7EB !important;
            transition: all 0.2s !important;
        }
        .as-swal-cancel:hover {
            background: #E5E7EB !important;
            transform: translateY(-2px) !important;
        }
        .swal2-popup {
            border-radius: 1rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
        }
        .swal2-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1B1D28 !important;
            padding: 0 !important;
            margin-bottom: 0.5rem !important;
        }
        .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .swal2-actions {
            margin-top: 1.25rem !important;
            gap: 0.5rem !important;
        }
        .swal2-confirm {
            padding: 0.6rem 1.8rem !important;
            font-weight: 600 !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            background: linear-gradient(135deg, #2C29CA, #6366F1) !important;
            box-shadow: 0 4px 14px rgba(44,41,202,0.25) !important;
            transition: all 0.2s !important;
            letter-spacing: 0.01em !important;
        }
        .swal2-confirm:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(44,41,202,0.35) !important;
        }
        .swal2-close {
            color: #6B7280 !important;
            transition: all 0.2s !important;
        }
        .swal2-close:hover {
            color: #1B1D28 !important;
            transform: rotate(90deg) !important;
        }
    `;
        document.head.appendChild(styleSheet);
    </script>

    // Add this after your existing scripts
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the currently selected scale ID from localStorage
        const selectedScaleId = localStorage.getItem('selectedScaleId');
        
        if (selectedScaleId) {
            // Find the list item button with matching data-target
            const targetButton = document.querySelector(`.scale-list-item[data-target="scale-detail-${selectedScaleId}"]`);
            
            if (targetButton) {
                // Remove active class from all items
                document.querySelectorAll('.scale-list-item').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.as-detail').forEach(d => d.classList.remove('active'));
                
                // Add active class to the selected button and its corresponding detail
                targetButton.classList.add('active');
                const detailElement = document.getElementById(`scale-detail-${selectedScaleId}`);
                if (detailElement) {
                    detailElement.classList.add('active');
                }
            } else {
                // If the saved scale doesn't exist (e.g., was deleted), fallback to first
                fallbackToFirstScale();
            }
        } else {
            // If no scale was previously selected, show the first one
            fallbackToFirstScale();
        }

        // Add click event listeners to all scale list items
        document.querySelectorAll('.scale-list-item').forEach(btn => {
            btn.addEventListener('click', function() {
                // Extract the scale ID from data-target attribute
                const targetId = this.dataset.target;
                const scaleId = targetId.replace('scale-detail-', '');
                
                // Store the selected scale ID in localStorage
                localStorage.setItem('selectedScaleId', scaleId);
                
                // Update UI
                document.querySelectorAll('.scale-list-item').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.as-detail').forEach(d => d.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(targetId)?.classList.add('active');
            });
        });
    });

    // Helper function to fallback to the first scale
    function fallbackToFirstScale() {
        const firstButton = document.querySelector('.scale-list-item');
        if (firstButton) {
            const targetId = firstButton.dataset.target;
            const scaleId = targetId.replace('scale-detail-', '');
            localStorage.setItem('selectedScaleId', scaleId);
            
            firstButton.classList.add('active');
            document.getElementById(targetId)?.classList.add('active');
        }
    }
</script>

    @php
        $scalesData = [];
        foreach ($scales as $scale) {
            $scalesData[$scale->id] = [
                'id' => $scale->id,
                'name' => $scale->name,
                'description' => $scale->description,
                'min_score' => $scale->min_score,
                'max_score' => $scale->max_score,
                'allow_custom_score' => $scale->allow_custom_score,
                'grade_mode' => $scale->grade_mode,
                'grading_scheme_id' => $scale->grading_scheme_id,
                'is_default' => $scale->is_default,
                'presets' => $scale->presets->map(fn($p) => [
                    'score' => $p->score,
                    'min_score' => $p->min_score ?? $p->score,
                    'max_score' => $p->max_score ?? $p->score,
                    'label' => $p->label,
                    'remark' => $p->remark,
                ])->toArray(),
            ];
        }
    @endphp

    <script>
        window.__scalesById = {!! json_encode($scalesData) !!};
    </script>
@endsection