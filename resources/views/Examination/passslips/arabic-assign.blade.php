{{-- resources/views/Examination/passslips/arabic-assign.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        .ar-wrap { display: grid; grid-template-columns: 400px 1fr; gap: 18px; align-items: start; }
        @media (max-width: 1100px) { .ar-wrap { grid-template-columns: 1fr; } }
        .ar-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px; box-shadow: 0 1px 3px rgba(16, 24, 40, .05); }
        .ar-panel h6 { font-weight: 800; margin: 0 0 10px; text-transform: uppercase; font-size: 11px; color: #64748b; letter-spacing: .6px; }
        .ar-badge { display: inline-block; background: #e0e7ff; color: #3730a3; border-radius: 999px; padding: 2px 10px; font-size: 12px; font-weight: 700; }
        .ar-row { display: flex; align-items: center; gap: 10px; padding: 9px 4px; border-bottom: 1px solid #f1f5f9; }
        .ar-row:last-child { border-bottom: 0; }
        .ar-row label { margin: 0; flex: 1; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; align-items: center; gap: 9px; }
        .ar-row input[type=checkbox] { width: 18px; height: 18px; accent-color: #2C29CA; }
        .ar-row select { width: 112px; border: 1px solid #d5d8ee; border-radius: 8px; padding: 4px 6px; font-size: 13px; background: #fff; }
        .ar-state { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; white-space: nowrap; }
        .ar-state.on { background: #dcfce7; color: #166534; }
        .ar-state.other { background: #fef3c7; color: #92400e; }
        .ar-tools { display: flex; gap: 8px; margin-bottom: 8px; }
        .ar-tools button { border: 1px solid #c7c9f5; background: #fff; color: #2C29CA; border-radius: 99px; font-size: 12px; font-weight: 700; padding: 3px 12px; cursor: pointer; }
        .ar-opt { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #334155; }
        .ar-opt:last-of-type { border-bottom: 0; }
        .ar-opt input[type=checkbox] { width: 18px; height: 18px; accent-color: #2C29CA; }
        .ar-opt input[type=color] { width: 44px; height: 30px; border: 1px solid #d5d8ee; border-radius: 8px; padding: 2px; background: #fff; }
        .ar-save { width: 100%; margin-top: 14px; border: 0; border-radius: 10px; padding: 11px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #2C29CA, #6d28d9); cursor: pointer; }
        .ar-prev-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 12px; }
        .ar-prev-bar select { border: 1px solid #d5d8ee; border-radius: 8px; padding: 6px 10px; min-width: 200px; }
        .ar-seg { display: inline-flex; background: #eef0ff; border-radius: 9px; padding: 3px; gap: 2px; }
        .ar-seg button { border: 0; background: transparent; padding: 5px 14px; border-radius: 7px; font-weight: 700; font-size: 13px; color: #475569; cursor: pointer; }
        .ar-seg button.active { background: #2C29CA; color: #fff; }
        #arFrame { width: 100%; height: 1240px; border: 1px solid #e5e7eb; border-radius: 12px; background: #dde1e7; }
        .ar-hint { font-size: 12.5px; color: #64748b; margin-top: 10px; line-height: 1.5; }
    </style>
@endsection

@section('content')
    @php $previewUrl = route('examination.passslips.preview', $exam->id); @endphp
    <div class="container-fluid pt-4">
        <a href="{{ route('examination.passslips.index', $exam->id) }}" class="btn btn-sm btn-outline-secondary mb-3">← Back to Report Cards</a>
        <h3 class="mb-1">Arabic Report Card</h3>
        <p class="text-muted">
            <span class="ar-badge">{{ $exam->exam_name }}</span>
            &nbsp;Tick the classes that should print the Arabic report card. They print it automatically from
            Report Cards (one student, a class, or all classes) — no separate button.
        </p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        @if($classes->isEmpty())
            <div class="alert alert-warning">This examination has no classes yet.</div>
        @else
            <div class="ar-wrap">
                <form class="ar-panel" method="POST" action="{{ route('examination.passslips.arabic.save', $exam->id) }}">
                    @csrf
                    @foreach($classes as $c)
                        <input type="hidden" name="all_class_ids[]" value="{{ $c->class_id }}">
                    @endforeach

                    <h6>Classes</h6>
                    <div class="ar-tools">
                        <button type="button" onclick="arTick(true)">Select all</button>
                        <button type="button" onclick="arTick(false)">Clear</button>
                    </div>
                    @foreach($classes as $c)
                        <div class="ar-row">
                            <label>
                                <input type="checkbox" name="class_ids[]" value="{{ $c->class_id }}" class="ar-cls" {{ $c->assigned ? 'checked' : '' }}>
                                <span>{{ $c->name }}</span>
                            </label>
                            @if($c->active)
                                <span class="ar-state on">Active</span>
                            @elseif($c->assigned)
                                <span class="ar-state other" title="Another design was saved for this class after the Arabic one. Save here to use Arabic again.">Other design active</span>
                            @endif
                            <select name="langs[{{ $c->class_id }}]" title="Language the card opens in">
                                <option value="ar" {{ $c->lang === 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="en" {{ $c->lang === 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                    @endforeach

                    <h6 class="mt-4">Look</h6>
                    <div class="ar-opt"><span>Accent colour</span><input type="color" name="accent" id="arAccent" value="{{ $shared['accent'] ?? '#2f63b8' }}"></div>
                    <div class="ar-opt"><span>Student photo</span><input type="checkbox" name="show_photo" value="1" id="arPhoto" {{ ($shared['show_photo'] ?? true) ? 'checked' : '' }}></div>
                    <div class="ar-opt"><span>School stamp</span><input type="checkbox" name="show_stamp" value="1" id="arStamp" {{ ($shared['show_stamp'] ?? true) ? 'checked' : '' }}></div>
                    <div class="ar-opt"><span>Notes for parents</span><input type="checkbox" name="show_notes" value="1" id="arNotes" {{ ($shared['show_notes'] ?? true) ? 'checked' : '' }}></div>
                    <div class="ar-opt"><span>Arabic digits (٠١٢٣) in Arabic</span><input type="checkbox" name="arabic_digits" value="1" id="arDigits" {{ ($shared['arabic_digits'] ?? true) ? 'checked' : '' }}></div>

                    <button type="submit" class="ar-save">Save Arabic report card for the ticked classes</button>
                    <div class="ar-hint">
                        Un-ticking a class only removes the Arabic design for it; whatever design it had before
                        (Classic, Modern, ...) is used again. The language you pick here is only the starting
                        language — it can be switched between Arabic and English on the card itself.
                    </div>
                </form>

                <div class="ar-panel">
                    <h6>Preview</h6>
                    <div class="ar-prev-bar">
                        <select id="arPrevClass">
                            @foreach($classes as $c)
                                <option value="{{ $c->class_id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="ar-seg" id="arLangSeg">
                            <button type="button" data-lang="ar" class="active">العربية</button>
                            <button type="button" data-lang="en">English</button>
                        </div>
                    </div>
                    <iframe id="arFrame" title="Arabic report card preview"></iframe>
                </div>
            </div>
        @endif
    </div> </div> </div> </div>

    <script>
        (function () {
            var PREVIEW = @json($previewUrl);
            var lang = 'ar';
            var frame = document.getElementById('arFrame');
            if (!frame) { return; }

            window.arTick = function (on) {
                document.querySelectorAll('.ar-cls').forEach(function (c) { c.checked = on; });
            };

            function flag(id) { return document.getElementById(id).checked ? '1' : '0'; }

            function refresh() {
                var q = new URLSearchParams({
                    template: 'arabic-classic',
                    embed: '1',
                    class_id: document.getElementById('arPrevClass').value,
                    card_lang: lang,
                    accent: document.getElementById('arAccent').value,
                    show_photo: flag('arPhoto'),
                    show_stamp: flag('arStamp'),
                    show_notes: flag('arNotes'),
                    arabic_digits: flag('arDigits')
                });
                frame.src = PREVIEW + '?' + q.toString();
            }

            document.getElementById('arLangSeg').addEventListener('click', function (e) {
                var b = e.target.closest('button[data-lang]');
                if (!b) { return; }
                lang = b.getAttribute('data-lang');
                this.querySelectorAll('button').forEach(function (x) { x.classList.toggle('active', x === b); });
                refresh();
            });
            ['arPrevClass', 'arAccent', 'arPhoto', 'arStamp', 'arNotes', 'arDigits'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', refresh);
            });

            refresh();
        })();
    </script>
@endsection
