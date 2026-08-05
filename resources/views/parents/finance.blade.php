@extends('parents.layout')

@section('title', $student->firstname . ' — Fee Statement')

@section('content')
    @include('parents.partials.child-nav', ['active' => 'finance'])

    <div class="pp-card">
        <form method="GET" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
            <label style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--gray-500);">Academic Year</label>
            <select name="year" onchange="this.form.submit()" style="border:1px solid var(--brand-pale);border-radius:0.5rem;padding:0.5rem 0.7rem;font-size:0.85rem;">
                @forelse ($availableYears as $y)
                    <option value="{{ $y }}" {{ (string) $y === (string) $year ? 'selected' : '' }}>{{ $y }}</option>
                @empty
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforelse
            </select>
        </form>

        <div class="pp-stat-grid" style="margin-bottom:1.25rem;">
            <div class="pp-stat">
                <div class="pp-stat-label">Total Charged</div>
                <div class="pp-stat-value">{{ number_format($totalCharges) }}</div>
            </div>
            <div class="pp-stat success">
                <div class="pp-stat-label">Total Paid</div>
                <div class="pp-stat-value">{{ number_format($totalPaid) }}</div>
            </div>
            <div class="pp-stat {{ $arrears > 0 ? 'danger' : 'success' }}">
                <div class="pp-stat-label">Balance</div>
                <div class="pp-stat-value">{{ number_format($arrears) }}</div>
            </div>
        </div>

        @if ($statement->isEmpty())
            <div class="pp-empty">
                <i class="fas fa-receipt"></i>
                <h6>No fee records for {{ $year }}</h6>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="pp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th style="text-align:right;">Charge</th>
                            <th style="text-align:right;">Payment</th>
                            <th style="text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statement as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td style="text-align:right; color: var(--danger);">
                                    {{ $row['debit'] !== null ? number_format($row['debit']) : '—' }}
                                </td>
                                <td style="text-align:right; color: var(--success);">
                                    {{ $row['credit'] !== null ? number_format($row['credit']) : '—' }}
                                </td>
                                <td style="text-align:right; font-weight:700;">{{ number_format($row['balance']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <p style="font-size:0.78rem;color:var(--gray-500);text-align:center;">
        For any billing questions or to make a payment, please contact {{ $student->school_name }} directly.
    </p>
@endsection
