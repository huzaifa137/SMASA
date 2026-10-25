<style>
    /* Navigation buttons shared by the student pages (search / add / all / consolidate / promote ...).
       - Each button has its own palette (--nc solid, --nt tint, --nb border) so they are easy to tell apart.
       - The icon sits in a coloured chip, centred with flexbox. The gap is a CSS `gap`, because this theme
         runs Bootstrap 4, where Bootstrap 5's `me-2` margin class does not exist (that was why the icon
         touched the text).
       - Colours are pinned with !important on every state so dark-mode extensions / WebView "force dark"
         can't repaint them (same protection as before). */
    .subjects-nav-btn,
    .subjects-nav-btn:hover,
    .subjects-nav-btn:focus,
    .subjects-nav-btn:focus-visible,
    .subjects-nav-btn:active,
    .subjects-nav-btn:visited {
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: .6rem;
        min-height: 44px;
        padding: .45rem .9rem !important;
        font-weight: 600;
        line-height: 1.2;
        text-align: left;
        background-color: var(--nt, #f5f6ff) !important;
        background-image: none !important;
        color: var(--nc, #424e79) !important;
        border: 1px solid var(--nb, #e7e9f1) !important;
        border-left: 4px solid var(--nc, #424e79) !important;
        box-shadow: none !important;
        outline: none !important;
        text-decoration: none !important;
        forced-color-adjust: none;
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .subjects-nav-btn .sn-ic {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        height: 28px;
        margin: 0 !important;
        border-radius: 8px;
        background-color: var(--nc, #424e79) !important;
        color: #ffffff !important;
        font-size: .8rem;
        line-height: 1;
        box-shadow: 0 3px 8px rgba(15, 23, 42, .18);
    }

    .subjects-nav-btn .sn-ic::before { display: block; line-height: 1; }

    .subjects-nav-btn:hover,
    .subjects-nav-btn:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.14) !important;
    }

    .subjects-nav-btn:active {
        transform: translateY(0);
    }

    .sn-indigo  { --nc: #4f46e5; --nt: #eef2ff; --nb: #c7d2fe; }
    .sn-emerald { --nc: #059669; --nt: #ecfdf5; --nb: #a7f3d0; }
    .sn-sky     { --nc: #0284c7; --nt: #f0f9ff; --nb: #bae6fd; }
    .sn-violet  { --nc: #7c3aed; --nt: #f5f3ff; --nb: #ddd6fe; }
    .sn-amber   { --nc: #d97706; --nt: #fffbeb; --nb: #fde68a; }
    .sn-rose    { --nc: #e11d48; --nt: #fff1f2; --nb: #fecdd3; }
    .sn-teal    { --nc: #0d9488; --nt: #f0fdfa; --nb: #99f6e4; }
</style>

@php
    use App\Http\Controllers\Helper;
    use App\Helpers\PermissionHelper;
@endphp

<div class="row w-100 g-2">
    <div class="col-12 col-sm-3 mb-2">
        <a href="{{ route('students.individual.search') }}"
            class="btn w-100 rounded subjects-nav-btn sn-indigo">
            <i class="fas fa-search sn-ic"></i><span>Search Student</span>
        </a>
    </div>

    <div class="col-12 col-sm-3 mb-2">
        @if(PermissionHelper::canFeature('add_student'))<a href="{{ route('students.add.new.student') }}"
            class="btn w-100 rounded subjects-nav-btn sn-emerald">
            <i class="fas fa-user-plus sn-ic"></i><span>Add Students</span>
        </a>@endif
    </div>

    <div class="col-12 col-sm-3 mb-2">
        @if(PermissionHelper::canFeature('view_students'))<a href="{{ route('students.all.students') }}" class="btn w-100 rounded subjects-nav-btn sn-sky">
            <i class="fas fa-users sn-ic"></i><span>All Students</span>
        </a>@endif
    </div>

    <div class="col-12 col-sm-3 mb-2">
        @if(PermissionHelper::canFeature('view_student_consolidation'))<a href="{{ route('students.consolidation') }}" class="btn w-100 rounded subjects-nav-btn sn-violet">
            <i class="fas fa-code-branch sn-ic"></i><span>Consolidate Students</span>
        </a>@endif
    </div>

    <div class="col-12 col-sm-3 mb-2">
        @if(PermissionHelper::canFeature('view_student_promotion'))<a href="{{ route('students.promotion') }}" class="btn w-100 rounded subjects-nav-btn sn-amber">
            <i class="fas fa-stairs sn-ic"></i><span>Promote Students</span>
        </a>@endif
    </div>

    <div class="col-12 col-sm-3 mb-2">
        @if(PermissionHelper::canFeature('import_student_photos'))<a href="{{ route('students.bulk.photo.import.form') }}"
            class="btn w-100 rounded subjects-nav-btn sn-rose">
            <i class="fas fa-portrait sn-ic"></i><span>Bulk Photo Import</span>
        </a>@endif
    </div>

    @if(PermissionHelper::canFeature('view_alevel_combinations') && in_array('Secondary A-Level', Helper::schoolClassTypes(Session('LoggedSchool')), true))
        <div class="col-12 col-sm-3 mb-2">
            <a href="{{ route('alevel.combinations.entry') }}" class="btn w-100 rounded subjects-nav-btn sn-teal">
                <i class="fas fa-graduation-cap sn-ic"></i><span>A-Level Combinations</span>
        </a>
        </div>
    @endif

    @if(PermissionHelper::canFeature('view_olevel_electives') && in_array('Secondary O-Level', Helper::schoolClassTypes(Session('LoggedSchool')), true))
        <div class="col-12 col-sm-3 mb-2">
            <a href="{{ route('olevel.electives.entry') }}" class="btn w-100 rounded subjects-nav-btn sn-indigo">
                <i class="fas fa-list-check sn-ic"></i><span>O-Level Electives</span>
        </a>
        </div>
    @endif

    @if((PermissionHelper::canFeature('view_nlsc_topics') || PermissionHelper::canFeature('view_nlsc_projects') || PermissionHelper::canFeature('view_nlsc_achievements')) && in_array('Secondary O-Level', Helper::schoolClassTypes(Session('LoggedSchool')), true))
        @if(PermissionHelper::canFeature('view_nlsc_topics'))
        <div class="col-12 col-sm-3 mb-2">
            <a href="{{ route('school.nlsc-topics') }}" class="btn w-100 rounded subjects-nav-btn sn-sky">
                <i class="fas fa-book-open sn-ic"></i><span>Topics &amp; Competency Areas</span>
        </a>
        </div>
        @endif
        @if(PermissionHelper::canFeature('view_nlsc_projects'))
        <div class="col-12 col-sm-3 mb-2">
            <a href="{{ route('school.nlsc-projects') }}" class="btn w-100 rounded subjects-nav-btn sn-violet">
                <i class="fas fa-diagram-project sn-ic"></i><span>Projects (Project Work)</span>
        </a>
        </div>
        @endif
        @if(PermissionHelper::canFeature('view_nlsc_achievements'))
        <div class="col-12 col-sm-3 mb-2">
            <a href="{{ route('school.nlsc-subject-achievements') }}" class="btn w-100 rounded subjects-nav-btn sn-emerald">
                <i class="fas fa-bullseye sn-ic"></i><span>Subject Achievement</span>
        </a>
        </div>
        @endif
    @endif
</div>