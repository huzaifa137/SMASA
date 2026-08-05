@extends('parents.layout')

@section('title', 'My Children')

@section('content')
    <div class="pp-card" style="background: linear-gradient(135deg, var(--brand), var(--brand-mid)); color: #fff;">
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.3rem;">
            <i class="fas fa-people-roof me-2 opacity-75"></i> Welcome back
        </h2>
        <p style="opacity: 0.85; font-size: 0.88rem; margin: 0;">
            Select a child below to see their results, attendance and fee statement.
        </p>
    </div>

    @forelse ($children as $schoolId => $kids)
        <div class="pp-card">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--gray-500); margin-bottom: 1rem;">
                <i class="fas fa-school me-1"></i> {{ $kids->first()->school_name ?? 'School' }}
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem;">
                @foreach ($kids as $child)
                    <a href="{{ route('parents.child', $child->id) }}" style="text-decoration: none; color: inherit;">
                        <div style="border: 1.5px solid var(--brand-pale); border-radius: 0.9rem; padding: 1.1rem; transition: all .2s ease;"
                            onmouseover="this.style.borderColor='var(--brand)'; this.style.background='var(--brand-ultra)';"
                            onmouseout="this.style.borderColor='var(--brand-pale)'; this.style.background='#fff';">
                            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom: 0.6rem;">
                                <div style="width:42px;height:42px;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;">
                                    {{ strtoupper(substr($child->firstname, 0, 1) . substr($child->lastname, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.95rem;">{{ $child->firstname }} {{ $child->lastname }}</div>
                                    <div style="font-size:0.75rem;color:var(--gray-500);">{{ $child->admission_number }}</div>
                                </div>
                            </div>
                            <div style="font-size:0.78rem;color:var(--gray-700);">
                                <i class="fas fa-chalkboard me-1"></i> {{ $child->class_name }}
                                @if ($child->stream_name) — {{ $child->stream_name }} @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="pp-card pp-empty">
            <i class="fas fa-user-slash"></i>
            <h6>No students linked to this phone number yet</h6>
            <p class="mb-0">If this doesn't look right, please contact your child's school to confirm the contact number on file.</p>
        </div>
    @endforelse
@endsection
