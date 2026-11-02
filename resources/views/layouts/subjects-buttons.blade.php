<style>
    /* Navigation buttons shared by the student pages (search / add / all / consolidate / promote ...).

       Layout
       - The buttons live in a CSS grid, not Bootstrap columns. Every button gets the same width and height,
         buttons you are not allowed to see leave no empty cell behind, and there are no negative gutter
         margins pushing the group off-centre inside the card header.
       - Column width is fluid: as many 232px+ columns as fit, so wide screens show 4-5 per row and phones 1.

       Button anatomy
       - [coloured icon chip] [label], icons always line up in one column and long labels wrap onto a second
         line inside the button instead of spilling over its edge (the theme sets nowrap on .btn, so the
         label resets it).
       - The gap is a CSS gap, because this theme runs Bootstrap 4, where Bootstrap 5 margin helpers such as
         me-2 do not exist.

       Colour
       - Each palette has an accent (--nc), a tint (--nt), a border (--nb) and a darker text colour (--nx)
         chosen so the label stays readable on its tint.
       - Colours are pinned with important on every state so dark-mode extensions and WebView force-dark
         cannot repaint them. */
    .subjects-nav {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 232px), 1fr));
        gap: .75rem;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .card-header > .subjects-nav {
        padding: .5rem 0;
    }

    .subjects-nav .subjects-nav-btn,
    .subjects-nav .subjects-nav-btn:hover,
    .subjects-nav .subjects-nav-btn:focus,
    .subjects-nav .subjects-nav-btn:focus-visible,
    .subjects-nav .subjects-nav-btn:active,
    .subjects-nav .subjects-nav-btn:visited {
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
        gap: .7rem;
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        min-height: 48px;
        margin: 0;
        padding: .55rem 1rem .55rem .8rem !important;
        font-size: .875rem;
        font-weight: 600;
        line-height: 1.3;
        letter-spacing: .01em;
        text-align: left;
        white-space: normal;
        border-radius: .5rem;
        background-color: var(--nt, #f5f6ff) !important;
        background-image: none !important;
        color: var(--nx, #3730a3) !important;
        border: 1px solid var(--nb, #e7e9f1) !important;
        border-left: 4px solid var(--nc, #424e79) !important;
        box-shadow: none !important;
        outline: none !important;
        text-decoration: none !important;
        forced-color-adjust: none;
        -webkit-tap-highlight-color: transparent;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .subjects-nav .subjects-nav-btn .sn-ic {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        margin: 0 !important;
        border-radius: 8px;
        background-color: var(--nc, #424e79) !important;
        color: #ffffff !important;
        font-size: .85rem;
        line-height: 1;
        box-shadow: 0 3px 8px rgba(15, 23, 42, .18);
    }

    .subjects-nav .subjects-nav-btn .sn-ic::before {
        display: block;
        line-height: 1;
    }

    .subjects-nav .subjects-nav-btn .sn-label {
        flex: 1 1 auto;
        min-width: 0;
        overflow-wrap: break-word;
        text-wrap: balance;
    }

    .subjects-nav .subjects-nav-btn:hover,
    .subjects-nav .subjects-nav-btn:active {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, .16) !important;
    }

    .subjects-nav .subjects-nav-btn:active {
        transform: translateY(0);
    }

    .subjects-nav .subjects-nav-btn:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 0 0 3px var(--nb, #c7d2fe), 0 6px 14px rgba(15, 23, 42, .16) !important;
    }

    /* The page you are on: solid fill, white label, white icon chip, and a white outline ring so it stays
       clearly visible against the blue card header (a dark fill alone blends into it). */
    .subjects-nav .subjects-nav-btn.is-active,
    .subjects-nav .subjects-nav-btn.is-active:hover,
    .subjects-nav .subjects-nav-btn.is-active:focus,
    .subjects-nav .subjects-nav-btn.is-active:focus-visible,
    .subjects-nav .subjects-nav-btn.is-active:active,
    .subjects-nav .subjects-nav-btn.is-active:visited {
        background-color: var(--nx, #3730a3) !important;
        border-color: var(--nx, #3730a3) !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 2px #ffffff, 0 4px 12px rgba(15, 23, 42, .28) !important;
    }

    .subjects-nav .subjects-nav-btn.is-active .sn-ic {
        background-color: #ffffff !important;
        color: var(--nx, #3730a3) !important;
        box-shadow: none;
    }

    .sn-indigo  { --nc: #4f46e5; --nt: #eef2ff; --nb: #c7d2fe; --nx: #3730a3; }
    .sn-emerald { --nc: #059669; --nt: #ecfdf5; --nb: #a7f3d0; --nx: #047857; }
    .sn-sky     { --nc: #0284c7; --nt: #f0f9ff; --nb: #bae6fd; --nx: #0369a1; }
    .sn-violet  { --nc: #7c3aed; --nt: #f5f3ff; --nb: #ddd6fe; --nx: #5b21b6; }
    .sn-amber   { --nc: #d97706; --nt: #fffbeb; --nb: #fde68a; --nx: #b45309; }
    .sn-rose    { --nc: #e11d48; --nt: #fff1f2; --nb: #fecdd3; --nx: #be123c; }
    .sn-teal    { --nc: #0d9488; --nt: #f0fdfa; --nb: #99f6e4; --nx: #0f766e; }

    @media (prefers-reduced-motion: reduce) {
        .subjects-nav .subjects-nav-btn,
        .subjects-nav .subjects-nav-btn:hover,
        .subjects-nav .subjects-nav-btn:focus-visible,
        .subjects-nav .subjects-nav-btn:active {
            transition: none;
            transform: none;
        }
    }
</style>

@php
    // Class types are only looked up when a button that depends on them has passed its permission check.
    $snSchoolTypes = null;
    $snHasClassType = function (string $type) use (&$snSchoolTypes) {
        $snSchoolTypes = $snSchoolTypes ?? \App\Http\Controllers\Helper::schoolClassTypes(Session('LoggedSchool'));

        return in_array($type, $snSchoolTypes, true);
    };

    // [route name, permission key (null = always shown), required class type (null = any), icon, palette, label]
    $snItems = [
        ['students.individual.search',           null,                          null,                'fa-search',          'sn-indigo',  'Search Student'],
        ['students.add.new.student',             'add_student',                 null,                'fa-user-plus',      'sn-emerald', 'Add Students'],
        ['students.all.students',                'view_students',               null,                'fa-users',          'sn-sky',     'All Students'],
        ['students.consolidation',               'view_student_consolidation',  null,                'fa-code-branch',    'sn-violet',  'Consolidate Students'],
        ['students.promotion',                   'view_student_promotion',      null,                'fa-stairs',         'sn-amber',   'Promote Students'],
        ['students.bulk.photo.import.form',      'import_student_photos',       null,                'fa-portrait',       'sn-rose',    'Bulk Photo Import'],
        ['alevel.combinations.entry',            'view_alevel_combinations',    'Secondary A-Level', 'fa-graduation-cap', 'sn-teal',    'A Level Combinations'],
        ['olevel.electives.entry',               'view_olevel_electives',       'Secondary O-Level', 'fa-list-check',     'sn-indigo',  'O Level Electives'],
        ['school.nlsc-topics',                   'view_nlsc_topics',            'Secondary O-Level', 'fa-book-open',      'sn-sky',     'Topics & Competency Areas'],
        ['school.nlsc-projects',                 'view_nlsc_projects',          'Secondary O-Level', 'fa-diagram-project', 'sn-violet', 'Projects (Project Work)'],
        ['school.nlsc-subject-achievements',     'view_nlsc_achievements',      'Secondary O-Level', 'fa-bullseye',       'sn-emerald', 'Subject Achievement'],
    ];
@endphp

<nav class="subjects-nav" aria-label="Student management">
    @foreach ($snItems as [$snRoute, $snPermission, $snType, $snIcon, $snPalette, $snLabel])
        @if (($snPermission === null || \App\Helpers\PermissionHelper::canFeature($snPermission)) && ($snType === null || $snHasClassType($snType)))
            @php $snActive = request()->routeIs($snRoute); @endphp
            <a href="{{ route($snRoute) }}"
                class="btn subjects-nav-btn {{ $snPalette }}{{ $snActive ? ' is-active' : '' }}"
                aria-current="{{ $snActive ? 'page' : 'false' }}">
                <i class="fas {{ $snIcon }} sn-ic" aria-hidden="true"></i><span class="sn-label">{{ $snLabel }}</span>
            </a>
        @endif
    @endforeach
</nav>
