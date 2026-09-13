@extends('layouts-side-bar.master')

@section('css')
    <style>
        .alc-hero {
            background: linear-gradient(135deg, #1a1a7a 0%, #2C29CA 60%, #6b69e8 100%);
            border-radius: 0 0 2.5rem 2.5rem;
            padding: 2.5rem 2.5rem 3.5rem;
            margin-bottom: -1.5rem;
        }

        .alc-hero .hero-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: .4rem 1.2rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .alc-hero .hero-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .alc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .7);
            font-size: 1rem;
        }

        .alc-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 28px rgba(44, 41, 202, .08);
            overflow: hidden;
            background: #fff;
        }

        .alc-card .card-header-custom {
            padding: 1.25rem 1.75rem;
            border-bottom: 2px solid #f0eeff;
        }

        .alc-card .card-header-custom .title {
            font-weight: 700;
            font-size: .95rem;
            color: #1e1b4b;
        }

        .alc-card .card-body-custom {
            padding: 1.5rem 1.75rem;
        }

        .class-pill {
            display: inline-block;
            padding: .5rem 1.1rem;
            margin: .2rem .4rem .2rem 0;
            border-radius: 999px;
            border: 1.5px solid #e4e2ff;
            color: #3a37b8;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
        }

        .class-pill.active {
            background: #2C29CA;
            border-color: #2C29CA;
            color: #fff;
        }

        .alc-table {
            margin-bottom: 0;
            font-size: .85rem;
        }

        .alc-table thead th {
            background: #2C29CA;
            color: #fff;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            padding: 0.9rem 0.75rem;
            border: none;
            white-space: nowrap;
        }

        .alc-table tbody td {
            vertical-align: top;
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid #f0eeff;
        }

        .alc-table tbody td:first-child {
            font-weight: 600;
            color: #1e1b4b;
            white-space: nowrap;
        }

        .gp-chip {
            display: inline-block;
            background: #eef0ff;
            color: #3a37b8;
            border-radius: .5rem;
            padding: .3rem .6rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .principal-group-label {
            font-size: .68rem;
            text-transform: uppercase;
            color: #9a97c9;
            font-weight: 700;
            margin: .2rem 0;
        }

        .btn-save {
            background: #2C29CA;
            color: #fff;
            border: none;
            border-radius: .8rem;
            padding: .8rem 1.8rem;
            font-weight: 700;
        }

        .combo-preview {
            font-size: .78rem;
            font-weight: 600;
            color: #2C29CA;
            background: #f3f2ff;
            border: 1px dashed #cfccff;
            border-radius: .6rem;
            padding: .45rem .7rem;
            margin-top: .5rem;
        }

        .combo-preview.is-empty {
            color: #a3a0c9;
            border-style: dashed;
            font-weight: 500;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #a3a0c9;
        }

        .alc-hero {
            background: linear-gradient(135deg, #0a0a0f 0%, #14143a 40%, #1e1b8a 75%, #2C29CA 100%);
            border-radius: 1.75rem 1.75rem 1.75rem 1.75rem;
            padding: 1.5rem 2rem 2rem;
            margin-bottom: -1rem;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid #2C29CA;
        }

        /* Subtle glow accent */
        .alc-hero::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(44, 41, 202, 0.45) 0%, transparent 70%);
            pointer-events: none;
        }

        .alc-hero::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -5%;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(107, 105, 232, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .alc-hero>* {
            position: relative;
            z-index: 1;
        }

        .alc-hero .hero-badge {
            background: rgba(44, 41, 202, 0.25);
            border: 1px solid rgba(107, 105, 232, 0.5);
            color: #c7c5ff;
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            backdrop-filter: blur(6px);
        }

        .alc-hero .hero-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            margin-bottom: .25rem;
            text-shadow: 0 2px 12px rgba(44, 41, 202, 0.4);
        }

        .alc-hero .hero-subtitle {
            color: rgba(255, 255, 255, .65);
            font-size: .82rem;
            line-height: 1.5;
            max-width: 720px;
        }

        .combo-preview.is-empty {
            color: #dc3545;
            border-style: dashed;
            font-weight: 500;
            font-style: italic;
        }

        /* ===== Combo preview row (full text + short-form badge) ===== */
        .combo-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .65rem;
            /* a touch more breathing room above */
            padding-top: .15rem;
            /* nudges the whole row down a bit */
        }

        .combo-row .combo-preview {
            margin-top: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: .55rem .8rem;
            /* slightly taller + wider padding */
            line-height: 1;
            /* Optical centering nudge — text sits a hair low by default
                                           because of the font's ascent/descent asymmetry. Pulling the
                                           padding-top down pushes the text visually to the middle. */
            padding-top: .6rem;
            padding-bottom: .5rem;
        }

        .combo-row .combo-shortform {
            display: none;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
            font-weight: 800;
            color: #fff;
            background: #2C29CA;
            border-radius: .5rem;
            padding: .6rem .8rem;
            padding-top: .65rem;
            /* matches preview's optical nudge */
            padding-bottom: .55rem;
            letter-spacing: .02em;
            min-height: 34px;
            line-height: 1;
            white-space: nowrap;
            border: 1px dashed transparent;
            /* matches preview's border width */
        }

        .combo-row .combo-shortform.is-visible {
            display: inline-flex;
        }

        /* ===== Per-row subject search ===== */
        .subject-search-wrap {
            position: relative;
            margin-bottom: .75rem;
            max-width: 260px;
        }

        .subject-search-icon {
            position: absolute;
            top: 50%;
            left: .75rem;
            transform: translateY(-50%);
            color: #9a97c9;
            font-size: .75rem;
            pointer-events: none;
        }

        .subject-search-input {
            width: 100%;
            height: 34px;
            padding: 0 .75rem 0 1.85rem;
            border: 1.5px solid #e4e2ff;
            border-radius: .6rem;
            background: #fafaff;
            font-size: .8rem;
            color: #1e1b4b;
            outline: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }

        .subject-search-input::placeholder {
            color: #b3b0d4;
        }

        .subject-search-input:focus {
            border-color: #2C29CA;
            background: #fff;
            box-shadow: 0 0 0 .18rem rgba(44, 41, 202, .12);
        }

        /* Hide filtered-out subject checkboxes */
        .subject-search-wrap~[data-principal-group] .form-check.is-filtered-out {
            display: none;
        }

        /* Optional: subtle highlight for matches — remove if you don't want it */
        .subject-search-wrap~[data-principal-group] .form-check.is-match .form-check-label {
            color: #2C29CA;
            font-weight: 700;
        }

        /* ===== Unsaved chip (sits next to the short-form badge) ===== */
.combo-row .pending-chip {
    display: none;
    align-items: center;
    gap: .35rem;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #9a6b00;
    background: #fff3cd;
    border: 1px solid #ffe8a3;
    border-radius: 999px;
    padding: .35rem .75rem;
    min-height: 34px;
    line-height: 1;
    white-space: nowrap;
}

.combo-row .pending-chip i {
    font-size: .45rem;
}

.combo-row .pending-chip.is-visible {
    display: inline-flex;
}
/* ===== Subsidiary dropdown (matches Add Your Own Subject styling) ===== */
/* ===== Subsidiary dropdown (matches Add Your Own Subject styling) ===== */
.subsidiary-select {
    width: 100%;
    height: 44px;
    padding: 0 2.25rem 0 .9rem;
    border: 1.5px solid #e4e2ff;
    border-radius: .7rem;
    background-color: #fff;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%232C29CA' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right .9rem center;
    background-size: 12px;
    font-size: .85rem;
    font-weight: 600;
    color: #1e1b4b;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    /* line-height removed — let the fixed height center the text */
    transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    /* Let the browser center the selected option text vertically */
    vertical-align: middle;
}

.subsidiary-select:hover {
    border-color: #cfccff;
    background-color: #fafaff;
}

.subsidiary-select:focus {
    border-color: #2C29CA;
    background-color: #fff;
    box-shadow: 0 0 0 .18rem rgba(44, 41, 202, .12);
}

/* Dropdown list options — keep readable, browser controls their own height */
.subsidiary-select option {
    color: #1e1b4b;
    background: #fff;
    font-weight: 500;
    padding: .5rem;
}   
/* ===== Manage Your Own Subjects — center the edit/delete icons ===== */
.my-subject-row .my-subject-edit,
.my-subject-row .my-subject-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;                    /* kill Bootstrap .btn-sm padding */
    line-height: 1;
    flex: 0 0 auto;
}

.my-subject-row .my-subject-edit i,
.my-subject-row .my-subject-delete i {
    display: block;
    line-height: 1;
    font-size: .82rem;
}
    </style>
@endsection

@section('content')
    <div class="side-app">

        <div class="row px-3 px-md-4">
            <div class="col-12">

                {{-- ===== HERO ===== --}}
                <div class="alc-hero mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <span class="hero-badge">
                                <i class="fas fa-graduation-cap me-1"></i> A-Level Combinations
                            </span>
                            <h1 class="hero-title mt-1">Build Student Combinations</h1>
                            <p class="hero-subtitle mb-0">
                                Each student's own principal subjects + optional subsidiary. General
                                Paper is compulsory for every student — it's implied automatically, not chosen here.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row px-3 px-md-4">
                    <div class="col-12">

                        {{-- ===== CLASS/STREAM PICKER ===== --}}
                        <div class="alc-card mb-4">
                            <div class="card-header-custom">
                                <div class="title"><i class="fas fa-layer-group"></i> Stream</div>
                            </div>
                            <div class="card-body-custom">
                                @forelse($classOptions as $opt)
                                    <a class="class-pill {{ (string) $opt->class_id === (string) $selectedClassId && (string) $opt->stream_id === (string) $selectedStreamId ? 'active' : '' }}"
                                        href="{{ route('alevel.combinations.entry') }}?class_id={{ $opt->class_id }}&stream_id={{ $opt->stream_id }}">
                                        {{ $opt->class_name }}{{ $opt->stream_name ? ' — ' . $opt->stream_name : '' }}
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash d-block mb-2" style="font-size:1.8rem;"></i>
                                        No Senior 5 / Senior 6 streams found yet. Create one from Create Class first.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- ===== ADD YOUR OWN SUBJECT ===== ---
                        Adds to THIS school's own list only (school_alevel_subjects)
                        — on top of the global Principal - Arts/Sciences/Subsidiary
                        list every school starts with. See
                        ALevelCombinationController::addSchoolSubject(). --}}
                        @if($students->count())
                            <div class="alc-card mb-4"
                                style="border-radius: 1.25rem; background: #fff; box-shadow: 0 8px 32px rgba(44, 41, 202, 0.12); border-top: 4px solid #2C29CA; overflow: hidden;">
                                <div
                                    style="padding: 1.25rem 1.75rem; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: .75rem;">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 50%; background: rgba(44, 41, 202, .1); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-plus" style="color: #2C29CA; font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div
                                                style="font-weight: 800; font-size: 1rem; color: #1e1b4b; letter-spacing: -.01em;">
                                                Add Your Own Subject</div>
                                            <div style="font-size: .75rem; color: #9a97c9;">Visible to your school only</div>
                                        </div>
                                    </div>
                                </div>
                                <div style="padding: 0 1.75rem 1.75rem;">
                                    <div
                                        style="display: grid; grid-template-columns: 220px 1fr auto; gap: 1rem; align-items: end;">
                                        <div>
                                            <label
                                                style="display: block; font-size: .7rem; font-weight: 700; color: #6b6899; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem;">Group</label>
                                            <select id="newSubjectGroup"
                                                style="width: 100%; height: 48px; padding: 0 1rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; background: #fff; font-size: .88rem; font-weight: 600; color: #1e1b4b; outline: none; line-height: 48px;">
                                                <option>Principal - Arts</option>
                                                <option>Principal - Sciences</option>
                                                <option>Subsidiary</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                style="display: block; font-size: .7rem; font-weight: 700; color: #6b6899; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .5rem;">Subject
                                                Name</label>
                                            <input type="text" id="newSubjectName" placeholder="e.g. Technical Drawing"
                                                style="width: 100%; height: 48px; padding: 0 1rem; border: 1.5px solid #e4e2ff; border-radius: .7rem; font-size: .88rem; color: #1e1b4b; outline: none;">
                                        </div>
                                        <div>
                                            <button type="button" id="addSubjectBtn"
                                                style="height: 48px; padding: 0 1.75rem; background: #2C29CA; color: #fff; border: none; border-radius: .7rem; font-weight: 700; font-size: .85rem; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: .5rem; box-shadow: 0 4px 14px rgba(44, 41, 202, .3);">
                                                <i class="fas fa-check"></i> Add Subject
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        style="margin-top: 1.25rem; padding: .8rem 1rem; background: linear-gradient(135deg, #f7f6ff, #eef0ff); border-radius: .65rem; font-size: .78rem; color: #2C29CA; font-weight: 500;">
                                        <i class="fas fa-info-circle" style="margin-right: .4rem;"></i>
                                        This subject will only be visible to your school, and appears immediately below for
                                        every student in this stream.
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ===== MANAGE YOUR OWN SUBJECTS (edit/delete) ===== ---
                        Everything this school has added via "Add Your Own Subject"
                        above — renamed/regrouped or removed here without touching
                        the global Principal - Arts/Sciences/Subsidiary list every
                        school starts with. See
                        ALevelCombinationController::updateSchoolSubject()/
                        deleteSchoolSubject(). --}}
                        @if($students->count() && $mySchoolSubjects->count())
                            <div class="alc-card mb-4"
                                style="border-radius: 1.25rem; background: #fff; box-shadow: 0 8px 32px rgba(44, 41, 202, 0.12); border-top: 4px solid #2C29CA; overflow: hidden;">
                                <div style="padding: 1.25rem 1.75rem; display: flex; align-items: center; gap: .75rem;">
                                    <div
                                        style="width: 40px; height: 40px; border-radius: 50%; background: rgba(44, 41, 202, .1); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-pen-to-square" style="color: #2C29CA; font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 1rem; color: #1e1b4b; letter-spacing: -.01em;">
                                            Manage Your Own Subjects</div>
                                        <div style="font-size: .75rem; color: #9a97c9;">Rename or remove a subject you added
                                            above</div>
                                    </div>
                                </div>
                                <div style="padding: 0 1.75rem 1.75rem;">
                                    <div id="mySubjectsList" style="display: flex; flex-direction: column; gap: .5rem;">
                                        @foreach($mySchoolSubjects as $mySubject)
                                            <div class="my-subject-row" data-id="{{ $mySubject->id }}"
                                                data-md-id="{{ $mySubject->syntheticId() }}"
                                                style="display: flex; align-items: center; gap: .75rem; padding: .6rem .9rem; border: 1.5px solid #e4e2ff; border-radius: .7rem;">
                                                <span class="badge"
                                                    style="background:#eef0ff; color:#3a37b8; font-weight:700; font-size:.68rem; padding:.4rem .6rem; white-space:nowrap;">{{ $mySubject->subject_group }}</span>
                                                <span class="my-subject-name"
                                                    style="flex: 1; font-weight: 600; color: #1e1b4b; font-size: .88rem;">{{ $mySubject->subject_name }}</span>
                                                <button type="button" class="btn btn-sm my-subject-edit" title="Rename"
                                                    style="background:transparent; border:1px solid #e4e2ff; border-radius:.5rem; color:#2C29CA; width:32px; height:32px;">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm my-subject-delete" title="Delete"
                                                    style="background:transparent; border:1px solid #ffd9d9; border-radius:.5rem; color:#dc3545; width:32px; height:32px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ===== COMBINATIONS GRID ===== --}}
                        @if($students->count())
                            <div class="alc-card mb-4">
                                <div class="table-responsive">
                                    <table class="table alc-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>General Paper</th>
                                                <th>Principal Subjects</th>
                                                <th>Subsidiary (optional)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $stu)
                                                @php
                                                    $existing = $combinations->get($stu->id);
                                                    $existingPrincipals = $existing->principal_subject_ids ?? [];
                                                    $existingSubsidiary = $existing->subsidiary_subject_id ?? null;
                                                @endphp
                                                <tr data-student-id="{{ $stu->id }}"
                                                    data-initial-principals='@json(array_values($existingPrincipals))'>
                                                   <td>{{ $stu->lastname }} {{ $stu->firstname }}</td>
                                                    <td><span class="gp-chip">GP — compulsory</span></td>

                                                    <td>
                                                        {{-- Per-student subject search: filters both Principal - Arts
                                                        and Principal - Sciences checkboxes live as the user types. --}}
                                                        <div class="subject-search-wrap">
                                                            <i class="fas fa-search subject-search-icon"></i>
                                                            <input type="text" class="subject-search-input"
                                                                placeholder="Search subjects…" autocomplete="off">
                                                        </div>

                                                        @foreach($principalSubjects as $group => $subjectsInGroup)
                                                            <div class="principal-group-label">{{ $group }}</div>
                                                            <div class="d-flex flex-wrap gap-2 mb-2"
                                                                data-principal-group="{{ $group }}">
                                                                @foreach($subjectsInGroup as $subject)
                                                                    <label class="form-check form-check-inline" style="margin-right:.75rem;"
                                                                        data-subject-name="{{ strtolower($subject->md_name) }}">
                                                                        <input type="checkbox" class="form-check-input principal-checkbox"
                                                                            value="{{ $subject->md_id }}" @if(in_array($subject->md_id, $existingPrincipals)) checked @endif>
                                                                        <span class="form-check-label"
                                                                            style="font-size:.8rem;">{{ $subject->md_name }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                           @if($group === 'Principal - Sciences')
    <div class="combo-row">
        <span class="combo-preview" data-role="combo-preview">—</span>
        <span class="combo-shortform" data-role="combo-shortform"></span>
        <span class="pending-chip" data-role="pending-chip">
            <i class="fas fa-circle"></i> Unsaved
        </span>
    </div>
@endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-control-sm subsidiary-select">
                                                            <option value="">None</option>
                                                            @foreach($subsidiarySubjects as $sub)
                                                                <option value="{{ $sub->md_id }}" @if((string) $existingSubsidiary === (string) $sub->md_id) selected @endif>
                                                                    {{ $sub->md_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-body-custom d-flex align-items-center justify-content-end gap-3">
                                    <span id="pendingSummary"
                                        style="display:none; font-size:.82rem; font-weight:700; color:#9a6b00;">
                                        <i class="fas fa-triangle-exclamation"></i> <span id="pendingCount">0</span> student(s)
                                        have unsaved changes
                                    </span> &nbsp; &nbsp;
                                    <button id="saveCombinationsBtn" class="btn-save">
                                        <i class="fas fa-save me-2"></i> <span id="saveBtnLabel">Save All Combinations</span>
                                    </button>
                                </div>
                            </div>
                        @elseif($classOptions->count())
                            <div class="alc-card mb-4">
                                <div class="card-body-custom">
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate d-block mb-2" style="font-size:1.8rem;"></i>
                                        No students found for this stream yet.
                                    </div>
                                </div>
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
    <script>
        const PRINCIPAL_LIMIT = 3;

        // Shared toast style — small, top-right, auto-dismissing —
        // reused by Add/Rename/Delete on this school's own subjects so
        // all three feel consistent instead of only Add having one.
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        // md_id -> subject name, for building the "Physics, Chemistry,
        // Biology / Subsidiary Mathematics" preview text client-side
        // without another round-trip — same subjects already rendered as
        // checkboxes/options above, just flattened into a lookup map.
        const subjectNameMap = {};
        @foreach($principalSubjects as $group => $subjectsInGroup)
            @foreach($subjectsInGroup as $subject)
                subjectNameMap['{{ $subject->md_id }}'] = @json($subject->md_name);
            @endforeach
        @endforeach
        @foreach($subsidiarySubjects as $sub)
            subjectNameMap['{{ $sub->md_id }}'] = @json($sub->md_name);
        @endforeach

                                // Each row's principal subjects, in the order the user actually
                                // checked them (or, on first load, the order they were originally
                                // saved in) — NOT DOM/render order, which is fixed by subject-list
                                // position and would otherwise silently reorder an already-saved
                                // combination every time this page is reopened.
                                const rowPrincipalOrder = new WeakMap();

        // Rows with a change made this session that hasn't been sent to
        // Save yet — drives the per-row "Unsaved" chip and the Save
        // button's pending count, so a forgotten combination is obvious
        // rather than silently lost on refresh/navigation.
        const dirtyRows = new Set();

        function principalOrderFor(row) {
            if (!rowPrincipalOrder.has(row)) {
                rowPrincipalOrder.set(row, []);
            }
            return rowPrincipalOrder.get(row);
        }

        function updateComboPreview(row) {
            const preview = row.querySelector('[data-role="combo-preview"]');
            if (!preview) return;

            const principalNames = principalOrderFor(row).map(id => subjectNameMap[id] || id);
            const subsidiaryId = row.querySelector('.subsidiary-select')?.value || '';
            const subsidiaryName = subsidiaryId ? (subjectNameMap[subsidiaryId] || subsidiaryId) : '';

            if (principalNames.length === 0 && !subsidiaryName) {
                preview.textContent = 'No combination selected yet';
                preview.classList.add('is-empty');
            } else {
                let text = principalNames.join(', ');
                if (subsidiaryName) {
                    text = text ? `${text} / ${subsidiaryName}` : subsidiaryName;
                }
                preview.textContent = text;
                preview.classList.remove('is-empty');
            }

            updateComboShortform(row);
        }

        // "Physics" / "Chemistry" / "Mathematics" -> "P" / "C" / "M".
        function principalInitial(name) {
            return (name || '').trim().charAt(0).toUpperCase();
        }

        // The two standard UACE subsidiaries get their conventional short
        // forms; anything else (including a school's own custom
        // subsidiary — see "Add Your Own Subject") falls back to a
        // reasonable "Sub" + the distinguishing word, since there's no
        // fixed convention for a name nobody has agreed on yet.
        function subsidiaryShortform(name) {
            const n = (name || '').toLowerCase();
            if (n.includes('ict')) return 'ICT';
            if (n.includes('math')) return 'SubMaths';

            const rest = (name || '').replace(/subsidiary/i, '').trim();
            const firstWord = rest.split(/\s+/)[0] || rest;
            return firstWord ? 'Sub' + firstWord.charAt(0).toUpperCase() + firstWord.slice(1) : 'Sub';
        }

        // The short-form badge shown beside the full combo preview, e.g.
        // "Physics, Chemistry, Mathematics / Subsidiary ICT" -> "PCM/ICT".
        function updateComboShortform(row) {
            const badge = row.querySelector('[data-role="combo-shortform"]');
            if (!badge) return;

            const principalNames = principalOrderFor(row).map(id => subjectNameMap[id] || id);
            const subsidiaryId = row.querySelector('.subsidiary-select')?.value || '';
            const subsidiaryName = subsidiaryId ? (subjectNameMap[subsidiaryId] || subsidiaryId) : '';

            if (principalNames.length === 0 && !subsidiaryName) {
                badge.style.display = 'none';
                badge.textContent = '';
                return;
            }

            let short = principalNames.map(principalInitial).join('');
            if (subsidiaryName) {
                short = short ? `${short}/${subsidiaryShortform(subsidiaryName)}` : subsidiaryShortform(subsidiaryName);
            }
            badge.textContent = `(${short})`;
            badge.style.display = short ? 'inline-block' : 'none';
        }

function markRowDirty(row) {
    dirtyRows.add(row);
    const chip = row.querySelector('[data-role="pending-chip"]');
    if (chip) chip.classList.add('is-visible');
    updatePendingSummary();
}

function markRowClean(row) {
    dirtyRows.delete(row);
    const chip = row.querySelector('[data-role="pending-chip"]');
    if (chip) chip.classList.remove('is-visible');
    updatePendingSummary();
}

        function updatePendingSummary() {
            const summary = document.getElementById('pendingSummary');
            const count = document.getElementById('pendingCount');
            const saveLabel = document.getElementById('saveBtnLabel');
            if (!summary || !count || !saveLabel) return;

            if (dirtyRows.size > 0) {
                summary.style.display = 'inline-block';
                count.textContent = dirtyRows.size;
                saveLabel.textContent = `Save All Combinations (${dirtyRows.size} pending)`;
            } else {
                summary.style.display = 'none';
                saveLabel.textContent = 'Save All Combinations';
            }
        }

        // A forgotten, unsaved combination is easy to lose by navigating
        // away entirely — warn on the way out too, not just on-page.
        window.addEventListener('beforeunload', (e) => {
            if (dirtyRows.size > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Once PRINCIPAL_LIMIT are checked in a row, lock the rest until
        // the user frees a slot by unchecking one — rather than letting
        // them keep piling on and only rejecting it later at Save.
        function applyPrincipalLimit(row) {
            const order = principalOrderFor(row);
            const atLimit = order.length >= PRINCIPAL_LIMIT;
            row.querySelectorAll('.principal-checkbox').forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = atLimit;
                }
            });
        }

        // Wire one principal checkbox: keeps rowPrincipalOrder in sync
        // (append on check, remove on uncheck) instead of just re-deriving
        // order from :checked position every time, then re-renders the
        // preview and the 3-subject lock for that row.
        function wirePrincipalCheckbox(cb, row) {
            cb.addEventListener('change', () => {
                const order = principalOrderFor(row);
                const i = order.indexOf(cb.value);
                if (cb.checked && i === -1) {
                    order.push(cb.value);
                } else if (!cb.checked && i !== -1) {
                    order.splice(i, 1);
                }
                applyPrincipalLimit(row);
                updateComboPreview(row);
                markRowDirty(row);
            });
        }

        // Wire up every row: seed each row's check-order from whatever was
        // actually saved (data-initial-principals — see
        // ALevelCombinationController::entry()/$existingPrincipals), fall
        // back to current DOM order for anything checked that wasn't in
        // that saved list (shouldn't normally happen, but keeps a stray
        // checked box from vanishing off the preview), then render once
        // immediately so already-saved combinations show right away
        // rather than only after the next edit — nothing here counts as a
        // "change" yet, so rows start clean, not pending.
        document.querySelectorAll('tr[data-student-id]').forEach(row => {
            let initial = [];
            try {
                initial = JSON.parse(row.dataset.initialPrincipals || '[]').map(String);
            } catch (e) {
                initial = [];
            }
            const checkedNow = new Set(
                Array.from(row.querySelectorAll('.principal-checkbox:checked')).map(cb => cb.value)
            );
            const order = initial.filter(id => checkedNow.has(id));
            checkedNow.forEach(id => { if (!order.includes(id)) order.push(id); });
            rowPrincipalOrder.set(row, order);

            row.querySelectorAll('.principal-checkbox').forEach(cb => wirePrincipalCheckbox(cb, row));
            row.querySelectorAll('.subsidiary-select').forEach(el => {
                el.addEventListener('change', () => {
                    updateComboPreview(row);
                    markRowDirty(row);
                });
            });

            applyPrincipalLimit(row);
            updateComboPreview(row);
        });

        document.getElementById('addSubjectBtn')?.addEventListener('click', function () {
            const group = document.getElementById('newSubjectGroup').value;
            const name = document.getElementById('newSubjectName').value.trim();
            if (!name) {
                Swal.fire('Missing name', 'Please type a subject name first.', 'warning');
                return;
            }

            const $btn = this;
            const originalHTML = $btn.innerHTML;

            // Step 1: spinner state
            $btn.disabled = true;
            $btn.style.background = '#4d4be0';
            $btn.style.cursor = 'not-allowed';
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            fetch('{{ route('alevel.combinations.add-subject') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ subject_group: group, subject_name: name }),
            })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        // Restore on failure
                        $btn.disabled = false;
                        $btn.style.background = '#2C29CA';
                        $btn.style.cursor = 'pointer';
                        $btn.innerHTML = originalHTML;
                        Swal.fire('Error', res.message || 'Failed to add subject.', 'error');
                        return;
                    }

                    const subject = res.subject;
                    subjectNameMap[subject.md_id] = subject.md_name;
                    document.getElementById('newSubjectName').value = '';

                    // Drop the new checkbox/option into every student row
                    document.querySelectorAll('tr[data-student-id]').forEach(row => {
                        if (subject.md_misc1 === 'Subsidiary') {
                            const select = row.querySelector('.subsidiary-select');
                            if (select) {
                                const opt = document.createElement('option');
                                opt.value = subject.md_id;
                                opt.textContent = subject.md_name;
                                select.appendChild(opt);
                            }
                            return;
                        }

                        let group = row.querySelector(`[data-principal-group="${subject.md_misc1}"]`);
                        if (!group) return;

                        const label = document.createElement('label');
                        label.className = 'form-check form-check-inline';
                        label.style.marginRight = '.75rem';
                        label.innerHTML = `
                                            <input type="checkbox" class="form-check-input principal-checkbox" value="${subject.md_id}">
                                            <span class="form-check-label" style="font-size:.8rem;">${subject.md_name}</span>
                                        `;
                        group.appendChild(label);
                        wirePrincipalCheckbox(label.querySelector('.principal-checkbox'), row);
                        applyPrincipalLimit(row);
                    });

                    // Also drop it into the "Manage Your Own Subjects" list so it
                    // can be renamed/deleted right away without a page reload.
                    if (subject.id) {
                        addRowToManageList(subject);
                    }

                    // Step 2: success state on the button itself
                    $btn.style.background = '#1e9e5a';
                    $btn.innerHTML = '<i class="fas fa-check"></i> Added!';

                    // Small toast, no click needed
                    Toast.fire({
                        icon: 'success',
                        title: `"${subject.md_name}" added`,
                    });

                    // Step 3: hold "Added!" for 2 seconds, then reset
                    setTimeout(() => {
                        $btn.disabled = false;
                        $btn.style.background = '#2C29CA';
                        $btn.style.cursor = 'pointer';
                        $btn.innerHTML = originalHTML;
                    }, 2000);
                })
                .catch(() => {
                    // Restore on network error
                    $btn.disabled = false;
                    $btn.style.background = '#2C29CA';
                    $btn.style.cursor = 'pointer';
                    $btn.innerHTML = originalHTML;
                    Swal.fire('Error', 'Failed to add subject — check your connection.', 'error');
                });
        });

        // ===== MANAGE YOUR OWN SUBJECTS (edit/delete) =====
        // addSchoolSubject() (above) only ever returns {md_id, md_name,
        // md_misc1} — no real ->id — since that's all the combinations
        // grid needs. The manage list needs the real id too (that's what
        // update/delete are keyed on), so this builds its row from the
        // full {id, md_id, md_name, md_misc1} shape
        // updateSchoolSubject()/addSchoolSubject() both return.
        function addRowToManageList(subject) {
            const list = document.getElementById('mySubjectsList');
            if (!list) return;

            const row = document.createElement('div');
            row.className = 'my-subject-row';
            row.dataset.id = subject.id;
            row.dataset.mdId = subject.md_id;
            row.style.cssText = 'display:flex; align-items:center; gap:.75rem; padding:.6rem .9rem; border:1.5px solid #e4e2ff; border-radius:.7rem;';
            row.innerHTML = `
                                        <span class="badge" style="background:#eef0ff; color:#3a37b8; font-weight:700; font-size:.68rem; padding:.4rem .6rem; white-space:nowrap;">${subject.md_misc1}</span>
                                        <span class="my-subject-name" style="flex:1; font-weight:600; color:#1e1b4b; font-size:.88rem;">${subject.md_name}</span>
                                        <button type="button" class="btn btn-sm my-subject-edit" title="Rename" style="background:transparent; border:1px solid #e4e2ff; border-radius:.5rem; color:#2C29CA; width:32px; height:32px;"><i class="fas fa-pen"></i></button>
                                        <button type="button" class="btn btn-sm my-subject-delete" title="Delete" style="background:transparent; border:1px solid #ffd9d9; border-radius:.5rem; color:#dc3545; width:32px; height:32px;"><i class="fas fa-trash"></i></button>
                                    `;
            list.appendChild(row);
            wireManageRow(row);
        }

        function wireManageRow(row) {
            const mdId = row.dataset.mdId;

            row.querySelector('.my-subject-edit')?.addEventListener('click', () => {
                const nameEl = row.querySelector('.my-subject-name');
                const currentName = nameEl.textContent.trim();
                const currentGroup = row.querySelector('.badge').textContent.trim();

                Swal.fire({
                    title: 'Rename subject',
                    html:
                        `<input id="swalSubjectName" class="swal2-input" placeholder="Subject name" value="${currentName.replace(/"/g, '&quot;')}">` +
                        `<select id="swalSubjectGroup" class="swal2-input">` +
                        `<option value="Principal - Arts"${currentGroup === 'Principal - Arts' ? ' selected' : ''}>Principal - Arts</option>` +
                        `<option value="Principal - Sciences"${currentGroup === 'Principal - Sciences' ? ' selected' : ''}>Principal - Sciences</option>` +
                        `<option value="Subsidiary"${currentGroup === 'Subsidiary' ? ' selected' : ''}>Subsidiary</option>` +
                        `</select>`,
                    confirmButtonText: 'Save',
                    confirmButtonColor: '#2C29CA',
                    showCancelButton: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        const name = document.getElementById('swalSubjectName').value.trim();
                        const group = document.getElementById('swalSubjectGroup').value;
                        if (!name) {
                            Swal.showValidationMessage('Please enter a subject name.');
                            return false;
                        }

                        // showLoaderOnConfirm swaps the "Save" button for a
                        // spinner for the duration of this promise, instead
                        // of a separate loading state we'd have to manage
                        // ourselves.
                        return fetch(`{{ url('a-level-combinations/subjects') }}/${row.dataset.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ subject_group: group, subject_name: name }),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.showValidationMessage(res.message || 'Failed to rename subject.');
                                    return false;
                                }
                                return res.subject;
                            })
                            .catch(() => {
                                Swal.showValidationMessage('Failed to rename — check your connection.');
                                return false;
                            });
                    },
                    showLoaderOnConfirm: true,
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;

                    const subject = result.value;
                    subjectNameMap[subject.md_id] = subject.md_name;
                    nameEl.textContent = subject.md_name;
                    row.querySelector('.badge').textContent = subject.md_misc1;

                    // Reflect the new name/group everywhere this subject's
                    // checkbox/option already appears.
                    document.querySelectorAll('tr[data-student-id]').forEach(studentRow => {
                        const cb = studentRow.querySelector(`.principal-checkbox[value="${mdId}"]`);
                        if (cb) {
                            cb.closest('label').querySelector('.form-check-label').textContent = subject.md_name;
                            const newGroupEl = studentRow.querySelector(`[data-principal-group="${subject.md_misc1}"]`);
                            if (newGroupEl && cb.closest('label').parentElement !== newGroupEl) {
                                newGroupEl.appendChild(cb.closest('label'));
                            }
                        }
                        const opt = studentRow.querySelector(`.subsidiary-select option[value="${mdId}"]`);
                        if (opt) opt.textContent = subject.md_name;
                        updateComboPreview(studentRow);
                    });

                    Toast.fire({ icon: 'success', title: `"${subject.md_name}" renamed` });
                });
            });

            row.querySelector('.my-subject-delete')?.addEventListener('click', () => {
                Swal.fire({
                    title: 'Delete this subject?',
                    text: 'This cannot be undone. Students who already have it in their combination will need it removed from this list only if it is not currently in use.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: () => !Swal.isLoading(),
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(`{{ url('a-level-combinations/subjects') }}/${row.dataset.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.success) {
                                    Swal.showValidationMessage(res.message || 'Failed to delete subject.');
                                    return false;
                                }
                                return true;
                            })
                            .catch(() => {
                                Swal.showValidationMessage('Failed to delete — check your connection.');
                                return false;
                            });
                    },
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;

                    const removedName = row.querySelector('.my-subject-name')?.textContent.trim();
                    delete subjectNameMap[mdId];

                    document.querySelectorAll('tr[data-student-id]').forEach(studentRow => {
                        studentRow.querySelector(`.principal-checkbox[value="${mdId}"]`)?.closest('label')?.remove();
                        studentRow.querySelector(`.subsidiary-select option[value="${mdId}"]`)?.remove();

                        const order = principalOrderFor(studentRow);
                        const i = order.indexOf(String(mdId));
                        if (i !== -1) order.splice(i, 1);

                        applyPrincipalLimit(studentRow);
                        updateComboPreview(studentRow);
                    });

                    row.remove();
                    Toast.fire({ icon: 'success', title: `"${removedName}" deleted` });
                });
            });
        }

        document.querySelectorAll('.my-subject-row').forEach(wireManageRow);

        document.getElementById('saveCombinationsBtn')?.addEventListener('click', function () {
            const combinations = [];
            document.querySelectorAll('tr[data-student-id]').forEach(row => {
                const studentId = row.dataset.studentId;
                const principals = principalOrderFor(row).slice();
                const subsidiary = row.querySelector('.subsidiary-select')?.value || null;
                combinations.push({
                    student_id: studentId,
                    principal_subject_ids: principals,
                    subsidiary_subject_id: subsidiary,
                });
            });

            const $btn = this;
            $btn.disabled = true;
            $btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

            fetch('{{ route('alevel.combinations.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ combinations }),
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.querySelectorAll('tr[data-student-id]').forEach(row => markRowClean(row));
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: res.message || 'All combinations have been saved.',
                            confirmButtonColor: '#2C29CA',
                            timer: 2500,
                            timerProgressBar: true,
                        });
                    } else {
                        Swal.fire('Error', res.message || 'Failed to save combinations.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to save — check your connection.', 'error'))
                .finally(() => {
                    $btn.disabled = false;
                    $btn.innerHTML = '<i class="fas fa-save me-2"></i> <span id="saveBtnLabel">Save All Combinations</span>';
                    updatePendingSummary();
                });
        });

        // ===== Per-row subject search =====
        // Filters each student's Principal - Arts / Sciences checkboxes
        // live as the user types. Matching is case-insensitive substring
        // against the label text (stored in data-subject-name).
        document.querySelectorAll('tr[data-student-id]').forEach(row => {
            const input = row.querySelector('.subject-search-input');
            if (!input) return;

            input.addEventListener('input', () => {
                const q = input.value.trim().toLowerCase();

                row.querySelectorAll('.form-check[data-subject-name]').forEach(label => {
                    const name = label.dataset.subjectName || '';
                    const match = q === '' || name.includes(q);

                    label.classList.toggle('is-filtered-out', !match);
                    label.classList.toggle('is-match', q !== '' && match);
                });
            });
        });
    </script>
@endsection