{{-- Payment warning shown at the top of every school page (above the page content):
       • an invoice is OVERDUE            → red, cannot be dismissed
       • an invoice falls due within 10 days → amber/blue, can be hidden for the browser session
     Only schools' own users with the Billing feature see it; platform admins browsing a school do not.
     Never allowed to break a page: any billing/DB problem simply shows nothing. --}}
@php
    $__notices = collect();
    $__cur = 'UGX';
    try {
        if (session()->has('LoggedSchool') && !session()->has('LoggedAdmin')
            && !request()->routeIs('school.billing.*')
            && \App\Helpers\PermissionHelper::canFeature('view_billing')) {
            $__svc = app(\App\Services\BillingService::class);
            $__notices = $__svc->schoolNotices((int) session('LoggedSchool'));
            $__cur = $__notices->isNotEmpty() ? ($__svc->settings()->currency ?: 'UGX') : 'UGX';
        }
    } catch (\Throwable $e) {
        $__notices = collect();
    }
@endphp
@if ($__notices->isNotEmpty())
    @php
        $__top   = $__notices->first();
        $__more  = $__notices->count() - 1;
        $__over  = $__top['overdue'];
        $__days  = $__top['days'];
        $__tone  = $__over ? 'over' : ($__days <= 3 ? 'soon' : 'info');
        $__key   = 'smasa-bn-' . $__top['invoice']->id . '-' . $__top['due']->format('Ymd');
        $__state = $__over ? 'is overdue' : ($__days === 0 ? 'is due today' : 'is due in ' . $__days . ' ' . \Illuminate\Support\Str::plural('day', $__days));
        $__when  = $__over
            ? 'was due ' . $__top['due']->format('j M Y') . ' — ' . $__days . ' ' . \Illuminate\Support\Str::plural('day', $__days) . ' ago'
            : 'due ' . $__top['due']->format('j M Y');
    @endphp
    <style>
        .smasa-bn { display: flex; align-items: center; gap: .9rem; margin: 0 0 1rem; padding: .85rem 1.1rem; border-radius: 14px; border: 1px solid; border-left-width: 5px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05); position: relative; }
        .smasa-bn.over { border-color: #fecaca; border-left-color: #dc2626; background: #fff5f5; }
        .smasa-bn.soon { border-color: #fde68a; border-left-color: #f59e0b; background: #fffbeb; }
        .smasa-bn.info { border-color: #c7d2fe; border-left-color: #2f2ccb; background: #f5f6ff; }
        .smasa-bn-ico { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; color: #fff; }
        .smasa-bn.over .smasa-bn-ico { background: #dc2626; } .smasa-bn.soon .smasa-bn-ico { background: #f59e0b; } .smasa-bn.info .smasa-bn-ico { background: #2f2ccb; }
        .smasa-bn-body { flex: 1 1 auto; min-width: 0; }
        .smasa-bn-title { font-size: .95rem; color: #0f172a; line-height: 1.35; }
        .smasa-bn-sub { font-size: .8rem; color: #64748b; margin-top: .1rem; }
        .smasa-bn-act { flex: 0 0 auto; display: flex; gap: .45rem; align-items: center; flex-wrap: wrap; }
        .smasa-bn-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .9rem; border-radius: 10px; font-size: .82rem; font-weight: 600; text-decoration: none !important; border: 1px solid transparent; white-space: nowrap; }
        .smasa-bn.over .smasa-bn-btn.main { background: #dc2626; color: #fff; } .smasa-bn.soon .smasa-bn-btn.main { background: #d97706; color: #fff; } .smasa-bn.info .smasa-bn-btn.main { background: #2f2ccb; color: #fff; }
        .smasa-bn-btn.alt { background: #fff; color: #334155; border-color: #cbd5e1; }
        .smasa-bn-x { background: none; border: 0; color: #94a3b8; font-size: 1.3rem; line-height: 1; padding: 0 .2rem; cursor: pointer; }
        .smasa-bn-x:hover { color: #475569; }
        @media (max-width: 640px) { .smasa-bn { flex-wrap: wrap; } .smasa-bn-act { width: 100%; } }
    </style>
    <div class="smasa-bn mt-5 {{ $__tone }}" id="smasaBillingNotice" role="alert" data-key="{{ $__key }}" data-dismissible="{{ $__over ? 0 : 1 }}">
        <span class="smasa-bn-ico"><i class="fas {{ $__over ? 'fa-triangle-exclamation' : 'fa-clock' }}"></i></span>
        <div class="smasa-bn-body">
            <div class="smasa-bn-title"><strong>{{ $__top['term'] }}: {{ $__cur }} {{ number_format($__top['amount'], 0) }}</strong> {{ $__state }} — {{ $__when }}.</div>
            <div class="smasa-bn-sub">
                Invoice {{ $__top['invoice']->invoice_number }}@if ($__top['invoice']->status === 'partial') · partially paid @endif
                @if ($__more > 0) · and {{ $__more }} more unpaid {{ \Illuminate\Support\Str::plural('invoice', $__more) }} need attention @endif
            </div>
        </div>
        <div class="smasa-bn-act">
            <a class="smasa-bn-btn main" href="{{ route('school.billing.show', $__top['invoice']->id) }}"><i class="fas fa-file-invoice-dollar"></i> View invoice</a>
            @if ($__more > 0)<a class="smasa-bn-btn alt" href="{{ route('school.billing.index') }}">All invoices</a>@endif
            @unless ($__over)<button type="button" class="smasa-bn-x" id="smasaBnClose" title="Hide for now" aria-label="Hide">&times;</button>@endunless
        </div>
    </div>
    <script>
        (function () {
            var el = document.getElementById('smasaBillingNotice');
            if (!el || el.dataset.dismissible !== '1') return;
            try { if (sessionStorage.getItem(el.dataset.key)) { el.style.display = 'none'; return; } } catch (e) {}
            var x = document.getElementById('smasaBnClose');
            if (x) x.addEventListener('click', function () {
                el.style.display = 'none';
                try { sessionStorage.setItem(el.dataset.key, '1'); } catch (e) {}
            });
        })();
    </script>
@endif
