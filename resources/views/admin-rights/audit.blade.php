@extends('layouts-side-bar.master')

@section('css')
    @include('admin-rights._styles')
@endsection

@section('content')
    @include('admin-rights._nav', ['arpActive' => 'audit'])

    <div class="arp-card">
        <div class="arp-toolbar">
            <form method="GET" class="d-flex gap-2">
                <select name="action" class="arp-input" onchange="this.form.submit()">
                    <option value="">All activity</option>
                    @foreach (['role' => 'Roles', 'user' => 'Admin users', 'super_admin' => 'Super administrators'] as $k => $label)
                        <option value="{{ $k }}" @selected($action === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <span class="arp-muted">{{ $logs->total() }} entr{{ $logs->total() === 1 ? 'y' : 'ies' }}</span>
        </div>

        <div class="arp-wrap">
            <table class="arp-table">
                <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="white-space:nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ optional($log->actor)->username ?? 'system / console' }}</td>
                        <td><span class="arp-badge role">{{ $log->action }}</span></td>
                        <td>{{ $log->description }}
                            @if (!empty($log->meta['added']) || !empty($log->meta['removed']))
                                <details class="arp-muted"><summary>changes</summary>
                                    @foreach (($log->meta['added'] ?? []) as $k) <div style="color:#059669">+ {{ $k }}</div> @endforeach
                                    @foreach (($log->meta['removed'] ?? []) as $k) <div style="color:#dc2626">− {{ $k }}</div> @endforeach
                                </details>
                            @endif
                        </td>
                        <td class="arp-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="arp-muted">No activity recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links('pagination::bootstrap-4') }}</div>
    </div>
    </div>
        </div>
    </div>
@endsection
