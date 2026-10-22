<?php use App\Http\Controllers\Helper; ?>
@extends('layouts-side-bar.master')

@section('css')
    @include('Examination.open-assessments._styles')
@endsection

@section('content')
    @php
        $canManage = \App\Helpers\PermissionHelper::canFeature('manage_open_assessments');
        $canDelete = \App\Helpers\PermissionHelper::canFeature('delete_open_assessments');
        $baseUrl = route('open-assessments.store', $classSubject->id);
    @endphp

    <div class="oa-hero">
        <span class="hero-badge"><i class="fas fa-list-check me-1"></i> Open Assessments</span>
        <div class="hero-title">{{ $subjectName }} — {{ $className }}@if($streamLabel) · {{ $streamLabel }}@endif</div>
        <div class="hero-subtitle">{{ $studentCount }} student(s) · create as many assessments as you like — no examination is needed.</div>
        <div class="hero-links mt-2">
            <a href="{{ route('open-assessments.index') }}"><i class="fas fa-arrow-left me-1"></i> My subjects</a>
        </div>
    </div>

    @if($canManage)
        <div class="oa-card">
            <div class="oa-card-head"><span id="formTitle"><i class="fas fa-plus-circle me-2"></i> New assessment</span></div>
            <div class="oa-card-body">
                <div class="oa-grid">
                    <div>
                        <label class="oa-label">Assessment type</label>
                        <select id="fType" class="oa-input">
                            <option value="">General (no type)</option>
                            <optgroup label="Curriculum (NLSC)">
                                @foreach($nlscTypes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                            </optgroup>
                            <optgroup label="Other types">
                                @foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div id="customTypeWrap" style="display:none;">
                        <label class="oa-label">Name this type <span class="oa-sub">(optional)</span></label>
                        <input type="text" id="fCustom" class="oa-input" maxlength="40" placeholder="e.g. Lab report, Oral test">
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="oa-label" id="titleLabel">Title *</label>
                        <input type="text" id="fTitle" class="oa-input" maxlength="150" placeholder="e.g. Quiz 1, Mid-term test, Group project">
                    </div>
                </div>

                {{-- Curriculum types only: Topic / Project → Competency Area / Achievement Statement --}}
                <div class="oa-grid mt-3" id="nlscBlock" style="display:none;">
                    <div style="grid-column: span 2;">
                        <div class="oa-label-row">
                            <label class="oa-label" id="matterLabel">Topics *</label>
                            <a id="catalogueLink" href="#" target="_blank" rel="noopener" class="oa-link" style="display:none;">Manage topics</a>
                        </div>
                        <div class="oa-searchable" id="matterSearchable">
                            <button type="button" class="oa-search-input" id="matterTrigger" disabled>
                                <span class="oa-selected-text placeholder" id="matterText">Select assessment type first...</span>
                                <i class="fas fa-chevron-down oa-caret"></i>
                            </button>
                            <div class="oa-search-panel">
                                <div class="oa-search-box"><input type="text" id="matterSearch" placeholder="Search..." autocomplete="off"></div>
                                <div class="oa-options-list" id="matterOptions"><div class="oa-no-results">No options</div></div>
                            </div>
                            <input type="hidden" id="matterValue" value="">
                        </div>
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="oa-label" id="secondLabel">Competency Areas *</label>
                        <div class="oa-searchable" id="secondSearchable">
                            <button type="button" class="oa-search-input" id="secondTrigger" disabled>
                                <span class="oa-selected-text placeholder" id="secondText">Select a topic/project first...</span>
                                <i class="fas fa-chevron-down oa-caret"></i>
                            </button>
                            <div class="oa-search-panel">
                                <div class="oa-search-box"><input type="text" id="secondSearch" placeholder="Search..." autocomplete="off"></div>
                                <div class="oa-options-list" id="secondOptions"><div class="oa-no-results">No options</div></div>
                            </div>
                            <input type="hidden" id="secondValue" value="">
                        </div>
                    </div>
                </div>

                <div class="oa-grid mt-3">
                    <div>
                        <label class="oa-label">Marked out of *</label>
                        <input type="number" id="fMax" class="oa-input" min="0.01" max="1000" step="0.5" placeholder="e.g. 20">
                    </div>
                    <div>
                        <label class="oa-label">Term *</label>
                        <select id="fTerm" class="oa-input">
                            <option value="">— Choose —</option>
                            @foreach($terms as $t)<option value="{{ $t }}" {{ $t === $defaultTerm ? 'selected' : '' }}>{{ \App\Support\Term::label($t) }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Year *</label>
                        <select id="fYear" class="oa-input">
                            @foreach($years as $y)<option value="{{ $y }}" {{ (string) $y === (string) $defaultYear ? 'selected' : '' }}>{{ $y }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="oa-label">Date</label>
                        <input type="date" id="fDate" class="oa-input">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="oa-label">Notes (optional)</label>
                    <input type="text" id="fDesc" class="oa-input" maxlength="1000" placeholder="What does this assessment cover?">
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="oa-btn" id="saveBtn"><i class="fas fa-check"></i> <span id="saveLabel">Create assessment</span></button>
                    <button type="button" class="oa-btn light" id="cancelEditBtn" style="display:none;">Cancel edit</button>
                </div>
            </div>
        </div>
    @endif

    <div class="oa-card">
        <div class="oa-card-head">
            <span><i class="fas fa-list me-2"></i> Assessments ({{ $assessments->count() }})</span>
            <span class="oa-sub">The administrator decides later which of these count on the report card.</span>
        </div>
        <div class="table-responsive">
            <table class="table oa-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Term</th>
                        <th>Date</th>
                        <th style="width:90px;">Out of</th>
                        <th style="width:170px;">Marks entered</th>
                        <th style="width:90px;">Class avg</th>
                        <th>Created by</th>
                        <th style="width:240px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $a)
                        @php
                            $pct = $studentCount > 0 ? min(100, round(($a->entered_count / $studentCount) * 100)) : 0;
                            $isNlsc = !empty($a->assessment_type);
                            $matterId = $a->assessment_type === 'projects' ? $a->nlsc_project_id : $a->nlsc_topic_id;
                            $secondId = $a->assessment_type === 'subject_achievement' ? $a->nlsc_subject_achievement_id : $a->nlsc_competency_area_id;
                        @endphp
                        <tr data-id="{{ $a->id }}"
                            data-title="{{ $a->title }}" data-type="{{ $isNlsc ? $a->assessment_type : $a->category }}" data-max="{{ $a->max_marks }}"
                            data-term="{{ $a->term }}" data-year="{{ $a->academic_year }}"
                            data-matter-id="{{ $matterId }}" data-second-id="{{ $secondId }}"
                            data-date="{{ optional($a->assessment_date)->format('Y-m-d') }}" data-desc="{{ $a->description }}">
                            <td class="fw-bold">
                                {{ $a->title }}
                                @if($a->nlsc)
                                    <div class="oa-detail">
                                        <b>{{ $a->nlsc['matter_label'] }}:</b> {{ $a->nlsc['matter'] ?? '—' }}<br>
                                        <b>{{ $a->nlsc['competency_label'] }}:</b> {{ $a->nlsc['competency'] ?? '—' }}
                                    </div>
                                @endif
                                @if($a->description)<div class="oa-sub">{{ \Illuminate\Support\Str::limit($a->description, 70) }}</div>@endif
                            </td>
                            <td>@if($a->type_label)<span class="oa-pill">{{ $a->type_label }}</span>@else<span class="oa-sub">—</span>@endif</td>
                            <td>{{ \App\Support\Term::label($a->term) }} {{ $a->academic_year }}</td>
                            <td>{{ optional($a->assessment_date)->format('d M Y') ?? '—' }}</td>
                            <td>{{ rtrim(rtrim(number_format($a->max_marks, 2), '0'), '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="oa-bar flex-grow-1"><span style="width: {{ $pct }}%;"></span></div>
                                    <span class="oa-sub">{{ $a->entered_count }}/{{ $studentCount }}</span>
                                </div>
                            </td>
                            <td>{{ $a->average_percent !== null ? $a->average_percent . '%' : '—' }}</td>
                            <td class="oa-sub">{{ $a->creator_name }}
                                @if(!empty($a->card_names))<div><span class="oa-pill green" title="Used on: {{ implode(', ', $a->card_names) }}"><i class="fas fa-lock me-1"></i> On report card</span></div>@endif
                            </td>
                            <td class="text-end">
                                <a class="oa-btn sm" href="{{ route('open-assessments.marks-entry', ['classSubjectId' => $classSubject->id, 'assessmentId' => $a->id]) }}">
                                    <i class="fas fa-pen-to-square"></i> Marks
                                </a>
                                @if($canManage)<button type="button" class="oa-btn sm light edit-btn"><i class="fas fa-pen"></i></button>@endif
                                @if($canDelete && empty($a->card_names))<button type="button" class="oa-btn sm danger delete-btn" data-has-marks="{{ $a->entered_count }}"><i class="fas fa-trash"></i></button>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="oa-empty"><i class="fas fa-clipboard"></i>No assessments yet — create your first one above, then enter marks for it.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const CSRF = @json(csrf_token());
            const BASE = @json($baseUrl);
            const MATTER_URL = @json(route('open-assessments.subject-matter-options', $classSubject->id));
            const SECOND_URL = @json(route('open-assessments.competency-area-options'));
            const CATALOGUE = @json($catalogueUrls);
            const NLSC_TYPES = @json(array_keys($nlscTypes));
            const GENERAL_TYPES = @json($categories);

            let editingId = null;
            let pendingMatterId = null;
            let pendingSecondId = null;

            const $ = id => document.getElementById(id);
            const isNlsc = () => NLSC_TYPES.includes($('fType')?.value);

            /* ---------- searchable dropdown (same behaviour as "Create Assessment") ---------- */
            function createSearchable(ids) {
                const root = $(ids.root), trigger = $(ids.trigger), textEl = $(ids.text),
                      searchInput = $(ids.search), list = $(ids.options), hidden = $(ids.hidden);
                let options = [], filtered = [], selected = '', placeholder = textEl.textContent, open = false;

                const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                const render = () => {
                    list.innerHTML = filtered.length
                        ? filtered.map(o => `<div class="oa-option${String(o.id) === String(selected) ? ' selected' : ''}" data-value="${o.id}">${esc(o.label)}</div>`).join('')
                        : '<div class="oa-no-results">No results found</div>';
                };
                const filter = q => {
                    q = (q || '').trim().toLowerCase();
                    filtered = q ? options.filter(o => String(o.label).toLowerCase().includes(q)) : options.slice();
                    render();
                };
                const closePanel = () => { open = false; root.classList.remove('open'); };
                const showPlaceholder = () => { textEl.textContent = placeholder; textEl.classList.add('placeholder'); };

                trigger.addEventListener('click', e => {
                    e.preventDefault();
                    if (trigger.disabled) return;
                    if (open) return closePanel();
                    document.querySelectorAll('.oa-searchable.open').forEach(el => { if (el !== root) el.classList.remove('open'); });
                    open = true; root.classList.add('open'); searchInput.value = ''; filter('');
                    setTimeout(() => searchInput.focus(), 30);
                });
                searchInput.addEventListener('input', () => filter(searchInput.value));
                searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });
                list.addEventListener('click', e => {
                    const el = e.target.closest('.oa-option');
                    if (!el) return;
                    const opt = options.find(o => String(o.id) === String(el.dataset.value));
                    if (opt) api.setValue(opt.id, opt.label);
                    closePanel();
                });
                document.addEventListener('click', e => { if (!root.contains(e.target)) closePanel(); });

                const api = {
                    setOptions(opts, ph) {
                        options = Array.isArray(opts) ? opts : []; filtered = options.slice();
                        if (typeof ph === 'string') placeholder = ph;
                        selected = ''; hidden.value = ''; showPlaceholder(); render();
                    },
                    enable() { trigger.disabled = false; },
                    disable(ph) {
                        trigger.disabled = true;
                        if (typeof ph === 'string') placeholder = ph;
                        selected = ''; hidden.value = ''; options = []; filtered = [];
                        showPlaceholder(); render(); closePanel();
                    },
                    setValue(val, label) {
                        selected = val ? String(val) : ''; hidden.value = selected;
                        if (selected) { textEl.textContent = label || selected; textEl.classList.remove('placeholder'); } else { showPlaceholder(); }
                        render(); hidden.dispatchEvent(new Event('change'));
                    },
                    clear() { selected = ''; hidden.value = ''; showPlaceholder(); render(); hidden.dispatchEvent(new Event('change')); },
                };
                return api;
            }

            if ($('matterSearchable')) {
                const matter = createSearchable({ root: 'matterSearchable', trigger: 'matterTrigger', text: 'matterText', search: 'matterSearch', options: 'matterOptions', hidden: 'matterValue' });
                const second = createSearchable({ root: 'secondSearchable', trigger: 'secondTrigger', text: 'secondText', search: 'secondSearch', options: 'secondOptions', hidden: 'secondValue' });

                const loadJson = url => fetch(url, { headers: { 'Accept': 'application/json' } }).then(r => r.json());

                // Choosing the assessment type filters the Topics / Projects, exactly like "Create Assessment".
                $('fType').addEventListener('change', function () {
                    const type = this.value;
                    const nlsc = NLSC_TYPES.includes(type);

                    $('nlscBlock').style.display = nlsc ? '' : 'none';
                    $('titleLabel').textContent = nlsc ? 'Title (optional — built from your selection if left blank)' : 'Title *';
                    matter.disable(nlsc ? 'Loading...' : 'Select assessment type first...');
                    second.disable('Select a topic/project first...');

                    const link = $('catalogueLink');
                    const url = type === 'projects' ? CATALOGUE.projects : CATALOGUE.topics;
                    if (nlsc && url) {
                        link.href = url; link.textContent = 'Manage ' + (type === 'projects' ? 'projects' : 'topics') + ' for this subject'; link.style.display = 'inline';
                    } else { link.style.display = 'none'; }

                    if (!nlsc) return;

                    $('matterLabel').textContent = (type === 'projects' ? 'Project' : 'Topics') + ' *';
                    $('secondLabel').textContent = (type === 'subject_achievement' ? 'Achievement Statement' : 'Competency Areas') + ' *';

                    loadJson(`${MATTER_URL}?assessment_type=${type}`).then(res => {
                        if (!res.success || !res.options.length) return matter.disable('None set up yet — add one from the catalogue first');
                        matter.setOptions(res.options, 'Select...'); matter.enable();
                        if (pendingMatterId) {
                            const m = res.options.find(o => String(o.id) === String(pendingMatterId));
                            pendingMatterId = null;
                            if (m) matter.setValue(m.id, m.label);
                        }
                    }).catch(() => matter.disable('Failed to load — try again'));
                });

                // Choosing a Topic / Project loads its Competency Areas (or the topic's Achievement Statements).
                $('matterValue').addEventListener('change', function () {
                    if (!this.value) return second.disable('Select a topic/project first...');
                    second.disable('Loading...');

                    loadJson(`${SECOND_URL}?assessment_type=${$('fType').value}&subject_matter_id=${this.value}`).then(res => {
                        if (!res.success || !res.options.length) return second.disable('None set up yet — add one from the catalogue first');
                        second.setOptions(res.options, 'Select...'); second.enable();
                        if (pendingSecondId) {
                            const m = res.options.find(o => String(o.id) === String(pendingSecondId));
                            pendingSecondId = null;
                            if (m) second.setValue(m.id, m.label);
                        }
                    }).catch(() => second.disable('Failed to load — try again'));
                });

                window.__oaResetNlsc = () => { matter.clear(); matter.disable('Select assessment type first...'); second.clear(); second.disable('Select a topic/project first...'); };
            }

            // "Other" lets the teacher give the assessment type their own name.
            $('fType')?.addEventListener('change', function () {
                const other = this.value === 'Other';
                $('customTypeWrap').style.display = other ? '' : 'none';
                if (!other) $('fCustom').value = '';
                else setTimeout(() => $('fCustom').focus(), 30);
            });

            /* ---------- form ---------- */
            function resetForm() {
                editingId = null; pendingMatterId = null; pendingSecondId = null;
                ['fTitle', 'fMax', 'fDate', 'fDesc'].forEach(i => { if ($(i)) $(i).value = ''; });
                if ($('fType')) { $('fType').value = ''; $('fType').dispatchEvent(new Event('change')); }
                if (window.__oaResetNlsc) window.__oaResetNlsc();
                if ($('saveLabel')) $('saveLabel').textContent = 'Create assessment';
                if ($('formTitle')) $('formTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i> New assessment';
                if ($('cancelEditBtn')) $('cancelEditBtn').style.display = 'none';
            }

            async function send(url, method, body) {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: body ? JSON.stringify(body) : undefined,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) {
                    const first = data.errors ? Object.values(data.errors)[0][0] : null;
                    throw new Error(first || data.message || 'Something went wrong.');
                }
                return data;
            }

            $('saveBtn')?.addEventListener('click', async function () {
                const type = $('fType').value;
                const nlsc = NLSC_TYPES.includes(type);
                const title = $('fTitle').value.trim();

                if (nlsc) {
                    if (!$('matterValue').value) return Swal.fire('Choose a ' + (type === 'projects' ? 'project' : 'topic'), 'Pick what this assessment is set against.', 'warning');
                    if (!$('secondValue').value) return Swal.fire('Almost there', 'Choose the ' + (type === 'subject_achievement' ? 'achievement statement' : 'competency area') + '.', 'warning');
                } else if (!title) {
                    return Swal.fire('Title needed', 'Give the assessment a title.', 'warning');
                }
                if (!$('fTerm').value) return Swal.fire('Term needed', 'Choose the term this assessment belongs to.', 'warning');
                if (!$('fMax').value || parseFloat($('fMax').value) <= 0) return Swal.fire('Marked out of', 'Enter the maximum marks (greater than 0).', 'warning');

                const body = {
                    title: title || null,
                    assessment_type: nlsc ? type : null,
                    category: nlsc ? null : (type || null),
                    custom_category: type === 'Other' ? ($('fCustom').value.trim() || null) : null,
                    subject_matter_id: nlsc ? $('matterValue').value : null,
                    nlsc_competency_area_id: nlsc && type !== 'subject_achievement' ? $('secondValue').value : null,
                    nlsc_subject_achievement_id: nlsc && type === 'subject_achievement' ? $('secondValue').value : null,
                    max_marks: $('fMax').value, assessment_date: $('fDate').value || null,
                    term: $('fTerm').value, academic_year: $('fYear').value,
                    description: $('fDesc').value.trim() || null,
                };

                this.disabled = true;
                try {
                    await send(editingId ? BASE + '/' + editingId : BASE, editingId ? 'PUT' : 'POST', body);
                    window.location.reload();
                } catch (e) {
                    Swal.fire('Could not save', e.message, 'error');
                    this.disabled = false;
                }
            });

            $('cancelEditBtn')?.addEventListener('click', resetForm);

            document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', function () {
                const d = this.closest('tr').dataset;
                editingId = d.id;
                $('fTitle').value = d.title;
                $('fTerm').value = d.term || ''; $('fYear').value = d.year || $('fYear').value;
                $('fMax').value = d.max; $('fDate').value = d.date || ''; $('fDesc').value = d.desc || '';

                // Re-select the saved Topic/Project and Competency/Achievement once their options load.
                pendingMatterId = d.matterId || null;
                pendingSecondId = d.secondId || null;
                let type = d.type || '', custom = '';
                if (type && !NLSC_TYPES.includes(type) && !GENERAL_TYPES.includes(type)) { custom = type; type = 'Other'; }
                $('fType').value = type;
                $('fType').dispatchEvent(new Event('change'));
                $('fCustom').value = custom;

                $('saveLabel').textContent = 'Save changes';
                $('formTitle').innerHTML = '<i class="fas fa-pen me-2"></i> Edit assessment';
                $('cancelEditBtn').style.display = 'inline-flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }));

            document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', function () {
                const row = this.closest('tr');
                const marks = parseInt(this.dataset.hasMarks || '0', 10);
                Swal.fire({
                    title: 'Delete “' + row.dataset.title + '”?',
                    text: marks ? 'The ' + marks + ' mark(s) already entered for it will be deleted too.' : 'This cannot be undone.',
                    icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete',
                }).then(async r => {
                    if (!r.isConfirmed) return;
                    try { await send(BASE + '/' + row.dataset.id, 'DELETE'); window.location.reload(); }
                    catch (e) { Swal.fire('Could not delete', e.message, 'error'); }
                });
            }));
        })();
    </script>
@endsection
