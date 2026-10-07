@extends('layouts-side-bar.master')

@section('content')
    <style>
        .crc-page {
            --crc-radius: 14px;
            --crc-border: #e6e9f0;
            --crc-muted: #6b7385;
            --crc-bg: #f6f8fb;
        }

        .crc-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .crc-hero h3 {
            font-weight: 700;
            margin: 0 0 4px;
        }

        .crc-hero p {
            margin: 0;
            color: var(--crc-muted);
        }

        .crc-hero code,
        .crc-panel code {
            background: #eef1f7;
            color: #3b4a6b;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: .82rem;
        }

        .crc-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .crc-stat {
            background: #fff;
            border: 1px solid var(--crc-border);
            border-radius: var(--crc-radius);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .crc-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .crc-stat-num {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .crc-stat-label {
            font-size: .78rem;
            color: var(--crc-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: 4px;
        }

        .ic-blue {
            background: #e8f0ff;
            color: #2f6bff;
        }

        .ic-green {
            background: #e5f7ee;
            color: #13a561;
        }

        .ic-amber {
            background: #fff4dc;
            color: #d98a00;
        }

        .ic-red {
            background: #fde9e9;
            color: #d93636;
        }

        .crc-panel {
            background: #fff;
            border: 1px solid var(--crc-border);
            border-radius: var(--crc-radius);
            padding: 20px 22px;
            margin-bottom: 22px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .crc-panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .crc-panel-head h5 {
            margin: 0 0 2px;
            font-weight: 600;
        }

        .crc-sync {
            border: 1.5px dashed #c9d3e6;
            background: var(--crc-bg);
        }

        .crc-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .crc-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid var(--crc-border);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: .88rem;
        }

        .crc-chip .slug {
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            font-size: .8rem;
            color: #3b4a6b;
        }

        .crc-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 6px 0 14px;
        }

        .crc-section-title h5 {
            margin: 0;
            font-weight: 600;
        }

        /* 2 cards per row (col-md-6). Use "1fr" for one per row (col-md-12). */
        .crc-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        @media (max-width: 767px) {
            .crc-grid {
                grid-template-columns: 1fr;
            }
        }

        .crc-tpl {
            background: #fff;
            border: 1px solid var(--crc-border);
            border-radius: var(--crc-radius);
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
            display: flex;
            flex-direction: column;
            transition: box-shadow .15s, transform .15s;
            overflow: hidden;
        }

        .crc-tpl:hover {
            box-shadow: 0 8px 24px rgba(16, 24, 40, .08);
            transform: translateY(-1px);
        }

        .crc-tpl.is-off {
            opacity: .8;
            background: #fcfcfd;
        }

        .crc-tpl-top {
            padding: 20px 24px 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }

        .crc-tpl-slug {
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            font-size: .78rem;
            color: var(--crc-muted);
            margin-top: 6px;
        }

        .crc-tpl-body {
            padding: 8px 24px 18px;
            flex: 1;
        }

        .crc-tpl-body label {
            font-size: .74rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--crc-muted);
            margin-bottom: 4px;
        }

        .crc-tpl-body .form-control {
            border-radius: 10px;
            border-color: var(--crc-border);
            padding: .6rem .85rem;
            font-size: .95rem;
        }

        .crc-tpl-body .form-control:focus {
            border-color: #2f6bff;
            box-shadow: 0 0 0 3px rgba(47, 107, 255, .12);
        }

        .crc-missing {
            background: #fde9e9;
            color: #b42318;
            font-size: .82rem;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .crc-tpl-foot {
            border-top: 1px solid var(--crc-border);
            background: var(--crc-bg);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .crc-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .crc-meta .schools {
            font-size: .82rem;
            color: var(--crc-muted);
        }

        .crc-actions {
            display: flex;
            gap: 6px;
        }

        .crc-actions .btn {
            border-radius: 8px;
        }

        .crc-empty {
            text-align: center;
            padding: 48px 20px;
            color: var(--crc-muted);
        }

        .crc-empty .em-icon {
            font-size: 2.4rem;
            margin-bottom: 8px;
            opacity: .6;
        }
    </style>

    <div class="container-fluid pt-4 crc-page">

        <div class="crc-hero">
            <div>
                <h3>Custom Report Cards</h3>
                <p>Bespoke report-card designs for schools that keep their own layout. Design files live in
                    <code>{{ $viewDir }}</code></p>
            </div>
        </div>

        @include('Admin.custom-report-cards._nav')

        @if($ready)

            @php
                $tplCollection = collect($templates);
                $activeCount = $tplCollection->filter(fn($t) => $t->is_active)->count();
                $missingCount = $tplCollection->filter(fn($t) => !$t->file_exists)->count();
            @endphp

            {{-- Stats --}}
            <div class="crc-stats">
                <div class="crc-stat">
                    <div class="crc-stat-icon ic-blue"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <div class="crc-stat-num">{{ $tplCollection->count() }}</div>
                        <div class="crc-stat-label">Registered</div>
                    </div>
                </div>
                <div class="crc-stat">
                    <div class="crc-stat-icon ic-green"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="crc-stat-num">{{ $activeCount }}</div>
                        <div class="crc-stat-label">Active</div>
                    </div>
                </div>
                <div class="crc-stat">
                    <div class="crc-stat-icon ic-amber"><i class="fas fa-file-import"></i></div>
                    <div>
                        <div class="crc-stat-num">{{ count($unregistered) }}</div>
                        <div class="crc-stat-label">Not registered</div>
                    </div>
                </div>
                <div class="crc-stat">
                    <div class="crc-stat-icon ic-red"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="crc-stat-num">{{ $missingCount }}</div>
                        <div class="crc-stat-label">Missing files</div>
                    </div>
                </div>
            </div>

            {{-- Sync panel --}}
            <div class="crc-panel crc-sync">
                <div class="crc-panel-head">
                    <div>
                        <h5>Design files on disk not yet registered ({{ count($unregistered) }})</h5>
                        <div class="text-muted small">Drop a new <code>&lt;slug&gt;.blade.php</code> into the folder, then sync.
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.custom-report-cards.sync') }}" id="sync-form">@csrf
                        <button type="button" class="btn btn-primary px-4" style="border-radius:10px;" onclick="confirmSync()">
                            <i class="fas fa-layer-group mr-1"></i> Scan &amp; register new designs
                        </button>
                    </form>
                </div>

                @if(count($unregistered))
                    <div class="crc-chips">
                        @foreach($unregistered as $slug => $m)
                            <div class="crc-chip">
                                <span class="slug">{{ $slug }}</span>
                                <span class="text-muted">—</span>
                                <span>{{ $m['name'] }}</span>
                                <span class="crc-badge crc-b-{{ $m['level'] }}">{{ $levels[$m['level']] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Registered designs --}}
            <div class="crc-section-title">
                <h5>Registered designs</h5>
            </div>

            @if(count($templates))
                <div class="crc-grid">
                    @foreach($templates as $t)
                        <form method="POST" action="{{ route('admin.custom-report-cards.templates.update', $t->id) }}"
                            class="crc-tpl {{ $t->is_active ? '' : 'is-off' }}">
                            @csrf @method('PUT')

                            <div class="crc-tpl-top">
                                <div>
                                    <span class="crc-badge crc-b-{{ $t->level }}">{{ $levels[$t->level] ?? $t->level }}</span>
                                    <div class="crc-tpl-slug"><i class="fas fa-file-code mr-1"></i>{{ $t->slug }}</div>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="active-{{ $t->id }}" name="is_active"
                                        value="1" {{ $t->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label small" for="active-{{ $t->id }}">Active</label>
                                </div>
                            </div>

                            @unless($t->file_exists)
                                <div class="crc-missing"><i class="fas fa-exclamation-circle"></i> File missing!</div>
                            @endunless

                            <div class="crc-tpl-body">
                                <div class="form-group mb-3">
                                    <label>Name</label>
                                    <input name="name" class="form-control" value="{{ $t->name }}" required maxlength="120">
                                </div>
                                <div class="form-group mb-0">
                                    <label>Description</label>
                                    <textarea name="description" rows="3" class="form-control"
                                        maxlength="1000">{{ $t->description }}</textarea>
                                </div>
                            </div>

                            <div class="crc-tpl-foot">
                                <div class="meta crc-meta" style="backgr" >
                                    <span class="schools" style="color: #2C29CA;"><i class="fas fa-school mr-1"></i>{{ $t->assignments_count }} school(s)</span>
                                </div>
                                <div class="crc-actions">
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('admin.custom-report-cards.studio', ['slug' => $t->slug]) }}">
                                        <i class="fas fa-eye mr-1"></i>Preview
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" onclick="confirmSave(this)">
                                        <i class="fas fa-save mr-1"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete({{ $t->id }}, '{{ addslashes($t->name) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <form id="del-{{ $t->id }}" method="POST"
                            action="{{ route('admin.custom-report-cards.templates.delete', $t->id) }}">
                            @csrf @method('DELETE')
                        </form>
                    @endforeach
                </div>
            @else
                <div class="crc-panel crc-empty">
                    <div class="em-icon"><i class="fas fa-folder-open"></i></div>
                    <p class="mb-0">No designs registered yet. Add a file and press “Scan &amp; register”.</p>
                </div>
            @endif

        @endif
    </div>
    </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSync() {
            Swal.fire({
                title: 'Scan & Register?',
                text: 'This will scan the folder for new design files and register them.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f6bff',
                cancelButtonColor: '#6b7385',
                confirmButtonText: 'Yes, scan now',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('sync-form').submit();
                }
            });
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Remove Design?',
                html: 'Are you sure you want to remove <strong>' + name + '</strong> from the registry?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d93636',
                cancelButtonColor: '#6b7385',
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('del-' + id).submit();
                }
            });
        }

        function confirmSave(button) {
            let form = button.closest('form');
            Swal.fire({
                title: 'Save Changes?',
                text: 'Do you want to save the changes to this report card design?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#13a561',
                cancelButtonColor: '#6b7385',
                confirmButtonText: 'Yes, save it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

@endsection