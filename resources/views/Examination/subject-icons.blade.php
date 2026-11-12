@extends('layouts-side-bar.master')

@section('content')
    <div class="side-app">

        <x-exam-hero icon="fa-icons" title="Subject Icons"
            subtitle="Choose the picture each nursery subject shows on the report card — from the system library, or upload your own.">
            <x-slot:actions>
                <a href="{{ route('examination.index') }}" class="exh-btn"><i class="fas fa-arrow-left"></i><span>Back to Dashboard</span></a>
            </x-slot:actions>
        </x-exam-hero>

        @if (session('success'))
            <div class="ex-alert ex-alert--success"><i class="fas fa-check-circle me-2 mr-2"></i>{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="ex-alert ex-alert--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="ex-alert ex-alert--error">{{ $errors->first() }}</div>
        @endif
        @if (!$ready)
            <div class="ex-alert ex-alert--warning">A database update is pending. Run <code>php artisan migrate</code> and reload this page.</div>
        @endif

        <style>
            .si-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem}
            .si-card{border:1px solid #E6E7EE;border-radius:14px;padding:1rem;background:#fff}
            .si-head{display:flex;align-items:center;gap:.9rem}
            .si-img{width:56px;height:56px;border-radius:12px;background:#F5F6FF;border:1px solid #E6E7EE;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
            .si-img img{max-width:100%;max-height:100%;object-fit:contain}
            .si-name{font-weight:700;color:#1a1a2e}.si-sub{font-size:.75rem;color:#6B7280}
            .si-card details{margin-top:.8rem}.si-card summary{cursor:pointer;font-size:.82rem;font-weight:600;color:#2C29CA;list-style:none}
            .si-card summary::-webkit-details-marker{display:none}
            .si-lib{display:grid;grid-template-columns:repeat(auto-fill,minmax(74px,1fr));gap:.5rem;margin:.8rem 0}
            .si-lib label{border:2px solid #E6E7EE;border-radius:10px;padding:.4rem;text-align:center;cursor:pointer;font-size:.62rem;color:#374151;display:block;margin:0}
            .si-lib input{position:absolute;opacity:0;pointer-events:none}
            .si-lib img{width:36px;height:36px;object-fit:contain;display:block;margin:0 auto .2rem}
            .si-lib label:has(input:checked){border-color:#2C29CA;background:#F0F0FF}
        </style>

        @forelse ($groups as $g)
            <div class="ex-card">
                <h5 class="ex-card-title"><i class="fas fa-chalkboard-teacher" style="color:#2C29CA"></i> {{ $g['class'] }}</h5>
                <div class="si-grid">
                    @foreach ($g['subjects'] as $sub)
                        <div class="si-card">
                            <div class="si-head">
                                <div class="si-img">
                                    @if ($sub['chosen'] ?? $sub['auto'])
                                        <img src="{{ $sub['chosen'] ?? $sub['auto'] }}" alt="{{ $sub['name'] }}">
                                    @else
                                        <i class="fas fa-image" style="color:#9CA3AF"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="si-name">{{ $sub['name'] }}</div>
                                    <div class="si-sub">{{ $sub['chosen'] ? 'Your chosen icon' : ($sub['auto'] ? 'Automatic icon' : 'No icon') }}</div>
                                </div>
                            </div>
                            <details>
                                <summary><i class="fas fa-pen me-1 mr-1"></i> Change icon</summary>
                                <form method="POST" action="{{ route('examination.subject-icons.update', $sub['id']) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="si-lib">
                                        @foreach ($library as $ic)
                                            <label title="{{ $ic['label'] }}">
                                                <input type="radio" name="icon" value="{{ $ic['file'] }}">
                                                <img src="{{ $ic['url'] }}" alt="">{{ $ic['label'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <label class="ex-label">…or upload your own (PNG, JPG or WEBP, up to 512 KB)</label>
                                    <input type="file" name="file" class="ex-input" accept=".png,.jpg,.jpeg,.webp">
                                    <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;margin:.8rem 0;cursor:pointer;">
                                        <input type="checkbox" name="apply_all" value="1" checked style="width:16px;height:16px;"> Use it for this subject in every nursery class
                                    </label>
                                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                        <button type="submit" class="ex-btn ex-btn--primary ex-btn--sm"><i class="fas fa-check"></i> Save icon</button>
                                        @if ($sub['chosen'])
                                            <button type="submit" name="reset" value="1" class="ex-btn ex-btn--ghost ex-btn--sm">Back to automatic</button>
                                        @endif
                                    </div>
                                </form>
                            </details>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="ex-card"><p class="ex-help" style="margin:0;">No nursery subjects found for this school yet.</p></div>
        @endforelse
    </div>
@endsection
