@extends('layouts-side-bar.master')

@section('content')

<style>
    table thead th {
    background-color: #2C29CA !important;
    color: #FFFFFF !important;
}
</style>
    @php
        $open = in_array($exam->status, ['marks_entry', 'active'], true);
        $fmt = fn($v) => $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    @endphp

    <div class="side-app">

        <x-exam-hero icon="fa-file-excel" title="Import marks from Excel"
            :subtitle="$exam->exam_name . ' · ' . \App\Support\Term::label($exam->term) . ' ' . $exam->academic_year">
            <x-slot:chips>
                <span class="exh-chip exh-chip--{{ $exam->status }}">{{ $exam->statusLabel() }}</span>
            </x-slot:chips>
            <x-slot:actions>
                <a href="{{ route('examination.marks.entry', $exam->id) }}" class="exh-btn">
                    <i class="fas fa-arrow-left"></i><span>Back to Marks Entry</span>
                </a>
            </x-slot:actions>
            <x-slot:features>
                <x-exam-hero-tile icon="fa-download" title="1 · Template" desc="Pupils and subjects pre-filled" color="#818cf8" />
                <x-exam-hero-tile icon="fa-magnifying-glass" title="2 · Preview" desc="Nothing is saved yet" color="#fbbf24" />
                <x-exam-hero-tile icon="fa-circle-check" title="3 · Import" desc="Graded like normal marks entry" color="#34d399" />
            </x-slot:features>
        </x-exam-hero>

        @if (session('success'))
            <div class="ex-alert ex-alert--success"><i class="fas fa-check-circle me-2 mr-2"></i>{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="ex-alert ex-alert--error"><i class="fas fa-exclamation-circle me-2 mr-2"></i>{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="ex-alert ex-alert--error">{{ $errors->first() }}</div>
        @endif
        @if (!$open)
            <div class="ex-alert ex-alert--warning">
                <i class="fas fa-lock me-2 mr-2"></i>Marks entry is <strong>{{ $exam->statusLabel() }}</strong> for this examination, so marks cannot be imported.
                Open marks entry for the examination first.
            </div>
        @endif

        @if ($classOptions->isEmpty())
            <div class="ex-card">
                <p class="ex-help" style="margin:0;">
                    <i class="fas fa-info-circle me-2 mr-2"></i>You have no subjects assigned to you in this examination, so there is nothing for you to import.
                    Subjects are assigned to teachers under Classes &rarr; Subjects. You only see and import the subjects assigned to you, exactly as on Marks Entry.
                </p>
            </div>
        @else
        {{-- STEP 1 --}}
        <div class="ex-card">
            <h5 class="ex-card-title"><span class="ex-step">1</span> Choose the class and the file</h5>
            <form method="POST" action="{{ route('examination.marks.import.preview', $exam->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="ex-form-row">
                    <div>
                        <label class="ex-label" for="impClass">Class</label>
                        <select id="impClass" name="cs" class="ex-input" required>
                            @foreach ($classOptions as $o)
                                <option value="{{ $o['class_id'] }}|{{ $o['stream_id'] }}"
                                    @selected($selected ? ($selected['class_id'] == $o['class_id'] && $selected['stream_id'] == $o['stream_id']) : old('cs') === $o['class_id'] . '|' . $o['stream_id'])>{{ $o['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ex-label" for="impFile">Excel / CSV file</label>
                        <input id="impFile" type="file" name="file" class="ex-input" accept=".xlsx,.xls,.csv">
                    </div>
                    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                        <button type="submit" class="ex-btn ex-btn--primary" @disabled(!$open)><i class="fas fa-magnifying-glass"></i> Preview import</button>
                        <button type="submit" class="ex-btn ex-btn--ghost" formmethod="GET" formnovalidate
                            formaction="{{ route('examination.marks.import.template', $exam->id) }}"><i class="fas fa-download"></i> Template</button>
                    </div>
                </div>
                <p class="ex-help">
                    The <strong>template</strong> lists the class's pupils (with their system ID) and one column per subject, with any marks already entered.
                    You can also upload the school's own marklist: pupil names and subject columns are detected for you and can be corrected in the preview.
                    You only see the classes and subjects assigned to you, the same as on Marks Entry.
                </p>
            </form>
        </div>
        @endif

        {{-- STEP 2 --}}
        @if ($plan)
            @if (!$plan['ok'])
                <div class="ex-alert ex-alert--error">{{ $plan['error'] }}</div>
            @else
                @php $st = $plan['stats']; $total = count($plan['changes']); @endphp

                <div class="ex-card">
                    <h5 class="ex-card-title"><span class="ex-step">2</span> Check the preview</h5>

                    <div class="ex-pills">
                        <span class="ex-pill ex-pill--ok">{{ $st['pupils_matched'] }} pupils matched</span>
                        <span class="ex-pill {{ $st['pupils_unmatched'] ? 'ex-pill--bad' : '' }}">{{ $st['pupils_unmatched'] }} pupils not matched</span>
                        <span class="ex-pill ex-pill--info">{{ $st['new'] }} new marks</span>
                        <span class="ex-pill ex-pill--info">{{ $st['updated'] }} marks changed</span>
                        <span class="ex-pill">{{ $st['same'] }} unchanged</span>
                        @if ($st['kept'])
                            <span class="ex-pill ex-pill--warn">{{ $st['kept'] }} existing marks kept</span>
                        @endif
                        @if ($st['invalid'])
                            <span class="ex-pill ex-pill--bad">{{ $st['invalid'] }} invalid cells</span>
                        @endif
                        <span class="ex-pill">{{ $st['blank'] + $st['absent'] }} blank / absent cells skipped</span>
                    </div>

                    @if (!empty($plan['skipped_subjects']))
                        <div class="ex-alert ex-alert--info">
                            <strong>Not importable here:</strong>
                            @foreach ($plan['skipped_subjects'] as $sk)
                                <span style="display:inline-block;margin-right:1rem;">{{ $sk['name'] }} <span style="opacity:.75">({{ $sk['reason'] }})</span></span>
                            @endforeach
                        </div>
                    @endif

                    {{-- column → subject mapping, sheet and overwrite choice; re-checks the same uploaded file --}}
                    <form method="POST" action="{{ route('examination.marks.import.preview', $exam->id) }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $selected['token'] }}">
                        <input type="hidden" name="class_id" value="{{ $selected['class_id'] }}">
                        <input type="hidden" name="stream_id" value="{{ $selected['stream_id'] }}">

                        <div class="ex-form-row" style="margin-bottom:1rem;">
                            @if (count($selected['sheets']) > 1)
                                <div>
                                    <label class="ex-label" for="impSheet">Sheet of the workbook</label>
                                    <select id="impSheet" name="sheet" class="ex-input">
                                        @foreach ($selected['sheets'] as $i => $n)
                                            <option value="{{ $i }}" @selected($i == $selected['sheet'])>{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="sheet" value="{{ $selected['sheet'] }}">
                            @endif
                            <div>
                                <label style="display:flex;align-items:center;gap:.6rem;font-size:.88rem;cursor:pointer;margin:0;">
                                    <input type="checkbox" name="overwrite" value="1" @checked($selected['overwrite']) style="width:18px;height:18px;">
                                    Replace marks that are already entered (otherwise they are kept)
                                </label>
                            </div>
                        </div>

                        <div class="ex-table-wrap" style="max-height:none;">
                            <table class="ex-table">
                                <thead><tr><th>Column in your sheet</th><th style="min-width:260px">Import it as</th></tr></thead>
                                <tbody>
                                @foreach ($plan['layout']['columns'] as $col => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>
                                            <select name="map[{{ $col }}]" class="ex-input" style="min-height:36px;padding:.3rem .8rem;">
                                                <option value="">— ignore this column —</option>
                                                @foreach ($plan['subjects'] as $sub)
                                                    <option value="{{ $sub['key'] }}" @selected($plan['chosen'][$col] === $sub['key'])>{{ $sub['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @if (in_array($col, $plan['duplicate_cols'], true))
                                                <div class="ex-help" style="color:#92400E;">That subject is already taken by a column further left, so this one is ignored.</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top:1rem;">
                            <button type="submit" class="ex-btn ex-btn--ghost ex-btn--sm"><i class="fas fa-rotate"></i> Update preview</button>
                        </div>
                    </form>
                </div>

                @if ($plan['problems'])
                    <div class="ex-card">
                        <h5 class="ex-card-title" style="color:#991B1B;"><i class="fas fa-triangle-exclamation"></i> Needs attention ({{ count($plan['problems']) }})
                            <small style="font-weight:500;color:#6B7280;">— these rows / cells are skipped</small></h5>
                        <div class="ex-table-wrap">
                            <table class="ex-table">
                                <thead><tr><th>Sheet row</th><th>Pupil</th><th>Problem</th><th>Details</th></tr></thead>
                                <tbody>
                                @foreach (array_slice($plan['problems'], 0, 300) as $p)
                                    <tr><td>{{ $p['line'] }}</td><td>{{ $p['name'] }}</td><td>{{ $p['type'] }}</td><td>{{ $p['detail'] }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="ex-card">
                    <h5 class="ex-card-title"><span class="ex-step">3</span> Import</h5>
                    @if ($total)
                        <div class="ex-table-wrap" style="margin-bottom:1rem;">
                            <table class="ex-table">
                                <thead><tr><th>Pupil</th><th>Subject</th><th class="ex-right">Now</th><th class="ex-right">Will become</th></tr></thead>
                                <tbody>
                                @foreach (array_slice($plan['changes'], 0, 150) as $c)
                                    <tr><td>{{ $c['student'] }}</td><td>{{ $c['subject'] }}</td><td class="ex-right">{{ $fmt($c['old']) }}</td><td class="ex-right"><strong>{{ $fmt($c['new']) }}</strong></td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($total > 150)
                            <p class="ex-help" style="margin-top:0;margin-bottom:1rem;">Showing the first 150 of {{ $total }} changes.</p>
                        @endif

                        <form method="POST" action="{{ route('examination.marks.import.commit', $exam->id) }}" onsubmit="return confirm('Save {{ $total }} mark(s)?');">
                            @csrf
                            <input type="hidden" name="token" value="{{ $selected['token'] }}">
                            <input type="hidden" name="class_id" value="{{ $selected['class_id'] }}">
                            <input type="hidden" name="stream_id" value="{{ $selected['stream_id'] }}">
                            <input type="hidden" name="sheet" value="{{ $selected['sheet'] }}">
                            @if ($selected['overwrite'])
                                <input type="hidden" name="overwrite" value="1">
                            @endif
                            @foreach ($plan['chosen'] as $col => $key)
                                <input type="hidden" name="map[{{ $col }}]" value="{{ $key }}">
                            @endforeach
                            <button type="submit" class="ex-btn ex-btn--success" @disabled(!$open)>
                                <i class="fas fa-check"></i> Import {{ $total }} mark{{ $total === 1 ? '' : 's' }}
                            </button>
                        </form>
                    @else
                        <p class="ex-help" style="margin:0;">There are no new or changed marks to import with the current choices.</p>
                    @endif
                </div>
            @endif
        @endif
    </div></div></div></div>
@endsection
