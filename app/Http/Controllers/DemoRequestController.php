<?php

namespace App\Http\Controllers;

use App\Mail\DemoRequestAcknowledgement;
use App\Mail\DemoRequestReceived;
use App\Models\DemoRequest;
use App\Support\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Homepage "Request a Demo" form.
 *
 *  store()   PUBLIC  - saves the request, emails the team (and the school).
 *  index()   ADMIN   - inbox with filters             (demo_requests.view)
 *  update()  ADMIN   - status / internal notes        (demo_requests.manage)
 */
class DemoRequestController extends Controller
{
    // ── Public ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        // Honeypot: real people never see or fill this field. Pretend success to bots.
        if ($request->filled('website')) {
            return $this->respond($request, true, 'Thank you! We will contact you within 24 hours.', 'DEMO-OK');
        }

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:191'],
            'contact_person' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+\-\s()]{7,40}$/'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'school_type' => ['required', Rule::in(array_keys(config('demo_requests.school_types')))],
            'student_range' => ['required', Rule::in(array_keys(config('demo_requests.student_ranges')))],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [
            'school_name.required' => 'Please enter your school name.',
            'contact_person.required' => 'Please tell us who we should speak to.',
            'phone.required' => 'Please enter a phone number we can reach you on.',
            'phone.regex' => 'That phone number does not look right.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'That email address does not look right.',
            'school_type.required' => 'Please select your school type.',
            'student_range.required' => 'Please select your approximate student population.',
        ]);

        // Double-click / resubmit protection: same school + email in the last 10 minutes.
        $existing = DemoRequest::where('email', $data['email'])
            ->where('school_name', $data['school_name'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        if ($existing) {
            return $this->respond($request, true, 'We already have your request. Our team will contact you shortly.', $existing->reference);
        }

        $demo = DemoRequest::create($data + [
            'reference' => DemoRequest::makeReference(),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->notify($demo);

        return $this->respond($request, true, 'Thank you! TechSate will contact you within 24 hours.', $demo->reference);
    }

    /** The request is already saved; mail trouble must never lose it or show the school an error. */
    private function notify(DemoRequest $demo): void
    {
        $to = config('demo_requests.notify_to');

        if (!empty($to)) {
            try {
                Mail::to($to)->cc(config('demo_requests.notify_cc'))->send(new DemoRequestReceived($demo));
                $demo->forceFill(['notified_at' => now(), 'notify_error' => null])->save();
            } catch (\Throwable $e) {
                Log::error('Demo request email failed', ['reference' => $demo->reference, 'error' => $e->getMessage()]);
                $demo->forceFill(['notify_error' => substr($e->getMessage(), 0, 1000)])->save();
            }
        }

        if (config('demo_requests.send_acknowledgement')) {
            try {
                Mail::to($demo->email)->send(new DemoRequestAcknowledgement($demo));
            } catch (\Throwable $e) {
                Log::warning('Demo request acknowledgement failed', ['reference' => $demo->reference, 'error' => $e->getMessage()]);
            }
        }
    }

    private function respond(Request $request, bool $ok, string $message, string $reference)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message, 'reference' => $reference]);
        }

        return redirect(url('/') . '#contact')->with('demo_success', $message . ' (Ref: ' . $reference . ')');
    }

    // ── Admin inbox ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $statuses = config('demo_requests.statuses');
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $query = DemoRequest::query()->latest();

        if ($status && isset($statuses[$status])) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('school_name', 'like', $like)
                    ->orWhere('contact_person', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('reference', 'like', $like);
            });
        }

        $counts = DemoRequest::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('Admin.demo-requests.index', [
            'requests' => $query->paginate((int) config('demo_requests.per_page', 20))->withQueryString(),
            'statuses' => $statuses,
            'counts' => $counts,
            'total' => $counts->sum(),
            'activeStatus' => $status,
            'search' => $q,
            'canManage' => AdminAccess::can('demo_requests.manage'),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $demo = DemoRequest::findOrFail($id);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('demo_requests.statuses')))],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($data['status'] !== 'new' && !$demo->contacted_at) {
            $demo->contacted_at = now();
        }

        $demo->fill($data);
        $demo->handled_by = AdminAccess::userId();
        $demo->save();

        return back()->with('success', "{$demo->school_name} ({$demo->reference}) updated.");
    }
}
