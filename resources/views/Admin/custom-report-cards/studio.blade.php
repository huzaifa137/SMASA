@extends('layouts-side-bar.master')

@section('content')
<div class="container-fluid pt-4">
    <h3 class="mb-1">Custom Report Cards — Preview studio</h3>
    <p class="text-muted mb-3">See any design with a school's real exams and students. Edit the Blade file, then press Refresh.</p>

    @include('Admin.custom-report-cards._nav')

    @if($ready)
    <div class="crc-card">
        <div class="row">
            <div class="col-md-3"><label class="small font-weight-bold">Design</label>
                <select id="sDesign" class="form-control">
                    @foreach($templates as $t)
                        <option value="{{ $t->slug }}" data-level="{{ $t->level }}" {{ $preselectSlug === $t->slug ? 'selected' : '' }}>{{ $t->name }} ({{ $t->level }})</option>
                    @endforeach
                </select></div>
            <div class="col-md-3"><label class="small font-weight-bold">School</label>
                <select id="sSchool" class="form-control"><option value="">Choose…</option>
                    @foreach($schools as $s)<option value="{{ $s->id }}" {{ (string)$preselectSchool === (string)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select></div>
            <div class="col-md-3"><label class="small font-weight-bold">Examination</label>
                <select id="sExam" class="form-control" disabled><option value="">Choose a school first</option></select></div>
            <div class="col-md-3"><label class="small font-weight-bold">Student</label>
                <select id="sStudent" class="form-control" disabled><option value="">Choose an exam first</option></select></div>
        </div>
        <div class="mt-3">
            <button id="btnRefresh" class="btn btn-primary" disabled>Refresh preview</button>
            <a id="btnOpen" class="btn btn-outline-secondary disabled" target="_blank" href="#">Open full size</a>
        </div>
    </div>
    <div class="crc-card p-0" style="overflow:hidden">
        <iframe id="frame" style="width:100%;height:1250px;border:0;background:#f1f5f9" title="Preview"></iframe>
    </div>
    @endif
</div>
@endsection

@section('js')
<script>
(function () {
    const $ = id => document.getElementById(id);
    const urls = { exams: @json(route('admin.custom-report-cards.studio.exams')), students: @json(route('admin.custom-report-cards.studio.students')), preview: @json(route('admin.custom-report-cards.studio.preview')) };
    const fill = (sel, items, empty) => {
        sel.innerHTML = '<option value="">' + empty + '</option>' + items.map(i => `<option value="${i.id}">${String(i.label).replace(/</g,'&lt;')}</option>`).join('');
        sel.disabled = items.length === 0;
    };
    const level = () => $('sDesign').selectedOptions[0]?.dataset.level || '';
    const previewUrl = () => urls.preview + '?' + new URLSearchParams({ slug: $('sDesign').value, school_id: $('sSchool').value, exam_id: $('sExam').value, student_id: $('sStudent').value });

    async function loadExams() {
        fill($('sExam'), [], 'Choose a school first'); fill($('sStudent'), [], 'Choose an exam first');
        if (!$('sSchool').value) return;
        const r = await (await fetch(urls.exams + '?school_id=' + $('sSchool').value)).json();
        fill($('sExam'), r.exams, r.exams.length ? 'Choose…' : 'No exams for this school');
    }
    async function loadStudents() {
        fill($('sStudent'), [], 'Choose an exam first');
        if (!$('sExam').value) return;
        const q = new URLSearchParams({ school_id: $('sSchool').value, exam_id: $('sExam').value, level: level() });
        const r = await (await fetch(urls.students + '?' + q)).json();
        fill($('sStudent'), r.students, r.students.length ? 'Choose…' : 'No students with marks at this level');
    }
    function refresh() {
        const ok = $('sDesign').value && $('sSchool').value && $('sExam').value && $('sStudent').value;
        $('btnRefresh').disabled = !ok; $('btnOpen').classList.toggle('disabled', !ok);
        if (ok) { $('frame').src = previewUrl(); $('btnOpen').href = previewUrl(); }
    }
    $('sSchool').addEventListener('change', loadExams);
    $('sExam').addEventListener('change', loadStudents);
    $('sDesign').addEventListener('change', loadStudents);
    $('sStudent').addEventListener('change', refresh);
    $('btnRefresh').addEventListener('click', refresh);
    if ($('sSchool').value) loadExams();
})();
</script>
@endsection
