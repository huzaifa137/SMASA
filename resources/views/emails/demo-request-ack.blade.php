<!DOCTYPE html>
<html>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
    <tr><td style="background:#0a1628;color:#e0c068;padding:22px 28px;font-size:20px;font-weight:bold">Thank you, {{ $demo->contact_person }}!</td></tr>
    <tr><td style="padding:26px 28px;font-size:14px;line-height:1.7">
      <p>We have received the demo request for <strong>{{ $demo->school_name }}</strong>.</p>
      <p>A member of the TechSate team will contact you within 24 hours on <strong>{{ $demo->phone }}</strong> to arrange a live walkthrough of SMASA.</p>
      <p style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:12px 16px">Your reference: <strong>{{ $demo->reference }}</strong></p>
      <p>Need us sooner? Call +256 702 082 209 or reply to this email.</p>
      <p style="color:#6b7280;margin-top:24px">TechSate Software Company LTD<br>Sky City Building, Bwaise, Kawempe Division, Kampala</p>
    </td></tr>
  </table>
</body>
</html>
