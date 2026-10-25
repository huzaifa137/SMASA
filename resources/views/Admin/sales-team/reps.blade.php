@extends('layouts-side-bar.master')

@section('content')
    @include('Admin.sales-team._styles')
    @php $F = \App\Support\SalesFormat::class; @endphp

    @include('Admin.sales-team._hero', [
        'title' => 'Sales Representatives',
        'subtitle' => 'Everyone who enrols schools and collects fees, and what each is responsible for',
        'actions' => $canManage ? '<button type="button" class="st-btn st-btn--primary" onclick="stOpen(\'dlg-add\')"><i class="fas fa-user-plus"></i> Add representative</button>' : '',
    ])
    @include('Admin.sales-team._nav', ['tab' => 'reps'])

    <div class="st-bar">
        @include('Admin.sales-team._period', ['extra' => ['status' => $status]])
        <span class="st-grow"></span>
        <form method="GET">
            @foreach($period->query() as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <select name="status" onchange="this.form.submit()">
                @foreach(['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach
            </select>
        </form>
    </div>

    <div class="st-card">
        @if($rows->isEmpty())
            <div class="st-empty"><i class="fas fa-users"></i>No sales representatives yet. Add an admin account as a representative to start tracking schools and targets.</div>
        @else
            <div class="st-scroll"><table class="st-table">
                <thead><tr><th>Representative</th><th>Status</th><th class="num">Schools</th><th class="num">Students</th><th class="num">Outstanding</th><th class="num">Overdue</th><th>Contact</th><th></th></tr></thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td><div class="st-who"><span class="st-avatar">{{ $r['initials'] }}</span><div><a href="{{ route('sales.show', $r['user_id']) }}">{{ $r['name'] }}</a><small>{{ $r['code'] }}{{ $r['region'] ? ' · ' . $r['region'] : '' }}</small></div></div></td>
                        <td>
                            <span class="st-pill {{ $r['is_active'] ? 'ok' : 'mute' }}">{{ $r['is_active'] ? 'Active' : 'Inactive' }}</span>
                            @if(!$r['login_active'])<span class="st-pill bad" title="The admin login for this person is disabled">Login disabled</span>@endif
                        </td>
                        <td class="num">{{ $r['schools'] }}</td>
                        <td class="num">{{ number_format($r['students']) }}</td>
                        <td class="num">{{ $F::money($r['outstanding'], '') }}</td>
                        <td class="num" style="{{ $r['overdue'] > 0 ? 'color:#b42318;font-weight:700' : '' }}">{{ $F::money($r['overdue'], '') }}</td>
                        <td class="st-mute">{{ $r['email'] }}{{ $r['phone'] ? ' · ' . $r['phone'] : '' }}</td>
                        <td class="num"><a class="st-btn st-btn--sm" href="{{ route('sales.show', $r['user_id']) }}">Open</a>
                            @if($canManage && !$r['is_active'])
                                <form method="POST" action="{{ route('sales.reps.reactivate', $r['user_id']) }}" style="display:inline">@csrf<button class="st-btn st-btn--sm">Reactivate</button></form>
                            @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    @if($canManage)
        <dialog class="st-dlg" id="dlg-add">
            <form method="POST" action="{{ route('sales.reps.store') }}">@csrf
                <h5><i class="fas fa-user-plus"></i> Add a sales representative</h5>
                @if($candidates->isEmpty())
                    <div class="st-note">Every active admin account is already a representative.
                        @if($canCreateAdmin) <a href="{{ url('/admin-rights') }}">Create a new admin user first</a>.@endif</div>
                @else
                    <label>Admin account</label>
                    <select name="user_id" required>
                        <option value="">Choose a person…</option>
                        @foreach($candidates as $u)<option value="{{ $u->id }}">{{ trim($u->name ?: ($u->firstname . ' ' . $u->lastname)) ?: $u->username }} — {{ $u->email }}</option>@endforeach
                    </select>
                    <div class="row2">
                        <div><label>Region / territory</label><input name="region" maxlength="120" placeholder="e.g. Central"></div>
                        <div><label>Commission % (optional)</label><input name="commission_rate" type="number" step="0.01" min="0" max="100"></div>
                    </div>
                    <label>Joined on</label><input name="joined_on" type="date" value="{{ now()->toDateString() }}">
                    <label>Notes</label><textarea name="notes" rows="2" maxlength="2000"></textarea>
                @endif
                <div class="foot"><button type="button" class="st-btn" onclick="stClose(this)">Cancel</button>@if($candidates->isNotEmpty())<button class="st-btn st-btn--primary">Add representative</button>@endif</div>
            </form>
        </dialog>
    @endif
@endsection
