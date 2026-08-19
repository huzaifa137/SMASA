@extends('layouts-side-bar.master')

@section('css')
<style>
    .rc-gallery { padding: 1.5rem 2rem 3rem; }
    .rc-gallery h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: .25rem; color: #1e1e2d; }
    .rc-gallery > p { color: #6c7293; max-width: 720px; margin-bottom: 2rem; }

    .rc-gallery-section { margin-bottom: 2.5rem; }
    .rc-gallery-section h2 { font-size: 1.1rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: #5351e4; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 2px solid #eceffc; }

    .rc-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1.25rem; }

    .rc-gallery-card { background: #fff; border-radius: 14px; overflow: hidden;
        box-shadow: 0 2px 10px rgba(30,30,45,.06); border: 1px solid #eef0f7; transition: transform .15s, box-shadow .15s; }
    .rc-gallery-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(30,30,45,.12); }
    .rc-gallery-card.is-default { border-color: #5351e4; }

    .rc-gallery-thumb { position: relative; width: 100%; aspect-ratio: 794 / 1123; background: #f4f5fa; overflow: hidden; }
    .rc-gallery-thumb iframe { position: absolute; top: 0; left: 0;
        width: 794px; height: 1123px; border: 0; pointer-events: none;
        transform: scale(var(--rc-thumb-scale, 0.29)); transform-origin: top left; }

    .rc-gallery-card-body { padding: .9rem 1rem 1.1rem; }
    .rc-gallery-card-body h3 { font-size: .95rem; font-weight: 700; margin: 0 0 .4rem; color: #2d2d3d; }

    .badge { display: inline-block; font-size: .68rem; font-weight: 600; padding: .18rem .55rem; border-radius: 20px;
        background: #eef0fb; color: #5351e4; margin-right: .3rem; margin-bottom: .5rem; }
    .badge-default { background: #e6f8ee; color: #17a06d; }

    .rc-gallery-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .5rem; }
    .rc-gallery-actions form { margin: 0; }
    .rc-gallery-actions button, .rc-gallery-actions a {
        display: inline-block; font-size: .8rem; font-weight: 600; padding: .4rem .75rem; border-radius: 8px;
        border: 1px solid #dcdfef; background: #fff; color: #4a4a68; cursor: pointer; text-decoration: none; transition: all .15s;
    }
    .rc-gallery-actions a { background: #5351e4; border-color: #5351e4; color: #fff; }
    .rc-gallery-actions a:hover { background: #423fc9; color: #fff; }
    .rc-gallery-actions button:hover { background: #f4f5fa; }

    .rc-new-blank { margin-top: 1rem; padding: 1.5rem; background: #fff; border: 1px dashed #c8cdf0; border-radius: 14px;
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .rc-new-blank h3 { flex-basis: 100%; font-size: .95rem; font-weight: 700; color: #2d2d3d; margin: 0 0 .25rem; }
    .rc-new-blank input, .rc-new-blank select {
        padding: .55rem .75rem; border-radius: 8px; border: 1px solid #dcdfef; font-size: .85rem; min-width: 200px;
    }
    .rc-new-blank button {
        padding: .55rem 1rem; border-radius: 8px; border: none; background: #1e1e2d; color: #fff; font-weight: 600; cursor: pointer;
    }
    .rc-new-blank button:hover { background: #333349; }
</style>
@endsection

@section('content')
<div class="rc-gallery">
    <h1>Report Card Designs</h1>
    <p>Start from a template for your school level, then drag, resize, and restyle every element — logos, tables, remarks, signatures — until it's exactly yours. Whatever's marked <strong>Default</strong> is what teachers actually print with.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @foreach(['nursery','primary','secondary','custom'] as $category)
        @if(!empty($templates[$category]))
        <section class="rc-gallery-section">
            <h2>{{ ucfirst($category) }}</h2>
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
        <h3>Or start from a blank canvas</h3>
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
