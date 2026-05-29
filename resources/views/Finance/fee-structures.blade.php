@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --fin-green:#059669;--fin-green-l:rgba(5,150,105,.10);
    --fin-red:#dc2626;--fin-red-l:rgba(220,38,38,.10);
    --fin-blue:#2563eb;--fin-blue-l:rgba(37,99,235,.10);
    --fin-amber:#d97706;--fin-amber-l:rgba(217,119,6,.10);
    --fin-purple:#7c3aed;--fin-purple-l:rgba(124,58,237,.10);
    --surface:#ffffff;--bg:#f0f4f8;--border:#e2e8f0;
    --text-1:#0f172a;--text-2:#475569;--text-3:#94a3b8;
    --radius:16px;--radius-sm:10px;
    --shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.05);
}
*{font-family:'DM Sans',sans-serif;box-sizing:border-box;}
body{background:var(--bg);}
.fin-hero{background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#0f2d4a 100%);border-radius:24px;padding:2rem 2.5rem;margin-bottom:1.75rem;position:relative;overflow:hidden;}
.fin-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(5,150,105,.25) 0%,transparent 70%);}
.fin-hero h1{color:#fff;font-size:1.6rem;font-weight:700;margin:0;}
.fin-hero p{color:#94a3b8;margin:.2rem 0 0;font-size:.9rem;}
.hero-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(5,150,105,.2);border:1px solid rgba(5,150,105,.35);color:#34d399;padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:600;margin-bottom:.6rem;}
.fin-card{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;}
.fin-card-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.fin-card-header h3{margin:0;font-size:1rem;font-weight:700;color:var(--text-1);}
.btn-fin{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.2rem;border-radius:10px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-primary-fin{background:var(--fin-green);color:#fff;}
.btn-primary-fin:hover{background:#047857;color:#fff;transform:translateY(-1px);}
.btn-sm-fin{padding:.35rem .85rem;font-size:.8rem;}
.btn-outline-fin{background:transparent;border:1.5px solid var(--border);color:var(--text-2);}
.btn-outline-fin:hover{border-color:var(--fin-blue);color:var(--fin-blue);}
.btn-danger-fin{background:var(--fin-red-l);color:var(--fin-red);border:1px solid rgba(220,38,38,.2);}
.btn-danger-fin:hover{background:var(--fin-red);color:#fff;}
.fs-table{width:100%;border-collapse:collapse;}
.fs-table th{background:#f8fafc;padding:.8rem 1.25rem;font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);white-space:nowrap;}
.fs-table td{padding:.9rem 1.25rem;border-bottom:1px solid #f8fafc;font-size:.875rem;color:var(--text-1);vertical-align:middle;}
.fs-table tr:last-child td{border-bottom:none;}
.fs-table tr:hover td{background:#fafbfc;}
.badge-fin{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .7rem;border-radius:20px;font-size:.75rem;font-weight:600;}
.badge-green{background:var(--fin-green-l);color:var(--fin-green);}
.badge-amber{background:var(--fin-amber-l);color:var(--fin-amber);}
.badge-gray{background:#f1f5f9;color:var(--text-2);}
.badge-blue{background:var(--fin-blue-l);color:var(--fin-blue);}
.amount-mono{font-family:'DM Mono',monospace;font-weight:600;}
.empty-state{text-align:center;padding:3.5rem 2rem;color:var(--text-3);}
.empty-state i{font-size:3rem;opacity:.3;margin-bottom:1rem;display:block;}
.empty-state p{margin:.5rem 0 1rem;font-size:.9rem;}
.stat-pill{display:flex;align-items:center;gap:.5rem;background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:.5rem 1rem;font-size:.82rem;font-weight:600;color:var(--text-2);}
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;}
@media(max-width:768px){
    .fin-hero{padding:1.5rem;}
    .fin-hero h1{font-size:1.3rem;}
    .fs-table th,.fs-table td{padding:.7rem .9rem;}
    .toolbar{flex-direction:column;align-items:stretch;}
}
</style>
@endsection

@section('page-header')
<div class="fin-hero">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge"><i class="fas fa-layer-group"></i> Finance — Fee Structures</div>
        <h1>Fee Structures</h1>
        <p>Manage tuition fee templates by term, class level and student type</p>
    </div>
</div>
@endsection

@section('content')
<div class="toolbar">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <div class="stat-pill"><i class="fas fa-layer-group" style="color:var(--fin-blue);"></i> {{ $structures->count() }} Structures</div>
        <div class="stat-pill"><i class="fas fa-check-circle" style="color:var(--fin-green);"></i> {{ $structures->where('is_active',true)->count() }} Active</div>
    </div>
    <a href="{{ route('finance.fee-structures.create') }}" class="btn-fin btn-primary-fin">
        <i class="fas fa-plus"></i> New Fee Structure
    </a>
</div>

<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-layer-group" style="color:var(--fin-blue);margin-right:.5rem;"></i>All Fee Structures</h3>
    </div>

    @if($structures->isEmpty())
        <div class="empty-state">
            <i class="fas fa-layer-group"></i>
            <p>No fee structures yet. Create your first one to start allocating fees to students.</p>
            <a href="{{ route('finance.fee-structures.create') }}" class="btn-fin btn-primary-fin">
                <i class="fas fa-plus"></i> Create Fee Structure
            </a>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="fs-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Term</th>
                        <th>Class Level</th>
                        <th>Student Type</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($structures as $s)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:var(--text-1);">{{ $s->name }}</div>
                            @if($s->notes)
                            <div style="font-size:.75rem;color:var(--text-3);margin-top:2px;">{{ Str::limit($s->notes,50) }}</div>
                            @endif
                        </td>
                        <td><span class="badge-fin badge-blue">{{ $s->academic_year }}</span></td>
                        <td><span class="badge-fin badge-gray">{{ $s->termLabel() }}</span></td>
                        <td>{{ $s->class_level ?? '—' }}</td>
                        <td>
                            @if($s->student_type === 'boarding')
                                <span class="badge-fin badge-blue"><i class="fas fa-building"></i> Boarding</span>
                            @elseif($s->student_type === 'day')
                                <span class="badge-fin badge-green"><i class="fas fa-sun"></i> Day</span>
                            @else
                                <span class="badge-fin badge-gray">All</span>
                            @endif
                        </td>
                        <td>
                            <span class="amount-mono" style="color:var(--fin-green);">UGX {{ number_format($s->total_amount, 0) }}</span>
                        </td>
                        <td>
                            @if($s->is_active)
                                <span class="badge-fin badge-green"><i class="fas fa-circle" style="font-size:.5rem;"></i> Active</span>
                            @else
                                <span class="badge-fin badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.5rem;align-items:center;">
                                <a href="{{ route('finance.fee-structures.edit', $s->id) }}" class="btn-fin btn-sm-fin btn-outline-fin" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('finance.fee-structures.destroy', $s->id) }}" onsubmit="return confirm('Delete this fee structure?');" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-fin btn-sm-fin btn-danger-fin" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if(session('success'))
<div style="position:fixed;bottom:1.5rem;right:1.5rem;background:#059669;color:#fff;padding:.85rem 1.4rem;border-radius:12px;font-size:.875rem;font-weight:600;box-shadow:0 8px 24px rgba(5,150,105,.35);z-index:9999;display:flex;align-items:center;gap:.5rem;" id="toast-msg">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
<script>setTimeout(()=>{let t=document.getElementById('toast-msg');if(t){t.style.opacity='0';t.style.transition='opacity .4s';setTimeout(()=>t.remove(),400);}},3500);</script>
@endif
@endsection