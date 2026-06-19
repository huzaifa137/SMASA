<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
  <title>SMASA – Privacy Policy | Comprehensive Academic System</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    :root {
      --navy: #0a1628;
      --navy2: #0f2040;
      --navy3: #162a50;
      --gold: #c9a84c;
      --gold2: #e8c97a;
      --gold3: #f5e0a0;
      --cream: #fdf8ef;
      --white: #ffffff;
      --text-muted: #8a9bbf;
      --teal: #0ea5a0;
      --teal2: #14c9c3;
      --green: #22c55e;
      --red: #ef4444;
    }

    html {
      scroll-behavior: smooth
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--white);
      overflow-x: hidden
    }

    .arabic {
      font-family: 'Noto Naskh Arabic', serif
    }

    /* NAV (identical to homepage) */
    nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      padding: 0 5%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 72px;
      background: rgba(10, 22, 40, 0.92);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(201, 168, 76, 0.2)
    }

    .nav-logo {
      display: flex;
      align-items: center;
      gap: 12px
    }

    .nav-logo-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--gold), var(--gold2));
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      font-weight: 900;
      color: var(--navy);
      font-family: 'Playfair Display', serif
    }

    .nav-logo-text {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--gold2)
    }

    .nav-links {
      display: flex;
      gap: 32px;
      list-style: none
    }

    .nav-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: color .2s
    }

    .nav-links a:hover {
      color: var(--gold2)
    }

    .nav-cta {
      background: linear-gradient(135deg, var(--gold), var(--gold2));
      color: var(--navy);
      border: none;
      padding: 10px 24px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: transform .2s, box-shadow .2s
    }

    .nav-cta:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 24px rgba(201, 168, 76, 0.35)
    }

    /* PAGE CONTAINER */
    .policy-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 120px 5% 80px;
    }

    /* Policy card styles – matches homepage elegance */
    .policy-card {
      background: rgba(15, 32, 64, 0.6);
      backdrop-filter: blur(2px);
      border: 1px solid rgba(201, 168, 76, 0.2);
      border-radius: 32px;
      overflow: hidden;
      transition: all 0.3s;
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
    }

    .policy-header {
      background: linear-gradient(135deg, rgba(201, 168, 76, 0.12), rgba(15, 32, 64, 0.8));
      padding: 32px 40px;
      border-bottom: 1px solid rgba(201, 168, 76, 0.25);
    }

    .policy-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--gold2);
      margin-bottom: 8px;
    }

    .policy-header .policy-meta {
      color: var(--text-muted);
      font-size: 0.9rem;
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    .policy-body {
      padding: 40px;
    }

    .policy-section {
      margin-bottom: 48px;
    }

    .policy-section h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--gold2);
      border-left: 4px solid var(--gold);
      padding-left: 20px;
      margin-bottom: 24px;
    }

    .policy-section h3 {
      font-size: 1.3rem;
      font-weight: 600;
      margin: 24px 0 16px 0;
      color: var(--gold3);
    }

    .policy-section h4 {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--teal2);
      margin: 20px 0 12px 0;
    }

    .policy-section p {
      color: var(--text-muted);
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .policy-section ul,
    .policy-section .custom-list {
      list-style: none;
      margin-bottom: 20px;
    }

    .policy-section ul li,
    .custom-list li {
      position: relative;
      padding-left: 28px;
      margin-bottom: 12px;
      color: #b9c7e0;
      line-height: 1.6;
    }

    .policy-section ul li:before {
      content: "▹";
      position: absolute;
      left: 0;
      color: var(--gold2);
      font-size: 14px;
    }

    .grid-2-cols {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    .info-card {
      background: rgba(10, 22, 40, 0.6);
      border-radius: 20px;
      padding: 24px;
      border: 1px solid rgba(201, 168, 76, 0.15);
      transition: all 0.2s;
    }

    .info-card:hover {
      border-color: rgba(201, 168, 76, 0.4);
    }

    .info-card strong {
      color: var(--gold2);
    }

    .badge {
      background: rgba(14, 165, 160, 0.12);
      color: var(--teal2);
      padding: 4px 12px;
      border-radius: 100px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      margin-right: 8px;
    }

    .table-policy {
      width: 100%;
      border-collapse: collapse;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 16px;
      overflow: hidden;
    }

    .table-policy th,
    .table-policy td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .table-policy th {
      background: rgba(201, 168, 76, 0.1);
      color: var(--gold2);
      font-weight: 600;
    }

    .alert-info {
      background: rgba(14, 165, 160, 0.1);
      border-left: 4px solid var(--teal2);
      padding: 20px;
      border-radius: 16px;
      margin: 20px 0;
    }

    .btn-back {
      background: linear-gradient(135deg, var(--gold), var(--gold2));
      color: var(--navy);
      border: none;
      padding: 12px 32px;
      border-radius: 40px;
      font-weight: 700;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .btn-back:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(201, 168, 76, 0.3);
    }

    .accordion-item {
      background: rgba(10, 22, 40, 0.5);
      border: 1px solid rgba(201, 168, 76, 0.2);
      border-radius: 16px;
      margin-bottom: 12px;
      overflow: hidden;
    }

    .accordion-header {
      padding: 16px 20px;
      font-weight: 600;
      color: var(--gold2);
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .accordion-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      padding: 0 20px;
      border-top: none;
    }

    .accordion-content.open {
      max-height: 500px;
      padding: 0 20px 20px 20px;
    }

    .footer-policy {
      background: rgba(10, 22, 40, 0.7);
      border-top: 1px solid rgba(201, 168, 76, 0.1);
      text-align: center;
      padding: 24px;
      font-size: 13px;
      color: var(--text-muted);
    }

    @media (max-width: 900px) {
      .policy-header h1 {
        font-size: 1.8rem;
      }

      .grid-2-cols {
        grid-template-columns: 1fr;
      }

      .policy-body {
        padding: 24px;
      }

      .nav-links {
        display: none;
      }
    }

    @media (max-width: 600px) {
      .policy-container {
        padding: 100px 5% 60px;
      }
    }
  </style>
</head>

<body>

  <!-- NAV -->
  <nav>
    <div class="nav-logo">
      <div class="nav-logo-icon">S</div>
      <span class="nav-logo-text">SMASA</span>
    </div>
    <ul class="nav-links">
      <li><a href="{{ url('/') }}">Home</a></li>
      <li><a href="{{ url('/') }}">Modules</a></li>
      <li><a href="{{ url('/') }}">Features</a></li>
      <li><a href="{{ url('/') }}">Attendance</a></li>
      <li><a href="{{ url('/') }}">Timetable</a></li>
      <li><a href="{{ url('/') }}">Pricing</a></li>
      <li><a href="{{ url('/users/privacy-policy') }}">Privacy Policy</a></li>

      <li><a href="{{ url('/') }}">Contact</a></li>
    </ul>
    <button class="nav-cta" onclick="window.location.href='/users/login'">
      <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Login
    </button>
  </nav>

  <div class="policy-container">
    <div class="policy-card fade-in">
      <div class="policy-header">
        <h1><i class="fas fa-shield-alt me-3" style="color:var(--gold2);"></i> Privacy Policy</h1>
        <div class="policy-meta">
          <span><i class="far fa-calendar-alt"></i> Last Updated: May 23, 2026</span>
          <span><i class="fas fa-gavel"></i> Effective Date: May 23, 2026</span>
          <span><i class="fas fa-building"></i> SMASA – Comprehensive Academic System</span>
        </div>
      </div>
      <div class="policy-body">

        <!-- 1. INTRODUCTION -->
        <div class="policy-section">
          <h2><i class="fas fa-info-circle me-2"></i> 1. Introduction</h2>
          <p>Welcome to <strong>SMASA (School Management and Administration System)</strong>, a comprehensive school
            management platform developed by <strong>TechSate Software Company</strong>. We are committed to protecting
            the privacy and security of all users, including students, teachers, parents, and school administrators.</p>
          <p>This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our
            Service. By accessing or using SMASA, you agree to the terms outlined in this policy.</p>
          <div class="alert-info">
            <i class="fas fa-lock me-2" style="color:var(--teal2);"></i> <strong>Our commitment:</strong> We process
            personal data transparently and only for legitimate educational purposes.
          </div>
        </div>

        <!-- 2. INFORMATION WE COLLECT (Cards + nested) -->
        <div class="policy-section">
          <h2><i class="fas fa-database me-2"></i> 2. Information We Collect</h2>
          <div class="grid-2-cols">
            <div class="info-card">
              <strong><i class="fas fa-user me-2"></i> Personal Information</strong><br>
              Account info, student records (name, DOB, gender, address, photo), teacher/staff profiles, parent/guardian
              contact details, school administrative data.
            </div>
            <div class="info-card">
              <strong><i class="fas fa-graduation-cap me-2"></i> Academic & Administrative Data</strong><br>
              Class allocations, subject enrollments, examination grades, attendance logs, discipline records, fee
              payments (if applicable).
            </div>
            <div class="info-card">
              <strong><i class="fas fa-laptop-code me-2"></i> Technical Data</strong><br>
              IP address, browser type, device info, OS, timestamps, pages visited, cookies and similar tracking
              technologies.
            </div>
            <div class="info-card">
              <strong><i class="fas fa-cookie-bite me-2"></i> Cookies</strong><br>
              Essential, functional, analytics, and security cookies to improve performance and security.
            </div>
          </div>
          <p class="mt-3">All collected data is strictly limited to what is necessary for operating the academic
            platform and ensuring a seamless experience for schools.</p>
        </div>

        <!-- 3. HOW WE USE INFORMATION (table) -->
        <div class="policy-section">
          <h2><i class="fas fa-cogs me-2"></i> 3. How We Use Your Information</h2>
          <table class="table-policy">
            <thead>
              <tr>
                <th>Purpose</th>
                <th>Data Used</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Provide & maintain Service</td>
                <td>All personal and academic data</td>
              </tr>
              <tr>
                <td>Manage user accounts</td>
                <td>Account information, credentials</td>
              </tr>
              <tr>
                <td>Process school admin tasks</td>
                <td>School, student, teacher data</td>
              </tr>
              <tr>
                <td>Generate academic reports</td>
                <td>Examination results, attendance, grades</td>
              </tr>
              <tr>
                <td>Communicate with users</td>
                <td>Contact info (email, phone)</td>
              </tr>
              <tr>
                <td>Improve & secure service</td>
                <td>Usage data, IP addresses, logs</td>
              </tr>
              <tr>
                <td>Comply with legal obligations</td>
                <td>All data as required by law</td>
              </tr>
              <tr>
                <td>Prevent fraud & abuse</td>
                <td>Technical and behavioral patterns</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 4. LEGAL BASIS (GDPR) -->
        <div class="policy-section">
          <h2><i class="fas fa-balance-scale me-2"></i> 4. Legal Basis for Processing (GDPR Compliance)</h2>
          <ul>
            <li><strong>Consent:</strong> When explicit consent is provided.</li>
            <li><strong>Contract Performance:</strong> To fulfill service agreements with schools.</li>
            <li><strong>Legal Obligation:</strong> Compliance with applicable laws.</li>
            <li><strong>Legitimate Interests:</strong> Security, fraud prevention, service improvement.</li>
            <li><strong>Vital Interests:</strong> To protect safety of students and staff.</li>
          </ul>
        </div>

        <!-- 5. INFORMATION SHARING -->
        <div class="policy-section">
          <h2><i class="fas fa-share-alt me-2"></i> 5. Information Sharing and Disclosure</h2>
          <p>We share data only as necessary for school operations and legal compliance:</p>
          <ul>
            <li><strong>With Your School:</strong> Administrators, teachers, and authorized personnel.</li>
            <li><strong>With Service Providers:</strong> Cloud hosting (Google Cloud Platform), email services,
              analytics, security providers – all contractually bound to confidentiality.</li>
            <li><strong>Legal Reasons:</strong> To comply with law enforcement, court orders, or public authorities.
            </li>
          </ul>
          <div class="alert-info"><i class="fas fa-ban me-2"></i> <strong>We do NOT sell your personal information to
              third parties.</strong></div>
        </div>

        <!-- 6. DATA SECURITY -->
        <div class="policy-section">
          <h2><i class="fas fa-shield-alt me-2"></i> 6. Data Security</h2>
          <div class="grid-2-cols">
            <div class="info-card">
              <p><i class="fas fa-lock me-2" style="color: var(--gold2);"></i> <strong>Encryption:</strong> HTTPS/TLS in
                transit & AES-256 at rest.</p>
              <p><i class="fas fa-user-shield me-2" style="color: var(--gold2);"></i> <strong>Role-Based Access Control
                  (RBAC):</strong> Granular permissions for every user type.</p>
              <p><i class="fas fa-key me-2" style="color: var(--gold2);"></i> <strong>Password Security:</strong> bcrypt
                hashed passwords.</p>
              <p><i class="fas fa-file-alt me-2" style="color: var(--gold2);"></i> <strong>Audit Trails:</strong>
                Comprehensive logging of all system activities.</p>
            </div>
            <div class="info-card">
              <p><i class="fas fa-cloud me-2" style="color: var(--gold2);"></i> <strong>Automated Backups:</strong>
                Regular backups with disaster recovery procedures.</p>
              <p><i class="fas fa-firewall me-2" style="color: var(--gold2);"></i> <strong>Network Security:</strong>
                Firewalls & DDoS protection.</p>
              <p><i class="fas fa-database me-2" style="color: var(--gold2);"></i> <strong>Data Minimization:</strong>
                We collect only what is necessary for our Service.</p>
              <p><i class="fas fa-server me-2" style="color: var(--gold2);"></i> <strong>Infrastructure:</strong> Google
                Cloud Platform secure data centers.</p>
            </div>
          </div>
          <div class="alert-info mt-3">
            <i class="fas fa-shield-virus me-2"></i> <strong>Security First:</strong> We continuously monitor and update
            our security practices to protect your data.
          </div>
        </div>

        <!-- 7. DATA RETENTION -->
        <div class="policy-section">
          <h2><i class="fas fa-clock me-2"></i> 7. Data Retention</h2>
          <table class="table-policy">
            <thead>
              <tr>
                <th>Data Type</th>
                <th>Retention Period</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Student academic records</td>
                <td>7-10 years after graduation (per educational laws)</td>
              </tr>
              <tr>
                <td>User account information</td>
                <td>Active account + 2 years</td>
              </tr>
              <tr>
                <td>Attendance records</td>
                <td>As required by school policies</td>
              </tr>
              <tr>
                <td>Examination results</td>
                <td>Permanent (transcripts)</td>
              </tr>
              <tr>
                <td>System logs</td>
                <td>90 days</td>
              </tr>
              <tr>
                <td>Backups</td>
                <td>30–90 days (rotating)</td>
              </tr>
            </tbody>
          </table>
          <p>You may request deletion of data as per Section 9.</p>
        </div>

        <!-- 8. COOKIES -->
        <div class="policy-section">
          <h2><i class="fas fa-cookie-bite me-2"></i> 8. Cookies & Tracking Technologies</h2>
          <p>We use essential cookies (laravel_session, XSRF-TOKEN), functional cookies (remember preferences),
            analytics (Google Analytics) and security cookies. You can manage cookies via browser settings, but
            essential cookies are required for platform operation.</p>
        </div>

        <!-- 9. YOUR PRIVACY RIGHTS -->
        <div class="policy-section">
          <h2><i class="fas fa-user-check me-2"></i> 9. Your Privacy Rights</h2>
          <div class="grid-2-cols">
            <div class="info-card"><strong>Access & Portability</strong><br>Request access or receive a portable copy of
              your data.</div>
            <div class="info-card"><strong>Correction</strong><br>Rectify inaccurate or incomplete information.</div>
            <div class="info-card"><strong>Deletion</strong><br>Request erasure, subject to legal holds.</div>
            <div class="info-card"><strong>Restriction & Objection</strong><br>Restrict processing or object to
              legitimate interest processing.</div>
            <div class="info-card"><strong>Withdraw Consent</strong><br>Withdraw consent at any time when processing is
              based on consent.</div>
          </div>
          <div class="alert-info mt-3">
            <i class="fas fa-envelope me-2"></i> To exercise these rights, contact us at
            <strong>privacy@techsatesoftwarecompany.com</strong>. Response within 30 days.
          </div>
        </div>

        <!-- 10. CHILDREN'S PRIVACY (COPPA) -->
        <div class="policy-section">
          <h2><i class="fas fa-child me-2"></i> 10. Children's Privacy (COPPA Compliance)</h2>
          <p>SMASA is designed for schools. Schools act as data controllers and are responsible for obtaining parental
            consent where required (COPPA). We do not use student information for targeted advertising. Parents may
            review or delete child's information upon request.</p>
        </div>

        <!-- 11-12. THIRD PARTY & INTERNATIONAL TRANSFERS -->
        <div class="policy-section">
          <h2><i class="fas fa-globe me-2"></i> 11. International Transfers & Third-Party Links</h2>
          <p>Data may be processed on Google Cloud Platform servers located in the US/EU. We utilize Standard
            Contractual Clauses (SCCs) for adequate protection. Our service may contain links to external sites — we are
            not responsible for their privacy practices.</p>
        </div>

        <!-- 13. CONTACT US -->
        <div class="policy-section">
          <h2><i class="fas fa-envelope me-2"></i> 12. Contact Us & Data Controller</h2>
          <div class="info-card" style="margin-bottom: 20px;">
            <p><strong>Data Controller:</strong> TechSate Software Company<br>
              <strong>Address:</strong> Kampala, Uganda<br>
              <strong>Email:</strong> <a href="mailto:privacy@techsatesoftwarecompany.com"
                style="color:var(--gold2);">privacy@techsatesoftwarecompany.com</a><br>
              <strong>Phone:</strong> +256 702 082 209<br>
              <strong>DPO:</strong> TechSate Software Company – dpo@techsatesoftwarecompany.com
            </p>
          </div>
          <p>For schools acting as data controllers: please direct privacy inquiries from your community to your
            designated school privacy contact.</p>
        </div>

        <!-- 14. CHANGES & 15. JURISDICTION NOTICES (Accordion) -->
        <div class="policy-section">
          <h2><i class="fas fa-sync-alt me-2"></i> 13. Changes to Policy & Jurisdiction-Specific Notices</h2>
          <p>We may update this Privacy Policy. Changes will be posted with updated "Last Updated" date. Please review
            periodically.</p>
          <div class="accordion-item" onclick="this.querySelector('.accordion-content').classList.toggle('open')">
            <div class="accordion-header">🇺🇸 United States (COPPA, FERPA) <i class="fas fa-chevron-down"></i></div>
            <div class="accordion-content">We comply with COPPA (parental consent for under 13) and FERPA regarding
              student education records.</div>
          </div>
          <div class="accordion-item" onclick="this.querySelector('.accordion-content').classList.toggle('open')">
            <div class="accordion-header">🇪🇺 European Union (GDPR) <i class="fas fa-chevron-down"></i></div>
            <div class="accordion-content">Legal basis per Section 4. Data subject rights under GDPR; right to lodge
              complaint with local DPA.</div>
          </div>
          <div class="accordion-item" onclick="this.querySelector('.accordion-content').classList.toggle('open')">
            <div class="accordion-header">🇺🇬 Uganda (Data Protection Act, 2019) <i class="fas fa-chevron-down"></i>
            </div>
            <div class="accordion-content">Processing aligns with the Uganda Data Protection and Privacy Act, 2019. Data
              subject rights as described.</div>
          </div>
          <div class="accordion-item" onclick="this.querySelector('.accordion-content').classList.toggle('open')">
            <div class="accordion-header">
              <i class="fas fa-globe me-2"></i> Other Jurisdictions
              <i class="fas fa-chevron-down"></i>
            </div>
            <div class="accordion-content">We adhere to applicable data protection laws in all regions where we operate.
            </div>
          </div>
        </div>

        <!-- Back to top button -->
        <div class="text-center mt-5">
          <button class="btn-back" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-arrow-up"></i>
            Back to Top</button>
        </div>
      </div>
      <div class="footer-policy">
        © 2026 SMASA – School Management and Administration System · Developed by TechSate Software Company · All rights
        reserved.
      </div>
    </div>
  </div>

  <script>
    // Simple fade-in animation (like homepage)
    const faders = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.style.opacity = '1';
      });
    }, { threshold: 0.1 });
    faders.forEach(el => { el.style.opacity = '0'; el.style.transition = 'opacity 0.7s ease'; observer.observe(el); });
    // force first visible
    document.querySelector('.policy-card').style.opacity = '1';
  </script>

  @auth
<script src="{{ asset('js/push-init.js') }}"></script>
@endauth
</body>

</html>