@extends('layouts-side-bar.master')

@section('css')
    <style>
        /* ------------------------------------------------------------
           Admin "User Profile" — mirrors the school-portal profile look
           ------------------------------------------------------------ */
        .apf-wrap { max-width: 1100px; margin: 0 auto; padding: 8px 4px 40px; }

        .apf-head {
            display: flex; flex-wrap: wrap; gap: 12px;
            align-items: center; justify-content: space-between;
            margin-bottom: 22px;
        }
        .apf-head h2 { margin: 0 0 2px; font-weight: 700; font-size: 1.6rem; }
        .apf-head p { margin: 0; color: #6b7280; }

        .apf-card {
            background: #fff; border: 0; border-radius: 16px;
            box-shadow: 0 2px 10px rgba(28, 43, 82, .07);
            height: 100%;
        }
        .apf-card-head { padding: 20px 24px 0; }
        .apf-card-head h5 { margin: 0; font-weight: 700; }
        .apf-card-head h5 i { color: #2C29CA; margin-right: 8px; }
        .apf-card-body { padding: 20px 24px 24px; }

        .apf-avatar {
            width: 120px; height: 120px; margin: 0 auto 14px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 42px; font-weight: 700; color: #fff; letter-spacing: 1px;
            background: linear-gradient(135deg, #6f6dff, #2C29CA);
            border: 4px solid #fff; box-shadow: 0 0 0 3px #e9ecef;
            text-transform: uppercase;
        }
        .apf-name { font-weight: 700; margin-bottom: 4px; word-break: break-word; }
        .apf-user { color: #2C29CA; margin-bottom: 14px; word-break: break-all; }

        .apf-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 700;
        }
        .apf-chip.role   { background: #eef0ff; color: #2C29CA; border: 1px solid #dfe2ff; }
        .apf-chip.super  { background: #fdf0d1; color: #9a6700; border: 1px solid #f6dfa5; }
        .apf-chip.active { background: #e0f5f0; color: #0e7c66; border: 1px solid #bce9de; }
        .apf-chip.off    { background: #fdeaea; color: #b91c1c; border: 1px solid #f9c9c9; }

        .apf-meta { text-align: left; margin-top: 18px; }
        .apf-meta-row {
            display: flex; justify-content: space-between; gap: 12px;
            padding: 9px 0; border-top: 1px solid #f1f4fa; font-size: 13px;
        }
        .apf-meta-row span:first-child { color: #8a94a6; flex-shrink: 0; }
        .apf-meta-row span:last-child { font-weight: 600; color: #1c2b52; text-align: right; word-break: break-word; }

        .apf-label { font-weight: 600; font-size: 13px; color: #6b7280; margin-bottom: 4px; }
        .apf-input {
            width: 100%; border: 1px solid transparent; background: #f4f6fb;
            border-radius: 10px; padding: 10px 14px; font-size: 14px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .apf-input:focus {
            outline: none; background: #fff; border-color: #2C29CA;
            box-shadow: 0 0 0 4px rgba(44, 41, 202, .12);
        }
        .apf-input.is-invalid { border-color: #dc3545; background: #fff; }
        .apf-error { color: #dc3545; font-size: 12px; margin-top: 4px; }

        .apf-pw { position: relative; }
        .apf-pw .apf-input { padding-right: 46px; }
        .apf-pw button {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            border: 0; background: transparent; color: #6b7280;
            width: 36px; height: 36px; border-radius: 8px; cursor: pointer;
        }
        .apf-pw button:hover { color: #2C29CA; background: #eef0ff; }

        .apf-hint { font-size: 12px; color: #8a94a6; margin-top: 4px; }

        .apf-save { display: flex; justify-content: flex-end; margin-top: 22px; }
        .apf-save .btn { border-radius: 999px; padding: 10px 34px; }

        @media (max-width: 575.98px) {
            .apf-head h2 { font-size: 1.3rem; }
            .apf-card-head { padding: 16px 16px 0; }
            .apf-card-body { padding: 16px; }
            .apf-save .btn { width: 100%; }
        }
    </style>
@endsection

@section('content')
    @php
        // users.name is what the top navbar shows. If first/last name were never
        // filled in, pre-fill them from it so the form is not blank.
        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);
        $first = trim((string) ($user->firstname ?? '')) ?: ($nameParts[0] ?? '');
        $last = trim((string) ($user->lastname ?? '')) ?: ($nameParts[1] ?? '');
        $displayName = trim($first . ' ' . $last) !== '' ? trim($first . ' ' . $last) : ($user->name ?: $user->username);

        $initials = ($first !== '' || $last !== '')
            ? mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1))
            : mb_strtoupper(mb_substr((string) $user->username, 0, 1));

        $isActive = (bool) ($user->is_active ?? true);
        $gender = ucfirst(strtolower((string) ($user->gender ?? '')));
        $lastLogin = !empty($user->last_login_at) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->format('d M Y, H:i') : null;
        $memberSince = $user->created_at ? $user->created_at->format('d M Y') : null;
    @endphp

    <div class="apf-wrap">
        <div class="apf-head mt-5">
            <div>
                <h2><i class="fas fa-user-shield text-primary mr-2"></i>My Profile</h2>
                <p>View and update your admin account details</p>
            </div>
        </div>

        <form id="adminProfileForm" novalidate autocomplete="off">
            @csrf

            <div class="row">
                {{-- Identity card --}}
                <div class="col-lg-4 mb-4">
                    <div class="apf-card">
                        <div class="apf-card-body text-center">
                            <div class="apf-avatar" id="apfAvatar">{{ $initials }}</div>
                            <h4 class="apf-name" id="apfName">{{ $displayName }}</h4>
                            <div class="apf-user"><i class="fas fa-at mr-1"></i><span id="apfUser">{{ $user->username }}</span></div>

                            <div class="d-flex flex-wrap justify-content-center" style="gap:8px;">
                                <span class="apf-chip {{ $isSuperAdmin ? 'super' : 'role' }}">
                                    <i class="fas {{ $isSuperAdmin ? 'fa-crown' : 'fa-user-tag' }}"></i>{{ $roleName }}
                                </span>
                                <span class="apf-chip {{ $isActive ? 'active' : 'off' }}">
                                    <i class="fas fa-circle" style="font-size:7px"></i>{{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="apf-meta">
                                @if ($memberSince)
                                    <div class="apf-meta-row"><span>Account created</span><span>{{ $memberSince }}</span></div>
                                @endif
                                @if ($lastLogin)
                                    <div class="apf-meta-row"><span>Last sign-in</span><span>{{ $lastLogin }}</span></div>
                                @endif
                                <div class="apf-meta-row"><span>Role</span><span>{{ $roleName }}</span></div>
                            </div>

                            <p class="apf-hint mt-3 mb-0 text-left">
                                Your role and permissions are managed by a Super Administrator in
                                Admin Rights &amp; Privileges.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Personal information --}}
                <div class="col-lg-8 mb-4">
                    <div class="apf-card">
                        <div class="apf-card-head">
                            <h5><i class="fas fa-user-circle"></i>Personal Information</h5>
                        </div>
                        <div class="apf-card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="apf-label">First Name</div>
                                    <input type="text" name="firstname" class="apf-input" maxlength="100"
                                        value="{{ old('firstname', $first) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="apf-label">Last Name</div>
                                    <input type="text" name="lastname" class="apf-input" maxlength="100"
                                        value="{{ old('lastname', $last) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="apf-label">Username <span class="text-danger">*</span></div>
                                    <input type="text" name="username" class="apf-input" maxlength="100"
                                        value="{{ old('username', $user->username) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="apf-label">Email Address <span class="text-danger">*</span></div>
                                    <input type="email" name="email" class="apf-input" maxlength="190"
                                        value="{{ old('email', $user->email) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">Gender</div>
                                    <select name="gender" class="apf-input">
                                        <option value="">— Select —</option>
                                        <option value="Male" @selected($gender === 'Male')>Male</option>
                                        <option value="Female" @selected($gender === 'Female')>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">Phone Number</div>
                                    <input type="text" name="phonenumber" class="apf-input" maxlength="20"
                                        value="{{ old('phonenumber', $user->phonenumber) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">Country</div>
                                    <input type="text" name="country" class="apf-input" maxlength="100"
                                        value="{{ old('country', $user->country) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="col-12 mb-2">
                    <div class="apf-card">
                        <div class="apf-card-head">
                            <h5><i class="fas fa-key"></i>Change Password</h5>
                        </div>
                        <div class="apf-card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">Current Password</div>
                                    <div class="apf-pw">
                                        <input type="password" name="current_password" id="current_password"
                                            class="apf-input" placeholder="Required to set a new password"
                                            autocomplete="current-password">
                                        <button type="button" class="apf-toggle" data-target="current_password"
                                            aria-label="Show or hide password"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">New Password</div>
                                    <div class="apf-pw">
                                        <input type="password" name="password" id="password" class="apf-input"
                                            placeholder="Enter new password" autocomplete="new-password">
                                        <button type="button" class="apf-toggle" data-target="password"
                                            aria-label="Show or hide password"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="apf-label">Confirm New Password</div>
                                    <div class="apf-pw">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="apf-input" placeholder="Confirm new password"
                                            autocomplete="new-password">
                                        <button type="button" class="apf-toggle" data-target="password_confirmation"
                                            aria-label="Show or hide password"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="apf-hint">Leave all three blank to keep your current password. Minimum 4 characters.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="apf-save">
                <button type="submit" class="btn btn-primary shadow-sm" id="apfSubmit">
                    <i class="fas fa-save mr-2"></i>Save All Changes
                </button>
            </div>
        </form>
    </div></div></div></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('adminProfileForm');
            var submitBtn = document.getElementById('apfSubmit');

            // Show / hide password
            document.querySelectorAll('.apf-toggle').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(btn.getAttribute('data-target'));
                    var icon = btn.querySelector('i');
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            });

            function clearErrors() {
                form.querySelectorAll('.apf-input').forEach(function (el) { el.classList.remove('is-invalid'); });
                form.querySelectorAll('.apf-error').forEach(function (el) { el.remove(); });
            }

            function flag(name, message) {
                var el = form.querySelector('[name="' + name + '"]');
                if (!el) return;
                el.classList.add('is-invalid');
                var holder = el.closest('.apf-pw') || el;
                var note = document.createElement('div');
                note.className = 'apf-error';
                note.textContent = message;
                holder.insertAdjacentElement('afterend', note);
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearErrors();

                var v = function (n) { return (form.elements[n].value || '').trim(); };
                var problems = 0;

                if (!v('username')) { flag('username', 'Username is required.'); problems++; }
                if (!v('email') || !/^\S+@\S+\.\S+$/.test(v('email'))) { flag('email', 'A valid email address is required.'); problems++; }

                var pw = form.elements['password'].value;
                var pwc = form.elements['password_confirmation'].value;
                var cur = form.elements['current_password'].value;

                if (pw || pwc || cur) {
                    if (!cur) { flag('current_password', 'Enter your current password.'); problems++; }
                    if (!pw) { flag('password', 'Enter a new password.'); problems++; }
                    else if (pw.length < 4) { flag('password', 'Password must be at least 4 characters.'); problems++; }
                    if (pw && pw !== pwc) { flag('password_confirmation', 'Passwords do not match.'); problems++; }
                }

                if (problems) {
                    Swal.fire({ icon: 'error', title: 'Please check the form', text: 'Some fields need your attention.' });
                    return;
                }

                Swal.fire({
                    title: 'Save changes?',
                    text: 'Your profile will be updated.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    var original = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Saving... <i class="fas fa-spinner fa-spin ml-1"></i>';

                    fetch('{{ route('admin.profile.update') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form),
                        credentials: 'same-origin'
                    })
                    .then(function (res) {
                        return res.json().catch(function () { return {}; }).then(function (body) {
                            return { ok: res.ok, status: res.status, body: body };
                        });
                    })
                    .then(function (r) {
                        if (r.ok) {
                            Swal.fire({ icon: 'success', title: 'Saved', text: r.body.message || 'Profile updated.' })
                                .then(function () { window.location.reload(); });
                            return;
                        }

                        var msg = r.body.message || 'Something went wrong. Please try again.';
                        if (r.status === 422 && r.body.errors) {
                            Object.keys(r.body.errors).forEach(function (field) {
                                flag(field, r.body.errors[field][0]);
                            });
                            msg = Object.keys(r.body.errors).map(function (k) { return r.body.errors[k][0]; }).join('<br>');
                        }
                        Swal.fire({ icon: 'error', title: 'Could not save', html: msg });
                    })
                    .catch(function () {
                        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please check your connection and try again.' });
                    })
                    .finally(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = original;
                    });
                });
            });
        });
    </script>
@endsection
