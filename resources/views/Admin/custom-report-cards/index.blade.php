@extends('layouts-side-bar.master')

@section('content')
<div class="container-fluid pt-4">
    <h3 class="mb-1">Custom Report Cards</h3>
    <p class="text-muted mb-3">Bespoke report-card designs for schools that keep their own layout. Design files live in <code>{{ $viewDir }}</code>.</p>

    @include('Admin.custom-report-cards._nav')

    @if($ready)
    <div class="crc-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="mb-1">Design files on disk not yet registered ({{ count($unregistered) }})</h5>
                <div class="text-muted small">Drop a new <code>&lt;slug&gt;.blade.php</code> into the folder, then sync.</div>
            </div>
            <form method="POST" action="{{ route('admin.custom-report-cards.sync') }}">@csrf
                <button class="btn btn-primary">Scan &amp; register new designs</button>
            </form>
        </div>
        @if(count($unregistered))
            <ul class="mt-3 mb-0">
                @foreach($unregistered as $slug => $m)
                    <li><code>{{ $slug }}</code> — {{ $m['name'] }} <span class="crc-badge crc-b-{{ $m['level'] }}">{{ $levels[$m['level']] }}</span></li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="crc-card">
        <h5 class="mb-3">Registered designs</h5>
        @forelse($templates as $t)
            <form method="POST" action="{{ route('admin.custom-report-cards.templates.update', $t->id) }}" class="border rounded p-3 mb-3">
                @csrf @method('PUT')
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Name</label>
                        <input name="name" class="form-control" value="{{ $t->name }}" required maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="small font-weight-bold">Description</label>
                        <input name="description" class="form-control" value="{{ $t->description }}" maxlength="1000">
                    </div>
                    <div class="col-md-2">
                        <span class="crc-badge crc-b-{{ $t->level }}">{{ $levels[$t->level] ?? $t->level }}</span>
                        <div class="small text-muted mt-1"><code>{{ $t->slug }}</code></div>
                        @unless($t->file_exists)<div class="small text-danger">File missing!</div>@endunless
                    </div>
                    <div class="col-md-1">
                        <label class="small mb-0"><input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}> Active</label>
                        <div class="small text-muted">{{ $t->assignments_count }} school(s)</div>
                    </div>
                    <div class="col-md-2 text-right">
                        <button class="btn btn-sm btn-success">Save</button>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.custom-report-cards.studio', ['slug' => $t->slug]) }}">Preview</a>
                        <button type="submit" form="del-{{ $t->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this design from the registry?')">Delete</button>
                    </div>
                </div>
            </form>
            <form id="del-{{ $t->id }}" method="POST" action="{{ route('admin.custom-report-cards.templates.delete', $t->id) }}">@csrf @method('DELETE')</form>
        @empty
            <p class="text-muted mb-0">No designs registered yet. Add a file and press “Scan &amp; register”.</p>
        @endforelse
    </div>
    @endif
</div>
</div>
</div>
@endsection
