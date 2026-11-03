{{-- resources/views/notifications/routing.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .nr-wrap { max-width: 1000px; margin: 0 auto; }
        .nr-hero { background: linear-gradient(135deg, #2f2ccb, #5351e4); color: #fff; border-radius: 16px; padding: 1.4rem 1.6rem; margin-bottom: 1.25rem; }
        .nr-hero h2 { margin: 0; font-size: 1.35rem; font-weight: 700; color: #fff; }
        .nr-hero p { margin: .3rem 0 0; opacity: .88; font-size: .9rem; max-width: 720px; }
        .nr-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.15rem 1.35rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .nr-head { display: flex; gap: .8rem; align-items: flex-start; }
        .nr-ico { width: 40px; height: 40px; border-radius: 12px; background: rgba(47,44,203,.1); color: #2f2ccb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .nr-head h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; }
        .nr-head .desc { color: #64748b; font-size: .83rem; margin-top: .15rem; }
        .nr-modes { display: flex; gap: .5rem; flex-wrap: wrap; margin: .9rem 0 .2rem; }
        .nr-mode { display: flex; align-items: center; gap: .45rem; padding: .45rem .8rem; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: .85rem; font-weight: 600; color: #334155; }
        .nr-mode input { margin: 0; }
        .nr-mode.active { border-color: #2f2ccb; background: rgba(47,44,203,.06); color: #2f2ccb; }
        .nr-mode.off.active { border-color: #dc2626; background: rgba(220,38,38,.06); color: #b91c1c; }
        .nr-reach { font-size: .8rem; color: #475569; margin-top: .55rem; }
        .nr-reach b { color: #0f172a; }
        .nr-custom { display: none; margin-top: .9rem; border-top: 1px dashed #e2e8f0; padding-top: .9rem; }
        .nr-custom.show { display: block; }
        .nr-sub { font-size: .78rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .03em; margin: 0 0 .4rem; }
        .nr-chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .9rem; }
        .nr-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .65rem; border: 1.5px solid #e2e8f0; border-radius: 999px; font-size: .8rem; cursor: pointer; }
        .nr-chip input { margin: 0; }
        .nr-chip.sel { border-color: #2f2ccb; background: rgba(47,44,203,.07); }
        .nr-chip small { color: #94a3b8; }
        .nr-chip .warn { color: #d97706; }
        .nr-search { width: 100%; padding: .5rem .7rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: .88rem; margin-bottom: .5rem; }
        .nr-people { max-height: 190px; overflow-y: auto; }
        .nr-note { background: #fffbeb; color: #92400e; border-radius: 10px; padding: .6rem .8rem; font-size: .8rem; margin-bottom: .75rem; }
        .nr-alert { padding: .7rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem; }
        .nr-alert.ok { background: rgba(5,150,105,.1); color: #047857; }
        .nr-alert.err { background: rgba(220,38,38,.08); color: #b91c1c; }
        .nr-btn { background: #2f2ccb; color: #fff; border: 0; border-radius: 10px; padding: .75rem 1.6rem; font-weight: 700; cursor: pointer; }
        .nr-btn:hover { background: #2421a8; }
        .nr-help { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1rem 1.2rem; margin-bottom: 1.25rem; font-size: .85rem; color: #475569; }
        .nr-help b { color: #0f172a; }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-5 nr-wrap">

        <div class="nr-hero">
            <h2><i class="fas fa-user-check"></i> Who Gets Notified</h2>
            <p>Choose, for each automatic notification, who in your school receives it: the default people, the roles
                or individuals you pick, or nobody.</p>
        </div>

        @if(session('success')) <div class="nr-alert ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> @endif
        @if(session('error')) <div class="nr-alert err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div> @endif

        <div class="nr-help">
            <b>How “Default” works:</b> a staff member is notified when their role can open the matching area
            (e.g. Finance for fee payments, Library for book issues) <i>and</i> can open “My Notifications”.
            @unless($schoolHasRoles)
                Your school has not created any roles yet, so every active staff member gets everything until you do.
            @endunless
            A staff member whose role cannot open “My Notifications” is marked <i class="fas fa-bell-slash warn"
                style="color:#d97706"></i> below: they would not see a custom notification until you give their role
            that right under User Rights.
        </div>

        <form method="POST" action="{{ route('notifications.routing.update') }}" id="routingForm">
            @csrf

            @foreach($events as $key => $event)
                @php
                    $rule = $rules->get($key);
                    $mode = old("rules.$key.mode", $rule->mode ?? 'default');
                    $selRoles = collect(old("rules.$key.role_ids", $rule->role_ids ?? []))->map(fn($i) => (int) $i)->all();
                    $selPeople = collect(old("rules.$key.teacher_ids", $rule->teacher_ids ?? []))->map(fn($i) => (int) $i)->all();
                    $isStaff = $event['audience'] === 'staff';
                @endphp

                <div class="nr-card" data-event="{{ $key }}">
                    <div class="nr-head">
                        <div class="nr-ico"><i class="fas {{ $event['icon'] }}"></i></div>
                        <div>
                            <h3>{{ $event['label'] }}</h3>
                            <div class="desc">{{ $event['description'] }}</div>
                        </div>
                    </div>

                    <div class="nr-modes">
                        <label class="nr-mode {{ $mode === 'default' ? 'active' : '' }}">
                            <input type="radio" name="rules[{{ $key }}][mode]" value="default" {{ $mode === 'default' ? 'checked' : '' }}>
                            {{ $isStaff ? 'Default' : 'On' }}
                        </label>
                        @if($isStaff)
                            <label class="nr-mode {{ $mode === 'custom' ? 'active' : '' }}">
                                <input type="radio" name="rules[{{ $key }}][mode]" value="custom" {{ $mode === 'custom' ? 'checked' : '' }}>
                                Choose roles / people
                            </label>
                        @endif
                        <label class="nr-mode off {{ $mode === 'disabled' ? 'active' : '' }}">
                            <input type="radio" name="rules[{{ $key }}][mode]" value="disabled" {{ $mode === 'disabled' ? 'checked' : '' }}>
                            Off
                        </label>
                    </div>

                    @if($isStaff && isset($reach[$key]))
                        <div class="nr-reach">
                            @if($reach[$key]['count'] === 0)
                                Currently reaches <b>nobody</b>.
                            @else
                                Currently reaches <b>{{ $reach[$key]['count'] }}</b>
                                {{ \Illuminate\Support\Str::plural('person', $reach[$key]['count']) }}:
                                {{ implode(', ', $reach[$key]['names']) }}{{ $reach[$key]['count'] > count($reach[$key]['names']) ? '…' : '' }}
                            @endif
                        </div>
                    @endif

                    @if($isStaff)
                        <div class="nr-custom {{ $mode === 'custom' ? 'show' : '' }}">
                            <p class="nr-sub">Everyone holding these roles</p>
                            <div class="nr-chips">
                                @forelse($roles as $role)
                                    <label class="nr-chip {{ in_array($role->id, $selRoles) ? 'sel' : '' }}">
                                        <input type="checkbox" name="rules[{{ $key }}][role_ids][]" value="{{ $role->id }}"
                                            {{ in_array($role->id, $selRoles) ? 'checked' : '' }}>
                                        {{ $role->name }}
                                    </label>
                                @empty
                                    <span style="font-size:.82rem;color:#94a3b8">No roles yet. Create them under User Rights.</span>
                                @endforelse
                            </div>

                            <p class="nr-sub">…and these individual people</p>
                            <input type="text" class="nr-search" placeholder="Search staff…" oninput="nrFilter(this)">
                            <div class="nr-chips nr-people">
                                @foreach($staff as $person)
                                    <label class="nr-chip {{ in_array((int) $person->id, $selPeople) ? 'sel' : '' }}"
                                        data-name="{{ strtolower($person->name) }}">
                                        <input type="checkbox" name="rules[{{ $key }}][teacher_ids][]" value="{{ $person->id }}"
                                            {{ in_array((int) $person->id, $selPeople) ? 'checked' : '' }}>
                                        {{ $person->name }}
                                        @if($person->role_name) <small>{{ $person->role_name }}</small> @endif
                                        @unless($person->can_open_bell)
                                            <i class="fas fa-bell-slash warn" title="Their role cannot open My Notifications"></i>
                                        @endunless
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="nr-btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
@endsection

@section('js')
    <script>
        document.querySelectorAll('.nr-card').forEach(card => {
            const custom = card.querySelector('.nr-custom');
            card.querySelectorAll('.nr-mode input').forEach(radio => {
                radio.addEventListener('change', () => {
                    card.querySelectorAll('.nr-mode').forEach(l => l.classList.toggle('active', l.querySelector('input').checked));
                    if (custom) custom.classList.toggle('show', radio.value === 'custom' && radio.checked);
                });
            });
            card.querySelectorAll('.nr-chip input').forEach(cb => {
                cb.addEventListener('change', () => cb.closest('.nr-chip').classList.toggle('sel', cb.checked));
            });
        });

        function nrFilter(input) {
            const q = input.value.trim().toLowerCase();
            input.parentElement.querySelectorAll('.nr-people .nr-chip').forEach(chip => {
                chip.style.display = !q || (chip.dataset.name || '').includes(q) ? '' : 'none';
            });
        }

        // Confirm before switching a notification fully off.
        document.getElementById('routingForm').addEventListener('submit', function (e) {
            const offs = [];
            document.querySelectorAll('.nr-card').forEach(card => {
                const checked = card.querySelector('.nr-mode.off input:checked');
                if (checked) offs.push(card.querySelector('h3').textContent.trim());
            });
            if (!offs.length || !window.Swal || this.dataset.confirmed) return;
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Switch off ' + offs.length + ' notification' + (offs.length > 1 ? 's' : '') + '?',
                html: '<div style="text-align:left">Nobody will be told about:<br>• ' + offs.join('<br>• ') + '</div>',
                showCancelButton: true,
                confirmButtonText: 'Save anyway',
                cancelButtonText: 'Go back'
            }).then(r => { if (r.isConfirmed) { this.dataset.confirmed = '1'; this.submit(); } });
        });
    </script>
@endsection
