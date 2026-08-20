{{--
    SECTION BUILDER — replaces the old blank Fabric.js canvas.

    You never start empty here: $template->elements already comes seeded
    from one of the Classic / Modern / Minimal starters (see
    ReportCardTemplateSeeder + report-cards/index.blade.php "Use as
    starting point"). Editing means:
      - drag a section's right edge (or use the width buttons) to resize
        it in grid units — col-md-3, col-md-6, col-md-12, etc.
      - drag a section by its handle to reorder it, up/down within its
        row or into another row
      - add a section from the palette if you want something extra
    There is no free-form x/y canvas any more — every section always
    belongs to a row and a width, exactly like render.blade.php renders it.
--}}
@extends('layouts-side-bar.master')

@section('css')
    <style>
        body { overflow-x: hidden; }

        #rc-builder {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr) 300px;
            gap: 0;
            background: #eef0f4;
        }

        @media (max-width: 1200px) {
            #rc-builder { grid-template-columns: 200px minmax(0, 1fr) 260px; }
        }

        @media (max-width: 900px) {
            #rc-builder { grid-template-columns: 1fr; grid-template-rows: auto auto auto; height: auto !important; }
            .rc-palette, .rc-props { max-height: 40vh; }
            .rc-canvas-wrap { min-height: 50vh; }
        }

        #rc-js-error-banner {
            display: none; position: sticky; top: 0; z-index: 50;
            background: #fdecec; color: #9c1c1c; border-bottom: 1px solid #f3c2c2;
            padding: .6rem 1rem; font-size: .82rem; font-family: monospace; white-space: pre-wrap;
        }

        .rc-palette, .rc-props { background: #fff; border-right: 1px solid #e5e7f2; overflow-y: auto; padding: 1rem; }
        .rc-props { border-right: none; border-left: 1px solid #e5e7f2; }
        .rc-palette h3, .rc-props h3 {
            font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: #6c7293; margin: 1rem 0 .6rem;
        }
        .rc-palette h3:first-child { margin-top: 0; }
        .rc-palette p.rc-hint { font-size: .78rem; color: #8a8fa8; margin: 0 0 1rem; line-height: 1.5; }

        .rc-palette-list button {
            display: block; width: 100%; text-align: left; font-size: .78rem; padding: .55rem .6rem;
            border: 1px solid #e2e4f3; border-radius: 8px; background: #fafbff; cursor: pointer;
            color: #333349; margin-bottom: .4rem; transition: all .12s;
        }
        .rc-palette-list button:hover { background: #5351e4; color: #fff; border-color: #5351e4; }

        /* min-width: 0 is the actual fix here — grid items default to
           min-width: auto, which stops this middle track shrinking below
           the fixed-width .rc-sheet (794px) inside it. Without it, the
           whole #rc-builder grid overflows past the viewport and pushes
           the right-hand .rc-props column off-screen instead of the
           canvas simply scrolling within its own column. */
        .rc-stage { display: flex; flex-direction: column; height: 100%; min-height: 0; min-width: 0; }

        .rc-toolbar {
            display: flex; align-items: center; gap: .75rem; padding: .6rem 1rem;
            background: #fff; border-bottom: 1px solid #e5e7f2;
        }
        .rc-toolbar #rc-template-name {
            font-size: .95rem; font-weight: 700; border: 1px solid transparent; border-radius: 6px;
            padding: .3rem .5rem; flex: 1; max-width: 320px; color: #1e1e2d;
        }
        .rc-toolbar #rc-template-name:hover, .rc-toolbar #rc-template-name:focus {
            border-color: #dcdfef; outline: none; background: #fafbff;
        }
        .rc-save-status { font-size: .75rem; color: #8a8fa8; margin-right: auto; }
        .rc-toolbar button {
            font-size: .82rem; font-weight: 600; padding: .5rem .9rem; border-radius: 8px;
            border: 1px solid #dcdfef; background: #fff; color: #4a4a68; cursor: pointer;
        }
        .rc-toolbar button:hover { background: #f4f5fa; }
        .rc-toolbar button.primary { background: #5351e4; border-color: #5351e4; color: #fff; }
        .rc-toolbar button.primary:hover { background: #423fc9; }

        .rc-canvas-wrap { flex: 1; overflow: auto; padding: 2rem; }
        .rc-sheet {
            width: 794px; min-height: 400px; margin: 0 auto; background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.15); padding: 40px; box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .rc-row {
            width: 100%; overflow: hidden; margin-bottom: 12px; padding: 6px;
            border: 1px dashed transparent; border-radius: 6px; transition: border-color .12s, background .12s;
        }
        .rc-row.rc-row-dragover { border-color: #5351e4; background: #f5f5ff; }
        .rc-row-controls {
            display: flex; align-items: center; gap: .3rem; margin-bottom: 4px; opacity: 0; transition: opacity .12s;
        }
        .rc-row:hover .rc-row-controls { opacity: 1; }
        .rc-row-controls span { font-size: .68rem; color: #a5a8c0; text-transform: uppercase; letter-spacing: .04em; margin-right: auto; }
        .rc-row-controls button {
            font-size: .7rem; padding: .15rem .4rem; border: 1px solid #e2e4f3; border-radius: 4px;
            background: #fafbff; cursor: pointer; color: #4a4a68;
        }
        .rc-row-controls button:hover { background: #eef0fb; }

        .rc-col {
            float: left; box-sizing: border-box; padding: 0 6px; position: relative; cursor: pointer;
        }
        .rc-col-inner {
            position: relative; border: 1.5px solid #e5e7f2; border-radius: 6px; background: #fafbff;
            padding: 8px 10px; min-height: 44px; overflow: hidden;
        }
        .rc-col.selected .rc-col-inner { border-color: #5351e4; box-shadow: 0 0 0 2px rgba(83,81,228,.15); background: #f6f6ff; }
        .rc-col-inner:hover { border-color: #b7bbea; }
        .rc-col-badge {
            font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
            color: #8a8fa8; margin-bottom: 3px; display: flex; align-items: center; justify-content: space-between;
        }
        .rc-col-badge .rc-col-width { color: #5351e4; }
        .rc-col-preview { font-size: .78rem; color: #333349; line-height: 1.35; }
        .rc-col-preview table { width: 100%; border-collapse: collapse; font-size: .68rem; }
        .rc-col-preview table th, .rc-col-preview table td { border: 1px solid #ddd; padding: 2px 4px; }

        .rc-col-drag {
            position: absolute; top: 2px; left: 2px; font-size: .8rem; color: #b5b8cc; cursor: grab;
            padding: 2px 4px; line-height: 1; user-select: none;
        }
        .rc-col-del {
            position: absolute; top: 2px; right: 2px; font-size: .72rem; color: #c9ccdc; cursor: pointer;
            border: none; background: none; padding: 2px 4px; line-height: 1;
        }
        .rc-col-del:hover { color: #d64545; }
        .rc-col-resize {
            position: absolute; top: 0; right: -4px; width: 9px; height: 100%; cursor: ew-resize; z-index: 5;
        }
        .rc-col-resize::after {
            content: ''; position: absolute; top: 50%; right: 3px; width: 3px; height: 20px;
            background: #d7d9ee; border-radius: 2px; transform: translateY(-50%);
        }
        .rc-col:hover .rc-col-resize::after, .rc-col.selected .rc-col-resize::after { background: #5351e4; }

        .rc-add-row-btn {
            display: block; width: 100%; text-align: center; padding: .6rem; margin-top: .5rem;
            border: 1px dashed #c8cdf0; border-radius: 8px; background: #fafbff; color: #5351e4;
            font-size: .8rem; font-weight: 600; cursor: pointer;
        }
        .rc-add-row-btn:hover { background: #eef0fb; }

        .rc-props label {
            display: block; font-size: .78rem; font-weight: 600; color: #4a4a68; margin-bottom: .9rem;
        }
        .rc-props label input[type=text], .rc-props label input[type=number],
        .rc-props label textarea, .rc-props label select {
            display: block; width: 100%; margin-top: .3rem; padding: .45rem .55rem;
            border: 1px solid #dcdfef; border-radius: 6px; font-size: .82rem; font-weight: 400;
        }
        .rc-props label input[type=color] { width: 100%; height: 34px; margin-top: .3rem; border: 1px solid #dcdfef; border-radius: 6px; }
        .rc-props label input[type=checkbox] { margin-left: .5rem; }
        .rc-props .rc-prop-row { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
        .rc-props .rc-empty-hint { color: #8a8fa8; font-size: .85rem; margin-top: 2rem; text-align: center; }
        .rc-props .rc-type-badge {
            display: inline-block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: #5351e4; background: #eef0fb; padding: .2rem .5rem; border-radius: 20px; margin-bottom: .8rem;
        }
        .rc-width-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: .3rem; margin-top: .3rem; }
        .rc-width-grid button {
            font-size: .72rem; padding: .35rem 0; border: 1px solid #dcdfef; border-radius: 6px;
            background: #fafbff; cursor: pointer; color: #4a4a68;
        }
        .rc-width-grid button.active { background: #5351e4; border-color: #5351e4; color: #fff; }
        .rc-el-actions { display: flex; gap: .4rem; margin: 0 0 1.25rem; }
        .rc-el-actions button {
            flex: 1; font-size: .76rem; font-weight: 600; padding: .45rem 0; border-radius: 6px;
            border: 1px solid #dcdfef; background: #fafbff; cursor: pointer;
        }
        .rc-el-actions button:hover { background: #eef0fb; }
        .rc-el-actions button.rc-delete-btn { color: #d64545; }
        .rc-el-actions button.rc-delete-btn:hover { background: #fdf1f1; }
    </style>
@endsection

@section('content')
    <div id="rc-js-error-banner"></div>
    <div id="rc-builder" style="margin-top:2rem;">

        {{-- LEFT: add-section palette --}}
        <aside class="rc-palette">
            <h3>Add a section</h3>
            <p class="rc-hint">This design already starts from the {{ $template->name }} template. Add a section only if you need something extra — resize and reorder what's already here by dragging.</p>
            <div class="rc-palette-list">
                <button data-add="text">🔤 Text</button>
                <button data-add="logo" data-slot="logo_primary">🖼 Logo (Left)</button>
                <button data-add="logo" data-slot="logo_secondary">🖼 Logo (Right)</button>
                <button data-add="student_field" data-field="name">👤 Student Name</button>
                <button data-add="student_field" data-field="admission_no">🔢 Admission No.</button>
                <button data-add="student_field" data-field="class">🏫 Class</button>
                <button data-add="student_photo">🖼 Student Photo</button>
                <button data-add="subjects_table">📊 Subjects Table</button>
                <button data-add="grading_key">🗝 Grading Key</button>
                <button data-add="remarks" data-role="class_teacher">📝 Teacher Remarks</button>
                <button data-add="remarks" data-role="head_teacher">📝 Head Remarks</button>
                <button data-add="signature">✍️ Signature Line</button>
                <button data-add="attendance">📅 Attendance</button>
                <button data-add="divider">➖ Divider</button>
                <button data-add="shape">▭ Shape / Accent Band</button>
                <button data-add="watermark">💧 Watermark</button>
                <button data-add="qr_code">▦ QR Code</button>
            </div>

            <h3>Page background</h3>
            <label>Color <input type="color" id="rc-bg-color" value="{{ $template->background['color'] ?? '#FFFFFF' }}"></label>
        </aside>

        {{-- CENTER: the sections, in rows --}}
        <main class="rc-stage">
            <div class="rc-toolbar">
                <input id="rc-template-name" value="{{ $template->name }}" />
                <span id="rc-save-status" class="rc-save-status">Saved</span>
                <button id="rc-preview-btn">Preview with sample data</button>
                <button id="rc-publish-btn" class="primary">Publish</button>
            </div>
            <div class="rc-canvas-wrap">
                <div class="rc-sheet" id="rc-sheet"></div>
                <button class="rc-add-row-btn" id="rc-add-row-btn" style="max-width:794px; margin:.5rem auto 0;">+ Add row</button>
            </div>
        </main>

        {{-- RIGHT: properties panel for the selected section --}}
        <aside class="rc-props" id="rc-props">
            <p class="rc-empty-hint">Select a section to resize, reorder, or edit it — or add one from the left.</p>
        </aside>
    </div></div></div></div>
@endsection

@section('js')
    <script>
        function rcShowError(msg) {
            var banner = document.getElementById('rc-js-error-banner');
            banner.style.display = 'block';
            banner.textContent = '⚠ Report card builder error — open DevTools console for full details.\n' + msg;
            console.error('[Report Card Builder]', msg);
        }
        // The builder used to lock #rc-builder to a fixed pixel height
        // measured once from the viewport top. That math goes stale the
        // moment the page scrolls under a sticky/fixed top bar, which is
        // what was rendering content underneath the nav. Letting the
        // builder flow with the page (like every other screen in this
        // app) instead of fighting its scroll removes that bug entirely.

        // Scale the 794px sheet down (via CSS zoom, which — unlike
        // transform: scale() — actually shrinks the box for layout
        // purposes) so narrower screens never need a horizontal
        // scrollbar just to see the whole page.
        function rcFitSheetWidth() {
            var wrap = document.querySelector('.rc-canvas-wrap');
            var sheet = document.getElementById('rc-sheet');
            if (!wrap || !sheet) return;
            var wrapStyles = getComputedStyle(wrap);
            var horizontalPadding = parseFloat(wrapStyles.paddingLeft) + parseFloat(wrapStyles.paddingRight);
            var available = wrap.clientWidth - horizontalPadding;
            var scale = Math.max(0.3, Math.min(1, available / 794));
            sheet.style.zoom = scale;
        }
        window.addEventListener('resize', rcFitSheetWidth);
        document.addEventListener('DOMContentLoaded', rcFitSheetWidth);
        rcFitSheetWidth();
        window.addEventListener('error', function (e) {
            rcShowError((e.error && e.error.stack) ? e.error.stack : e.message);
        });

        try {
            (function () {
                const SAVE_URL = "{{ route('report-templates.autosave', $template) }}";
                const PUBLISH_URL = "{{ route('report-templates.publish', $template) }}";
                const PREVIEW_URL = "{{ route('report-templates.preview', $template) }}";
                const CSRF = "{{ csrf_token() }}";
                const WIDTHS = [2, 3, 4, 6, 8, 9, 12];

                // Every element already has {id, type, row, width, props} —
                // seeded from the Classic/Modern/Minimal starter this
                // template was duplicated from. Legacy free-canvas elements
                // (x/y, no row/width) are migrated on load so old data
                // doesn't crash the new editor.
                let elements = @json($template->elements ?: []);
                elements = elements.map((el, i) => ({
                    id: el.id || ('el-' + i),
                    type: el.type,
                    row: el.row ?? i,
                    width: el.width ?? 12,
                    props: el.props || {},
                }));
                let selectedId = elements[0]?.id ?? null;
                let dragId = null;

                const TYPE_LABELS = {
                    logo: 'Logo', text: 'Text', student_field: 'Student Field', student_photo: 'Student Photo',
                    subjects_table: 'Subjects Table', grading_key: 'Grading Key', remarks: 'Remarks', signature: 'Signature',
                    attendance: 'Attendance', divider: 'Divider', shape: 'Shape / Band', watermark: 'Watermark', qr_code: 'QR Code',
                };

                function rows() {
                    const byRow = {};
                    elements.forEach(el => { (byRow[el.row] = byRow[el.row] || []).push(el); });
                    return Object.keys(byRow).map(Number).sort((a, b) => a - b).map(r => ({ row: r, els: byRow[r] }));
                }

                function nextRowNumber() {
                    return elements.length ? Math.max(...elements.map(e => e.row)) + 1 : 0;
                }

                // ---- lightweight, per-type preview markup (right panel + "Preview with sample data" gives full fidelity) ----
                function colPreview(el) {
                    const p = el.props || {};
                    switch (el.type) {
                        case 'text': return `<div style="font-size:${Math.min(p.fontSize||14,20)}px;font-weight:${p.fontWeight||400};color:${p.color||'#111'};">${(p.content||'Text').replace(/<[^>]+>/g,' ')}</div>`;
                        case 'logo': return `<div style="height:${Math.min(p.height||60,60)}px;display:flex;align-items:center;justify-content:center;background:#eef2ff;border:1px dashed #c7d2fe;border-radius:6px;color:#4338ca;font-size:.68rem;">Logo${p.slot==='logo_secondary'?' (R)':''}</div>`;
                        case 'student_photo': return `<div style="height:60px;display:flex;align-items:center;justify-content:center;background:#eef2ff;border:1px dashed #c7d2fe;border-radius:6px;color:#4338ca;font-size:.68rem;">Student Photo</div>`;
                        case 'student_field': return `<div><strong>${p.label||p.field||''}:</strong> Sample</div>`;
                        case 'subjects_table': {
                            const cols = p.columns || ['name','score','grade','remark'];
                            return `<table><thead><tr>${cols.map(c=>`<th>${c}</th>`).join('')}</tr></thead><tbody><tr>${cols.map(()=>`<td>—</td>`).join('')}</tr></tbody></table>`;
                        }
                        case 'grading_key': return `<div style="color:#666;">A: 80–100 &nbsp; B: 65–79 &nbsp; C: 50–64</div>`;
                        case 'remarks': return `<div><strong style="text-transform:capitalize;">${(p.role||'').replace('_',' ')} remarks:</strong> <span style="color:#888;">…</span></div>`;
                        case 'signature': return `<div style="padding-top:16px;"><div style="border-top:1px solid #333;margin-bottom:2px;"></div><span style="font-size:.7rem;color:#555;">${p.label||'Signature'}</span></div>`;
                        case 'attendance': return `<div>Present: — &nbsp; Absent: —</div>`;
                        case 'divider': return `<div style="height:${p.thickness||2}px;background:${p.color||'#ccc'};"></div>`;
                        case 'shape': return `<div style="height:${Math.min(p.height||20,30)}px;background:${p.fill||'#e5e7f2'};border-radius:${p.borderRadius||0}px;"></div>`;
                        case 'watermark': return `<div style="color:#bbb;font-size:.7rem;">Watermark: "${p.content||''}" (renders faint, behind everything)</div>`;
                        case 'qr_code': return `<div style="width:40px;height:40px;background:repeating-linear-gradient(45deg,#ddd,#ddd 3px,#fff 3px,#fff 6px);border-radius:4px;"></div>`;
                        default: return '';
                    }
                }

                function render() {
                    const sheet = document.getElementById('rc-sheet');
                    sheet.style.background = bgColor;
                    sheet.innerHTML = '';

                    rows().forEach(({ row, els }) => {
                        const rowEl = document.createElement('div');
                        rowEl.className = 'rc-row';
                        rowEl.dataset.row = row;

                        const controls = document.createElement('div');
                        controls.className = 'rc-row-controls';
                        controls.innerHTML = `<span>Row</span>
                            <button data-row-up>↑ row</button>
                            <button data-row-down>↓ row</button>
                            <button data-row-del>🗑 row</button>`;
                        controls.querySelector('[data-row-up]').onclick = () => moveRow(row, -1);
                        controls.querySelector('[data-row-down]').onclick = () => moveRow(row, 1);
                        controls.querySelector('[data-row-del]').onclick = () => deleteRow(row);
                        rowEl.appendChild(controls);

                        els.forEach(el => rowEl.appendChild(colNode(el)));

                        rowEl.addEventListener('dragover', e => { e.preventDefault(); rowEl.classList.add('rc-row-dragover'); });
                        rowEl.addEventListener('dragleave', () => rowEl.classList.remove('rc-row-dragover'));
                        rowEl.addEventListener('drop', e => {
                            e.preventDefault();
                            rowEl.classList.remove('rc-row-dragover');
                            if (dragId) moveElementToRow(dragId, row);
                        });

                        sheet.appendChild(rowEl);
                    });

                    renderProps();
                }

                function colNode(el) {
                    const wrap = document.createElement('div');
                    wrap.className = 'rc-col' + (el.id === selectedId ? ' selected' : '');
                    wrap.style.cssText = `width:${el.width/12*100}%;`;
                    wrap.dataset.id = el.id;

                    const inner = document.createElement('div');
                    inner.className = 'rc-col-inner';
                    inner.innerHTML = `
                        <span class="rc-col-drag" draggable="true" title="Drag to reorder">⠿</span>
                        <button class="rc-col-del" title="Delete section">✕</button>
                        <div class="rc-col-badge"><span>${TYPE_LABELS[el.type] || el.type}</span><span class="rc-col-width">md-${el.width}</span></div>
                        <div class="rc-col-preview">${colPreview(el)}</div>
                        <div class="rc-col-resize"></div>
                    `;
                    inner.addEventListener('click', (e) => {
                        if (e.target.closest('.rc-col-del') || e.target.closest('.rc-col-resize')) return;
                        selectedId = el.id;
                        render();
                    });
                    inner.querySelector('.rc-col-del').addEventListener('click', (e) => { e.stopPropagation(); deleteElement(el.id); });

                    const dragHandle = inner.querySelector('.rc-col-drag');
                    dragHandle.addEventListener('dragstart', (e) => { dragId = el.id; e.dataTransfer.effectAllowed = 'move'; });
                    dragHandle.addEventListener('dragend', () => { dragId = null; });

                    // drop-to-reorder within/between rows, positioned before this section
                    wrap.addEventListener('dragover', e => e.preventDefault());
                    wrap.addEventListener('drop', e => {
                        e.preventDefault(); e.stopPropagation();
                        if (dragId && dragId !== el.id) reorderBefore(dragId, el.id);
                    });

                    // drag-to-resize the right edge, snapping to the nearest grid width
                    const resizer = inner.querySelector('.rc-col-resize');
                    resizer.addEventListener('mousedown', (ev) => {
                        ev.preventDefault(); ev.stopPropagation();
                        selectedId = el.id;
                        const sheetWidth = document.getElementById('rc-sheet').clientWidth;
                        const startX = ev.clientX;
                        const startWidth = el.width;
                        function onMove(e2) {
                            const deltaCols = Math.round((e2.clientX - startX) / (sheetWidth / 12));
                            el.width = Math.max(2, Math.min(12, startWidth + deltaCols));
                            render();
                        }
                        function onUp() {
                            document.removeEventListener('mousemove', onMove);
                            document.removeEventListener('mouseup', onUp);
                            scheduleSave();
                        }
                        document.addEventListener('mousemove', onMove);
                        document.addEventListener('mouseup', onUp);
                    });

                    wrap.appendChild(inner);
                    return wrap;
                }

                function reorderBefore(movingId, targetId) {
                    const moving = elements.find(e => e.id === movingId);
                    const target = elements.find(e => e.id === targetId);
                    if (!moving || !target) return;
                    moving.row = target.row;
                    elements = elements.filter(e => e.id !== movingId);
                    const idx = elements.findIndex(e => e.id === targetId);
                    elements.splice(idx, 0, moving);
                    selectedId = movingId;
                    render(); scheduleSave();
                }

                function moveElementToRow(id, row) {
                    const el = elements.find(e => e.id === id);
                    if (!el) return;
                    el.row = row;
                    selectedId = id;
                    render(); scheduleSave();
                }

                function moveRow(row, dir) {
                    const rs = rows().map(r => r.row);
                    const idx = rs.indexOf(row);
                    const swapWith = rs[idx + dir];
                    if (swapWith === undefined) return;
                    elements.forEach(el => {
                        if (el.row === row) el.row = swapWith;
                        else if (el.row === swapWith) el.row = row;
                    });
                    render(); scheduleSave();
                }

                function deleteRow(row) {
                    if (!confirm('Remove every section in this row?')) return;
                    elements = elements.filter(e => e.row !== row);
                    if (!elements.find(e => e.id === selectedId)) selectedId = elements[0]?.id ?? null;
                    render(); scheduleSave();
                }

                function deleteElement(id) {
                    elements = elements.filter(e => e.id !== id);
                    if (selectedId === id) selectedId = elements[0]?.id ?? null;
                    render(); scheduleSave();
                }

                function addElement(type, extraProps) {
                    const id = type + '-' + Date.now();
                    const defaults = {
                        text: { content: 'New text', fontSize: 14 },
                        logo: { slot: extraProps?.slot || 'logo_primary', height: 70 },
                        student_field: { field: extraProps?.field || 'name', label: 'Field' },
                        student_photo: { height: 120 },
                        subjects_table: { columns: ['name', 'score', 'grade', 'remark'], zebra: true, fontSize: 12 },
                        grading_key: { fontSize: 11 },
                        remarks: { role: extraProps?.role || 'class_teacher', fontSize: 12 },
                        signature: { label: 'Signature' },
                        attendance: { fontSize: 12 },
                        divider: { color: '#cccccc', thickness: 2 },
                        shape: { fill: '#f1f5f9', height: 20 },
                        watermark: { content: 'DRAFT', opacity: 0.08 },
                        qr_code: { dataField: '', size: 90 },
                    };
                    const el = { id, type, row: nextRowNumber(), width: 12, props: { ...(defaults[type] || {}), ...(extraProps || {}) } };
                    elements.push(el);
                    selectedId = id;
                    render(); scheduleSave();
                }

                document.querySelectorAll('[data-add]').forEach(btn => {
                    btn.addEventListener('click', () => addElement(btn.dataset.add, {
                        slot: btn.dataset.slot, field: btn.dataset.field, role: btn.dataset.role,
                    }));
                });
                document.getElementById('rc-add-row-btn').addEventListener('click', () => addElement('text', { content: 'New text' }));

                // ---- right-hand properties panel ----
                function renderProps() {
                    const panel = document.getElementById('rc-props');
                    const el = elements.find(e => e.id === selectedId);
                    if (!el) { panel.innerHTML = '<p class="rc-empty-hint">Select a section to resize, reorder, or edit it — or add one from the left.</p>'; return; }

                    let html = `<span class="rc-type-badge">${TYPE_LABELS[el.type] || el.type}</span>
                        <div class="rc-el-actions"><button class="rc-delete-btn" id="rc-delete-btn">Delete section</button></div>
                        <label>Section width (grid columns, out of 12)
                            <div class="rc-width-grid">
                                ${WIDTHS.map(w => `<button data-width="${w}" class="${el.width === w ? 'active' : ''}">${w}</button>`).join('')}
                            </div>
                        </label>
                        <label>Row <input type="number" min="0" data-p="row" value="${el.row}"></label>`;

                    const p = el.props || {};
                    if (el.type === 'text') {
                        html += `<label>Content (HTML + @{{merge_tags}} allowed) <textarea rows="3" data-pp="content">${p.content || ''}</textarea></label>
                        <div class="rc-prop-row">
                            <label>Font size <input type="number" data-pp="fontSize" value="${p.fontSize || 14}"></label>
                            <label>Weight <input type="number" step="100" data-pp="fontWeight" value="${p.fontWeight || 400}"></label>
                        </div>
                        <label>Color <input type="color" data-pp="color" value="${p.color || '#111111'}"></label>
                        <label>Align
                            <select data-pp="align">
                                <option value="left" ${(p.align||'left')==='left'?'selected':''}>Left</option>
                                <option value="center" ${p.align==='center'?'selected':''}>Center</option>
                                <option value="right" ${p.align==='right'?'selected':''}>Right</option>
                            </select>
                        </label>`;
                    }
                    if (el.type === 'logo') {
                        html += `<label>Slot
                            <select data-pp="slot">
                                <option value="logo_primary" ${p.slot!=='logo_secondary'?'selected':''}>Primary</option>
                                <option value="logo_secondary" ${p.slot==='logo_secondary'?'selected':''}>Secondary</option>
                            </select></label>
                        <label>Height (px) <input type="number" data-pp="height" value="${p.height || 70}"></label>
                        <label>Rounding <input type="number" data-pp="borderRadius" value="${p.borderRadius || 0}"></label>
                        <label>Drop shadow <input type="checkbox" data-pp="shadow" ${p.shadow ? 'checked' : ''}></label>`;
                    }
                    if (el.type === 'student_field') {
                        html += `<label>Field
                            <select data-pp="field">
                                <option value="name" ${p.field==='name'?'selected':''}>Name</option>
                                <option value="admission_no" ${p.field==='admission_no'?'selected':''}>Admission No.</option>
                                <option value="class" ${p.field==='class'?'selected':''}>Class</option>
                                <option value="stream" ${p.field==='stream'?'selected':''}>Stream</option>
                                <option value="dob" ${p.field==='dob'?'selected':''}>Date of Birth</option>
                            </select></label>
                        <label>Label <input type="text" data-pp="label" value="${p.label || ''}"></label>
                        <label>Font size <input type="number" data-pp="fontSize" value="${p.fontSize || 13}"></label>`;
                    }
                    if (el.type === 'student_photo') {
                        html += `<label>Height (px) <input type="number" data-pp="height" value="${p.height || 120}"></label>
                        <label>Rounding <input type="number" data-pp="borderRadius" value="${p.borderRadius || 4}"></label>`;
                    }
                    if (el.type === 'subjects_table') {
                        html += `<label>Columns (comma separated) <input type="text" data-pp="columns" value="${(p.columns || []).join(',')}"></label>
                        <label>Header color <input type="color" data-pp="headerColor" value="${p.headerColor || '#f2f2f2'}"></label>
                        <label>Font size <input type="number" data-pp="fontSize" value="${p.fontSize || 12}"></label>
                        <label>Zebra stripes <input type="checkbox" data-pp="zebra" ${p.zebra ? 'checked' : ''}></label>`;
                    }
                    if (el.type === 'remarks') {
                        html += `<label>Role
                            <select data-pp="role">
                                <option value="class_teacher" ${p.role==='class_teacher'?'selected':''}>Class Teacher</option>
                                <option value="head_teacher" ${p.role==='head_teacher'?'selected':''}>Head Teacher</option>
                            </select></label>
                        <label>Font size <input type="number" data-pp="fontSize" value="${p.fontSize || 12}"></label>`;
                    }
                    if (el.type === 'signature') {
                        html += `<label>Label <input type="text" data-pp="label" value="${p.label || ''}"></label>`;
                    }
                    if (el.type === 'divider') {
                        html += `<label>Color <input type="color" data-pp="color" value="${p.color || '#cccccc'}"></label>
                        <label>Thickness (px) <input type="number" data-pp="thickness" value="${p.thickness || 2}"></label>`;
                    }
                    if (el.type === 'shape') {
                        html += `<label>Fill <input type="color" data-pp="fill" value="${p.fill || '#f1f5f9'}"></label>
                        <label>Height (px) <input type="number" data-pp="height" value="${p.height || 20}"></label>
                        <label>Rounding <input type="number" data-pp="borderRadius" value="${p.borderRadius || 0}"></label>`;
                    }
                    if (el.type === 'watermark') {
                        html += `<label>Text <input type="text" data-pp="content" value="${p.content || ''}"></label>
                        <label>Opacity <input type="number" step="0.01" min="0" max="1" data-pp="opacity" value="${p.opacity ?? 0.08}"></label>`;
                    }
                    if (el.type === 'qr_code') {
                        html += `<label>Encodes <input type="text" data-pp="dataField" value="${p.dataField || ''}"></label>
                        <p style="font-size:.72rem;color:#8a8fa8;margin-top:-.6rem;">Merge tag, e.g. @{{student.admission_no}}. Leave blank to use the verification QR already on pass slips.</p>
                        <label>Size (px) <input type="number" data-pp="size" value="${p.size || 90}"></label>`;
                    }

                    panel.innerHTML = html;
                    panel.querySelector('#rc-delete-btn').addEventListener('click', () => deleteElement(el.id));

                    panel.querySelectorAll('[data-width]').forEach(btn => {
                        btn.addEventListener('click', () => { el.width = Number(btn.dataset.width); render(); scheduleSave(); });
                    });
                    panel.querySelectorAll('[data-p]').forEach(input => {
                        input.addEventListener('input', () => { el[input.dataset.p] = Number(input.value); render(); scheduleSave(); });
                    });
                    panel.querySelectorAll('[data-pp]').forEach(input => {
                        input.addEventListener('input', () => {
                            let val = input.type === 'checkbox' ? input.checked : input.value;
                            if (input.dataset.pp === 'columns') val = val.split(',').map(s => s.trim()).filter(Boolean);
                            if (input.dataset.pp === 'opacity') val = Number(val);
                            el.props[input.dataset.pp] = val;
                            render(); scheduleSave();
                        });
                    });
                }

                // ---- page background ----
                let bgColor = {!! json_encode($template->background['color'] ?? '#ffffff') !!};
                document.getElementById('rc-bg-color').addEventListener('input', (e) => {
                    bgColor = e.target.value;
                    render(); scheduleSave();
                });

                document.getElementById('rc-template-name').addEventListener('input', scheduleSave);

                // ---- autosave (debounced) ----
                let saveTimer;
                function scheduleSave() {
                    document.getElementById('rc-save-status').textContent = 'Saving…';
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(save, 800);
                }
                function save() {
                    fetch(SAVE_URL, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            elements,
                            name: document.getElementById('rc-template-name').value,
                            background: { color: bgColor },
                        }),
                    })
                        .then(r => r.ok ? r.json() : Promise.reject(r))
                        .then(() => document.getElementById('rc-save-status').textContent = 'Saved')
                        .catch(() => document.getElementById('rc-save-status').textContent = 'Save failed — retrying…');
                }

                document.getElementById('rc-publish-btn').addEventListener('click', () => {
                    save();
                    fetch(PUBLISH_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } })
                        .then(() => {
                            const status = document.getElementById('rc-save-status');
                            status.textContent = 'Published ✓';
                            setTimeout(() => status.textContent = 'Saved', 2000);
                        });
                });

                document.getElementById('rc-preview-btn').addEventListener('click', () => {
                    save();
                    window.open(PREVIEW_URL, '_blank');
                });

                window.addEventListener('beforeunload', (e) => {
                    if (document.getElementById('rc-save-status').textContent === 'Saving…') {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                render();
                rcFitSheetWidth();
            })();
        } catch (err) {
            rcShowError(err && err.stack ? err.stack : String(err));
        }
    </script>
@endsection