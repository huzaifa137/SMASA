@extends('layouts-side-bar.master')

@section('css')
    <style>
        .rc-gallery {
            padding: 1.5rem 2rem 3rem;
        }

        .rc-gallery h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: .25rem;
            color: #1e1e2d;
        }

        .rc-gallery>p {
            color: #6c7293;
            max-width: 720px;
            margin-bottom: 2rem;
        }

        .rc-gallery-section {
            margin-bottom: 2.5rem;
        }

        .rc-gallery-section h2 {
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #5351e4;
            margin-bottom: 1rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid #eceffc;
        }

        .rc-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.25rem;
        }

        .rc-gallery-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(30, 30, 45, .06);
            border: 1px solid #eef0f7;
            transition: transform .15s, box-shadow .15s;
        }

        .rc-gallery-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(30, 30, 45, .12);
        }

        .rc-gallery-card.is-default {
            border-color: #5351e4;
        }

        .rc-gallery-thumb {
            position: relative;
            width: 100%;
            aspect-ratio: 794 / 1123;
            background: #f4f5fa;
            overflow: hidden;
        }

        .rc-gallery-thumb iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 794px;
            height: 1123px;
            border: 0;
            pointer-events: none;
            transform: scale(var(--rc-thumb-scale, 0.29));
            transform-origin: top left;
        }

        .rc-gallery-card-body {
            padding: .9rem 1rem 1.1rem;
        }

        .rc-gallery-card-body h3 {
            font-size: .95rem;
            font-weight: 700;
            margin: 0 0 .4rem;
            color: #2d2d3d;
        }

        .badge {
            display: inline-block;
            font-size: .68rem;
            font-weight: 600;
            padding: .18rem .55rem;
            border-radius: 20px;
            background: #eef0fb;
            color: #5351e4;
            margin-right: .3rem;
            margin-bottom: .5rem;
        }

        .badge-default {
            background: #e6f8ee;
            color: #17a06d;
        }

        .rc-gallery-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: .5rem;
        }

        .rc-gallery-actions form {
            margin: 0;
        }

        .rc-gallery-actions button,
        .rc-gallery-actions a {
            display: inline-block;
            font-size: .8rem;
            font-weight: 600;
            padding: .4rem .75rem;
            border-radius: 8px;
            border: 1px solid #dcdfef;
            background: #fff;
            color: #4a4a68;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
        }

        .rc-gallery-actions a {
            background: #5351e4;
            border-color: #5351e4;
            color: #fff;
        }

        .rc-gallery-actions a:hover {
            background: #423fc9;
            color: #fff;
        }

        .rc-gallery-actions button:hover {
            background: #f4f5fa;
        }

        .rc-gallery-actions button.rc-btn-danger {
            color: #d9435e;
            border-color: #f3cdd6;
        }

        .rc-gallery-actions button.rc-btn-danger:hover {
            background: #fdecef;
        }

        .rc-gallery-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            border-bottom: 2px solid #eceffc;
            margin-bottom: 1rem;
            padding-bottom: .5rem;
        }

        .rc-gallery-section-head h2 {
            margin: 0;
            padding: 0;
            border: 0;
        }

        .rc-btn-restore {
            font-size: .8rem;
            font-weight: 600;
            padding: .4rem .75rem;
            border-radius: 8px;
            border: 1px solid #dcdfef;
            background: #fff;
            color: #5351e4;
            cursor: pointer;
        }

        .rc-btn-restore:hover {
            background: #eef0fb;
        }

        .rc-new-blank {
            margin-top: 1rem;
            padding: 1.5rem;
            background: #fff;
            border: 1px dashed #c8cdf0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .rc-new-blank h3 {
            flex-basis: 100%;
            font-size: .95rem;
            font-weight: 700;
            color: #2d2d3d;
            margin: 0 0 .25rem;
        }

        .rc-new-blank input,
        .rc-new-blank select {
            padding: .55rem .75rem;
            border-radius: 8px;
            border: 1px solid #dcdfef;
            font-size: .85rem;
            min-width: 200px;
        }

        .rc-new-blank button {
            padding: .55rem 1rem;
            border-radius: 8px;
            border: none;
            background: #1e1e2d;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .rc-new-blank button:hover {
            background: #333349;
        }
        .rc-danger-zone {
            margin-top: -.5rem;
            margin-bottom: 2rem;
            padding: .9rem 1.1rem;
            background: #fff8f8;
            border: 1px solid #f3cdd6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .rc-danger-zone p {
            margin: 0;
            font-size: .82rem;
            color: #8a4a56;
            max-width: 560px;
        }

        .rc-btn-reset-all {
            font-size: .82rem;
            font-weight: 700;
            padding: .55rem .9rem;
            border-radius: 8px;
            border: 1px solid #d9435e;
            background: #d9435e;
            color: #fff;
            cursor: pointer;
            white-space: nowrap;
        }

        .rc-btn-reset-all:hover {
            background: #c22e49;
        }
    </style>
@endsection

@section('content')
    <div class="rc-gallery">
        <h1>Report Card Designs</h1>
        <p>Pick one of the designs below — the same Classic, Modern and Minimal styles offered when generating pass slips —
            then customize it: drag a section wider or narrower (col-md-3 up to col-md-12) and drag it up or down to
            reorder. Your data stays exactly the same; only the layout changes. Whatever's marked <strong>Default</strong>
            is what teachers actually print with.</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @php
            $hasAnyOwnDesign = collect($templates)->flatten()->contains(fn ($t) => $t->school_id);
        @endphp
        @if($hasAnyOwnDesign)
            <div class="rc-danger-zone">
                <p><strong>Start over completely:</strong> permanently deletes every custom design your school has created
                    or duplicated, in every category, and puts Nursery, Primary, Secondary and Custom all back on the
                    system default (Modern, Classic, Minimal). This can't be undone.</p>
                <form method="POST" action="{{ route('report-templates.reset-all') }}"
                      onsubmit="return confirm('Delete ALL of your custom report card designs, in every category, and reset everything back to the system defaults? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="rc-btn-reset-all">Reset everything to system defaults</button>
                </form>
            </div>
        @endif


        @foreach(['nursery', 'primary', 'secondary', 'custom'] as $category)
            @if(!empty($templates[$category]))
                <section class="rc-gallery-section">
                    <div class="rc-gallery-section-head">
                        <h2>{{ ucfirst($category) }}</h2>
                        @php
                            $hasCustomDefault = collect($templates[$category])
                                ->contains(fn ($t) => $t->school_id && $t->is_default);
                        @endphp
                        @if($hasCustomDefault)
                            <form method="POST" action="{{ route('report-templates.restore-default', $category) }}"
                                  onsubmit="return confirm('Switch back to the system default {{ $category }} design (Modern, Classic or Minimal)? Your custom design stays saved — you can set it as default again any time.');">
                                @csrf
                                <button type="submit" class="rc-btn-restore">Restore default</button>
                            </form>
                        @endif
                    </div>
                    <div class="rc-gallery-grid">
                        @foreach($templates[$category] as $t)
                            <div class="rc-gallery-card {{ $t->is_default && $t->school_id ? 'is-default' : '' }}">
                                <div class="rc-gallery-thumb">
                                    {{-- Render a scaled-down live preview instead of a static screenshot --}}
                                    <iframe src="{{ route('report-templates.preview', $t) }}" loading="lazy"></iframe>
                                </div>
                                <div class="rc-gallery-card-body">
                                    <h3>{{ $t->name }}</h3>
                                    @if($t->school_id === null)
                                        <span class="badge">Starter template</span>
                                    @endif
                                    @if($t->is_default && $t->school_id)
                                        <span class="badge badge-default">Default</span>
                                    @endif
                                    <div class="rc-gallery-actions">
                                        @if($t->school_id === null)
                                            <form method="POST" action="{{ route('report-templates.duplicate', $t) }}">
                                                @csrf
                                                <input type="hidden" name="name" value="{{ $t->name }} (My Version)">
                                                <button type="submit">Use as starting point</button>
                                            </form>
                                        @else
                                            <a href="{{ route('report-templates.edit', $t) }}">Edit design</a>
                                            @unless($t->is_default)
                                                <form method="POST" action="{{ route('report-templates.set-default', $t) }}">
                                                    @csrf
                                                    <button type="submit">Set as default</button>
                                                </form>
                                            @endunless
                                            <form method="POST" action="{{ route('report-templates.destroy', $t) }}"
                                                  onsubmit="return confirm('Delete &quot;{{ $t->name }}&quot;? This can\'t be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rc-btn-danger">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </section>
            @endif
        @endforeach

        <form method="POST" action="{{ route('report-templates.store') }}" class="rc-new-blank">
            @csrf
            <h3>Advanced: start completely blank</h3>
            <p style="flex-basis:100%; margin:-.25rem 0 0; font-size:.8rem; color:#8a8fa8;">Not recommended for most schools
                — you'll build every section yourself. Duplicating Classic, Modern or Minimal above and resizing/reordering
                its sections is faster.</p>
            <input name="name" placeholder="e.g. Junior School — Custom" required>
            <select name="category">
                <option value="nursery">Nursery</option>
                <option value="primary" selected>Primary</option>
                <option value="secondary">Secondary</option>
                <option value="custom">Custom / Other</option>
            </select>
            <button type="submit">Create blank template</button>
        </form>
    </div>
    </div>
    </div>
    </div>
@endsection