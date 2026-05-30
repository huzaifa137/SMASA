{{-- resources/views/Finance/payment-form.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --fin-green: #059669;
            --fin-green-l: rgba(5, 150, 105, .10);
            --fin-red: #dc2626;
            --fin-red-l: rgba(220, 38, 38, .10);
            --fin-blue: #2f2ccb;
            --fin-blue-l: rgba(47, 44, 203, .10);
            --fin-amber: #d97706;
            --fin-amber-l: rgba(217, 119, 6, .10);
            --fin-purple: #7c3aed;
            --fin-purple-l: rgba(124, 58, 237, .10);
            --surface: #ffffff;
            --bg: #f0f4f8;
            --border: #e2e8f0;
            --text-1: #0f172a;
            --text-2: #475569;
            --text-3: #94a3b8;
            --radius: 16px;
            --radius-sm: 12px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
        }

        * {
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
        }

        .fin-hero {
            background: linear-gradient(135deg, #464592 0%, rgb(22, 19, 201) 60%, #050352 100%);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .fin-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(5, 150, 105, .25) 0%, transparent 70%);
        }

        .fin-hero h1 {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }

        .fin-hero p {
            color: #94a3b8;
            margin: .2rem 0 0;
            font-size: .9rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(5, 150, 105, .2);
            border: 1px solid rgba(5, 150, 105, .35);
            color: #34d399;
            padding: .25rem .75rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: .6rem;
        }

        .fin-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .fin-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fin-card-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-1);
        }

        /* Steps indicator */
        .steps-bar {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 1.75rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex: 1;
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
            transition: all .3s;
        }

        .step-num.done {
            background: var(--fin-green);
            color: #fff;
        }

        .step-num.active {
            background: var(--fin-blue);
            color: #fff;
            box-shadow: 0 0 0 4px var(--fin-blue-l);
        }

        .step-num.pending {
            background: #f1f5f9;
            color: var(--text-3);
        }

        .step-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-2);
            white-space: nowrap;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--border);
            margin: 0 .5rem;
        }

        .step-line.done {
            background: var(--fin-green);
        }

        /* Selector cards (class / stream) */
        .selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: .75rem;
            margin-top: .5rem;
        }

        .selector-card {
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: .85rem .75rem;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: var(--surface);
        }

        .selector-card:hover {
            border-color: var(--fin-blue);
            transform: translateY(-2px);
        }

        .selector-card.selected {
            border-color: var(--fin-blue);
            background: var(--fin-blue-l);
            box-shadow: 0 0 0 3px var(--fin-blue-l);
        }

        .selector-card.selected .sc-label {
            color: var(--fin-blue);
            font-weight: 700;
        }

        .sc-icon {
            font-size: 1.4rem;
            margin-bottom: .35rem;
        }

        .sc-label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-2);
        }

        .sc-count {
            font-size: .7rem;
            color: var(--text-3);
            margin-top: .15rem;
        }

        /* Student table */
        .student-list {
            margin-top: .75rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .student-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .7rem 1rem;
            border-bottom: 1px solid #f8fafc;
            cursor: pointer;
            transition: background .15s;
        }

        .student-row:last-child {
            border-bottom: none;
        }

        .student-row:hover {
            background: #f8fafc;
        }

        .student-row.selected {
            background: var(--fin-green-l);
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
            color: #FFF !important;
        }

        .student-info-name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--text-1);
        }

        .student-info-adm {
            font-size: .75rem;
            color: var(--text-3);
        }

        .student-check {
            margin-left: auto;
            color: var(--fin-green);
            font-size: 1rem;
            display: none;
        }

        .student-row.selected .student-check {
            display: block;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: .45rem;
            font-weight: 600;
            color: var(--text-2);
            font-size: .83rem;
        }

        .form-group label .req {
            color: var(--fin-red);
            margin-left: .15rem;
        }

        .form-control-fin {
            width: 100%;
            padding: .7rem .9rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-size: .9rem;
            transition: all .2s;
            background: var(--surface);
            color: var(--text-1);
        }

        .form-control-fin:focus {
            outline: none;
            border-color: var(--fin-blue);
            box-shadow: 0 0 0 3px var(--fin-blue-l);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        /* Summary preview */
        .amount-preview {
            background: linear-gradient(135deg, var(--fin-green-l), rgba(5, 150, 105, .04));
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(5, 150, 105, .2);
        }

        .amount-preview .ap-label {
            font-size: .7rem;
            color: var(--text-3);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .amount-preview .ap-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--fin-green);
            font-family: 'DM Mono', monospace;
        }

        .btn-fin {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.3rem;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-primary-fin {
            background: #2c29ca;
            color: #fff;
        }

        .btn-primary-fin:hover {
            background: #2c29ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, .3);
            color: #fff;
        }

        .btn-outline-fin {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-2);
        }

        .btn-outline-fin:hover {
            border-color: var(--fin-blue);
            color: var(--fin-blue);
        }

        .form-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            margin-top: 1.25rem;
        }

        .section-block {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .section-block:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-3);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .badge-fin {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .65rem;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 600;
        }

        .badge-green {
            background: var(--fin-green-l);
            color: var(--fin-green);
        }

        .badge-red {
            background: var(--fin-red-l);
            color: var(--fin-red);
        }

        .badge-amber {
            background: var(--fin-amber-l);
            color: var(--fin-amber);
        }

        .loading-row {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-3);
            font-size: .85rem;
        }

        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 400% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 8px;
            height: 44px;
            margin-bottom: .5rem;
        }

        @keyframes shimmer {
            0% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0 50%
            }
        }

        .selected-student-card {
            background: var(--fin-green-l);
            border: 1.5px solid rgba(5, 150, 105, .3);
            border-radius: 12px;
            padding: .85rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .selected-student-card strong {
            color: var(--text-1);
        }

        .selected-student-card small {
            color: var(--text-2);
            font-size: .78rem;
        }

        @media(max-width:768px) {
            .fin-hero {
                padding: 1.5rem;
            }

            .fin-hero h1 {
                font-size: 1.3rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .selector-grid {
                grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            }

            .steps-bar {
                display: none;
            }
        }

        .method-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: .6rem;
        }

        .method-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .4rem;
            padding: .75rem .5rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: all .18s;
            background: var(--surface);
            text-align: center;
        }

        .method-card i {
            font-size: 1.2rem;
            color: var(--text-3);
            transition: color .18s;
        }

        .method-card span {
            font-size: .72rem;
            font-weight: 600;
            color: var(--text-2);
            line-height: 1.2;
        }

        .method-card:hover {
            border-color: var(--fin-blue);
            transform: translateY(-2px);
        }

        .method-card:hover i {
            color: var(--fin-blue);
        }

        .method-card.selected {
            border-color: var(--fin-blue);
            background: var(--fin-blue-l);
            box-shadow: 0 0 0 3px var(--fin-blue-l);
        }

        .method-card.selected i {
            color: var(--fin-blue);
        }

        .method-card.selected span {
            color: var(--fin-blue);
            font-weight: 700;
        }

        @media(max-width: 600px) {
            .method-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
@endsection

@section('page-header')
    <div class="fin-hero mt-5">
        <div style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="fas fa-plus-circle"></i> Finance — New Payment</div>
            <h1>Record Fee Payment</h1>
            <p style="color:#cbd5e1;">Pick a class → stream → student, then enter payment details</p>
        </div>
    </div>
@endsection

@section('content')

    {{-- Steps Bar --}}
    <div class="steps-bar" id="stepsBar">
        <div class="step">
            <div class="step-num active" id="sn1">1</div>
            <span class="step-label">Select Class</span>
        </div>
        <div class="step-line" id="sl1"></div>
        <div class="step">
            <div class="step-num pending" id="sn2">2</div>
            <span class="step-label">Pick Stream</span>
        </div>
        <div class="step-line" id="sl2"></div>
        <div class="step">
            <div class="step-num pending" id="sn3">3</div>
            <span class="step-label">Choose Student</span>
        </div>
        <div class="step-line" id="sl3"></div>
        <div class="step">
            <div class="step-num pending" id="sn4">4</div>
            <span class="step-label">Payment Details</span>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <h3><i class="fas fa-receipt" style="color:var(--fin-blue);margin-right:.5rem;"></i>Payment Information</h3>
            <span style="font-size:.75rem;color:var(--text-3);">Receipt: <strong
                    style="font-family:'DM Mono',monospace;color:var(--text-1);">{{ $receiptNum }}</strong></span>
        </div>

        <form method="POST" action="{{ route('finance.payments.store') }}" id="paymentForm">
            @csrf

            {{-- STEP 1 — Class Selection --}}
            <div class="section-block" id="stepClass">
                <div class="section-title"><i class="fas fa-chalkboard"></i> Step 1 — Select Class</div>

                @if($classrooms->isEmpty())
                    <div style="text-align:center;padding:2rem;color:var(--text-3);">
                        <i class="fas fa-exclamation-circle"
                            style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        No classes found for this school. Please set up classes first.
                    </div>
                @else
                    <div class="selector-grid" id="classGrid">
                        @foreach($classrooms as $cls)
                            @php $clsName = \App\Http\Controllers\Helper::recordMdname($cls->class_name) ?? $cls->class_name; @endphp
                            <div class="selector-card" data-class-id="{{ $cls->class_name }}" onclick="selectClass(this)">
                                <div class="sc-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="sc-label">{{ $clsName }}</div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedClassId" name="_class_id">
                @endif
            </div>

            {{-- STEP 2 — Stream Selection --}}
            <div class="section-block" id="stepStream" style="display:none;">
                <div class="section-title"><i class="fas fa-code-branch"></i> Step 2 — Select Stream</div>
                <div style="margin-bottom:.75rem;">
                    <span style="font-size:.82rem;color:var(--text-2);">Class: </span>
                    <span id="chosenClassName" style="font-size:.82rem;font-weight:700;color:var(--fin-blue);"></span>
                    <button type="button" onclick="resetToClass()"
                        style="background:none;border:none;color:var(--fin-red);font-size:.75rem;cursor:pointer;margin-left:.5rem;">
                        <i class="fas fa-times"></i> Change
                    </button>
                </div>
                <div class="selector-grid" id="streamGrid">
                    <div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading streams…</div>
                </div>
                <input type="hidden" id="selectedStreamId" name="_stream_id">
            </div>

            {{-- STEP 3 — Student Selection --}}
            <div class="section-block" id="stepStudent" style="display:none;">
                <div class="section-title"><i class="fas fa-user-graduate"></i> Step 3 — Select Student</div>
                <div style="margin-bottom:.75rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <div>
                        <span style="font-size:.82rem;color:var(--text-2);">Class: </span>
                        <span id="chosenClassLabel2" style="font-size:.82rem;font-weight:700;color:var(--fin-blue);"></span>
                        <span style="font-size:.82rem;color:var(--text-2);margin:0 .25rem;">›</span>
                        <span id="chosenStreamLabel"
                            style="font-size:.82rem;font-weight:700;color:var(--fin-purple);"></span>
                    </div>
                    <button type="button" onclick="resetToStream()"
                        style="background:none;border:none;color:var(--fin-red);font-size:.75rem;cursor:pointer;">
                        <i class="fas fa-times"></i> Change Stream
                    </button>
                </div>

                {{-- Search inside stream --}}
                <div style="position:relative;margin-bottom:.75rem;">
                    <i class="fas fa-search"
                        style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:.8rem;"></i>
                    <input type="text" id="studentSearch" class="form-control-fin"
                        placeholder="Search student name or admission no…" style="padding-left:2.2rem;"
                        oninput="filterStudents(this.value)">
                </div>

                <div id="studentList" class="student-list">
                    <div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading students…</div>
                </div>
                <input type="hidden" name="student_id" id="selectedStudentId">
            </div>

            {{-- STEP 4 — Payment Details --}}
            <div class="section-block" id="stepPayment" style="display:none;">
                <div class="section-title"><i class="fas fa-money-bill-wave"></i> Step 4 — Payment Details</div>

                {{-- Selected student recap --}}
                <div class="selected-student-card" id="studentRecap">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div id="studentAvatarBig"
                            style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:#fff;">
                        </div>
                        <div>
                            <strong id="recapName"></strong>
                            <br><small id="recapAdm"></small>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <span id="recapGender" class="badge-fin badge-green"></span>
                        <button type="button" onclick="resetToStudent()"
                            style="background:none;border:none;color:var(--fin-red);font-size:.75rem;cursor:pointer;">
                            <i class="fas fa-times"></i> Change
                        </button>
                    </div>
                </div>

                {{-- Fee Allocation --}}
                <div class="form-group" id="allocationGroup">
                    <label>Fee Allocation (Outstanding Balance) <span class="req">*</span></label>
                    <select name="allocation_id" id="allocation_id" class="form-control-fin">
                        <option value="">— loading allocations… —</option>
                    </select>
                    <small style="color:var(--text-3);display:block;margin-top:.3rem;">
                        <i class="fas fa-info-circle"></i> Shows fee allocations with outstanding balance for this student
                    </small>
                </div>
                <div id="allocationPreview" style="margin-bottom:1.25rem;"></div>

                {{-- Year & Term --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>Academic Year <span class="req">*</span></label>
                        <select name="academic_year" id="academic_year" class="form-control-fin" required>
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                            <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
                            <option value="{{ date('Y') - 2 }}">{{ date('Y') - 2 }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Term <span class="req">*</span></label>
                        <select name="term" id="term" class="form-control-fin" required>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                        </select>
                    </div>
                </div>

                {{-- Amount & Date --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>Amount Paid (UGX) <span class="req">*</span></label>
                        <input type="text" id="amount_paid_display" class="form-control-fin" placeholder="e.g. 500,000">
                        <input type="hidden" name="amount_paid" id="amount_paid">
                    </div>
                    <div class="form-group">
                        <label>Payment Date <span class="req">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control-fin"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="form-group">
                    <label>Payment Method <span class="req">*</span></label>
                    <input type="hidden" name="payment_method" id="payment_method" value="cash">
                    <div class="method-grid">
                        <div class="method-card selected" data-value="cash" onclick="selectMethod(this)">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </div>
                        <div class="method-card" data-value="bank_transfer" onclick="selectMethod(this)">
                            <i class="fas fa-university"></i>
                            <span>Bank Transfer</span>
                        </div>
                        <div class="method-card" data-value="mobile_money" onclick="selectMethod(this)">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile Money</span>
                        </div>
                        <div class="method-card" data-value="cheque" onclick="selectMethod(this)">
                            <i class="fas fa-money-check"></i>
                            <span>Cheque</span>
                        </div>
                        <div class="method-card" data-value="other" onclick="selectMethod(this)">
                            <i class="fas fa-ellipsis-h"></i>
                            <span>Other</span>
                        </div>
                    </div>
                </div>

                <div id="bankFields" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control-fin" placeholder="e.g. Stanbic Bank">
                        </div>
                        <div class="form-group">
                            <label>Cheque / Reference No.</label>
                            <input type="text" name="transaction_reference" class="form-control-fin"
                                placeholder="Reference number">
                        </div>
                    </div>
                </div>

                <div id="mobileFields" style="display:none;">
                    <div class="form-group">
                        <label>Transaction Reference / Phone</label>
                        <input type="text" name="transaction_reference" class="form-control-fin"
                            placeholder="MTN/Airtel ref or phone number">
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" class="form-control-fin" rows="2" placeholder="Any extra notes…"></textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('finance.payments.index') }}" class="btn-fin btn-outline-fin"><i
                            class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn-fin btn-primary-fin" id="submitBtn">
                        <i class="fas fa-save"></i> Record Payment & Generate Receipt
                    </button>
                </div>
            </div>{{-- end stepPayment --}}
        </form>
    </div>
    </div>
    </div>

    @if($errors->any())
        <div style="position:fixed;bottom:1.5rem;right:1.5rem;background:#dc2626;color:#fff;padding:.85rem 1.4rem;border-radius:12px;z-index:9999;max-width:320px;"
            id="errToast">
            <i class="fas fa-exclamation-triangle"></i>
            @foreach($errors->all() as $e) {{ $e }}@if(!$loop->last), @endif @endforeach
        </div>
        <script>setTimeout(() => { let t = document.getElementById('errToast'); if (t) { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); } }, 5000);</script>
    @endif

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ─────────────────────────────────────────────────────────────────
        // State
        // ─────────────────────────────────────────────────────────────────
        let state = { classId: null, className: null, streamId: null, streamName: null, studentId: null };
        let allStudents = [];
        let allAllocations = [];

        // ─────────────────────────────────────────────────────────────────
        // Amount input — display vs hidden
        // ─────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const display = document.getElementById('amount_paid_display');
            const hidden = document.getElementById('amount_paid');
            if (!display || !hidden) return;

            display.addEventListener('input', function () {
                const digits = this.value.replace(/\D/g, '');
                this.value = digits ? parseInt(digits, 10).toLocaleString('en-US') : '';
                hidden.value = digits || '';
            });

            display.addEventListener('blur', function () {
                const raw = parseInt(hidden.value, 10) || 0;
                const max = parseFloat(display.getAttribute('data-max') || '0');
                if (raw === 0) { this.value = ''; hidden.value = ''; return; }
                if (max > 0 && raw > max) {
                    Swal.fire({
                        icon: 'error', title: 'Amount Exceeds Balance',
                        text: `UGX ${raw.toLocaleString()} exceeds outstanding balance UGX ${max.toLocaleString()}`,
                        confirmButtonColor: '#2f2ccb'
                    });
                    const capped = Math.floor(max);
                    display.value = capped.toLocaleString('en-US');
                    hidden.value = capped;
                }
            });
        });

        // ─────────────────────────────────────────────────────────────────
        // Step indicator
        // ─────────────────────────────────────────────────────────────────
        function setStep(n) {
            document.querySelectorAll('[id^="sn"]').forEach((el, i) => {
                const num = i + 1;
                el.className = 'step-num ' + (num < n ? 'done' : num === n ? 'active' : 'pending');
                el.innerHTML = num < n ? '<i class="fas fa-check" style="font-size:.7rem;"></i>' : num;
            });
            document.querySelectorAll('[id^="sl"]').forEach((el, i) => {
                el.className = 'step-line ' + (i + 1 < n ? 'done' : '');
            });
        }

        // ─────────────────────────────────────────────────────────────────
        // STEP 1 — Class
        // ─────────────────────────────────────────────────────────────────
        function selectClass(card) {
            document.querySelectorAll('#classGrid .selector-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            state.classId = card.dataset.classId;
            state.className = card.querySelector('.sc-label').textContent.trim();
            document.getElementById('selectedClassId').value = state.classId;
            document.getElementById('chosenClassName').textContent = state.className;
            document.getElementById('chosenClassLabel2').textContent = state.className;
            setStep(2);
            document.getElementById('stepStream').style.display = 'block';
            document.getElementById('stepStudent').style.display = 'none';
            document.getElementById('stepPayment').style.display = 'none';
            document.getElementById('stepStream').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            loadStreams(state.classId);
        }

        function resetToClass() {
            state = { classId: null, className: null, streamId: null, streamName: null, studentId: null };
            document.querySelectorAll('#classGrid .selector-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('stepStream').style.display = 'none';
            document.getElementById('stepStudent').style.display = 'none';
            document.getElementById('stepPayment').style.display = 'none';
            setStep(1);
        }

        // ─────────────────────────────────────────────────────────────────
        // STEP 2 — Stream
        // ─────────────────────────────────────────────────────────────────
        async function loadStreams(classId) {
            const grid = document.getElementById('streamGrid');
            grid.innerHTML = '<div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading streams…</div>';
            try {
                const r = await fetch(`{{ route('finance.streams-by-class') }}?class_id=${classId}`);
                const data = await r.json();
                if (!data.length) {
                    grid.innerHTML = '<div class="loading-row"><i class="fas fa-exclamation-circle"></i> No streams found for this class.</div>';
                    return;
                }
                grid.innerHTML = data.map(s => `
                    <div class="selector-card" data-stream-id="${s.stream_id}" data-stream-name="${s.stream_name}" onclick="selectStream(this)">
                        <div class="sc-icon">📚</div>
                        <div class="sc-label">${s.stream_name || s.stream_id}</div>
                    </div>`).join('');
            } catch {
                grid.innerHTML = '<div class="loading-row" style="color:var(--fin-red);"><i class="fas fa-times-circle"></i> Failed to load streams.</div>';
            }
        }

        function selectStream(card) {
            document.querySelectorAll('#streamGrid .selector-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            state.streamId = card.dataset.streamId;
            state.streamName = card.dataset.streamName;
            document.getElementById('selectedStreamId').value = state.streamId;
            document.getElementById('chosenStreamLabel').textContent = state.streamName;
            setStep(3);
            document.getElementById('stepStudent').style.display = 'block';
            document.getElementById('stepPayment').style.display = 'none';
            document.getElementById('stepStudent').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            loadStudents(state.classId, state.streamId);
        }

        function resetToStream() {
            state.streamId = null; state.streamName = null; state.studentId = null;
            document.getElementById('stepPayment').style.display = 'none';
            document.getElementById('selectedStudentId').value = '';
            document.querySelectorAll('#streamGrid .selector-card').forEach(c => c.classList.remove('selected'));
            setStep(2);
            document.getElementById('stepStream').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // ─────────────────────────────────────────────────────────────────
        // STEP 3 — Student
        // ─────────────────────────────────────────────────────────────────
        const AVATAR_COLORS = ['#7c3aed', '#2563eb', '#059669', '#d97706', '#dc2626', '#0891b2'];
        function avatarColor(name) {
            let h = 0;
            for (let c of name) h = (h * 31 + c.charCodeAt(0)) % AVATAR_COLORS.length;
            return AVATAR_COLORS[h];
        }

        async function loadStudents(classId, streamId) {
            const list = document.getElementById('studentList');
            list.innerHTML = '<div class="loading-row"><i class="fas fa-spinner fa-spin"></i> Loading students…</div>';
            try {
                const r = await fetch(`{{ route('finance.students-by-stream') }}?class_id=${classId}&stream_id=${streamId}`);
                allStudents = await r.json();
                renderStudents(allStudents);
            } catch {
                list.innerHTML = '<div class="loading-row" style="color:var(--fin-red);"><i class="fas fa-times-circle"></i> Failed to load students.</div>';
            }
        }

        function renderStudents(students) {
            const list = document.getElementById('studentList');
            if (!students.length) {
                list.innerHTML = '<div class="loading-row"><i class="fas fa-user-slash"></i> No students found in this stream.</div>';
                return;
            }
            list.innerHTML = students.map(s => {
                const initials = (s.firstname[0] + (s.lastname?.[0] ?? '')).toUpperCase();
                const color = avatarColor(s.firstname);
                return `
                    <div class="student-row"
                         data-id="${s.id}"
                         data-name="${s.firstname} ${s.lastname}"
                         data-adm="${s.admission_number ?? 'N/A'}"
                         data-gender="${s.gender ?? ''}"
                         onclick="selectStudent(this)">
                        <div class="student-avatar" style="background:${color};">${initials}</div>
                        <div>
                            <div class="student-info-name">${s.firstname} ${s.lastname}</div>
                            <div class="student-info-adm">ADM: ${s.admission_number ?? 'N/A'} · ${s.gender ?? ''}</div>
                        </div>
                        <i class="fas fa-check-circle student-check"></i>
                    </div>`;
            }).join('');
        }

        function filterStudents(q) {
            const term = q.toLowerCase();
            renderStudents(allStudents.filter(s =>
                (s.firstname + ' ' + s.lastname).toLowerCase().includes(term) ||
                (s.admission_number ?? '').toLowerCase().includes(term)
            ));
        }

        function selectStudent(row) {
            document.querySelectorAll('.student-row').forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');
            state.studentId = row.dataset.id;
            document.getElementById('selectedStudentId').value = state.studentId;

            const name = row.dataset.name;
            const adm = row.dataset.adm;
            const gender = row.dataset.gender;
            const color = avatarColor(name.split(' ')[0]);
            const initials = (name[0] + (name.split(' ')[1]?.[0] ?? '')).toUpperCase();

            document.getElementById('recapName').textContent = name;
            document.getElementById('recapAdm').textContent = `ADM: ${adm} · ${state.className} › ${state.streamName}`;
            document.getElementById('recapGender').textContent = gender;
            document.getElementById('studentAvatarBig').style.background = color;
            document.getElementById('studentAvatarBig').textContent = initials;

            document.getElementById('amount_paid_display').value = '';
            document.getElementById('amount_paid').value = '';
            document.getElementById('amount_paid_display').removeAttribute('data-max');
            document.getElementById('amount_paid_display').placeholder = 'e.g. 500,000';

            setStep(4);
            document.getElementById('stepPayment').style.display = 'block';
            document.getElementById('stepPayment').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            loadAllocations(state.studentId);
        }

        function resetToStudent() {
            state.studentId = null;
            document.getElementById('selectedStudentId').value = '';
            document.querySelectorAll('.student-row').forEach(r => r.classList.remove('selected'));
            document.getElementById('stepPayment').style.display = 'none';
            setStep(3);
            document.getElementById('stepStudent').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // ─────────────────────────────────────────────────────────────────
        // STEP 4 — Allocations
        // ─────────────────────────────────────────────────────────────────
        async function loadAllocations(studentId) {
            const sel = document.getElementById('allocation_id');
            const year = document.getElementById('academic_year').value;
            sel.innerHTML = '<option value="">— loading… —</option>';
            document.getElementById('allocationPreview').innerHTML = '';

            try {
                const r = await fetch(`{{ route('finance.student-allocations') }}?student_id=${studentId}&year=${year}`);
                const data = await r.json();
                allAllocations = data.allocations ?? [];

                if (!allAllocations.length) {
                    sel.innerHTML = '<option value="">— No fee allocations found for this student —</option>';
                    return;
                }
                sel.innerHTML = '<option value="">— Select allocation —</option>' +
                    allAllocations.map(a =>
                        `<option value="${a.id}" data-balance="${a.balance}" data-term="${a.term}" data-status="${a.status}">${a.label}</option>`
                    ).join('');
            } catch {
                sel.innerHTML = '<option value="">— Error loading allocations —</option>';
            }
        }

        document.getElementById('allocation_id').addEventListener('change', function () {
            const id = this.value;
            const preview = document.getElementById('allocationPreview');
            const display = document.getElementById('amount_paid_display');
            const hidden = document.getElementById('amount_paid');

            display.value = '';
            hidden.value = '';

            if (!id) {
                preview.innerHTML = '';
                display.removeAttribute('data-max');
                display.placeholder = 'e.g. 500,000';
                return;
            }

            const alloc = allAllocations.find(a => a.id == id);
            if (!alloc) return;

            const balance = parseFloat(alloc.balance) || 0;
            display.setAttribute('data-max', balance);
            display.placeholder = `Max: UGX ${balance.toLocaleString('en-US')}`;
            document.getElementById('term').value = alloc.term;

            const statusMap = {
                paid: '<span class="badge-fin badge-green"><i class="fas fa-check-circle"></i> Fully Paid</span>',
                partial: '<span class="badge-fin badge-amber"><i class="fas fa-hourglass-half"></i> Partial</span>',
                unpaid: '<span class="badge-fin badge-red"><i class="fas fa-times-circle"></i> Unpaid</span>',
            };

            preview.innerHTML = `
                <div class="amount-preview">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
                        <span class="ap-label"><i class="fas fa-info-circle"></i> Selected Allocation</span>
                        ${statusMap[alloc.status] ?? ''}
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        <div>
                            <div class="ap-label">Outstanding Balance</div>
                            <div class="ap-value">UGX ${balance.toLocaleString('en-US')}</div>
                        </div>
                        <div>
                            <div class="ap-label">Term</div>
                            <div class="ap-value" style="font-size:1rem;">Term ${alloc.term}</div>
                        </div>
                    </div>
                    <div style="margin-top:.6rem;font-size:.75rem;color:var(--text-3);border-top:1px solid rgba(5,150,105,.15);padding-top:.5rem;">
                        <i class="fas fa-lightbulb"></i> Enter an amount up to UGX ${balance.toLocaleString('en-US')}
                    </div>
                </div>`;
        });

        document.getElementById('academic_year').addEventListener('change', function () {
            if (state.studentId) loadAllocations(state.studentId);
        });

        function selectMethod(card) {
            document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const method = card.dataset.value;
            document.getElementById('payment_method').value = method;

            document.getElementById('bankFields').style.display = ['bank_transfer', 'cheque'].includes(method) ? 'block' : 'none';
            document.getElementById('mobileFields').style.display = method === 'mobile_money' ? 'block' : 'none';
        }

        // ─────────────────────────────────────────────────────────────────
        // Form submit — SweetAlert confirm then native submit
        // ─────────────────────────────────────────────────────────────────
        const paymentForm = document.getElementById('paymentForm');
        let _submitting = false;

        paymentForm.addEventListener('submit', function (e) {
            if (_submitting) return; // already confirmed, let native POST through
            e.preventDefault();

            const display = document.getElementById('amount_paid_display');
            const hidden = document.getElementById('amount_paid');
            const rawAmount = parseInt(hidden.value, 10) || 0;

            if (!state.studentId) {
                return Swal.fire({
                    icon: 'error', title: 'No Student Selected',
                    text: 'Please select a student before proceeding.', confirmButtonColor: '#2f2ccb'
                });
            }
            if (rawAmount <= 0) {
                display.focus();
                return Swal.fire({
                    icon: 'error', title: 'Invalid Amount',
                    text: 'Please enter a valid amount greater than 0.', confirmButtonColor: '#2f2ccb'
                });
            }
            const max = parseFloat(display.getAttribute('data-max') || '0');
            if (max > 0 && rawAmount > max) {
                return Swal.fire({
                    icon: 'error', title: 'Amount Exceeds Balance',
                    text: `UGX ${rawAmount.toLocaleString()} exceeds the outstanding balance of UGX ${max.toLocaleString()}.`,
                    confirmButtonColor: '#2f2ccb'
                });
            }

            const studentName = document.getElementById('recapName').textContent || 'selected student';

            Swal.fire({
                title: 'Record Payment?',
                html: `<span style="color:#475569">You are about to record<br>
                       <strong style="font-size:1.1rem;color:#0f172a">UGX ${rawAmount.toLocaleString()}</strong><br>
                       for <strong>${studentName}</strong><br><br>
                       <small style="color:#94a3b8">This action cannot be undone.</small></span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2f2ccb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: '<i class="fas fa-check"></i> Yes, record payment!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                reverseButtons: true
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Processing…',
                    html: `<span style="color:#475569">Recording payment for <strong>${studentName}</strong>…</span>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                _submitting = true;
                paymentForm.submit();
            });
        });
    </script>
@endsection