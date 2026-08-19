@extends('layouts-side-bar.master')

@section('css')
    <style>
        body {
            overflow-x: hidden;
        }

        #rc-builder {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr) 280px;
            gap: 0;
            background: #eef0f4;
        }

        @media (max-width: 1200px) {
            #rc-builder {
                grid-template-columns: 210px minmax(0, 1fr) 240px;
            }

            .rc-palette-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            #rc-builder {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto;
                height: auto;
            }

            .rc-palette,
            .rc-props {
                max-height: 40vh;
            }

            .rc-canvas-wrap {
                min-height: 50vh;
            }
        }

        #rc-js-error-banner {
            display: none;
            position: sticky;
            top: 0;
            z-index: 50;
            background: #fdecec;
            color: #9c1c1c;
            border-bottom: 1px solid #f3c2c2;
            padding: .6rem 1rem;
            font-size: .82rem;
            font-family: monospace;
            white-space: pre-wrap;
        }

        .rc-palette,
        .rc-props {
            background: #fff;
            border-right: 1px solid #e5e7f2;
            overflow-y: auto;
            padding: 1rem;
        }

        .rc-props {
            border-right: none;
            border-left: 1px solid #e5e7f2;
        }

        .rc-palette h3,
        .rc-props h3 {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c7293;
            margin: 1rem 0 .6rem;
        }

        .rc-palette h3:first-child {
            margin-top: 0;
        }

        .rc-palette-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem;
        }

        .rc-palette-grid button {
            font-size: .74rem;
            padding: .55rem .4rem;
            border: 1px solid #e2e4f3;
            border-radius: 8px;
            background: #fafbff;
            cursor: pointer;
            text-align: left;
            transition: all .12s;
            color: #333349;
        }

        .rc-palette-grid button:hover {
            background: #5351e4;
            color: #fff;
            border-color: #5351e4;
        }

        .rc-align-row {
            display: flex;
            gap: .3rem;
        }

        .rc-align-row button {
            flex: 1;
            padding: .5rem 0;
            border: 1px solid #e2e4f3;
            border-radius: 8px;
            background: #fafbff;
            cursor: pointer;
        }

        .rc-align-row button:hover {
            background: #eef0fb;
        }

        .rc-layers {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .rc-layers li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .4rem;
            padding: .45rem .55rem;
            border-radius: 6px;
            font-size: .78rem;
            color: #4a4a68;
            cursor: pointer;
            margin-bottom: 2px;
        }

        .rc-layers li:hover {
            background: #f4f5fa;
        }

        .rc-layers li.active {
            background: #eef0fb;
            color: #423fc9;
            font-weight: 600;
        }

        .rc-layers li .rc-layer-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .rc-layers li .rc-layer-actions {
            display: flex;
            gap: 2px;
            opacity: .6;
        }

        .rc-layers li .rc-layer-actions button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: .7rem;
            padding: 2px 4px;
        }

        .rc-layers li .rc-layer-actions button:hover {
            opacity: 1;
            color: #d64545;
        }

        .rc-stage {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }

        .rc-toolbar {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e5e7f2;
        }

        .rc-toolbar #rc-template-name {
            font-size: .95rem;
            font-weight: 700;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: .3rem .5rem;
            flex: 1;
            max-width: 320px;
            color: #1e1e2d;
        }

        .rc-toolbar #rc-template-name:hover,
        .rc-toolbar #rc-template-name:focus {
            border-color: #dcdfef;
            outline: none;
            background: #fafbff;
        }

        .rc-save-status {
            font-size: .75rem;
            color: #8a8fa8;
            margin-right: auto;
        }

        .rc-toolbar button {
            font-size: .82rem;
            font-weight: 600;
            padding: .5rem .9rem;
            border-radius: 8px;
            border: 1px solid #dcdfef;
            background: #fff;
            color: #4a4a68;
            cursor: pointer;
        }

        .rc-toolbar button:hover {
            background: #f4f5fa;
        }

        .rc-toolbar button.primary {
            background: #5351e4;
            border-color: #5351e4;
            color: #fff;
        }

        .rc-toolbar button.primary:hover {
            background: #423fc9;
        }

        .rc-toolbar button.danger {
            color: #d64545;
            border-color: #f3d4d4;
        }

        .rc-toolbar button.danger:hover {
            background: #fdf1f1;
        }

        .rc-canvas-wrap {
            flex: 1;
            overflow: auto;
            display: flex;
            justify-content: center;
            padding: 2rem;
        }

        .rc-canvas-wrap canvas {
            box-shadow: 0 4px 24px rgba(0, 0, 0, .15);
            background: #fff;
        }

        .rc-props label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #4a4a68;
            margin-bottom: .9rem;
        }

        .rc-props label input[type=text],
        .rc-props label input[type=number],
        .rc-props label textarea,
        .rc-props label select {
            display: block;
            width: 100%;
            margin-top: .3rem;
            padding: .45rem .55rem;
            border: 1px solid #dcdfef;
            border-radius: 6px;
            font-size: .82rem;
            font-weight: 400;
        }

        .rc-props label input[type=color] {
            width: 100%;
            height: 34px;
            margin-top: .3rem;
            border: 1px solid #dcdfef;
            border-radius: 6px;
        }

        .rc-props label input[type=checkbox] {
            margin-left: .5rem;
        }

        .rc-props .rc-prop-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .rc-props .rc-empty-hint {
            color: #8a8fa8;
            font-size: .85rem;
            margin-top: 2rem;
            text-align: center;
        }

        .rc-props .rc-el-actions {
            display: flex;
            gap: .4rem;
            margin: 1rem 0 1.25rem;
        }

        .rc-props .rc-el-actions button {
            flex: 1;
            font-size: .76rem;
            font-weight: 600;
            padding: .45rem 0;
            border-radius: 6px;
            border: 1px solid #dcdfef;
            background: #fafbff;
            cursor: pointer;
        }

        .rc-props .rc-el-actions button:hover {
            background: #eef0fb;
        }

        .rc-props .rc-el-actions button.rc-delete-btn {
            color: #d64545;
        }

        .rc-props .rc-el-actions button.rc-delete-btn:hover {
            background: #fdf1f1;
        }

        .rc-props .rc-type-badge {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #5351e4;
            background: #eef0fb;
            padding: .2rem .5rem;
            border-radius: 20px;
            margin-bottom: .8rem;
        }
    </style>
@endsection

@section('content')
    <div id="rc-js-error-banner"></div>
    <div id="rc-builder" class="rc-builder" style="margin-top:2rem;"margin-bottom:2rem;">

        {{-- LEFT: element palette --}}
        <aside class="rc-palette">
            <h3>Add element</h3>
            <div class="rc-palette-grid">
                <button data-add="logo" data-slot="logo_primary">🖼 Logo (Left)</button>
                <button data-add="logo" data-slot="logo_secondary">🖼 Logo (Right)</button>
                <button data-add="text">🔤 Text</button>
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
                <button data-add="shape">▭ Shape / Accent</button>
                <button data-add="watermark">💧 Watermark</button>
                <button data-add="qr_code">▦ QR Code</button>
            </div>

            <h3>Alignment</h3>
            <div class="rc-align-row">
                <button data-align="left" title="Align left">⟸</button>
                <button data-align="center-h" title="Center horizontally">↔</button>
                <button data-align="right" title="Align right">⟹</button>
            </div>
            <div class="rc-align-row" style="margin-top:.4rem;">
                <button data-align="top" title="Align top">⟰</button>
                <button data-align="center-v" title="Center vertically">↕</button>
                <button data-align="bottom" title="Align bottom">⟱</button>
            </div>

            <h3>Canvas background</h3>
            <label>Color <input type="color" id="rc-bg-color"
                    value="{{ $template->background['color'] ?? '#FFFFFF' }}"></label>

            <h3>Layers <span style="font-weight:400; color:#b5b8cc;">(top → bottom)</span></h3>
            <ul id="rc-layers" class="rc-layers"></ul>
        </aside>

        {{-- CENTER: canvas --}}
        <main class="rc-stage">
            <div class="rc-toolbar">
                <input id="rc-template-name" value="{{ $template->name }}" />
                <span id="rc-save-status" class="rc-save-status">Saved</span>
                <button id="rc-preview-btn">Preview with sample data</button>
                <button id="rc-publish-btn" class="primary">Publish</button>
            </div>
            <div class="rc-canvas-wrap">
                <canvas id="rc-canvas" width="{{ $template->canvas_width }}"
                    height="{{ $template->canvas_height }}"></canvas>
            </div>
        </main>

        {{-- RIGHT: properties panel --}}
        <aside class="rc-props" id="rc-props">
            <p class="rc-empty-hint">Select an element to edit it, or add one from the left.</p>
        </aside>
    </div>
    </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script>
        function rcShowError(msg) {
            var banner = document.getElementById('rc-js-error-banner');
            banner.style.display = 'block';
            banner.textContent = '⚠ Report card builder error — open DevTools console for full details.\n' + msg;
            console.error('[Report Card Builder]', msg);
        }

        // Fit the 3-column builder to whatever vertical space is actually left
        // below this page's header/nav — measured, not guessed — and keep it
        // correct across window resizes and different screen sizes.
        function rcFitBuilderHeight() {
            var el = document.getElementById('rc-builder');
            if (!el) return;
            var top = el.getBoundingClientRect().top + window.scrollY;
            var h = Math.max(480, window.innerHeight - el.getBoundingClientRect().top);
            el.style.height = h + 'px';
        }
        window.addEventListener('resize', rcFitBuilderHeight);
        document.addEventListener('DOMContentLoaded', rcFitBuilderHeight);
        rcFitBuilderHeight();

        window.addEventListener('error', function (e) {
            rcShowError((e.error && e.error.stack) ? e.error.stack : e.message);
        });

        try {
            (function () {
                if (typeof fabric === 'undefined') {
                    rcShowError('fabric.js did not load — check your internet connection / that cdnjs.cloudflare.com is reachable, or download fabric.min.js locally and update the <script src> in resources/views/report-cards/builder.blade.php.');
                    return;
                }

                const SAVE_URL = "{{ route('report-templates.autosave', $template) }}";
                const PUBLISH_URL = "{{ route('report-templates.publish', $template) }}";
                const PREVIEW_URL = "{{ route('report-templates.preview', $template) }}";
                const CSRF = "{{ csrf_token() }}";

                const canvas = new fabric.Canvas('rc-canvas', { backgroundColor: {!! json_encode($template->background['color'] ?? '#ffffff') !!} });
                let elements = @json($template->elements ?: []);
                let selectedId = null;

                const TYPE_LABELS = {
                    logo: 'Logo', text: 'Text', student_field: 'Student Field', student_photo: 'Student Photo',
                    subjects_table: 'Subjects Table', grading_key: 'Grading Key', remarks: 'Remarks', signature: 'Signature',
                    attendance: 'Attendance', divider: 'Divider', shape: 'Shape', watermark: 'Watermark', qr_code: 'QR Code',
                };

                // ---- render existing elements onto the canvas as fabric objects ----
                function elementToFabricObject(el) {
                    // Each element type gets a lightweight visual proxy on the canvas —
                    // a labeled rectangle for structured types (tables, remarks, etc.)
                    // and a real text object for text, so designers get an
                    // accurate-enough WYSIWYG without re-implementing every renderer
                    // branch in canvas-land. The Blade renderer is the source of truth
                    // for the final pixel-perfect output.
                    const common = {
                        left: el.x, top: el.y, width: el.w, height: el.h,
                        angle: el.rotation || 0, selectable: true,
                    };

                    if (el.type === 'text') {
                        return new fabric.Textbox(el.props.content || 'Text', {
                            ...common,
                            fontSize: el.props.fontSize || 14,
                            fontWeight: el.props.fontWeight || 400,
                            fill: el.props.color || '#111',
                        });
                    }

                    if (el.type === 'logo' || el.type === 'student_photo') {
                        const rect = new fabric.Rect({ width: el.w, height: el.h, fill: '#eef2ff', stroke: '#c7d2fe', strokeDashArray: [4, 4] });
                        const label = new fabric.Text(el.type === 'logo' ? (el.props.slot === 'logo_secondary' ? 'Logo R' : 'Logo L') : 'Photo', {
                            fontSize: 12, fill: '#4338ca', originX: 'center', originY: 'center', left: el.w / 2, top: el.h / 2,
                        });
                        return new fabric.Group([rect, label], common);
                    }

                    // structured/data-bound elements -> labeled placeholder block
                    const labels = {
                        subjects_table: 'Subjects Table', grading_key: 'Grading Key',
                        remarks: (el.props.role === 'head_teacher' ? 'Head Teacher Remarks' : 'Class Teacher Remarks'),
                        signature: el.props.label || 'Signature', attendance: 'Attendance',
                        student_field: el.props.label || el.props.field, divider: 'Divider',
                        shape: 'Shape', watermark: 'Watermark', qr_code: 'QR Code',
                    };
                    const rect = new fabric.Rect({ width: el.w, height: el.h, fill: '#f8fafc', stroke: '#cbd5e1' });
                    const label = new fabric.Text(labels[el.type] || el.type, {
                        fontSize: 12, fill: '#334155', originX: 'center', originY: 'center', left: el.w / 2, top: el.h / 2,
                    });
                    return new fabric.Group([rect, label], common);
                }

                function loadElements() {
                    canvas.clear();
                    elements.forEach(el => {
                        const obj = elementToFabricObject(el);
                        obj.__elId = el.id;
                        canvas.add(obj);
                        if (el.id === selectedId) canvas.setActiveObject(obj);
                    });
                    canvas.renderAll();
                    renderLayers();
                }

                function renderLayers() {
                    const list = document.getElementById('rc-layers');
                    list.innerHTML = '';
                    elements.slice().sort((a, b) => (b.zIndex || 1) - (a.zIndex || 1)).forEach(el => {
                        const li = document.createElement('li');
                        li.className = el.id === selectedId ? 'active' : '';
                        li.innerHTML = `<span class="rc-layer-name">${TYPE_LABELS[el.type] || el.type}${el.props?.label ? ' — ' + el.props.label : ''}</span>
                    <span class="rc-layer-actions"><button data-del="${el.id}" title="Delete">✕</button></span>`;
                        li.addEventListener('click', (e) => {
                            if (e.target.closest('[data-del]')) return;
                            selectElement(el.id);
                        });
                        list.appendChild(li);
                    });
                    list.querySelectorAll('[data-del]').forEach(btn => {
                        btn.addEventListener('click', () => deleteElement(btn.dataset.del));
                    });
                }

                function selectElement(id) {
                    const obj = canvas.getObjects().find(o => o.__elId === id);
                    if (obj) canvas.setActiveObject(obj);
                    canvas.renderAll();
                }

                // ---- palette: add new element ----
                document.querySelectorAll('[data-add]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const type = btn.dataset.add;
                        const id = type + '-' + Date.now();
                        const defaults = {
                            logo: { w: 90, h: 90, props: { slot: btn.dataset.slot || 'logo_primary', borderRadius: 8 } },
                            text: { w: 200, h: 30, props: { content: 'Edit this text', fontSize: 16 } },
                            student_field: { w: 220, h: 24, props: { field: btn.dataset.field, label: btn.dataset.field } },
                            student_photo: { w: 110, h: 130, props: { borderRadius: 4 } },
                            subjects_table: { w: 500, h: 260, props: { columns: ['name', 'score', 'grade', 'remark'], zebra: true } },
                            grading_key: { w: 400, h: 30, props: {} },
                            remarks: { w: 500, h: 60, props: { role: btn.dataset.role } },
                            signature: { w: 160, h: 50, props: { label: 'Class Teacher' } },
                            attendance: { w: 220, h: 24, props: {} },
                            divider: { w: 600, h: 2, props: { color: '#ccc' } },
                            shape: { w: 120, h: 60, props: { fill: '#f1f5f9', borderRadius: 6 } },
                            watermark: { w: 400, h: 200, props: { content: 'SCHOOL', opacity: 0.06 } },
                            qr_code: { w: 100, h: 100, props: { dataField: '@{{student.admission_no}}' } },
                        }[type];

                        const maxZ = elements.reduce((m, e) => Math.max(m, e.zIndex || 1), 0);
                        const el = { id, type, x: 60, y: 60, rotation: 0, zIndex: maxZ + 1, align: 'left', ...defaults };
                        elements.push(el);
                        selectedId = id;
                        loadElements();
                        selectElement(id);
                        scheduleSave();
                    });
                });

                // ---- keep element x/y/w/h in sync when dragged/resized ----
                canvas.on('object:modified', (e) => {
                    const obj = e.target;
                    const el = elements.find(x => x.id === obj.__elId);
                    if (!el) return;
                    el.x = Math.round(obj.left);
                    el.y = Math.round(obj.top);
                    el.w = Math.round(obj.width * (obj.scaleX || 1));
                    el.h = Math.round(obj.height * (obj.scaleY || 1));
                    el.rotation = Math.round(obj.angle || 0);
                    obj.set({ scaleX: 1, scaleY: 1, width: el.w, height: el.h });
                    scheduleSave();
                    showProps(el);
                });

                canvas.on('selection:created', (e) => onSelect(e.selected[0]));
                canvas.on('selection:updated', (e) => onSelect(e.selected[0]));
                canvas.on('selection:cleared', () => {
                    selectedId = null;
                    document.getElementById('rc-props').innerHTML = '<p class="rc-empty-hint">Select an element to edit it, or add one from the left.</p>';
                    renderLayers();
                });

                function onSelect(obj) {
                    const el = elements.find(x => x.id === obj.__elId);
                    if (!el) return;
                    selectedId = el.id;
                    showProps(el);
                    renderLayers();
                }

                function deleteElement(id) {
                    elements = elements.filter(e => e.id !== id);
                    if (selectedId === id) selectedId = null;
                    loadElements();
                    document.getElementById('rc-props').innerHTML = '<p class="rc-empty-hint">Select an element to edit it, or add one from the left.</p>';
                    scheduleSave();
                }

                function duplicateElement(el) {
                    const copy = JSON.parse(JSON.stringify(el));
                    copy.id = el.type + '-' + Date.now();
                    copy.x = el.x + 16;
                    copy.y = el.y + 16;
                    const maxZ = elements.reduce((m, e) => Math.max(m, e.zIndex || 1), 0);
                    copy.zIndex = maxZ + 1;
                    elements.push(copy);
                    selectedId = copy.id;
                    loadElements();
                    selectElement(copy.id);
                    scheduleSave();
                }

                // ---- keyboard shortcuts: Delete/Backspace removes the selected element ----
                document.addEventListener('keydown', (e) => {
                    if (!selectedId) return;
                    const tag = (e.target.tagName || '').toLowerCase();
                    if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
                    if (e.key === 'Delete' || e.key === 'Backspace') {
                        e.preventDefault();
                        deleteElement(selectedId);
                    }
                });

                // ---- properties panel: type-specific fields ----
                function showProps(el) {
                    const panel = document.getElementById('rc-props');
                    let fields = `<span class="rc-type-badge">${TYPE_LABELS[el.type] || el.type}</span>
                <div class="rc-el-actions">
                    <button id="rc-dup-btn">⧉ Duplicate</button>
                    <button id="rc-delete-btn" class="rc-delete-btn">✕ Delete</button>
                </div>
                <div class="rc-prop-row">
                    <label>X <input type="number" data-p="x" value="${el.x}"></label>
                    <label>Y <input type="number" data-p="y" value="${el.y}"></label>
                </div>
                <div class="rc-prop-row">
                    <label>W <input type="number" data-p="w" value="${el.w}"></label>
                    <label>H <input type="number" data-p="h" value="${el.h}"></label>
                </div>
                <div class="rc-prop-row">
                    <label>Rotation <input type="number" data-p="rotation" value="${el.rotation || 0}"></label>
                    <label>Layer (z) <input type="number" data-p="zIndex" value="${el.zIndex || 1}"></label>
                </div>`;

                    if (el.type === 'text') {
                        fields += `<label>Content <textarea data-pp="content" rows="3">${el.props.content || ''}</textarea></label>
                    <p style="font-size:.72rem;color:#8a8fa8;margin:-.6rem 0 .8rem;">Use @{{school_name}}, @{{term}}, @{{year}}, @{{student.name}}, @{{overall_grade}}, etc.</p>
                    <div class="rc-prop-row">
                        <label>Font size <input type="number" data-pp="fontSize" value="${el.props.fontSize || 14}"></label>
                        <label>Weight <input type="number" step="100" data-pp="fontWeight" value="${el.props.fontWeight || 400}"></label>
                    </div>
                    <label>Color <input type="color" data-pp="color" value="${el.props.color || '#111111'}"></label>`;
                    }
                    if (el.type === 'logo') {
                        fields += `<label>Slot
                    <select data-pp="slot">
                        <option value="logo_primary" ${el.props.slot === 'logo_primary' ? 'selected' : ''}>Primary</option>
                        <option value="logo_secondary" ${el.props.slot === 'logo_secondary' ? 'selected' : ''}>Secondary</option>
                    </select></label>
                    <label>Rounding <input type="number" data-pp="borderRadius" value="${el.props.borderRadius || 0}"></label>
                    <label>Drop shadow <input type="checkbox" data-pp="shadow" ${el.props.shadow ? 'checked' : ''}></label>`;
                    }
                    if (el.type === 'student_photo') {
                        fields += `<label>Rounding <input type="number" data-pp="borderRadius" value="${el.props.borderRadius || 0}"></label>
                    <label>Grayscale <input type="checkbox" data-pp="grayscale" ${el.props.grayscale ? 'checked' : ''}></label>`;
                    }
                    if (el.type === 'student_field') {
                        fields += `<label>Field
                    <select data-pp="field">
                        ${['name', 'admission_no', 'class', 'stream', 'dob'].map(f => `<option value="${f}" ${el.props.field === f ? 'selected' : ''}>${f}</option>`).join('')}
                    </select></label>
                    <label>Label <input type="text" data-pp="label" value="${el.props.label || ''}"></label>
                    <label>Font size <input type="number" data-pp="fontSize" value="${el.props.fontSize || 13}"></label>`;
                    }
                    if (el.type === 'subjects_table') {
                        fields += `<label>Columns (comma separated) <input type="text" data-pp="columns" value="${(el.props.columns || []).join(',')}"></label>
                    <label>Header color <input type="color" data-pp="headerColor" value="${el.props.headerColor || '#f2f2f2'}"></label>
                    <label>Font size <input type="number" data-pp="fontSize" value="${el.props.fontSize || 12}"></label>
                    <label>Zebra stripes <input type="checkbox" data-pp="zebra" ${el.props.zebra ? 'checked' : ''}></label>`;
                    }
                    if (el.type === 'remarks') {
                        fields += `<label>Role
                    <select data-pp="role">
                        <option value="class_teacher" ${el.props.role === 'class_teacher' ? 'selected' : ''}>Class Teacher</option>
                        <option value="head_teacher" ${el.props.role === 'head_teacher' ? 'selected' : ''}>Head Teacher</option>
                    </select></label>
                    <label>Font size <input type="number" data-pp="fontSize" value="${el.props.fontSize || 12}"></label>`;
                    }
                    if (el.type === 'signature') {
                        fields += `<label>Label <input type="text" data-pp="label" value="${el.props.label || ''}"></label>`;
                    }
                    if (el.type === 'divider') {
                        fields += `<label>Color <input type="color" data-pp="color" value="${el.props.color || '#cccccc'}"></label>`;
                    }
                    if (el.type === 'shape') {
                        fields += `<label>Fill <input type="color" data-pp="fill" value="${el.props.fill || '#f1f5f9'}"></label>
                    <label>Border color <input type="color" data-pp="borderColor" value="${el.props.borderColor || '#cccccc'}"></label>
                    <label>Rounding <input type="number" data-pp="borderRadius" value="${el.props.borderRadius || 0}"></label>`;
                    }
                    if (el.type === 'watermark') {
                        fields += `<label>Text <input type="text" data-pp="content" value="${el.props.content || ''}"></label>
                    <label>Opacity <input type="number" step="0.01" min="0" max="1" data-pp="opacity" value="${el.props.opacity ?? 0.08}"></label>`;
                    }
                    if (el.type === 'qr_code') {
                        fields += `<label>Encodes <input type="text" data-pp="dataField" value="${el.props.dataField || ''}"></label>
                    <p style="font-size:.72rem;color:#8a8fa8;margin-top:-.6rem;">Merge tag, e.g. @{{student.admission_no}}</p>`;
                    }

                    panel.innerHTML = fields;

                    panel.querySelector('#rc-delete-btn').addEventListener('click', () => deleteElement(el.id));
                    panel.querySelector('#rc-dup-btn').addEventListener('click', () => duplicateElement(el));

                    panel.querySelectorAll('[data-p]').forEach(input => {
                        input.addEventListener('input', () => {
                            el[input.dataset.p] = Number(input.value);
                            loadElements(); scheduleSave();
                        });
                    });
                    panel.querySelectorAll('[data-pp]').forEach(input => {
                        input.addEventListener('input', () => {
                            let val = input.type === 'checkbox' ? input.checked : input.value;
                            if (input.dataset.pp === 'columns') val = val.split(',').map(s => s.trim()).filter(Boolean);
                            if (input.dataset.pp === 'opacity') val = Number(val);
                            el.props[input.dataset.pp] = val;
                            loadElements(); scheduleSave();
                        });
                    });
                }

                // ---- alignment tools (relative to canvas) ----
                document.querySelectorAll('[data-align]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const obj = canvas.getActiveObject();
                        if (!obj) return;
                        const cw = canvas.getWidth(), ch = canvas.getHeight();
                        const w = obj.width * (obj.scaleX || 1), h = obj.height * (obj.scaleY || 1);
                        switch (btn.dataset.align) {
                            case 'left': obj.set('left', 20); break;
                            case 'right': obj.set('left', cw - w - 20); break;
                            case 'center-h': obj.set('left', (cw - w) / 2); break;
                            case 'top': obj.set('top', 20); break;
                            case 'bottom': obj.set('top', ch - h - 20); break;
                            case 'center-v': obj.set('top', (ch - h) / 2); break;
                        }
                        canvas.fire('object:modified', { target: obj });
                        canvas.renderAll();
                    });
                });

                // ---- canvas background color ----
                let bgColor = {!! json_encode($template->background['color'] ?? '#ffffff') !!};
                document.getElementById('rc-bg-color').addEventListener('input', (e) => {
                    bgColor = e.target.value;
                    canvas.setBackgroundColor(bgColor, canvas.renderAll.bind(canvas));
                    scheduleSave();
                });

                // ---- template name ----
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

                // Warn before leaving with unsaved edits still in-flight.
                window.addEventListener('beforeunload', (e) => {
                    if (document.getElementById('rc-save-status').textContent === 'Saving…') {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                loadElements();
            })();
        } catch (err) {
            rcShowError(err && err.stack ? err.stack : String(err));
        }
    </script>
@endsection