<!-- app header -->
<div class="app-header header top-header">
    <div class="container-fluid">
        <div class="d-flex">
            <a class="header-brand" href="{{ url('/' . ($page = '#')) }}">
                <img src="{{ URL::asset('assets/images/brand/logo.png') }}" class="header-brand-img desktop-lgo"
                    alt="SMASA logo">
            </a>

            @php
                use App\Http\Controllers\Helper;
            @endphp

            <style>
                .swal2-container {
                    z-index: 20000 !important;
                }

                /* Hide by default (small devices) */
                .responsive-user-section {
                    display: none !important;
                }

                /* Show only on large screens (1372px and above) */
                @media (min-width: 1372px) {
                    .responsive-user-section {
                        display: flex !important;
                    }
                }

                @media (max-width: 576px) {
                    .admin-school-dropdown .dropdown-menu {
                        width: 95% !important;
                        left: 2.5% !important;
                    }
                }

                .dropdown-menu .form-control {
                    width: 100%;
                    box-sizing: border-box;
                }

                /* Responsive school dropdown */
                .admin-school-dropdown {
                    display: flex !important;
                    justify-content: center;
                    align-items: center;
                    flex: 1 1 auto;
                    min-width: 0;
                    max-width: 700px;
                    padding: 0 10px;
                }

                .admin-school-dropdown .dropdown {
                    width: 100%;
                }

                .admin-school-dropdown .dropdown button {
                    width: 100%;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                .admin-school-dropdown .dropdown-menu {
                    width: 100% !important;
                    left: 0 !important;
                    right: 0 !important;
                }

                @media (max-width: 989px),
                (max-height: 702px) {
                    .admin-school-dropdown {
                        max-width: 80%;
                        margin: 6px 0 !important;
                        padding: 0 6px;
                    }

                    .admin-school-dropdown .dropdown button {
                        font-size: 13px;
                    }
                }

                @media (max-width: 576px) {
                    .admin-school-dropdown .dropdown button {
                        font-size: 12px;
                        padding: 5px 8px;
                    }
                }

                @keyframes blink {
                    0% {
                        opacity: 1;
                        background-color: #dc3545;
                        color: white;
                    }

                    50% {
                        opacity: 0.7;
                        background-color: #ffc107;
                        color: #dc3545;
                    }

                    100% {
                        opacity: 1;
                        background-color: #dc3545;
                        color: white;
                    }
                }

                @keyframes glow {
                    0% {
                        text-shadow: 0 0 5px #dc3545;
                    }

                    50% {
                        text-shadow: 0 0 20px #ff0000, 0 0 30px #ff0000;
                    }

                    100% {
                        text-shadow: 0 0 5px #dc3545;
                    }
                }

                .password-warning-link {
                    animation: blink 1s infinite;
                    padding: 8px 20px;
                    border-radius: 50px;
                    font-weight: bold;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    border: 2px solid #fff;
                    box-shadow: 0 0 15px rgba(220, 53, 69, 0.5);
                    cursor: pointer;
                    transition: transform 0.3s ease;
                }

                .password-warning-link:hover {
                    transform: scale(1.05);
                }

                .password-warning-link i {
                    animation: glow 1s infinite;
                }

                .password-warning-link span {
                    animation: glow 1s infinite;
                }

                /* Modal Animation */
                @keyframes modalSlideIn {
                    from {
                        transform: translateY(-100px);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .modal-content {
                    animation: modalSlideIn 0.3s ease-out;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                }

                .modal-header {
                    border-radius: 15px 15px 0 0;
                    background: linear-gradient(135deg, #2C29CA, #2C29CA);
                }

                /* Input focus effects */
                .form-control:focus {
                    border-color: #dc3545;
                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                }

                /* Toggle password button hover */
                .toggle-password:hover {
                    background-color: #dc3545;
                    border-color: #dc3545;
                    color: white;
                }

                /* Password strength indicator - Simplified */
                .password-strength-weak {
                    color: #ffc107;
                }

                .password-strength-valid {
                    color: #28a745;
                }

                /* Responsive Active Info Display */
                @media (max-width: 768px) {
                    .active-info-container {
                        flex-direction: row !important;
                        flex-wrap: nowrap !important;
                        justify-content: center !important;
                        align-items: center !important;
                        gap: 10px;
                        width: 100%;
                    }

                    .active-year-badge,
                    .active-term-badge {
                        margin-right: 0 !important;
                        font-size: 12px;
                        text-align: center;
                        white-space: nowrap;
                    }

                    .active-year-badge i,
                    .active-term-badge i {
                        font-size: 12px;
                    }

                    .active-year-badge span,
                    .active-term-badge span {
                        font-size: 11px;
                    }
                }

                @media (max-width: 576px) {

                    .active-year-badge,
                    .active-term-badge {
                        font-size: 10px;
                        white-space: normal;
                        word-break: keep-all;
                    }

                    .active-year-badge .ml-1,
                    .active-term-badge .ml-1 {
                        margin-left: 4px !important;
                    }
                }

                /* For very small devices */
                @media (max-width: 480px) {

                    .active-year-badge,
                    .active-term-badge {
                        font-size: 9px;
                    }

                    .active-year-badge i,
                    .active-term-badge i {
                        font-size: 10px;
                        margin-right: 3px !important;
                    }
                }

                @keyframes softBlink {
                    0% {
                        opacity: 1;
                        background-color: #ffc107;
                        color: #856404;
                    }

                    50% {
                        opacity: 0.85;
                        background-color: #ff1707;
                        color: #856404;
                    }

                    100% {
                        opacity: 1;
                        background-color: #ffc107;
                        color: #856404;
                    }
                }

                @keyframes softGlow {
                    0% {
                        text-shadow: 0 0 2px #ffc107;
                    }

                    50% {
                        text-shadow: 0 0 8px #ff9800, 0 0 12px #ff9800;
                    }

                    100% {
                        text-shadow: 0 0 2px #ffc107;
                    }
                }

                .academic-term-warning-link {
                    animation: softBlink 2s infinite;
                    padding: 6px 15px;
                    border-radius: 50px;
                    font-weight: bold;
                    letter-spacing: 0.5px;
                    border: 1px solid #ffc107;
                    box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
                    cursor: pointer;
                    transition: transform 0.3s ease;
                    background-color: #ffc107;
                }

                .academic-term-warning-link:hover {
                    transform: scale(1.02);
                    animation: none;
                    background-color: #ffca2c;
                }

                .academic-term-warning-link i {
                    animation: softGlow 2s infinite;
                }

                .academic-term-warning-link span {
                    animation: softGlow 2s infinite;
                }

                /* Notification dropdown styling */
                #notifBell .dropdown-menu {
                    border: none;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
                    border-radius: 10px;
                    overflow: hidden;
                    z-index: 99999 !important;
                    max-width: calc(100vw - 16px);
                    right: 0 !important;
                    left: auto !important;
                    transform: none !important;

                    top: 100% !important;
                    margin-top: 1em !important;
                }

                #notifBell .dropdown-menu .notif-header {
                    background: #fafbfc;
                }

                .notif-drop-item {
                    transition: background-color 0.15s ease;
                }

                .notif-drop-item:hover {
                    background-color: #f1f3f6 !important;
                }

                .notif-icon-badge {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 36px;
                    height: 36px;
                    min-width: 36px;
                    border-radius: 50%;
                    font-size: 0.85rem;
                }

                @media (max-width: 576px) {
                    #notifBell .dropdown-menu {
                        width: 75vw !important;
                        max-width: 450px;
                    }
                }

                .app-header,
                .app-header .container-fluid {
                    overflow: visible !important;
                }

                @media (max-width: 768px) {
                    .app-header .container-fluid>.d-flex {
                        flex-wrap: wrap;
                        row-gap: 8px;
                    }

                    .active-info-container {
                        order: 1;
                    }

                    #pushStatusWrap,
                    #notifBell,
                    .profile-dropdown {
                        order: 2;
                    }

                    .app-header .container-fluid>.d-flex>.d-flex.order-lg-2.ml-auto.align-items-center:last-child {
                        width: 100%;
                        justify-content: center;
                        margin-top: 4px;
                    }
                }

                .app-header .dropdown-menu {
                    position: absolute !important;
                }
            </style>

            <div class="dropdown side-nav">
                <div class="app-sidebar__toggle" data-toggle="sidebar">
                    <a class="open-toggle" href="#">
                        <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </a>
                    <a class="close-toggle" href="#">
                        <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24"
                            width="24">
                            <path d="M0 0h24v24H0V0z" fill="none" />
                            <path
                                d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="d-none d-md-block">
                @include('layouts.language-switcher')
            </div>

            @php
                use Illuminate\Support\Facades\DB;

                // Fetch only id and name for the dropdown
                $schools = DB::table('schools')
                    ->select('id', 'name')
                    ->latest()
                    ->get();

                // Fetch only id and name for the selected school
                $selectedSchool = session('LoggedSchool')
                    ? DB::table('schools')
                        ->select('id', 'name')
                        ->where('id', session('LoggedSchool'))
                        ->first()
                    : null;
            @endphp

            @if (session('LoggedAdmin'))
                <div class="admin-school-dropdown mt-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle font-weight-bold w-100" type="button"
                            id="schoolDropdownButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $selectedSchool ? $selectedSchool->name : 'Select School' }}
                        </button>
                        <div class="dropdown-menu w-100 p-2" aria-labelledby="schoolDropdownButton"
                            style="max-height: 300px; overflow-y: auto;">
                            <input type="text" class="form-control mb-2" id="schoolSearch" placeholder="Search school...">
                            <div id="schoolList">
                                <a class="dropdown-item clear-school bg-light text-primary font-weight-bold rounded"
                                    href="#" style="border: 1px dashed #2C29CA; margin-bottom: 5px;">
                                    <i class="fas fa-undo-alt mr-2"></i> Clear School Selection
                                </a>
                                @forelse ($schools as $school)
                                    <a class="dropdown-item school-item" href="#" data-id="{{ $school->id }}"
                                        data-name="{{ $school->name }}">
                                        {{ $school->name }}
                                    </a>
                                @empty
                                    <a class="dropdown-item" href="#">No schools found.</a>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-flex order-lg-2 ml-auto" style="margin-top:0.7rem;">
                @if (Session('LoggedSchool'))
                    @php
                        $teacherId = Session::get('LoggedTeacher');
                        $user = \App\Models\Teacher::where('id', $teacherId)->first();
                    @endphp

                    @if ($user && $user->must_change_password)
                        <div class="d-flex align-items-center mx-3">
                            <a class="text-danger font-weight-bold d-flex align-items-center password-warning-link"
                                href="javascript:void(0);" onclick="showPasswordModal()" style="text-decoration: none;">
                                <i class="fas fa-exclamation-triangle fa-2x mr-2"></i>
                                <span style="color:#FFF;">⚠️ URGENT: Update Your Password ⚠️</span>
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <div class="d-flex order-lg-2 ml-auto align-items-center" style="margin-top:0.7rem; flex: 1;">
                @if (Session('LoggedSchool'))
                    @php
                        $teacherId = Session::get('LoggedTeacher');
                        $user = \App\Models\Teacher::where('id', $teacherId)->first();
                        $activeYear = Helper::activeAcademicYear();
                        $activeYearName = Helper::fetchActiveYearName($activeYear);
                        $activeTerm = Helper::activeTerm();
                        $TechSateActiveYear = Helper::systemActiveYear();

                        $hasActiveYear = !is_null($activeYear);
                        $hasActiveTerm = !is_null($activeTerm);
                        $hasSystemActiveYear = !is_null($TechSateActiveYear);
                        $HasNoSystemActiveYear = !$hasSystemActiveYear;
                        $missingBoth = !$hasActiveYear && !$hasActiveTerm;
                        $missingYearOnly = !$hasActiveYear && $hasActiveTerm;
                        $missingTermOnly = $hasActiveYear && !$hasActiveTerm;
                        $hasBoth = $hasActiveYear && $hasActiveTerm;
                    @endphp

                    @if ($user && !$user->must_change_password)
                        @if ($HasNoSystemActiveYear)
                            <div class="d-flex align-items-center justify-content-center mx-3" style="width: 100%;">
                                <a class="text-warning font-weight-bold d-flex align-items-center academic-term-warning-link"
                                    href="javascript:void(0);" onclick="showAcademicGeneralAlert('General')"
                                    style="text-decoration: none;">
                                    <i class="fas fa-clock fa-2x mr-2"></i>
                                    <span style="color:#FFF;">⚠️ No System Active Year Set ⚠️</span>
                                </a>
                            </div>
                        @elseif ($missingBoth)
                            <div class="d-flex align-items-center justify-content-center mx-3" style="width: 100%;">
                                <a class="text-warning font-weight-bold d-flex align-items-center academic-term-warning-link"
                                    href="javascript:void(0);" onclick="showAcademicTermAlert()" style="text-decoration: none;">
                                    <i class="fas fa-exclamation-circle fa-2x mr-2 text-red"></i>
                                    <span style="color:#FFF;">⚠️ No Active Year & No Active Term Set ⚠️</span>
                                </a>
                            </div>
                        @elseif ($missingYearOnly)
                            <div class="d-flex align-items-center justify-content-center mx-3" style="width: 100%;">
                                <a class="text-warning font-weight-bold d-flex align-items-center academic-term-warning-link"
                                    href="javascript:void(0);" onclick="showAcademicTermAlert('year')"
                                    style="text-decoration: none;">
                                    <i class="fas fa-calendar-times fa-2x mr-2"></i>
                                    <span style="color:#FFF;">⚠️ No Active Year Set ⚠️</span>
                                </a>
                            </div>
                        @elseif ($missingTermOnly)
                            <div class="d-flex align-items-center justify-content-center mx-3" style="width: 100%;">
                                <a class="text-warning font-weight-bold d-flex align-items-center academic-term-warning-link"
                                    href="javascript:void(0);" onclick="showAcademicTermAlert('term')"
                                    style="text-decoration: none;">
                                    <i class="fas fa-clock fa-2x mr-2"></i>
                                    <span style="color:#FFF;">⚠️ No Active Term Set ⚠️</span>
                                </a>
                            </div>
                        @elseif ($hasBoth)
                            <div class="d-flex align-items-center justify-content-center mx-3" style="width: 100%;">
                                <div class="active-info-container d-flex align-items-center">
                                    <div class="active-year-badge mr-3">
                                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                        <span class="font-weight-bold text-primary">Active Year:</span>
                                        <span class="ml-1 text-dark">
                                            @if ($hasActiveYear)
                                                {{ Helper::schoolActiveYearName() }}
                                            @else
                                                {{ 'N/A' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="active-term-badge">
                                        <i class="fas fa-clock text-primary mr-1"></i>
                                        <span class="font-weight-bold text-primary">Active Term:</span>
                                        <span class="ml-1 text-dark">
                                            @if ($activeTerm)
                                                {{ Helper::schoolActiveTermName() }}
                                            @else
                                                {{ 'N/A' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>

            <div class="d-flex order-lg-2 ml-auto align-items-center" style="margin-top:0.7rem;">
                <div class="display-name responsive-user-section">
                    @if (Session('LoggedSchool'))
                        <span style="line-height:40px;">
                            School :
                            <span class="text-primary font-weight-bold">
                                {{ Helper::schoolNameBySchoolID(Session('LoggedSchool')) }}
                            </span>
                        </span>
                    @else
                        <span style="line-height:40px;">
                            Admin :
                            <span class="text-primary font-weight-bold">
                                {{ Helper::logged_admin_user() }}
                            </span>
                        </span>
                    @endif
                </div>

                {{-- Notification Bell — paste inside header.blade.php nav area --}}
                @if(session('LoggedAdmin') || session('LoggedTeacher'))
                    <div id="pushStatusWrap" style="margin-left: 12px;">
                        <a href="javascript:void(0)" id="pushStatusToggle"
                            class="position-relative d-flex align-items-center justify-content-center"
                            style="text-decoration:none; width:42px; height:42px; display:none;"
                            title="Enable notifications">
                            <i class="fas fa-bell-slash" id="pushStatusIcon"
                                style="font-size: 1.3rem; color:#f39c12; line-height:1;"></i>
                        </a>
                    </div>

                    <div class="dropdown" id="notifBell" style="margin-left: 12px;">
                        <a href="javascript:void(0)"
                            class="position-relative d-flex align-items-center justify-content-center" id="notifBellToggle"
                            data-toggle="dropdown" aria-expanded="false"
                            style="text-decoration:none; width:42px; height:42px;">
                            <i class="fas fa-bell" style="font-size: 1.5rem; color:#444; line-height:1;"></i>
                            <span id="notifBadge" class="badge bg-danger rounded-pill position-absolute"
                                style="top:4px; right:2px; display:none; font-size:0.65rem;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated p-0"
                            style="width:340px; max-height:420px; overflow-y:auto;">
                            <div
                                class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom notif-header">
                                <span class="font-weight-bold" style="font-size:0.95rem;">Notifications</span>
                                <a href="{{ route('notifications.my') }}" class="small text-primary">View all</a>
                            </div>
                            <div id="notifDropdownList">
                                <div class="text-center text-muted py-4 small">Loading...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Refresh Button -->
                    <div id="refreshPageBtn" style="margin-left: 8px;">
                        <a href="javascript:void(0)"
                            class="position-relative d-flex align-items-center justify-content-center"
                            onclick="refreshCurrentPage()" title="Refresh Page"
                            style="text-decoration:none; width:42px; height:42px;">
                            <i id="refreshIcon" class="fas fa-spinner"
                                style="font-size:1.35rem; color:#2C29CA; line-height:1;"></i>
                        </a>
                    </div>

                    <script>
                        (function () {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                            const badge = document.getElementById('notifBadge');
                            const list = document.getElementById('notifDropdownList');
                            const bell = document.getElementById('notifBellToggle');

                            const notifColorMap = {
                                primary: { bg: 'rgba(13,110,253,0.12)', text: '#0d6efd' },
                                danger: { bg: 'rgba(220,53,69,0.12)', text: '#dc3545' },
                                warning: { bg: 'rgba(255,193,7,0.18)', text: '#b8860b' },
                                success: { bg: 'rgba(40,167,69,0.12)', text: '#28a745' },
                                info: { bg: 'rgba(23,162,184,0.12)', text: '#17a2b8' },
                                secondary: { bg: 'rgba(108,117,125,0.12)', text: '#6c757d' }
                            };

                            function notifColor(c) {
                                return notifColorMap[c] || notifColorMap.secondary;
                            }

                            function refreshUnreadCount() {
                                fetch("{{ route('notifications.ajax.unread') }}", { headers: { 'Accept': 'application/json' } })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.count > 0) {
                                            badge.style.display = 'inline-block';
                                            badge.textContent = data.count > 99 ? '99+' : data.count;
                                        } else {
                                            badge.style.display = 'none';
                                        }
                                    })
                                    .catch(() => { });
                            }

                            function loadDropdown() {
                                list.innerHTML = '<div class="text-center text-muted py-4 small">Loading...</div>';
                                fetch("{{ route('notifications.ajax.dropdown') }}", { headers: { 'Accept': 'application/json' } })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (!data.notifications || data.notifications.length === 0) {
                                            list.innerHTML = '<div class="text-center text-muted py-4 small">No notifications yet.</div>';
                                            return;
                                        }
                                        list.innerHTML = data.notifications.map(n => {
                                            const c = notifColor(n.color);
                                            return `
                                                        <a href="javascript:void(0)" class="d-flex align-items-start px-3 py-2 border-bottom text-decoration-none notif-drop-item"
                                                           data-id="${n.id}" data-url="${n.url || ''}" data-read="${n.is_read ? '1' : '0'}"
                                                           style="${n.is_read ? '' : 'background:#f8f9fb;'}">
                                                            <span class="notif-icon-badge mr-3" style="background-color:${c.bg}; color:${c.text};">
                                                                <i class="fas fa-${n.icon}"></i>
                                                            </span>
                                                            <span class="flex-grow-1" style="min-width:0;">
                                                                <span class="d-block small font-weight-bold text-dark">${n.title}</span>
                                                                <span class="d-block small text-muted">${n.body}</span>
                                                                <span class="d-block small text-muted mt-1">${n.time}</span>
                                                            </span>
                                                        </a>
                                                    `;
                                        }).join('');

                                        list.querySelectorAll('.notif-drop-item').forEach(el => {
                                            el.addEventListener('click', function () {
                                                const id = this.dataset.id;
                                                const url = this.dataset.url;
                                                if (this.dataset.read === '0') {
                                                    fetch(`/notifications/${id}/read`, {
                                                        method: 'POST',
                                                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                                    }).then(() => refreshUnreadCount());
                                                }
                                                if (url) window.location.href = url;
                                            });
                                        });
                                    })
                                    .catch(() => {
                                        list.innerHTML = '<div class="text-center text-muted py-4 small">Failed to load notifications.</div>';
                                    });
                            }

                            refreshUnreadCount();
                            setInterval(refreshUnreadCount, 60000); // poll every 60s

                            bell.addEventListener('click', loadDropdown);
                        })();
                    </script>

                    <script>
                        (function () {
                            var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
                            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                            if (!vapidMeta || !vapidMeta.content || !csrfMeta || !csrfMeta.content) return;
                            if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

                            var VAPID_PUBLIC_KEY = vapidMeta.content;
                            var CSRF_TOKEN = csrfMeta.content;
                            var toggle = document.getElementById('pushStatusToggle');
                            var icon = document.getElementById('pushStatusIcon');
                            if (!toggle || !icon) return;

                            function urlBase64ToUint8Array(base64String) {
                                var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                                var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                                var rawData = atob(base64);
                                var outputArray = new Uint8Array(rawData.length);
                                for (var i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
                                return outputArray;
                            }
                            function arrayBufferToBase64(buffer) {
                                var bytes = new Uint8Array(buffer), binary = '';
                                for (var i = 0; i < bytes.byteLength; i++) binary += String.fromCharCode(bytes[i]);
                                return btoa(binary);
                            }
                            function showToast(msg, color) {
                                var el = document.createElement('div');
                                el.textContent = msg;
                                el.style.cssText = [
                                    'position:fixed', 'bottom:20px', 'right:20px', 'z-index:99999',
                                    'background:' + (color || '#333'), 'color:#fff', 'padding:12px 18px',
                                    'border-radius:8px', 'font-size:14px', 'max-width:320px',
                                    'box-shadow:0 4px 14px rgba(0,0,0,.3)', 'word-break:break-word'
                                ].join(';');
                                document.body.appendChild(el);
                                setTimeout(function () { el.remove(); }, 6000);
                            }

                            function setState(state) {
                                toggle.style.display = 'flex';
                                toggle.dataset.state = state;
                                if (state === 'subscribed') {
                                    icon.className = 'fas fa-bell';
                                    icon.style.color = '#27ae60';
                                    toggle.title = 'Notifications enabled — click to disable';
                                } else if (state === 'blocked') {
                                    icon.className = 'fas fa-bell-slash';
                                    icon.style.color = '#e74c3c';
                                    toggle.title = 'Notifications blocked — enable them in your browser site settings';
                                } else {
                                    icon.className = 'fas fa-bell-slash';
                                    icon.style.color = '#f39c12';
                                    toggle.title = 'Enable notifications for this device';
                                }
                            }

                            function refreshState(reg) {
                                if (Notification.permission === 'denied') { setState('blocked'); return; }
                                reg.pushManager.getSubscription().then(function (sub) {
                                    setState(sub ? 'subscribed' : 'unsubscribed');
                                });
                            }

                            navigator.serviceWorker.register('/sw.js').then(function (reg) {
                                refreshState(reg);

                                toggle.addEventListener('click', function () {
                                    var state = toggle.dataset.state;

                                    if (state === 'blocked') {
                                        showToast('Notifications are blocked for this site. Tap the site info icon in your address bar, allow Notifications, then click this bell again.', '#e74c3c');
                                        return;
                                    }

                                    if (state === 'subscribed') {
                                        reg.pushManager.getSubscription().then(function (sub) {
                                            if (!sub) { setState('unsubscribed'); return; }
                                            var endpoint = sub.endpoint;
                                            sub.unsubscribe().then(function () {
                                                return fetch("{{ route('notifications.push.unsubscribe') }}", {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ endpoint: endpoint })
                                                });
                                            }).then(function () {
                                                setState('unsubscribed');
                                                showToast('🔕 Notifications disabled for this device.', '#555');
                                            }).catch(function (err) {
                                                showToast('Could not disable notifications: ' + err.message, '#e74c3c');
                                            });
                                        });
                                        return;
                                    }

                                    // unsubscribed -> subscribe
                                    Notification.requestPermission().then(function (perm) {
                                        if (perm !== 'granted') {
                                            setState(perm === 'denied' ? 'blocked' : 'unsubscribed');
                                            return;
                                        }
                                        reg.pushManager.subscribe({
                                            userVisibleOnly: true,
                                            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
                                        }).then(function (sub) {
                                            var key = sub.getKey('p256dh'), token = sub.getKey('auth');
                                            var enc = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
                                            return fetch("{{ route('notifications.push.subscribe') }}", {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                                                credentials: 'same-origin',
                                                body: JSON.stringify({
                                                    endpoint: sub.endpoint,
                                                    key: key ? arrayBufferToBase64(key) : null,
                                                    token: token ? arrayBufferToBase64(token) : null,
                                                    contentEncoding: enc
                                                })
                                            });
                                        }).then(function (res) {
                                            if (res.ok) {
                                                setState('subscribed');
                                                showToast('🎉 Welcome! You will now receive SMASA notifications on this device.', '#27ae60');
                                            } else {
                                                showToast('Could not save your subscription. Please try again.', '#e74c3c');
                                            }
                                        }).catch(function (err) {
                                            showToast('Subscribe failed: ' + err.message, '#e74c3c');
                                        });
                                    });
                                });
                            }).catch(function (err) {
                                console.error('[PushStatus] SW registration failed', err);
                            });
                        })();
                    </script>
                @endif

                <div class="dropdown profile-dropdown" style="margin-left: 8px;">
                    <a href="#" class="nav-link pr-0 leading-none d-flex align-items-center justify-content-center"
                        data-toggle="dropdown" style="width:42px; height:42px;">
                        <i class="fa fa-fw fa-cog fa-2x" style="line-height:1;"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                        <div class="text-center">
                            <a href="#" class="dropdown-item text-center user pb-0 font-weight-bold"></a>
                            <div class="dropdown-divider"></div>
                        </div>

                        @if (Session('LoggedSchool'))
                            @php
                                $teacherId = Session::get('LoggedTeacher');
                                $user = \App\Models\Teacher::where('id', $teacherId)->first();
                            @endphp

                            @if (Helper::isTechSateAdminOrSchoolAdminsOrTechSateSalesRepresentatives())
                                <a class="dropdown-item d-flex" href="{{ url('/term-dates/' . Session('LoggedSchool')) }}">
                                    <i class="fas fa-clock fa-2x mr-3"></i>
                                    <div class="mt-1">Active Year</div>
                                </a>

                                <a class="dropdown-item d-flex"
                                    href="{{ url('/school-individual-profile/' . Session('LoggedSchool')) }}">
                                    <i class="fas fa-school fa-2x mr-3"></i>
                                    <div class="mt-1">School Profile</div>
                                </a>
                            @endif

                            <a class="dropdown-item d-flex" href="{{ url('/update-teacher-profile', $teacherId) }}">
                                <i class="fa fa-user fa-2x mr-3"></i>
                                <div class="mt-1">User Profile</div>
                            </a>
                        @else
                            <a class="dropdown-item d-flex" href="{{ url('/add-academic-year') }}">
                                <i class="fas fa-clock fa-2x mr-3"></i>
                                <div class="mt-1">Active Year</div>
                            </a>

                            <a class="dropdown-item d-flex"
                                href="{{ url('/update-teacher-profile', Session('LoggedAdmin')) }}">
                                <i class="fa fa-user fa-2x mr-3"></i>
                                <div class="mt-1">User Profile</div>
                            </a>
                        @endif

                        <a class="dropdown-item d-flex" href="#" id="logoutLink">
                            <i class="fa fa-sign-out fa-2x mr-3"></i>
                            <div class="mt-1">Sign Out</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/app header-->

<!-- Password Update Modal - MOVED OUTSIDE HEADER -->
<div class="modal fade" id="passwordUpdateModal" tabindex="-1" role="dialog" aria-labelledby="passwordUpdateModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="passwordUpdateModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Security Alert: Password Update Required
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="closeModalX">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="passwordUpdateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle mr-2"></i>
                        For security reasons, you must update your password before continuing.
                    </div>

                    <!-- New Password Field -->
                    <div class="form-group mb-4">
                        <label for="new_password" class="font-weight-bold">
                            <i class="fas fa-lock mr-2 text-danger"></i>
                            New Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-key text-danger"></i>
                                </span>
                            </div>
                            <input type="password" class="form-control border-left-0" id="new_password"
                                name="new_password" placeholder="Enter new password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted" id="passwordStrength"></small>
                        <div class="invalid-feedback" id="new_password_error"></div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="form-group mb-4">
                        <label for="confirm_password" class="font-weight-bold">
                            <i class="fas fa-check-circle mr-2 text-danger"></i>
                            Confirm Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-check text-danger"></i>
                                </span>
                            </div>
                            <input type="password" class="form-control border-left-0" id="confirm_password"
                                name="confirm_password" placeholder="Confirm new password" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="confirm_password_error"></div>
                    </div>

                    <!-- Password Requirements - Simplified -->
                    <div class="alert alert-light border">
                        <small class="text-muted d-block mb-2">Password must contain:</small>
                        <small class="d-block" id="lengthCheck">
                            <i class="far fa-circle mr-2" id="lengthIcon"></i>
                            At least 4 characters
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModalBtn">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="submitPasswordBtn">
                        <i class="fas fa-save mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Ensure the dropdown never gets clipped on the right
    $('#notifBell').on('shown.bs.dropdown', function () {
        const menu = $(this).find('.dropdown-menu');
        const rect = menu[0].getBoundingClientRect();

        // If the right edge of the menu is beyond the viewport, shift it left
        if (rect.right > (window.innerWidth - 8)) {
            menu.css({
                right: 'auto',
                left: (window.innerWidth - rect.width - 8) + 'px'
            });
        } else {
            // Reset to right-aligned if it’s fine
            menu.css({
                right: '0',
                left: 'auto'
            });
        }
    });

    document.getElementById('logoutLink').addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: "Are you sure?",
            text: "Do you really want to sign out?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Sign out",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('student-logout') }}';
            }
        });
    });

    function showAcademicTermAlert(type = 'both') {
        let title = '';
        let text = '';
        let icon = 'warning';
        const schoolId = "{{ session('LoggedSchool') }}";

        if (type === 'both') {
            title = '⚠️ Configuration Required';
            text = 'No Academic Year and No Term have been set for this school. Please configure both to continue.';
        } else if (type === 'year') {
            title = '⚠️ Academic Year Missing';
            text = 'No Active Academic Year has been set. Please set an Academic Year first before setting Terms.';
        } else if (type === 'term') {
            title = '⚠️ Term Missing';
            text = 'No Active Term has been set. Please set the Term dates for the current Academic Year.';
        }

        Swal.fire({
            title: title,
            html: `
                <div class="text-left">
                    <p class="mb-3">${text}</p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Press Configure button to setup active year and term.</strong>
                    </div>
                </div>
            `,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: 'green',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-cog mr-2"></i>Configure Now',
            cancelButtonText: 'Later',
            backdrop: true,
            allowOutsideClick: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (type === 'both' || type === 'year' || type === 'term') {
                    window.location.href = `/term-dates/${schoolId}`;
                }
            }
        });
    }

    function showAcademicGeneralAlert(type = 'General') {
        let title = '';
        let text = '';
        let icon = 'warning';
        const schoolId = "{{ session('LoggedSchool') }}";

        if (type === 'General') {
            title = '⚠️ Configuration Required';
            text = 'No System Academic Year has been set in SMASA. Please talk to TechSate Technologies to set it up.';
        }

        Swal.fire({
            title: title,
            html: `
                <div class="text-left">
                    <p class="mb-3">${text}</p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Please Contact TechSate Technologies for Configuration.</strong>
                    </div>
                </div>
            `,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: 'green',
            cancelButtonColor: '#6c757d',
            cancelButtonText: 'Later',
            backdrop: true,
            allowOutsideClick: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (type === 'both' || type === 'year' || type === 'term') {
                    window.location.href = `/term-dates/${schoolId}`;
                }
            }
        });
    }

    $(document).ready(function () {
        window.showPasswordModal = function () {
            $('#passwordUpdateModal').modal('show');
        };

        @if (isset($user) && $user && $user->must_change_password)
            setTimeout(function () {
                $('#passwordUpdateModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#passwordUpdateModal').modal('show');
            }, 500);
        @endif

        $('.toggle-password').click(function () {
            const target = $(this).data('target');
            const input = $('#' + target);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#new_password').on('keyup', function () {
            const password = $(this).val();
            validatePassword(password);
            checkPasswordMatch();
        });

        $('#confirm_password').on('keyup', function () {
            checkPasswordMatch();
        });

        function validatePassword(password) {
            const isValidLength = password.length >= 4;
            const lengthIcon = $('#lengthIcon');
            if (isValidLength) {
                lengthIcon.removeClass('fa-circle').addClass('fa-check-circle');
                lengthIcon.css('color', '#28a745');
            } else {
                lengthIcon.removeClass('fa-check-circle').addClass('fa-circle');
                lengthIcon.css('color', '#dc3545');
            }
            updatePasswordStrength(isValidLength);
            return isValidLength;
        }

        function updatePasswordStrength(isValid) {
            const strengthText = $('#passwordStrength');
            if (isValid) {
                strengthText.html('<i class="fas fa-check-circle text-success"></i> Password length is valid');
                strengthText.removeClass('password-strength-weak').addClass('password-strength-valid');
            } else {
                strengthText.html(
                    '<i class="fas fa-exclamation-triangle text-warning"></i> Password must be at least 4 characters');
                strengthText.removeClass('password-strength-valid').addClass('password-strength-weak');
            }
        }

        function checkPasswordMatch() {
            const password = $('#new_password').val();
            const confirm = $('#confirm_password').val();

            if (confirm.length > 0) {
                if (password === confirm) {
                    $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
                    $('#confirm_password_error').text('');
                    return true;
                } else {
                    $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
                    $('#confirm_password_error').text('Passwords do not match');
                    return false;
                }
            } else {
                $('#confirm_password').removeClass('is-valid is-invalid');
                return false;
            }
        }

        $('#passwordUpdateForm').on('submit', function (e) {
            e.preventDefault();

            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();

            const isValid = validatePassword(newPassword);

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Password must be at least 4 characters long',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New password and confirm password do not match',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            Swal.fire({
                title: "Confirm Password Update",
                text: "Are you sure you want to update your password?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, update it",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    const submitBtn = $('#submitPasswordBtn');
                    submitBtn.prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');

                    $.ajax({
                        url: '{{ route('teacher.update-password') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            password: newPassword,
                            teacher_id: '{{ Session::get('LoggedTeacher') }}'
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#28a745',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#dc3545'
                                });
                                submitBtn.prop('disabled', false)
                                    .html('<i class="fas fa-save mr-2"></i>Update Password');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMessage,
                                confirmButtonColor: '#dc3545'
                            });

                            submitBtn.prop('disabled', false)
                                .html('<i class="fas fa-save mr-2"></i>Update Password');
                        }
                        //                         error: function(data) {
                        // $('body').html(data.responseText);
                        // }
                    });
                }
            });
        });

        let allowClose = false;

        $('#closeModalX, #closeModalBtn').on('click', function () {
            allowClose = true;
        });

        $('#passwordUpdateModal').on('hide.bs.modal', function (e) {
            @if (isset($user) && $user && $user->must_change_password)
                if (!allowClose) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Action',
                        text: 'You must update your password to continue using the system.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            @endif
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('schoolSearch');
        const schoolItems = document.querySelectorAll('.school-item');
        const dropdownBtn = document.getElementById('schoolDropdownButton');

        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const searchValue = this.value.toLowerCase();
                schoolItems.forEach(item => {
                    const schoolName = item.textContent.toLowerCase();
                    item.style.display = schoolName.includes(searchValue) ? '' : 'none';
                });
            });
        }

        schoolItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                const schoolId = this.dataset.id;
                const schoolName = this.dataset.name;

                Swal.fire({
                    title: "Switch School?",
                    text: `Are you sure you want to switch to "${schoolName}"?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, switch",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('school.select') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ school_id: schoolId })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status) {
                                    Swal.fire({
                                        title: "School Changed!",
                                        text: data.message,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error", data.message, "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error", "Something went wrong!", "error");
                            });
                    }
                });
            });
        });

        const clearSchoolBtn = document.querySelector('.clear-school');
        if (clearSchoolBtn) {
            clearSchoolBtn.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: "Clear School?",
                    text: "Do you want to clear the selected school?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, clear",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('school.clear') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status) {
                                    Swal.fire({
                                        title: "Cleared!",
                                        text: data.message,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = '/';
                                    });
                                } else {
                                    Swal.fire("Error", data.message, "error");
                                }
                            })
                            .catch(() => {
                                Swal.fire("Error", "Something went wrong!", "error");
                            });
                    }
                });
            });
        }

        // Sidebar state persistence
        // NOTE: on mobile (<768px) the "sidenav-toggled" class means the off-canvas
        // menu is OPEN, whereas on desktop it means the sidebar is MINIMIZED.
        // Because of that, this persistence must only apply on desktop — otherwise
        // an open mobile menu gets saved and re-opened automatically after every
        // page reload/navigation.
        const MOBILE_BREAKPOINT = 768;
        const isMobileViewport = () => window.innerWidth < MOBILE_BREAKPOINT;

        function setSidebarState(isMinimized) {
            if (isMinimized) {
                document.body.classList.add('sidenav-toggled');
                if (!isMobileViewport()) {
                    localStorage.setItem('sidebarMinimized', 'true');
                }
            } else {
                document.body.classList.remove('sidenav-toggled');
                if (!isMobileViewport()) {
                    localStorage.setItem('sidebarMinimized', 'false');
                }
            }
        }

        if (isMobileViewport()) {
            // Always start with the mobile off-canvas menu closed on load/reload.
            document.body.classList.remove('sidenav-toggled');
        } else {
            const savedState = localStorage.getItem('sidebarMinimized');
            if (savedState === 'true') {
                document.body.classList.add('sidenav-toggled');
            }
        }

        const toggleButtons = document.querySelectorAll('.app-sidebar__toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                setTimeout(() => {
                    const isMinimized = document.body.classList.contains('sidenav-toggled');
                    if (!isMobileViewport()) {
                        localStorage.setItem('sidebarMinimized', isMinimized);
                    }
                }, 100);
            });
        });
    });
</script>
<script>
    (function () {
        // Target the first-level flex row, not the whole header
        // (avoids counting hidden dropdown-menu panels in the height)
        const header = document.querySelector('.app-header .container-fluid > .d-flex');
        if (!header) return;

        function setHeaderHeightVar() {
            const height = header.getBoundingClientRect().height;
            document.documentElement.style.setProperty('--app-header-h', height + 'px');
        }

        setHeaderHeightVar();
        window.addEventListener('resize', setHeaderHeightVar);

        if ('ResizeObserver' in window) {
            const ro = new ResizeObserver(setHeaderHeightVar);
            ro.observe(header);
        }

        window.addEventListener('load', setHeaderHeightVar);
        setTimeout(setHeaderHeightVar, 300);
        setTimeout(setHeaderHeightVar, 1000);
    })();


    function refreshCurrentPage() {
        const icon = document.getElementById('refreshIcon');

        // Rotate the icon while refreshing
        icon.classList.add('fa-spin');

        // Small delay so the user sees the animation
        setTimeout(() => {
            window.location.reload();
        }, 300);
    }
</script>