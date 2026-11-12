@extends('layouts-side-bar.master')

@section('content')
    @php
        $open = in_array($exam->status, ['marks_entry', 'active'], true);
        $fmt = fn($v) => $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    @endphp

    <div class="container-fluid py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">
            <div>
                <h4 class="mb-0">Import marks from Excel</h4>
                <div class="text-muted">{{ $exam->exam_name }} · {{ $exam->term }} {{ $exam->academic_year }}</div>
            </div>
            <a href="{{ route('examination.marks.entry', $exam->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Marks Entry
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        @if (!$open)
            <div class="alert alert-warning">
                Marks entry is <strong>{{ $exam->statusLabel() }}</strong> for this examination, so marks cannot be imported.
                Open marks entry for the examination first.
            </div>
        @endif

        {{-- STEP 1 --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">1 · Choose the class and the file</h6>
                <form method="POST" action="{{ route('examination.marks.import.preview', $exam->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Class</label>
                            <select name="cs" class="form-select" required>
                                @foreach ($classOptions as $o)
                                    <option value="{{ $o['class_id'] }}|{{ $o['stream_id'] }}"
                                        @selected($selected && $selected['class_id'] == $o['class_id'] && $selected['stream_id'] == $o['stream_id'])>
                                        {{ $o['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Excel / CSV file</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv">
                        </div>
                        <div class="col-md-3 d-flex flex-wrap" style="gap:8px;">
                            <button type="submit" class="btn btn-primary" @disabled(!$open)>
                                <i class="fas fa-search me-1"></i> Preview import
                            </button>
                            <button type="submit" class="btn btn-outline-secondary" formmethod="GET" formnovalidate
                                formaction="{{ route('examination.marks.import.template', $exam->id) }}">
                                <i class="fas fa-download me-1"></i> Template
                            </button>
                        </div>
                    </div>
                    <div class="form-text mt-2">
                        The <strong>template</strong> lists the class's pupils (with their system ID) and one column per subject, with any marks already
                        entered. You can also upload the school's own marklist: the pupil names and the subject columns are detected for you, and you can
                        correct the subject columns in the preview. Nothing is saved until you confirm.
                    </div>
                </form>
            </div>
        </div>

        {{-- STEP 2 --}}
        @if ($plan)
            @if (!$plan['ok'])
                <div class="alert alert-danger">{{ $plan['error'] }}</div>
            @else
                @php $st = $plan['stats']; $total = count($plan['changes']); @endphp

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3">2 · Check the preview</h6>

                        <div class="d-flex flex-wrap mb-3" style="gap:10px;">
                            <span class="badge bg-success fs-6">{{ $st['pupils_matched'] }} pupils matched</span>
                            <span class="badge {{ $st['pupils_unmatched'] ? 'bg-danger' : 'bg-secondary' }} fs-6">{{ $st['pupils_unmatched'] }} pupils not matched</span>
                            <span class="badge bg-primary fs-6">{{ $st['new'] }} new marks</span>
                            <span class="badge bg-info text-dark fs-6">{{ $st['updated'] }} marks changed</span>
                            <span class="badge bg-secondary fs-6">{{ $st['same'] }} unchanged</span>
                            @if ($st['kept'])
                                <span class="badge bg-warning text-dark fs-6">{{ $st['kept'] }} existing marks kept</span>
                            @endif
                            @if ($st['invalid'])
                                <span class="badge bg-danger fs-6">{{ $st['invalid'] }} invalid cells</span>
                            @endif
                            <span class="badge bg-light text-dark border fs-6">{{ $st['blank'] + $st['absent'] }} blank / absent cells skipped</span>
                        </div>

                        @if (!empty($plan['skipped_subjects']))
                            <div class="alert alert-light border">
                                <strong>Not importable here:</strong>
                                @foreach ($plan['skipped_subjects'] as $sk)
                                    <span class="d-inline-block me-3">{{ $sk['name'] }} <span class="text-muted">({{ $sk['reason'] }})</span></span>
                                @endforeach
                            </div>
                        @endif

                        {{-- column → subject mapping, sheet and overwrite choice; re-checks the same uploaded file --}}
                        <form method="POST" action="{{ route('examination.marks.import.preview', $exam->id) }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $selected['token'] }}">
                            <input type="hidden" name="class_id" value="{{ $selected['class_id'] }}">
                            <input type="hidden" name="stream_id" value="{{ $selected['stream_id'] }}">

                            <div class="row g-3 mb-2">
                                @if (count($selected['sheets']) > 1)
                                    <div class="col-md-4">
                                        <label class="form-label">Sheet of the workbook</label>
                                        <select name="sheet" class="form-select">
                                            @foreach ($selected['sheets'] as $i => $n)
                                                <option value="{{ $i }}" @selected($i == $selected['sheet'])>{{ $n }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="sheet" value="{{ $selected['sheet'] }}">
                                @endif
                                <div class="col-md-5 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="ow" @checked($selected['overwrite'])>
                                        <label class="form-check-label" for="ow">Replace marks that are already entered (otherwise they are kept)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>Column in your sheet</th><th style="min-width:260px">Import it as</th></tr></thead>
                                    <tbody>
                                    @foreach ($plan['layout']['columns'] as $col => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td>
                                                <select name="map[{{ $col }}]" class="form-select form-select-sm">
                                                    <option value="">— ignore this column —</option>
                                                    @foreach ($plan['subjects'] as $sub)
                                                        <option value="{{ $sub['key'] }}" @selected($plan['chosen'][$col] === $sub['key'])>{{ $sub['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @if (in_array($col, $plan['duplicate_cols'], true))
                                                    <div class="form-text text-warning">That subject is already taken by a column further left, so this one is ignored.</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync me-1"></i> Update preview</button>
                        </form>
                    </div>
                </div>

                @if ($plan['problems'])
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-danger">Needs attention ({{ count($plan['problems']) }}) <small class="text-muted">— these rows/cells are skipped</small></h6>
                            <div class="table-responsive" style="max-height:320px; overflow:auto;">
                                <table class="table table-sm table-striped">
                                    <thead><tr><th>Sheet row</th><th>Pupil</th><th>Problem</th><th>Details</th></tr></thead>
                                    <tbody>
                                    @foreach (array_slice($plan['problems'], 0, 300) as $p)
                                        <tr><td>{{ $p['line'] }}</td><td>{{ $p['name'] }}</td><td>{{ $p['type'] }}</td><td>{{ $p['detail'] }}</td></tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="mb-3">3 · Import</h6>
                        @if ($total)
                            <div class="table-responsive mb-3" style="max-height:320px; overflow:auto;">
                                <table class="table table-sm table-striped">
                                    <thead><tr><th>Pupil</th><th>Subject</th><th class="text-end">Now</th><th class="text-end">Will become</th></tr></thead>
                                    <tbody>
                                    @foreach (array_slice($plan['changes'], 0, 150) as $c)
                                        <tr><td>{{ $c['student'] }}</td><td>{{ $c['subject'] }}</td><td class="text-end">{{ $fmt($c['old']) }}</td><td class="text-end fw-semibold">{{ $fmt($c['new']) }}</td></tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($total > 150)
                                <div class="text-muted mb-3">Showing the first 150 of {{ $total }} changes.</div>
                            @endif

                            <form method="POST" action="{{ route('examination.marks.import.commit', $exam->id) }}"
                                onsubmit="return confirm('Save {{ $total }} mark(s)?');">
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
                                <button type="submit" class="btn btn-success" @disabled(!$open)>
                                    <i class="fas fa-check me-1"></i> Import {{ $total }} mark{{ $total === 1 ? '' : 's' }}
                                </button>
                            </form>
                        @else
                            <div class="text-muted">There are no new or changed marks to import with the current choices.</div>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
