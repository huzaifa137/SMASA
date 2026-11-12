{{--
    Examination banner — the same look as the Audit Trail banner, shared by the Examination pages.

    <x-exam-hero icon="fa-pen-to-square" title="Mid-Term III" subtitle="Marks entry">
        <x-slot:chips>   small status pills next to the title   </x-slot:chips>
        <x-slot:actions> <a class="exh-btn exh-btn--primary" href="…">…</a> </x-slot:actions>
        <x-slot:features> <x-exam-hero-tile icon="fa-tag" title="Type" desc="Mid-term"/> … </x-slot:features>
        …anything else (progress bars, notes) goes in the default slot…
    </x-exam-hero>
--}}
@props(['icon' => 'fa-clipboard-list', 'title' => '', 'subtitle' => null])

@once
    <style>
        .exh-hero{background:linear-gradient(135deg,#000 0%,#070189 100%);border-radius:20px;padding:1.5rem 2rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,.08);box-shadow:0 4px 24px rgba(7,1,137,.3);color:#fff}
        .exh-main{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
        .exh-left{display:flex;align-items:center;gap:1.1rem;min-width:0}
        .exh-icon{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;flex-shrink:0;box-shadow:0 4px 12px rgba(102,126,234,.3)}
        .exh-info h4{font-size:1.35rem;font-weight:700;color:#fff !important;margin:0 0 .15rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;word-break:break-word}
        .exh-info p{color:rgba(255,255,255,.75) !important;font-size:.9rem;margin:0;word-break:break-word}
        .exh-actions{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center}
        .exh-actions form{margin:0}
        .exh-hero .exh-btn{display:inline-flex;align-items:center;gap:.55rem;padding:.6rem 1.25rem;border-radius:99px;font-size:.85rem;font-weight:600;line-height:1.2;white-space:nowrap;cursor:pointer;text-decoration:none !important;transition:all .2s ease;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);color:#fff !important}
        .exh-hero .exh-btn:hover,.exh-hero .exh-btn:focus{background:rgba(255,255,255,.24);border-color:rgba(255,255,255,.5);color:#fff !important;transform:translateY(-1px);outline:none}
        .exh-hero .exh-btn--primary{background:#fff;border-color:#fff;color:#1e1b4b !important}
        .exh-hero .exh-btn--primary:hover,.exh-hero .exh-btn--primary:focus{background:#eef0ff;border-color:#eef0ff;color:#1e1b4b !important}
        .exh-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,.08)}
        .exh-feature{display:flex;align-items:center;gap:.75rem;padding:.35rem .5rem;border-radius:10px}
        .exh-feature-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;border:1px solid rgba(255,255,255,.06)}
        .exh-feature-title{font-size:.8rem;font-weight:600;color:#fff;line-height:1.2}
        .exh-feature-desc{font-size:.72rem;color:rgba(255,255,255,.6)}
        .exh-chip{display:inline-block;padding:.12rem .6rem;border-radius:99px;font-size:.7rem;font-weight:700;background:rgba(255,255,255,.15);color:#fff}
        .exh-chip--active{background:#16a34a}.exh-chip--marks_entry{background:#d97706}.exh-chip--closed{background:#dc2626}.exh-chip--results_released{background:#4f46e5}.exh-chip--draft{background:#64748b}
        .exh-extra{margin-top:1.1rem}
        /* ── shared page furniture (cards, fields, buttons, tables) so Examination pages look alike ── */
        .ex-card{background:#fff;border:1px solid #E6E7EE;border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;box-shadow:0 1px 3px rgba(16,24,40,.04)}
        .ex-card-title{display:flex;align-items:center;gap:.6rem;font-size:1rem;font-weight:700;color:#1a1a2e;margin:0 0 1rem}
        .ex-step{width:26px;height:26px;border-radius:50%;background:#2C29CA;color:#fff;font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
        .ex-label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.35rem}
        .ex-input{width:100%;min-height:44px;padding:.55rem .9rem;border:1px solid #D1D5DB;border-radius:10px;background:#fff;font-size:.9rem;color:#111827;transition:border-color .15s,box-shadow .15s}
        .ex-input:focus{outline:none;border-color:#2C29CA;box-shadow:0 0 0 3px rgba(44,41,202,.15)}
        select.ex-input{appearance:none;-webkit-appearance:none;padding-right:2.3rem;background:#fff url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'><path d='M1 1l5 5 5-5' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round'/></svg>") no-repeat right .9rem center}
        input[type=file].ex-input{padding:.4rem .5rem;background:#F9FAFB;border-style:dashed}
        input[type=file].ex-input::file-selector-button{margin-right:.8rem;border:0;border-radius:8px;padding:.5rem .9rem;background:#2C29CA;color:#fff;font-weight:600;cursor:pointer}
        .ex-btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;min-height:44px;padding:.6rem 1.2rem;border-radius:10px;font-weight:600;font-size:.88rem;border:1px solid transparent;cursor:pointer;text-decoration:none !important;transition:all .15s}
        .ex-btn--primary{background:#2C29CA;color:#fff !important}.ex-btn--primary:hover{background:#2220a8}
        .ex-btn--success{background:#16a34a;color:#fff !important}.ex-btn--success:hover{background:#15803d}
        .ex-btn--ghost{background:#fff;border-color:#D1D5DB;color:#374151 !important}.ex-btn--ghost:hover{background:#F3F4F6}
        .ex-btn--sm{min-height:34px;padding:.35rem .85rem;font-size:.8rem}
        .ex-btn[disabled]{opacity:.5;cursor:not-allowed;pointer-events:none}
        .ex-form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;align-items:end}
        .ex-help{font-size:.78rem;color:#6B7280;margin-top:.6rem;line-height:1.5}
        .ex-alert{border-radius:12px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.9rem;border:1px solid transparent}
        .ex-alert--success{background:#ECFDF5;border-color:#A7F3D0;color:#065F46}.ex-alert--error{background:#FEF2F2;border-color:#FECACA;color:#991B1B}
        .ex-alert--warning{background:#FFFBEB;border-color:#FDE68A;color:#92400E}.ex-alert--info{background:#EFF6FF;border-color:#BFDBFE;color:#1E40AF}
        .ex-pills{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
        .ex-pill{padding:.3rem .8rem;border-radius:99px;font-size:.78rem;font-weight:600;background:#F3F4F6;color:#374151}
        .ex-pill--ok{background:#DCFCE7;color:#166534}.ex-pill--bad{background:#FEE2E2;color:#991B1B}.ex-pill--warn{background:#FEF3C7;color:#92400E}.ex-pill--info{background:#E0E7FF;color:#3730A3}
        .ex-table-wrap{overflow:auto;max-height:340px;border:1px solid #E6E7EE;border-radius:10px}
        .ex-table{width:100%;border-collapse:collapse;font-size:.85rem}
        .ex-table th{position:sticky;top:0;background:#F9FAFB;text-align:left;padding:.6rem .8rem;font-size:.7rem;letter-spacing:.04em;text-transform:uppercase;color:#6B7280;border-bottom:1px solid #E5E7EB}
        .ex-table td{padding:.55rem .8rem;border-bottom:1px solid #F1F2F6;vertical-align:middle}
        .ex-table tr:last-child td{border-bottom:0}
        .ex-right{text-align:right}
        @media (max-width:768px){.exh-hero{padding:1.25rem}.exh-main{flex-direction:column;align-items:stretch}.exh-actions{flex-direction:column;align-items:stretch}.exh-hero .exh-btn{justify-content:center;width:100%}}
    </style>
@endonce

<div class="exh-hero">
    <div class="exh-main">
        <div class="exh-left">
            <div class="exh-icon"><i class="fas {{ $icon }}"></i></div>
            <div class="exh-info">
                <h4>{{ $title }} {{ $chips ?? '' }}</h4>
                @if ($subtitle)
                    <p>{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if (isset($actions) && !$actions->isEmpty())
            <div class="exh-actions">{{ $actions }}</div>
        @endif
    </div>
    @if (isset($features) && !$features->isEmpty())
        <div class="exh-features">{{ $features }}</div>
    @endif
    @if (!$slot->isEmpty())
        <div class="exh-extra">{{ $slot }}</div>
    @endif
</div>
