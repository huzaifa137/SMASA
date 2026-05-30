{{-- resources/views/Finance/expense-form.blade.php --}}
@extends('layouts-side-bar.master')

@section('css')
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --fin-green: #059669;
    --fin-green-l: rgba(5,150,105,.10);
    --fin-red: #dc2626;
    --fin-red-l: rgba(220,38,38,.10);
    --fin-blue: #2f2ccb;
    --fin-blue-l: rgba(47,44,203,.10);
    --fin-amber: #d97706;
    --fin-amber-l: rgba(217,119,6,.10);
    --fin-purple: #7c3aed;
    --fin-purple-l: rgba(124,58,237,.10);
    --surface: #ffffff;
    --bg: #f0f4f8;
    --border: #e2e8f0;
    --text-1: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --radius: 16px;
    --radius-sm: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.05);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.10);
}
*{font-family:'DM Sans',sans-serif;box-sizing:border-box;}
body{background:var(--bg);}

/* Hero Section */
.fin-hero{
    background: linear-gradient(135deg, #464592 0%, #1613c9 60%, #050352 100%);
    border-radius: 24px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}
.fin-hero::before{
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(165,180,252,.2) 0%, transparent 70%);
}
.fin-hero h1{color:#fff;font-size:1.5rem;font-weight:700;margin:0;}
.fin-hero p{color:#c7d2fe;margin:.2rem 0 0;font-size:.88rem;}
.hero-badge{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    background:rgba(47,44,203,.25);
    border:1px solid rgba(165,180,252,.4);
    color:#a5b4fc;
    padding:.25rem .75rem;
    border-radius:20px;
    font-size:.75rem;
    font-weight:600;
    margin-bottom:.6rem;
}

/* Cards */
.fin-card{
    background:var(--surface);
    border-radius:var(--radius);
    border:1px solid var(--border);
    box-shadow:var(--shadow);
    overflow:hidden;
    margin-bottom:1.5rem;
}
.fin-card-header{
    padding:1.1rem 1.5rem;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#fafbff;
    flex-wrap:wrap;
    gap:1rem;
}
.fin-card-header h3{
    margin:0;
    font-size:.95rem;
    font-weight:700;
    color:var(--text-1);
    display:flex;
    align-items:center;
    gap:.6rem;
}

/* Form Container */
.form-container{padding:1.5rem;}
.form-group{margin-bottom:1.25rem;}
.form-group label{
    display:block;
    margin-bottom:.5rem;
    font-weight:600;
    color:var(--text-2);
    font-size:.85rem;
}
.form-group label .required{color:var(--fin-red);margin-left:.2rem;}
.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:.7rem .9rem;
    border-radius:12px;
    border:1.5px solid var(--border);
    font-size:.9rem;
    transition:all .2s;
    background:var(--surface);
    color:var(--text-1);
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    outline:none;
    border-color:#2f2ccb;
    box-shadow:0 0 0 3px rgba(47,44,203,.1);
}
.form-group input[readonly],
.form-group input:disabled{
    background:#f8fafc;
    color:var(--text-3);
    cursor:not-allowed;
}
.form-row{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;}

/* Buttons */
.btn-fin{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.6rem 1.25rem;
    border-radius:10px;
    font-size:.875rem;
    font-weight:600;
    border:none;
    cursor:pointer;
    text-decoration:none;
    transition:all .18s;
}
.btn-primary-fin{background:#2f2ccb;color:#fff;}
.btn-primary-fin:hover{background:#2420a8;transform:translateY(-1px);box-shadow:0 4px 14px rgba(47,44,203,.35);}
.btn-outline-fin{background:transparent;border:1.5px solid var(--border);color:var(--text-2);}
.btn-outline-fin:hover{border-color:#2f2ccb;color:#2f2ccb;}

/* Form Actions */
.form-actions{
    display:flex;
    gap:.75rem;
    justify-content:flex-end;
    padding-top:1rem;
    border-top:1px solid var(--border);
    margin-top:1rem;
}

/* Info Note */
.info-note{
    background:rgba(47,44,203,.08);
    border-radius:12px;
    padding:.8rem 1rem;
    margin-bottom:1.5rem;
    display:flex;
    align-items:center;
    gap:.5rem;
    font-size:.8rem;
    color:#2f2ccb;
    border:1px solid rgba(47,44,203,.15);
}
.info-note i{font-size:1rem;}

/* Category Preview */
.category-preview{
    background:#f8fafc;
    border-radius:12px;
    padding:.5rem .8rem;
    margin-top:.5rem;
    font-size:.75rem;
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    border:1px solid var(--border);
}

/* Amount Mono */
.amount-mono{font-family:'DM Mono',monospace;font-weight:600;}

/* Loading Overlay */
.loading{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.5);
    backdrop-filter:blur(4px);
    z-index:9999;
    align-items:center;
    justify-content:center;
}
.loading-spinner{
    width:50px;
    height:50px;
    border:4px solid var(--border);
    border-top-color:#2f2ccb;
    border-radius:50%;
    animation:spin 1s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg);}}

/* Responsive */
@media(max-width:768px){
    .fin-hero{padding:1.5rem;}
    .fin-hero h1{font-size:1.3rem;}
    .form-row{grid-template-columns:1fr;}
    .form-actions{flex-direction:column-reverse;}
    .form-actions .btn-fin{width:100%;justify-content:center;}
    .fin-card-header{flex-direction:column;align-items:flex-start;}
}
</style>
@endsection

@section('page-header')
<div class="fin-hero mt-5">
    <div style="position:relative;z-index:1;">
        <div class="hero-badge"><i class="fas fa-plus-circle"></i> Finance — {{ isset($expense) ? 'Edit' : 'New' }} Expense</div>
        <h1>{{ isset($expense) ? 'Edit Expense' : 'Record New Expense' }}</h1>
        <p>{{ isset($expense) ? 'Update expense details' : 'Enter expense information and track school spending' }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="fin-card">
    <div class="fin-card-header">
        <h3><i class="fas fa-file-invoice-dollar"></i> {{ isset($expense) ? 'Edit Expense Information' : 'Expense Information' }}</h3>
        @if(isset($expense))
        <span style="font-size:.75rem;color:var(--text-3);">Expense #: <strong class="amount-mono">{{ $expense->expense_number }}</strong></span>
        @else
        <span style="font-size:.75rem;color:#000;">New Record</span>
        @endif
    </div>

    <form method="POST" action="{{ isset($expense) ? route('finance.expenses.update', $expense->id) : route('finance.expenses.store') }}" id="expenseForm">
        @csrf
        @if(isset($expense)) @method('PUT') @endif
        
        <div class="form-container">
            {{-- Info Note --}}
            <div class="info-note">
                <i class="fas fa-info-circle"></i>
                <span>All expenses are recorded as <strong>paid</strong> by default. You can edit status later if needed.</span>
            </div>

            {{-- Expense Number (display only for edit) --}}
            @if(isset($expense))
            <div class="form-group">
                <label>Expense Number</label>
                <input type="text" value="{{ $expense->expense_number }}" readonly disabled>
            </div>
            @endif

            {{-- Category Selection --}}
            <div class="form-group">
                <label>Expense Category <span class="required">*</span></label>
                <select name="category_id" id="category_id" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" 
                            data-color="{{ $cat->color }}"
                            data-icon="{{ $cat->icon }}"
                            {{ (isset($expense) && $expense->category_id == $cat->id) ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
                <div id="categoryPreview" class="category-preview" style="display:none;"></div>
            </div>

            {{-- Title & Description --}}
            <div class="form-group">
                <label>Expense Title <span class="required">*</span></label>
                <input type="text" name="title" id="title" required 
                       value="{{ old('title', $expense->title ?? '') }}"
                       placeholder="e.g., Staff Salaries - March, Library Books Purchase">
            </div>

            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="3" placeholder="Detailed description of the expense...">{{ old('description', $expense->description ?? '') }}</textarea>
            </div>

            {{-- Amount & Date --}}
            <div class="form-row">
                <div class="form-group">
                    <label>Amount (UGX) <span class="required">*</span></label>
                    <input type="text" name="amount" id="amount" required 
                           value="{{ old('amount', isset($expense) ? number_format($expense->amount, 0) : '') }}"
                           placeholder="e.g., 500,000">
                </div>
                <div class="form-group">
                    <label>Expense Date <span class="required">*</span></label>
                    <input type="date" name="expense_date" id="expense_date" required 
                           value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}">
                </div>
            </div>

            {{-- Academic Year & Term --}}
            <div class="form-row">
                <div class="form-group">
                    <label>Academic Year <span class="required">*</span></label>
                    <select name="academic_year" id="academic_year" required>
                        <option value="{{ date('Y') }}" {{ (old('academic_year', $expense->academic_year ?? date('Y')) == date('Y')) ? 'selected' : '' }}>{{ date('Y') }}</option>
                        <option value="{{ date('Y')-1 }}" {{ (old('academic_year', $expense->academic_year ?? '') == date('Y')-1) ? 'selected' : '' }}>{{ date('Y')-1 }}</option>
                        <option value="{{ date('Y')-2 }}" {{ (old('academic_year', $expense->academic_year ?? '') == date('Y')-2) ? 'selected' : '' }}>{{ date('Y')-2 }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Term (Optional)</label>
                    <select name="term" id="term">
                        <option value="">-- Not Applicable --</option>
                        <option value="1" {{ (old('term', $expense->term ?? '') == '1') ? 'selected' : '' }}>Term 1</option>
                        <option value="2" {{ (old('term', $expense->term ?? '') == '2') ? 'selected' : '' }}>Term 2</option>
                        <option value="3" {{ (old('term', $expense->term ?? '') == '3') ? 'selected' : '' }}>Term 3</option>
                    </select>
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="form-group">
                <label>Payment Method <span class="required">*</span></label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cash" {{ (old('payment_method', $expense->payment_method ?? '') == 'cash') ? 'selected' : '' }}>💵 Cash</option>
                    <option value="bank_transfer" {{ (old('payment_method', $expense->payment_method ?? '') == 'bank_transfer') ? 'selected' : '' }}>🏦 Bank Transfer</option>
                    <option value="mobile_money" {{ (old('payment_method', $expense->payment_method ?? '') == 'mobile_money') ? 'selected' : '' }}>📱 Mobile Money</option>
                    <option value="cheque" {{ (old('payment_method', $expense->payment_method ?? '') == 'cheque') ? 'selected' : '' }}>📝 Cheque</option>
                    <option value="other" {{ (old('payment_method', $expense->payment_method ?? '') == 'other') ? 'selected' : '' }}>🔄 Other</option>
                </select>
            </div>

            {{-- Conditional Fields: Bank Transfer / Cheque --}}
            <div id="bankFields" style="display:none;">
                <div class="form-group">
                    <label>Transaction Reference / Cheque Number</label>
                    <input type="text" name="transaction_reference" id="transaction_reference_bank" 
                           value="{{ old('transaction_reference', $expense->transaction_reference ?? '') }}"
                           placeholder="e.g., TRF-2024-001 or Cheque #12345">
                </div>
            </div>

            {{-- Conditional Fields: Mobile Money --}}
            <div id="mobileFields" style="display:none;">
                <div class="form-group">
                    <label>Mobile Money Transaction Reference</label>
                    <input type="text" name="transaction_reference" id="transaction_reference_mobile" 
                           value="{{ old('transaction_reference', $expense->transaction_reference ?? '') }}"
                           placeholder="e.g., MTN/Airtel transaction ID or phone number">
                </div>
            </div>

            {{-- Payee Information --}}
            <div class="form-group">
                <label>Payee Name (Optional)</label>
                <input type="text" name="payee_name" value="{{ old('payee_name', $expense->payee_name ?? '') }}"
                       placeholder="Name of person or company receiving payment">
            </div>

            {{-- Status (only for edit) --}}
            @if(isset($expense))
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status">
                    <option value="paid" {{ $expense->status == 'paid' ? 'selected' : '' }}>✅ Paid</option>
                    <option value="approved" {{ $expense->status == 'approved' ? 'selected' : '' }}>✓ Approved</option>
                    <option value="draft" {{ $expense->status == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                    <option value="cancelled" {{ $expense->status == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                </select>
            </div>
            @endif

            {{-- Form Actions --}}
            <div class="form-actions">
                <a href="{{ route('finance.expenses.index') }}" class="btn-fin btn-outline-fin">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-fin btn-primary-fin" id="submitBtn">
                    <i class="fas fa-save"></i> {{ isset($expense) ? 'Update Expense' : 'Record Expense' }}
                </button>
            </div>
        </div>
    </form>
</div>
 </div>
        </div>
        </div>

{{-- Loading Overlay --}}
<div id="loadingOverlay" class="loading">
    <div class="loading-spinner"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// DOM Elements
const paymentMethod = document.getElementById('payment_method');
const bankFields = document.getElementById('bankFields');
const mobileFields = document.getElementById('mobileFields');
const categorySelect = document.getElementById('category_id');
const categoryPreview = document.getElementById('categoryPreview');
const amountInput = document.getElementById('amount');
const submitBtn = document.getElementById('submitBtn');
const expenseForm = document.getElementById('expenseForm');

// Format number with commas
function formatNumberWithCommas(value) {
    let numbers = value.toString().replace(/\D/g, '');
    if (numbers === '' || numbers === '0') return '';
    return parseInt(numbers, 10).toLocaleString('en-US');
}

function parseFormattedNumber(value) {
    return value.toString().replace(/,/g, '');
}

// Format amount input
if (amountInput) {
    amountInput.addEventListener('input', function(e) {
        const rawValue = this.value;
        const numericValue = parseFormattedNumber(rawValue);
        if (numericValue !== '') {
            this.value = formatNumberWithCommas(numericValue);
        }
    });
    
    // Format initial value
    if (amountInput.value && amountInput.value !== '0') {
        let rawValue = amountInput.value;
        if (!isNaN(rawValue) && rawValue.indexOf(',') === -1) {
            amountInput.value = formatNumberWithCommas(rawValue);
        }
    }
}

// Show loading overlay
function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

// Handle payment method change
function handlePaymentMethodChange() {
    const method = paymentMethod.value;
    
    bankFields.style.display = 'none';
    mobileFields.style.display = 'none';
    
    if (method === 'bank_transfer' || method === 'cheque') {
        bankFields.style.display = 'block';
    } else if (method === 'mobile_money') {
        mobileFields.style.display = 'block';
    }
}

// Handle category selection preview
function handleCategoryChange() {
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const categoryName = selectedOption.text;
    const categoryColor = selectedOption.dataset.color;
    const categoryIcon = selectedOption.dataset.icon || 'fa-tag';
    
    if (categorySelect.value) {
        categoryPreview.innerHTML = `
            <i class="fas ${categoryIcon}"></i>
            <span>Selected: <strong>${categoryName}</strong></span>
            <span style="width:10px;height:10px;background:${categoryColor};border-radius:50%;display:inline-block;margin-left:.5rem;"></span>
        `;
        categoryPreview.style.display = 'inline-flex';
    } else {
        categoryPreview.style.display = 'none';
    }
}

// Validate form before submit
function validateForm() {
    const amountRaw = parseFormattedNumber(amountInput.value);
    const amount = parseFloat(amountRaw);
    
    if (amount <= 0 || isNaN(amount)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Amount',
            text: 'Please enter a valid amount greater than 0.',
            confirmButtonColor: '#2f2ccb'
        });
        amountInput.focus();
        return false;
    }
    
    const category = categorySelect.value;
    if (!category) {
        Swal.fire({
            icon: 'error',
            title: 'Category Required',
            text: 'Please select an expense category.',
            confirmButtonColor: '#2f2ccb'
        });
        categorySelect.focus();
        return false;
    }
    
    const title = document.getElementById('title').value.trim();
    if (!title) {
        Swal.fire({
            icon: 'error',
            title: 'Title Required',
            text: 'Please enter an expense title.',
            confirmButtonColor: '#2f2ccb'
        });
        document.getElementById('title').focus();
        return false;
    }
    
    const expenseDate = document.getElementById('expense_date').value;
    if (!expenseDate) {
        Swal.fire({
            icon: 'error',
            title: 'Date Required',
            text: 'Please select the expense date.',
            confirmButtonColor: '#2f2ccb'
        });
        document.getElementById('expense_date').focus();
        return false;
    }
    
    return true;
}

// Form submit handler with SweetAlert confirmation
expenseForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateForm()) return;
    
    const isEdit = {{ isset($expense) ? 'true' : 'false' }};
    const title = isEdit ? 'Update Expense?' : 'Record New Expense?';
    const text = isEdit 
        ? 'Are you sure you want to update this expense record?'
        : 'Are you sure you want to record this expense?';
    
    // Convert amount back to raw number for submission
    const amountRaw = parseFormattedNumber(amountInput.value);
    amountInput.value = amountRaw;
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2f2ccb',
        cancelButtonColor: '#dc2626',
        confirmButtonText: isEdit ? 'Yes, update!' : 'Yes, record!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: isEdit ? 'Updating expense record...' : 'Recording expense...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    expenseForm.submit();
                }
            });
        } else {
            // Restore formatted value
            amountInput.value = formatNumberWithCommas(amountRaw);
        }
    });
});

// Event Listeners
paymentMethod.addEventListener('change', handlePaymentMethodChange);
categorySelect.addEventListener('change', handleCategoryChange);

// Initialize on page load
handlePaymentMethodChange();
handleCategoryChange();

// Set default date if not set
if (!document.getElementById('expense_date').value) {
    document.getElementById('expense_date').value = new Date().toISOString().split('T')[0];
}

// For edit mode: ensure conditional fields are shown correctly
@if(isset($expense))
    handlePaymentMethodChange();
    handleCategoryChange();
@endif

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#2f2ccb',
        timer: 3000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#2f2ccb'
    });
@endif
</script>
@endsection