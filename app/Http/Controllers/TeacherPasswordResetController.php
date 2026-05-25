<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Teacher;
use App\Models\TeacherPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeacherPasswordResetController extends Controller
{
    /**
     * TOKEN EXPIRY: 60 minutes.
     * Adjust this constant to change how long a reset link stays valid.
     */
    const TOKEN_EXPIRY_MINUTES = 60;

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1 – Show the "Forgot Password" form
    // ─────────────────────────────────────────────────────────────────────────

    public function showForgotPasswordForm()
    {

        // Fetch schools for the dropdown (same query as login page)
        $schools = House::where('Head', 0)->where('ContactPerson', 0)->get();

        return view('users.teacher-forgot-password', compact('schools'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 – Handle form submission: validate, generate token, send email
    // ─────────────────────────────────────────────────────────────────────────

    public function sendResetLink(Request $request)
    {

        $request->validate([
            'email'     => ['required', 'email'],
            'school_id' => ['required', 'integer'],
        ]);

        $email    = trim(strtolower($request->email));
        $schoolId = (int) $request->school_id;

        // Look up the teacher by BOTH email and school_id.
        // This is the crucial check since one email can exist across many schools.
        $teacher = Teacher::where('email', $email)
                          ->where('school_id', $schoolId)
                          ->first();

        /*
         * SECURITY NOTE – Prevent user enumeration:
         * Whether the teacher exists or not, we return the same generic success
         * message. This prevents attackers from fishing for valid email/school
         * combinations.
         */


        if (! $teacher) {
            return response()->json([
                'success' => true,
                'message' => 'If that email is registered under the selected school, a reset link has been sent.',
            ]);
        }



        // ── Invalidate all previous unused tokens for this teacher/school ──
        TeacherPasswordReset::where('email', $email)
            ->where('school_id', $schoolId)
            ->where('link_status', 0)
            ->update(['link_status' => 1]);

        // ── Generate a cryptographically secure token ──
        $plainToken = Str::random(64);          // What goes in the email URL
        $hashedToken = hash('sha256', $plainToken); // What we store in DB (never the raw token)

        // ── Persist the reset record ──
        TeacherPasswordReset::create([
            'email'      => $email,
            'school_id'  => $schoolId,
            'token'      => $plainToken,        // Kept plain for URL; hashed value is what DB uses for lookup
            'token_hash' => $hashedToken,
            'link_status'=> 0,
            'expires_at' => now()->addMinutes(self::TOKEN_EXPIRY_MINUTES),
        ]);

        // ── Build the reset URL – token is plain in URL, school_id encoded ──
        $resetUrl = route('teacher.password.reset.form', [
            'token'    => $plainToken,
            'school'   => $schoolId,
        ]);

        // ── Compose teacher display name ──
        $teacherName = trim(($teacher->firstname ?? '') . ' ' . ($teacher->surname ?? ''));
        if (empty(trim($teacherName))) {
            $teacherName = 'Teacher';
        }

        // ── Fetch school name for the email ──
        $school = House::find($schoolId);
        $schoolName = $school ? $school->House : 'Your School';

        // ── Send the reset email ──
        $emailData = [
            'teacherName' => $teacherName,
            'schoolName'  => $schoolName,
            'resetUrl'    => $resetUrl,
            'expiryMins'  => self::TOKEN_EXPIRY_MINUTES,
        ];

        try {
            Mail::send('emails.teacher-reset-password', $emailData, function ($message) use ($email, $schoolName) {
                $message->to($email)
                        ->subject("Password Reset Request – {$schoolName} | SMASA");
            });
        } catch (\Exception $e) {
            // Log but do NOT expose error details to the user
            \Log::error('Teacher password reset email failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'We could not send the reset email at this time. Please contact your school administrator.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'If that email is registered under the selected school, a reset link has been sent.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 3 – Show the "Reset Password" form (after clicking email link)
    // ─────────────────────────────────────────────────────────────────────────

    public function showResetPasswordForm(Request $request, string $token)
    {
        $schoolId = $request->query('school');

        // Look up by the plain token (stored in DB before hashing for lookup)
        $resetRecord = TeacherPasswordReset::where('token', $token)
                                           ->where('school_id', $schoolId)
                                           ->first();

        // ── Validate the token ──
        if (! $resetRecord) {
            return redirect()->route('teacher.forgot.password')
                             ->with('error', 'This password reset link is invalid. Please request a new one.');
        }

        if ($resetRecord->link_status === 1) {
            return redirect()->route('teacher.forgot.password')
                             ->with('error', 'This reset link has already been used. Please request a new one.');
        }

        if (now()->greaterThan($resetRecord->expires_at)) {
            // Mark it as consumed to keep DB clean
            $resetRecord->markUsed();

            return redirect()->route('teacher.forgot.password')
                             ->with('error', 'This reset link has expired (links are valid for ' . self::TOKEN_EXPIRY_MINUTES . ' minutes). Please request a new one.');
        }

        // Fetch school name for display
        $school     = House::find($schoolId);
        $schoolName = $school ? $school->House : '';

        return view('users.teacher-reset-password', [
            'token'      => $token,
            'school_id'  => $schoolId,
            'schoolName' => $schoolName,
            'email'      => $resetRecord->email,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 4 – Handle the new password submission
    // ─────────────────────────────────────────────────────────────────────────

    public function resetPassword(Request $request)
    {
        $request->validate(
            [
                'token'                 => ['required', 'string'],
                'school_id'             => ['required', 'integer'],
                'password'              => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',      // at least one uppercase
                    'regex:/[a-z]/',      // at least one lowercase
                    'regex:/[0-9]/',      // at least one digit
                    'regex:/[@$!%*?&#]/', // at least one special char
                    'confirmed',          // must match password_confirmation field
                ],
                'password_confirmation' => ['required'],
            ],
            [
                'password.min'    => 'Password must be at least 8 characters.',
                'password.regex'  => 'Password must include an uppercase letter, a lowercase letter, a number, and a special character (@$!%*?&#).',
                'password.confirmed' => 'Passwords do not match.',
            ]
        );

        $token    = $request->token;
        $schoolId = (int) $request->school_id;

        // Fetch the reset record
        $resetRecord = TeacherPasswordReset::where('token', $token)
                                           ->where('school_id', $schoolId)
                                           ->first();

        // ── Re-validate token integrity (guard against race conditions) ──
        if (! $resetRecord || ! $resetRecord->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This reset link is invalid or has expired. Please request a new one.',
            ], 422);
        }

        // ── Update the teacher's password ──
        $updated = Teacher::where('email', $resetRecord->email)
                          ->where('school_id', $schoolId)
                          ->update([
                              'password'             => Hash::make($request->password),
                              'must_change_password' => false, // They've now set their own password
                          ]);

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update the password. Please contact your administrator.',
            ], 500);
        }

        // ── Consume (invalidate) the token immediately ──
        $resetRecord->markUsed();

        // ── Also invalidate any other unused tokens for this email/school ──
        TeacherPasswordReset::where('email', $resetRecord->email)
            ->where('school_id', $schoolId)
            ->where('link_status', 0)
            ->update(['link_status' => 1]);

        return response()->json([
            'success'  => true,
            'message'  => 'Your password has been updated successfully. Please sign in with your new password.',
            'redirect' => route('users.login'),
        ]);
    }
}
