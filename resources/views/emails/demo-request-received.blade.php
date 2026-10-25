<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
    <tr><td style="background:#0a1628;color:#e0c068;padding:22px 28px">
      <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;opacity:.8">SMASA &middot; New demo request</div>
      <div style="font-size:22px;font-weight:bold;margin-top:6px">{{ $demo->school_name }}</div>
      <div style="font-size:13px;margin-top:4px;color:#cbd5e1">Reference {{ $demo->reference }} &middot; {{ $demo->created_at->format('d M Y, H:i') }}</div>
    </td></tr>
    <tr><td style="padding:26px 28px">
      <table width="100%" cellpadding="8" cellspacing="0" style="font-size:14px">
        <tr><td style="color:#6b7280;width:150px">Contact person</td><td><strong>{{ $demo->contact_person }}</strong></td></tr>
        <tr><td style="color:#6b7280">Phone</td><td><a href="tel:{{ preg_replace('/\s+/', '', $demo->phone) }}" style="color:#2563eb">{{ $demo->phone }}</a></td></tr>
        <tr><td style="color:#6b7280">Email</td><td><a href="mailto:{{ $demo->email }}" style="color:#2563eb">{{ $demo->email }}</a></td></tr>
        <tr><td style="color:#6b7280">School type</td><td>{{ $demo->schoolTypeLabel() }}</td></tr>
        <tr><td style="color:#6b7280">Students</td><td>{{ $demo->studentRangeLabel() }}</td></tr>
        <tr><td style="color:#6b7280;vertical-align:top">Message</td><td>{!! $demo->message ? nl2br(e($demo->message)) : '<span style="color:#9ca3af">— none —</span>' !!}</td></tr>
      </table>
      <div style="margin-top:22px">
        <a href="{{ url('/admin/demo-requests') }}" style="background:#0a1628;color:#e0c068;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:bold;font-size:14px;display:inline-block">Open in SMASA inbox</a>
      </div>
      <p style="font-size:12px;color:#9ca3af;margin-top:22px">Tip: just hit Reply — it goes straight to {{ $demo->email }}.</p>
    </td></tr>
  </table>
</body>
</html>
