@extends('layouts-side-bar.master')

@php
    use App\Helpers\PermissionHelper;
@endphp

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --navy: #0A2463;
            --electric: #3E92CC;
            --sky: #A7D3FF;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.10);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--light-gray);
        }

        /* ── Hero ── */
        .fin-hero {
            background: linear-gradient(135deg, var(--navy) 0%, #1a2744 55%, var(--navy) 100%);
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 2.5rem 2rem 4rem;
            margin-bottom: -2rem;
            margin-top: 1.5rem;
            position: relative;
            overflow: hidden;
            color: var(--white);
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(62, 146, 204, 0.20) 0%, transparent 70%);
        }

        .fin-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 5%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(10, 36, 99, 0.15) 0%, transparent 70%);
        }

        .fin-hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fin-hero p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0.25rem 0 0;
            font-size: 0.92rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(62, 146, 204, 0.2);
            border: 1px solid rgba(62, 146, 204, 0.35);
            color: var(--sky);
            padding: 0.28rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.7rem;
        }

        .hero-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.82rem;
        }

        .hero-stat strong {
            color: var(--white);
            font-weight: 600;
        }

        /* Mark all read pill inside hero */
        .alert-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .alert-pill:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .kpi {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.4rem 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .kpi:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .kpi-accent {
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            border-radius: 0 var(--radius) var(--radius) 0;
            background: var(--electric);
        }

        .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.9rem;
            background: rgba(62, 146, 204, 0.1);
            color: var(--electric);
        }

        .kpi-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            font-family: 'JetBrains Mono', monospace;
        }

        .kpi-val small {
            font-size: 0.85rem;
            font-weight: 400;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
        }

        .kpi-lbl {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-top: 0.28rem;
            font-weight: 500;
        }

        /* ── Card (Feed) ── */
        .fc {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .fc-hd {
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fc-hd h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }

        .fc-bd {
            padding: 1.4rem;
        }

        .sec-link {
            font-size: 0.78rem;
            color: var(--electric);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            transition: var(--transition);
        }

        .sec-link:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        /* ── Transaction-like notification row ── */
        .txn {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.12s;
            cursor: pointer;
        }

        .txn:last-child {
            border-bottom: none;
        }

        .txn:hover {
            background: rgba(62, 146, 204, 0.03);
        }

        .txn-ico {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .txn-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.15rem;
        }

        .txn-meta {
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 0.05rem;
        }

        .txn-extra {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-left: auto;
            white-space: nowrap;
            font-family: 'JetBrains Mono', monospace;
        }

        .unread-dot {
            width: 8px;
            height: 8px;
            background: var(--electric);
            border-radius: 50%;
            display: inline-block;
            margin-left: 0.5rem;
            vertical-align: middle;
        }

        /* ── Pagination ── */
        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $totalNotifications = $items->total() ?? $items->count();
        $unreadCount = $unreadCount ?? 0;
        $lastNotification = $items->first()?->notification->created_at?->diffForHumans() ?? 'N/A';
    @endphp

    <div class="side-app">
        <div class="container-fluid">
            {{-- Hero Banner --}}
            <div class="fin-hero">
                <div style="position: relative; z-index: 1;">
                    <div class="hero-badge"><i class="fas fa-bell"></i> Notifications</div>
                    <h1><i class="fas fa-envelope-open-text" style="opacity: 0.8;"></i> My Notifications</h1>
                    <p class="mt-2">Stay updated with school activities and important alerts</p>
                    <div style="display: flex; gap: 2rem; margin-top: 1.2rem; flex-wrap: wrap;">
                        <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: var(--sky);"></i>
                            <strong>{{ $totalNotifications }}</strong> total
                        </div>
                        <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: #f87171;"></i>
                            <strong>{{ $unreadCount }}</strong> unread
                        </div>
                        <div class="hero-stat"><i class="fas fa-circle" style="font-size: 0.5rem; color: #fbbf24;"></i>
                            <strong>{{ $lastNotification }}</strong> latest
                        </div>
                    </div>
                    @if($unreadCount > 0 && PermissionHelper::canFeature('mark_all_read'))
                        <div style="position: relative; z-index: 1; margin-top: 1.2rem;">
                            <button class="alert-pill"
                                style="background: rgba(62,146,204,0.25); color: var(--sky); border: 1px solid rgba(62,146,204,0.4);"
                                id="markAllReadBtn">
                                <i class="fas fa-check-double"></i> Mark all as read
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- KPI Row --}}
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="kpi-accent" style="background: var(--electric);"></div>
                    <div class="kpi-icon"><i class="fas fa-bell"></i></div>
                    <div class="kpi-val"><small>All </small>{{ $totalNotifications }}</div>
                    <div class="kpi-lbl">Total Notifications</div>
                </div>
                <div class="kpi">
                    <div class="kpi-accent" style="background: #dc2626;"></div>
                    <div class="kpi-icon" style="background: rgba(220,38,38,0.1); color: #dc2626;"><i
                            class="fas fa-exclamation-circle"></i></div>
                    <div class="kpi-val"><small>Unread </small>{{ $unreadCount }}</div>
                    <div class="kpi-lbl">Need Attention</div>
                </div>
                <div class="kpi">
                    <div class="kpi-accent" style="background: var(--navy);"></div>
                    <div class="kpi-icon" style="background: rgba(10,36,99,0.1); color: var(--navy);"><i
                            class="fas fa-clock"></i></div>
                    <div class="kpi-val" style="font-size: 1rem;"><small> </small>{{ $lastNotification }}</div>
                    <div class="kpi-lbl">Latest Activity</div>
                </div>
            </div>

            {{-- All Notifications Card --}}
            <div class="row" style="margin: 0 -10px;">
                <div class="col-lg-12" style="padding: 0 10px 20px;">
                    <div class="fc">
                        <div class="fc-hd">
                            <h3><i class="fas fa-list" style="color: var(--electric);"></i> All Notifications</h3>
                            <!-- <a href="#" class="sec-link">Refresh <i class="fas fa-sync-alt"></i></a> -->
                        </div>
                        <div class="fc-bd" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            @if($items->count() > 0)
                                @foreach($items as $item)
                                    @php
                                        $color = $item->notification->color ?? 'secondary';
                                        $icon = $item->notification->icon ?? 'bell';
                                        $bgColor = match ($color) {
                                            'primary' => '#e0e7ff', 'success' => '#d1fae5', 'danger' => '#fee2e2',
                                            'warning' => '#fef3c7', 'info' => '#dbeafe', default => '#f1f5f9'
                                        };
                                        $textColor = match ($color) {
                                            'primary' => '#3730a3', 'success' => '#065f46', 'danger' => '#991b1b',
                                            'warning' => '#92400e', 'info' => '#1e40af', default => '#334155'
                                        };
                                    @endphp
                                    <div class="txn notification-item {{ !$item->is_read ? 'unread' : '' }}"
                                        data-id="{{ $item->notification->id }}" data-read="{{ $item->is_read ? '1' : '0' }}"
                                        @if(PermissionHelper::canFeature('mark_notification_read')) onclick="markRead(this)" @endif>
                                        <div class="txn-ico" style="background:{{ $bgColor }}; color:{{ $textColor }};">
                                            <i class="fas fa-{{ $icon }}"></i>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div class="txn-name">{{ $item->notification->title }}</div>
                                            <div class="txn-meta">{{ Str::limit($item->notification->body, 80) }}</div>
                                        </div>
                                        <div class="txn-extra">
                                            {{ $item->notification->created_at->diffForHumans() }}
                                            @if(!$item->is_read)
                                                <span class="unread-dot"></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                                    <i class="fas fa-bell-slash"
                                        style="font-size: 2rem; opacity: 0.25; display: block; margin-bottom: 0.5rem;"></i>
                                    No notifications yet
                                </div>
                            @endif
                        </div>
                        @if($items->hasPages())
                            <div class="pagination-container"
                                style="padding: 1rem 1.4rem; border-top: 1px solid var(--border);">
                                {{ $items->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Mark single notification as read
            window.markRead = function (el) {
                const id = el.dataset.id;
                const alreadyRead = el.dataset.read === '1';
                if (alreadyRead) return;

                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                }).then(response => {
                    if (response.ok) {
                        el.classList.remove('unread');
                        el.dataset.read = '1';
                        const dot = el.querySelector('.unread-dot');
                        if (dot) dot.remove();
                        // Update unread count in KPI & hero
                        updateUnreadCount(-1);
                    }
                });
            };

            // Mark all as read
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function () {
                    fetch('{{ route('notifications.read-all') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    }).then(() => location.reload());
                });
            }

            function updateUnreadCount(delta) {
                // Update KPI "Need Attention"
                const kpiVals = document.querySelectorAll('.kpi-val');
                if (kpiVals.length >= 2) {
                    const unreadKpi = kpiVals[1];
                    const current = parseInt(unreadKpi.textContent.replace(/\D/g, '')) || 0;
                    unreadKpi.innerHTML = '<small>Unread </small>' + (current + delta);
                }
                // Update hero stat
                const heroStats = document.querySelectorAll('.hero-stat strong');
                if (heroStats.length >= 2) {
                    const heroUnread = heroStats[1];
                    const current = parseInt(heroUnread.textContent.replace(/\D/g, '')) || 0;
                    heroUnread.textContent = current + delta;
                }
                // Hide mark all button if unread reaches zero
                if (delta < 0) {
                    const totalUnread = (parseInt(document.querySelector('.kpi-val:nth-child(2)')?.textContent) || 0) + delta;
                    if (totalUnread <= 0 && markAllBtn) {
                        markAllBtn.parentElement.remove();
                    }
                }
            }
        });
    </script>
@endsection