<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>SMASA – Comprehensive Academic System</title>
     <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap"
          rel="stylesheet">
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

          /* ── NAV ── */
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
               background: rgba(10, 22, 40, 0.85);
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

          /* ── HERO ── */
          .hero {
               min-height: 100vh;
               display: flex;
               align-items: center;
               padding: 100px 5% 80px;
               position: relative;
               overflow: hidden
          }

          .hero-bg {
               position: absolute;
               inset: 0;
               background: radial-gradient(ellipse 80% 60% at 70% 50%, rgba(20, 42, 80, 0.9) 0%, var(--navy) 70%)
          }

          .hero-grid-lines {
               position: absolute;
               inset: 0;
               background-image: linear-gradient(rgba(201, 168, 76, 0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(201, 168, 76, 0.04) 1px, transparent 1px);
               background-size: 60px 60px;
               pointer-events: none
          }

          .hero-glow {
               position: absolute;
               top: 20%;
               right: 15%;
               width: 500px;
               height: 500px;
               background: radial-gradient(circle, rgba(201, 168, 76, 0.08) 0%, transparent 70%);
               pointer-events: none
          }

          .hero-content {
               position: relative;
               z-index: 2;
               max-width: 680px
          }

          .hero-badge {
               display: inline-flex;
               align-items: center;
               gap: 8px;
               background: rgba(201, 168, 76, 0.12);
               border: 1px solid rgba(201, 168, 76, 0.3);
               border-radius: 100px;
               padding: 6px 16px;
               font-size: 13px;
               color: var(--gold2);
               margin-bottom: 28px
          }

          .hero-badge-dot {
               width: 6px;
               height: 6px;
               background: var(--gold2);
               border-radius: 50%;
               animation: pulse 2s infinite
          }

          @keyframes pulse {

               0%,
               100% {
                    opacity: 1
               }

               50% {
                    opacity: 0.4
               }
          }

          .hero h1 {
               font-family: 'Playfair Display', serif;
               font-size: clamp(42px, 6vw, 76px);
               font-weight: 900;
               line-height: 1.05;
               margin-bottom: 20px
          }

          .hero h1 span {
               color: var(--gold2)
          }

          .hero-arabic {
               font-size: clamp(20px, 3vw, 30px);
               color: var(--gold3);
               opacity: 0.7;
               margin-bottom: 24px;
               line-height: 1.6;
               direction: rtl
          }

          .hero-desc {
               font-size: 18px;
               color: var(--text-muted);
               line-height: 1.8;
               max-width: 560px;
               margin-bottom: 40px
          }

          .hero-actions {
               display: flex;
               gap: 16px;
               flex-wrap: wrap
          }

          .btn-primary {
               background: linear-gradient(135deg, var(--gold), var(--gold2));
               color: var(--navy);
               padding: 14px 32px;
               border-radius: 10px;
               font-weight: 700;
               font-size: 15px;
               text-decoration: none;
               transition: all .2s;
               border: none;
               cursor: pointer;
               display: inline-block
          }

          .btn-primary:hover {
               transform: translateY(-2px);
               box-shadow: 0 12px 30px rgba(201, 168, 76, 0.4)
          }

          .btn-outline {
               border: 1px solid rgba(201, 168, 76, 0.4);
               color: var(--gold2);
               padding: 14px 32px;
               border-radius: 10px;
               font-weight: 600;
               font-size: 15px;
               text-decoration: none;
               transition: all .2s;
               background: transparent;
               cursor: pointer;
               display: inline-block
          }

          .btn-outline:hover {
               background: rgba(201, 168, 76, 0.08);
               border-color: var(--gold2)
          }

          .hero-stats {
               display: flex;
               gap: 48px;
               margin-top: 56px;
               padding-top: 40px;
               border-top: 1px solid rgba(201, 168, 76, 0.15)
          }

          .hero-stat-num {
               font-family: 'Playfair Display', serif;
               font-size: 36px;
               font-weight: 700;
               color: var(--gold2)
          }

          .hero-stat-label {
               font-size: 13px;
               color: var(--text-muted);
               margin-top: 2px
          }

          .hero-visual {
               position: absolute;
               right: 5%;
               top: 50%;
               transform: translateY(-50%);
               width: clamp(300px, 38vw, 520px);
               z-index: 1;
               pointer-events: none
          }

          /* FLOATING CARDS ON HERO */
          .hero-cards {
               position: relative;
               height: 480px
          }

          .hcard {
               position: absolute;
               background: rgba(15, 32, 64, 0.9);
               border: 1px solid rgba(201, 168, 76, 0.2);
               border-radius: 16px;
               padding: 20px;
               backdrop-filter: blur(20px)
          }

          .hcard-main {
               width: 280px;
               top: 50%;
               left: 50%;
               transform: translate(-50%, -50%);
               border-color: rgba(201, 168, 76, 0.4)
          }

          .hcard-tl {
               width: 170px;
               top: 20px;
               left: 10px;
               animation: float1 4s ease-in-out infinite
          }

          .hcard-tr {
               width: 160px;
               top: 30px;
               right: -20px;
               animation: float2 3.5s ease-in-out infinite
          }

          .hcard-bl {
               width: 155px;
               bottom: 40px;
               left: -10px;
               animation: float3 4.5s ease-in-out infinite
          }

          .hcard-br {
               width: 175px;
               bottom: 20px;
               right: 10px;
               animation: float1 5s ease-in-out infinite reverse
          }

          @keyframes float1 {

               0%,
               100% {
                    transform: translateY(0)
               }

               50% {
                    transform: translateY(-8px)
               }
          }

          @keyframes float2 {

               0%,
               100% {
                    transform: translateY(-4px)
               }

               50% {
                    transform: translateY(4px)
               }
          }

          @keyframes float3 {

               0%,
               100% {
                    transform: translateY(0)
               }

               50% {
                    transform: translateY(-12px)
               }
          }

          .hcard-icon {
               width: 36px;
               height: 36px;
               border-radius: 8px;
               display: flex;
               align-items: center;
               justify-content: center;
               font-size: 18px;
               margin-bottom: 10px
          }

          .hcard-title {
               font-size: 13px;
               font-weight: 600;
               color: var(--gold2);
               margin-bottom: 4px
          }

          .hcard-sub {
               font-size: 12px;
               color: var(--text-muted)
          }

          .hcard-num {
               font-family: 'Playfair Display', serif;
               font-size: 28px;
               font-weight: 700;
               color: var(--gold2)
          }

          .hcard-trend {
               font-size: 12px;
               color: var(--green);
               margin-top: 2px
          }

          .progress-bar-wrap {
               height: 4px;
               background: rgba(255, 255, 255, 0.08);
               border-radius: 2px;
               margin-top: 8px;
               overflow: hidden
          }

          .progress-bar-fill {
               height: 100%;
               border-radius: 2px;
               background: linear-gradient(90deg, var(--gold), var(--gold2))
          }

          /* ── SECTION SHELL ── */
          section {
               padding: 100px 5%
          }

          .section-label {
               font-size: 12px;
               font-weight: 600;
               letter-spacing: 0.12em;
               text-transform: uppercase;
               color: var(--gold);
               margin-bottom: 16px
          }

          .section-title {
               font-family: 'Playfair Display', serif;
               font-size: clamp(32px, 4vw, 52px);
               font-weight: 700;
               line-height: 1.15;
               margin-bottom: 20px
          }

          .section-desc {
               font-size: 17px;
               color: var(--text-muted);
               line-height: 1.8;
               max-width: 580px
          }

          .center {
               text-align: center
          }

          .center .section-desc {
               margin: 0 auto
          }

          .divider {
               width: 60px;
               height: 3px;
               background: linear-gradient(90deg, var(--gold), var(--gold2));
               border-radius: 2px;
               margin: 0 auto 20px
          }

          /* ── MODULES ── */
          .modules-bg {
               background: var(--navy2)
          }

          .modules-grid {
               display: grid;
               grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
               gap: 24px;
               margin-top: 60px
          }

          .module-card {
               background: rgba(22, 42, 80, 0.6);
               border: 1px solid rgba(201, 168, 76, 0.12);
               border-radius: 20px;
               padding: 32px;
               transition: all .3s;
               position: relative;
               overflow: hidden
          }

          .module-card::before {
               content: '';
               position: absolute;
               inset: 0;
               background: linear-gradient(135deg, rgba(201, 168, 76, 0.04) 0%, transparent 60%);
               opacity: 0;
               transition: .3s
          }

          .module-card:hover {
               border-color: rgba(201, 168, 76, 0.35);
               transform: translateY(-4px)
          }

          .module-card:hover::before {
               opacity: 1
          }

          .module-icon {
               width: 52px;
               height: 52px;
               border-radius: 14px;
               display: flex;
               align-items: center;
               justify-content: center;
               font-size: 26px;
               margin-bottom: 20px
          }

          .module-card h3 {
               font-size: 19px;
               font-weight: 600;
               color: var(--white);
               margin-bottom: 10px
          }

          .module-card p {
               font-size: 14px;
               color: var(--text-muted);
               line-height: 1.7
          }

          .module-tag {
               display: inline-block;
               margin-top: 16px;
               font-size: 11px;
               font-weight: 600;
               letter-spacing: .08em;
               text-transform: uppercase;
               padding: 4px 12px;
               border-radius: 100px
          }

          .tag-new {
               background: rgba(14, 165, 160, 0.15);
               color: var(--teal2)
          }

          .tag-core {
               background: rgba(201, 168, 76, 0.15);
               color: var(--gold2)
          }

          .tag-pro {
               background: rgba(139, 92, 246, 0.15);
               color: #a78bfa
          }

          /* FEATURES SPLIT */
          .features-split {
               display: grid;
               grid-template-columns: 1fr 1fr;
               gap: 80px;
               align-items: center
          }

          .features-list {
               list-style: none;
               display: flex;
               flex-direction: column;
               gap: 20px;
               margin-top: 32px
          }

          .feature-item {
               display: flex;
               gap: 16px;
               align-items: flex-start
          }

          .feature-check {
               width: 28px;
               height: 28px;
               border-radius: 8px;
               background: rgba(201, 168, 76, 0.15);
               display: flex;
               align-items: center;
               justify-content: center;
               flex-shrink: 0;
               color: var(--gold2);
               font-size: 14px;
               margin-top: 2px
          }

          .feature-item h4 {
               font-size: 15px;
               font-weight: 600;
               color: var(--white);
               margin-bottom: 4px
          }

          .feature-item p {
               font-size: 13px;
               color: var(--text-muted);
               line-height: 1.6
          }

          .screenshot-mock {
               background: rgba(15, 32, 64, 0.8);
               border: 1px solid rgba(201, 168, 76, 0.2);
               border-radius: 20px;
               padding: 24px;
               position: relative
          }

          .mock-header {
               display: flex;
               align-items: center;
               gap: 8px;
               margin-bottom: 20px
          }

          .mock-dot {
               width: 10px;
               height: 10px;
               border-radius: 50%
          }

          .mock-bar {
               flex: 1;
               height: 10px;
               background: rgba(255, 255, 255, 0.06);
               border-radius: 5px
          }

          .mock-row {
               display: flex;
               justify-content: space-between;
               align-items: center;
               padding: 14px 0;
               border-bottom: 1px solid rgba(255, 255, 255, 0.05)
          }

          .mock-row:last-child {
               border-bottom: none
          }

          .mock-avatar {
               width: 36px;
               height: 36px;
               border-radius: 10px;
               flex-shrink: 0
          }

          .mock-name {
               font-size: 13px;
               font-weight: 500;
               color: var(--white)
          }

          .mock-sub {
               font-size: 11px;
               color: var(--text-muted);
               margin-top: 2px
          }

          .mock-badge {
               font-size: 11px;
               font-weight: 600;
               padding: 4px 10px;
               border-radius: 100px
          }

          .badge-present {
               background: rgba(34, 197, 94, 0.15);
               color: #4ade80
          }

          .badge-late {
               background: rgba(234, 179, 8, 0.15);
               color: #fbbf24
          }

          .badge-absent {
               background: rgba(239, 68, 68, 0.15);
               color: #f87171
          }

          /* ── PRICING ── */
          .pricing-bg {
               background: linear-gradient(180deg, var(--navy) 0%, var(--navy2) 100%)
          }

          .pricing-tabs {
               display: flex;
               gap: 0;
               background: rgba(255, 255, 255, 0.05);
               border-radius: 12px;
               padding: 4px;
               width: fit-content;
               margin: 32px auto
          }

          .ptab {
               padding: 10px 28px;
               border-radius: 9px;
               font-size: 14px;
               font-weight: 500;
               cursor: pointer;
               border: none;
               background: transparent;
               color: var(--text-muted);
               transition: all .2s
          }

          .ptab.active {
               background: linear-gradient(135deg, var(--gold), var(--gold2));
               color: var(--navy);
               font-weight: 700
          }

          .pricing-panel {
               display: none
          }

          .pricing-panel.active {
               display: block
          }

          .pricing-cards {
               display: grid;
               grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
               gap: 32px;
               margin-top: 40px
          }

          .pricing-card {
               background: rgba(15, 32, 64, 0.7);
               border: 1px solid rgba(201, 168, 76, 0.15);
               border-radius: 24px;
               padding: 36px;
               position: relative;
               overflow: hidden
          }

          .pricing-card.featured {
               border-color: rgba(201, 168, 76, 0.5);
               background: rgba(22, 42, 80, 0.9)
          }

          .pricing-badge-top {
               position: absolute;
               top: -1px;
               left: 50%;
               transform: translateX(-50%);
               background: linear-gradient(90deg, var(--gold), var(--gold2));
               color: var(--navy);
               font-size: 11px;
               font-weight: 700;
               padding: 6px 20px;
               border-radius: 0 0 10px 10px;
               letter-spacing: .06em;
               text-transform: uppercase
          }

          .pricing-card h3 {
               font-family: 'Playfair Display', serif;
               font-size: 22px;
               font-weight: 700;
               color: var(--gold2);
               margin-bottom: 8px
          }

          .pricing-card .arabic {
               font-size: 18px;
               color: var(--gold3);
               opacity: .7;
               margin-bottom: 24px;
               direction: rtl
          }

          .pricing-table {
               width: 100%;
               border-collapse: collapse
          }

          .pricing-table th {
               font-size: 11px;
               font-weight: 600;
               letter-spacing: .08em;
               text-transform: uppercase;
               color: var(--text-muted);
               padding: 8px 12px;
               text-align: right;
               background: rgba(255, 255, 255, 0.03)
          }

          .pricing-table th:first-child {
               text-align: left
          }

          .pricing-table td {
               padding: 10px 12px;
               font-size: 14px;
               color: var(--white);
               border-bottom: 1px solid rgba(255, 255, 255, 0.05)
          }

          .pricing-table td:first-child {
               color: var(--text-muted)
          }

          .pricing-table td:not(:first-child) {
               text-align: right;
               font-weight: 500;
               color: var(--gold2)
          }

          .pricing-table tr:last-child td {
               border-bottom: none
          }

          .pricing-footer {
               margin-top: 28px;
               text-align: center
          }

          .pricing-cta {
               width: 100%;
               padding: 13px;
               border-radius: 10px;
               font-weight: 700;
               font-size: 15px;
               cursor: pointer;
               border: none;
               transition: all .2s
          }

          .pricing-cta-primary {
               background: linear-gradient(135deg, var(--gold), var(--gold2));
               color: var(--navy)
          }

          .pricing-cta-outline {
               background: transparent;
               border: 1px solid rgba(201, 168, 76, 0.35);
               color: var(--gold2)
          }

          .pricing-cta:hover {
               transform: translateY(-2px);
               box-shadow: 0 10px 24px rgba(201, 168, 76, .25)
          }

          .pricing-note {
               font-size: 12px;
               color: var(--text-muted);
               margin-top: 12px
          }

          /* FLYER SECTION */
          .flyer-section {
               background: var(--navy2);
               overflow: hidden
          }

          .flyer-grid {
               display: grid;
               grid-template-columns: 1fr 1fr;
               gap: 32px;
               margin-top: 60px
          }

          .flyer-card {
               border-radius: 20px;
               overflow: hidden;
               position: relative;
               border: 1px solid rgba(201, 168, 76, 0.2)
          }

          .flyer-card img {
               width: 100%;
               display: block;
               transition: transform .4s
          }

          .flyer-card:hover img {
               transform: scale(1.02)
          }

          .flyer-overlay {
               position: absolute;
               bottom: 0;
               left: 0;
               right: 0;
               padding: 20px 24px;
               background: linear-gradient(transparent, rgba(10, 22, 40, 0.95));
               pointer-events: none
          }

          .flyer-label {
               font-size: 13px;
               font-weight: 600;
               color: var(--gold2)
          }

          .flyer-sub {
               font-size: 12px;
               color: var(--text-muted);
               margin-top: 2px
          }

          /* ── TIMETABLE PREVIEW ── */
          .timetable-mock {
               background: rgba(10, 22, 40, 0.8);
               border: 1px solid rgba(201, 168, 76, 0.2);
               border-radius: 20px;
               overflow: hidden;
               margin-top: 48px
          }

          .tt-header {
               display: grid;
               grid-template-columns: 80px repeat(5, 1fr);
               background: rgba(201, 168, 76, 0.08);
               border-bottom: 1px solid rgba(201, 168, 76, 0.15)
          }

          .tt-cell {
               padding: 14px 12px;
               font-size: 12px;
               font-weight: 600;
               text-align: center;
               color: var(--gold2)
          }

          .tt-row {
               display: grid;
               grid-template-columns: 80px repeat(5, 1fr);
               border-bottom: 1px solid rgba(255, 255, 255, 0.04)
          }

          .tt-row:last-child {
               border-bottom: none
          }

          .tt-time {
               padding: 14px 12px;
               font-size: 11px;
               color: var(--text-muted);
               font-weight: 600;
               display: flex;
               align-items: center;
               justify-content: center
          }

          .tt-slot {
               padding: 10px 8px;
               font-size: 11px;
               line-height: 1.4;
               text-align: center;
               display: flex;
               align-items: center;
               justify-content: center
          }

          .slot-blue {
               background: rgba(14, 165, 160, 0.12);
               color: var(--teal2);
               border-radius: 6px;
               padding: 8px 4px;
               width: 100%
          }

          .slot-gold {
               background: rgba(201, 168, 76, 0.12);
               color: var(--gold2);
               border-radius: 6px;
               padding: 8px 4px;
               width: 100%
          }

          .slot-purple {
               background: rgba(139, 92, 246, 0.12);
               color: #a78bfa;
               border-radius: 6px;
               padding: 8px 4px;
               width: 100%
          }

          .slot-green {
               background: rgba(34, 197, 94, 0.12);
               color: #4ade80;
               border-radius: 6px;
               padding: 8px 4px;
               width: 100%
          }

          .slot-empty {
               color: var(--text-muted)
          }

          /* ── ATTENDANCE PREVIEW ── */
          .attendance-preview {
               display: grid;
               grid-template-columns: 1fr 1fr;
               gap: 24px;
               margin-top: 48px
          }

          .att-card {
               background: rgba(15, 32, 64, 0.7);
               border: 1px solid rgba(201, 168, 76, 0.15);
               border-radius: 20px;
               padding: 28px
          }

          .att-header {
               display: flex;
               justify-content: space-between;
               align-items: center;
               margin-bottom: 24px
          }

          .att-title {
               font-size: 16px;
               font-weight: 600;
               color: var(--white)
          }

          .att-date {
               font-size: 12px;
               color: var(--text-muted)
          }

          .att-donut {
               position: relative;
               width: 120px;
               height: 120px;
               margin: 0 auto 20px
          }

          .att-donut svg {
               transform: rotate(-90deg)
          }

          .att-donut-label {
               position: absolute;
               inset: 0;
               display: flex;
               flex-direction: column;
               align-items: center;
               justify-content: center
          }

          .att-donut-num {
               font-family: 'Playfair Display', serif;
               font-size: 26px;
               font-weight: 700;
               color: var(--gold2)
          }

          .att-donut-sub {
               font-size: 11px;
               color: var(--text-muted)
          }

          .att-legend {
               display: flex;
               justify-content: center;
               gap: 20px;
               font-size: 12px
          }

          .att-leg-item {
               display: flex;
               align-items: center;
               gap: 6px;
               color: var(--text-muted)
          }

          .att-leg-dot {
               width: 8px;
               height: 8px;
               border-radius: 50%
          }

          .teacher-list {
               display: flex;
               flex-direction: column;
               gap: 12px;
               margin-top: 8px
          }

          .teacher-row {
               display: flex;
               align-items: center;
               gap: 12px
          }

          .teacher-av {
               width: 36px;
               height: 36px;
               border-radius: 10px;
               display: flex;
               align-items: center;
               justify-content: center;
               font-size: 13px;
               font-weight: 700;
               flex-shrink: 0
          }

          .teacher-name {
               font-size: 13px;
               font-weight: 500;
               color: var(--white);
               flex: 1
          }

          .teacher-time {
               font-size: 11px;
               color: var(--text-muted)
          }

          /* ── CONTACT ── */
          .contact-bg {
               background: linear-gradient(135deg, var(--navy2) 0%, rgba(10, 22, 40, 0.95) 100%)
          }

          .contact-grid {
               display: grid;
               grid-template-columns: 1fr 1fr;
               gap: 80px;
               align-items: start
          }

          .contact-info {
               display: flex;
               flex-direction: column;
               gap: 24px;
               margin-top: 32px
          }

          .contact-item {
               display: flex;
               align-items: flex-start;
               gap: 16px
          }

          .contact-icon {
               width: 44px;
               height: 44px;
               border-radius: 12px;
               background: rgba(201, 168, 76, 0.12);
               display: flex;
               align-items: center;
               justify-content: center;
               font-size: 20px;
               flex-shrink: 0
          }

          .contact-label {
               font-size: 12px;
               color: var(--text-muted);
               margin-bottom: 4px
          }

          .contact-val {
               font-size: 15px;
               font-weight: 500;
               color: var(--white)
          }

          .contact-form {
               background: rgba(15, 32, 64, 0.6);
               border: 1px solid rgba(201, 168, 76, 0.15);
               border-radius: 24px;
               padding: 36px
          }

          .form-group {
               margin-bottom: 20px
          }

          .form-group label {
               display: block;
               font-size: 13px;
               color: var(--text-muted);
               margin-bottom: 8px;
               font-weight: 500
          }

          .form-group input,
          .form-group select,
          .form-group textarea {
               width: 100%;
               background: rgba(255, 255, 255, 0.05);
               border: 1px solid rgba(255, 255, 255, 0.1);
               border-radius: 10px;
               padding: 12px 16px;
               font-size: 14px;
               color: var(--white);
               font-family: 'DM Sans', sans-serif;
               transition: border-color .2s;
               resize: none
          }

          .form-group input:focus,
          .form-group select:focus,
          .form-group textarea:focus {
               outline: none;
               border-color: rgba(201, 168, 76, 0.5)
          }

          .form-group select option {
               background: var(--navy2);
               color: var(--white)
          }

          .form-grid-2 {
               display: grid;
               grid-template-columns: 1fr 1fr;
               gap: 16px
          }

          /* ── FOOTER ── */
          footer {
               background: var(--navy);
               border-top: 1px solid rgba(201, 168, 76, 0.1);
               padding: 60px 5% 32px
          }

          .footer-top {
               display: grid;
               grid-template-columns: 2fr 1fr 1fr 1fr;
               gap: 48px;
               margin-bottom: 48px
          }

          .footer-brand p {
               font-size: 14px;
               color: var(--text-muted);
               line-height: 1.8;
               margin-top: 16px;
               max-width: 280px
          }

          .footer-col h4 {
               font-size: 13px;
               font-weight: 700;
               color: var(--gold2);
               letter-spacing: .06em;
               text-transform: uppercase;
               margin-bottom: 20px
          }

          .footer-col ul {
               list-style: none;
               display: flex;
               flex-direction: column;
               gap: 12px
          }

          .footer-col a {
               font-size: 14px;
               color: var(--text-muted);
               text-decoration: none;
               transition: color .2s
          }

          .footer-col a:hover {
               color: var(--gold2)
          }

          .footer-bottom {
               display: flex;
               justify-content: space-between;
               align-items: center;
               padding-top: 28px;
               border-top: 1px solid rgba(255, 255, 255, 0.06)
          }

          .footer-bottom p {
               font-size: 13px;
               color: var(--text-muted)
          }

          .phones {
               display: flex;
               gap: 12px;
               flex-wrap: wrap;
               margin-top: 6px
          }

          .phone-badge {
               background: rgba(201, 168, 76, 0.1);
               border: 1px solid rgba(201, 168, 76, 0.2);
               color: var(--gold2);
               font-size: 13px;
               font-weight: 600;
               padding: 6px 14px;
               border-radius: 8px
          }

          /* SCROLL ANIMATION */
          .fade-in {
               opacity: 0;
               transform: translateY(30px);
               transition: opacity .7s ease, transform .7s ease
          }

          .fade-in.visible {
               opacity: 1;
               transform: translateY(0)
          }

          /* COUNTER */
          .count-up {
               display: inline-block
          }

          @media(max-width:900px) {

               .features-split,
               .contact-grid {
                    grid-template-columns: 1fr
               }

               .hero-visual {
                    display: none
               }

               .flyer-grid {
                    grid-template-columns: 1fr
               }

               .attendance-preview {
                    grid-template-columns: 1fr
               }

               .footer-top {
                    grid-template-columns: 1fr 1fr
               }

               .hero {
                    padding-top: 130px
               }

               .nav-links {
                    display: none
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
               <li><a href="#modules">Modules</a></li>
               <li><a href="#features">Features</a></li>
               <li><a href="#pricing">Pricing</a></li>
               <li><a href="#attendance">Attendance</a></li>
               <li><a href="#timetable">Timetable</a></li>
               <li><a href="#contact">Contact</a></li>
          </ul>
          <button class="nav-cta" onclick="document.getElementById('contact').scrollIntoView({behavior:'smooth'})">Get
               Started</button>
     </nav>

     <!-- HERO -->
     <section class="hero">
          <div class="hero-bg"></div>
          <div class="hero-grid-lines"></div>
          <div class="hero-glow"></div>
          <div class="hero-content fade-in">
               <div class="hero-badge"><span class="hero-badge-dot"></span>TechSate Company LTD — School Management
               </div>
               <h1>SMASA<br><span>Comprehensive</span><br>Academic System</h1>
               <div class="hero-arabic arabic">نظام SMASA الأكاديمي الشامل</div>
               <p class="hero-desc">A powerful, multilingual school management platform built for Uganda's Islamic
                    primary and secondary institutions — handling academics, finance, staff, attendance, timetables and
                    more.</p>
               <div class="hero-actions">
                    <a href="#contact" class="btn-primary">Request a Demo</a>
                    <a href="#modules" class="btn-outline">Explore Modules</a>
               </div>
               <div class="hero-stats">
                    <div>
                         <div class="hero-stat-num"><span class="count-up" data-target="200">0</span>+</div>
                         <div class="hero-stat-label">Schools Supported</div>
                    </div>
                    <div>
                         <div class="hero-stat-num"><span class="count-up" data-target="5000">0</span>+</div>
                         <div class="hero-stat-label">Students Managed</div>
                    </div>
                    <div>
                         <div class="hero-stat-num"><span class="count-up" data-target="12">0</span></div>
                         <div class="hero-stat-label">Modules Available</div>
                    </div>
               </div>
          </div>
          <div class="hero-visual">
               <div class="hero-cards">
                    <div class="hcard hcard-tl">
                         <div class="hcard-icon" style="background:rgba(14,165,160,0.15)">📋</div>
                         <div class="hcard-title">Attendance</div>
                         <div class="hcard-sub">Today's rate</div>
                         <div class="hcard-num">94%</div>
                         <div class="progress-bar-wrap">
                              <div class="progress-bar-fill" style="width:94%"></div>
                         </div>
                    </div>
                    <div class="hcard hcard-tr">
                         <div class="hcard-title" style="color:var(--teal2)">📅 Timetable</div>
                         <div class="hcard-sub" style="margin-top:8px">3 clashes resolved</div>
                         <div style="margin-top:8px;font-size:11px;color:var(--green)">✓ Auto-optimised</div>
                    </div>
                    <div class="hcard hcard-main">
                         <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                              <div>
                                   <div class="hcard-title" style="font-size:15px">Today's Overview</div>
                                   <div class="hcard-sub">Thursday, 15 May 2026</div>
                              </div>
                              <div
                                   style="width:36px;height:36px;border-radius:50%;background:rgba(201,168,76,0.15);display:flex;align-items:center;justify-content:center;font-size:18px">
                                   🏫</div>
                         </div>
                         <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                              <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px">
                                   <div style="font-size:11px;color:var(--text-muted)">Students</div>
                                   <div
                                        style="font-family:'Playfair Display',serif;font-size:22px;color:var(--gold2);font-weight:700">
                                        342</div>
                              </div>
                              <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px">
                                   <div style="font-size:11px;color:var(--text-muted)">Teachers</div>
                                   <div
                                        style="font-family:'Playfair Display',serif;font-size:22px;color:var(--teal2);font-weight:700">
                                        28</div>
                              </div>
                              <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px">
                                   <div style="font-size:11px;color:var(--text-muted)">Classes</div>
                                   <div
                                        style="font-family:'Playfair Display',serif;font-size:22px;color:var(--gold2);font-weight:700">
                                        14</div>
                              </div>
                              <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px">
                                   <div style="font-size:11px;color:var(--text-muted)">Exams</div>
                                   <div
                                        style="font-family:'Playfair Display',serif;font-size:22px;color:#a78bfa;font-weight:700">
                                        3</div>
                              </div>
                         </div>
                    </div>
                    <div class="hcard hcard-bl">
                         <div class="hcard-icon" style="background:rgba(201,168,76,0.15)">💰</div>
                         <div class="hcard-title">Finance</div>
                         <div class="hcard-num" style="font-size:20px">Shs 2.4M</div>
                         <div class="hcard-trend">↑ 18% this term</div>
                    </div>
                    <div class="hcard hcard-br">
                         <div class="hcard-title" style="color:#a78bfa">🎓 Theology Support</div>
                         <div class="hcard-sub" style="margin-top:8px">Idaad · Thanawi · Primary</div>
                         <div style="margin-top:10px;display:flex;gap:6px">
                              <span
                                   style="font-size:10px;background:rgba(201,168,76,0.12);color:var(--gold2);padding:3px 8px;border-radius:4px">Arabic</span>
                              <span
                                   style="font-size:10px;background:rgba(14,165,160,0.12);color:var(--teal2);padding:3px 8px;border-radius:4px">Secular</span>
                         </div>
                    </div>
               </div>
          </div>
     </section>

     <!-- MODULES -->
     <section id="modules" class="modules-bg">
          <div class="center fade-in">
               <div class="section-label">What's Inside</div>
               <div class="divider"></div>
               <h2 class="section-title">Everything Your School Needs</h2>
               <p class="section-desc">From student enrollment to exam analytics — SMASA covers every corner of school
                    administration in one unified platform.</p>
          </div>
          <div class="modules-grid fade-in">
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(201,168,76,0.12)">🎓</div>
                    <h3>Academic Management</h3>
                    <p>Manage student enrollment, classes, streams, subjects, and academic years. Full support for
                         Idaad, Thanawi, Primary Theology and Secular curricula.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(14,165,160,0.12)">📋</div>
                    <h3>Attendance Tracking</h3>
                    <p>Dual attendance system: class-level student attendance by teachers and school-gate teacher
                         attendance with arrival/departure timestamps and reports.</p>
                    <span class="module-tag tag-new">New Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(139,92,246,0.12)">📅</div>
                    <h3>Timetable Generator</h3>
                    <p>Advanced, conflict-aware timetable builder that handles any school complexity. Assign teachers,
                         rooms and subjects with a clean drag-and-drop interface.</p>
                    <span class="module-tag tag-new">New Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(201,168,76,0.12)">💰</div>
                    <h3>Finance Management</h3>
                    <p>Track school fees, generate receipts, handle outstanding balances and produce term-by-term
                         financial reports for administrators and parents.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(34,197,94,0.12)">👨‍🏫</div>
                    <h3>Staff Management</h3>
                    <p>Maintain complete teacher profiles, assign subjects and classes, track workloads, manage
                         credentials and payroll integration.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(239,68,68,0.12)">📊</div>
                    <h3>Reports & Analytics</h3>
                    <p>Comprehensive dashboards showing student performance, class averages, attendance trends, and
                         financial KPIs with export to Excel and PDF.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(14,165,160,0.12)">✉️</div>
                    <h3>Communication</h3>
                    <p>Send SMS and email notifications to parents and staff. Broadcast announcements, result slips and
                         fee reminders with one click.</p>
                    <span class="module-tag tag-pro">Pro Feature</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(201,168,76,0.12)">🕌</div>
                    <h3>Theology Support</h3>
                    <p>Native Arabic interface, RTL text, Islamic calendar integration and grading systems aligned with
                         Ugandan Muslim school requirements.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
               <div class="module-card">
                    <div class="module-icon" style="background:rgba(139,92,246,0.12)">📝</div>
                    <h3>Exam Management</h3>
                    <p>Schedule exams, upload results, auto-grade with configurable grading rubrics and generate
                         class-wide report cards with division calculations.</p>
                    <span class="module-tag tag-core">Core Module</span>
               </div>
          </div>
     </section>

     <!-- FEATURES SPLIT -->
     <section id="features" style="background:var(--navy)">
          <div class="features-split fade-in">
               <div>
                    <div class="section-label">Why SMASA</div>
                    <h2 class="section-title">Built for Uganda's Islamic Schools</h2>
                    <p class="section-desc">Not a generic school system — SMASA was engineered from the ground up for
                         the unique curriculum, language and compliance needs of Islamic institutions in Uganda.</p>
                    <ul class="features-list">
                         <li class="feature-item">
                              <div class="feature-check">✓</div>
                              <div>
                                   <h4>Bilingual Interface (English + Arabic)</h4>
                                   <p>Full RTL Arabic support with Naskh typography for all academic records, report
                                        cards and communications.</p>
                              </div>
                         </li>
                         <li class="feature-item">
                              <div class="feature-check">✓</div>
                              <div>
                                   <h4>Multi-curriculum Grading</h4>
                                   <p>Separate grading engines for Idaad, Thanawi, Primary Theology and Secular — each
                                        with its own rubrics and division classes.</p>
                              </div>
                         </li>
                         <li class="feature-item">
                              <div class="feature-check">✓</div>
                              <div>
                                   <h4>School-product Aware</h4>
                                   <p>The system auto-configures classes, subjects and reports based on whether the
                                        school runs theology, secular or a combined programme.</p>
                              </div>
                         </li>
                         <li class="feature-item">
                              <div class="feature-check">✓</div>
                              <div>
                                   <h4>Role-based Access Control</h4>
                                   <p>Separate portals for Super Admins, School Admins, Teachers, Students and
                                        Gate-keepers — each seeing only what they need.</p>
                              </div>
                         </li>
                         <li class="feature-item">
                              <div class="feature-check">✓</div>
                              <div>
                                   <h4>Offline-tolerant Design</h4>
                                   <p>Core data entry works with intermittent connectivity and syncs automatically —
                                        critical for schools in rural Uganda.</p>
                              </div>
                         </li>
                    </ul>
               </div>
               <div class="screenshot-mock">
                    <div class="mock-header">
                         <div class="mock-dot" style="background:#ef4444"></div>
                         <div class="mock-dot" style="background:#f59e0b"></div>
                         <div class="mock-dot" style="background:#22c55e"></div>
                         <div class="mock-bar"></div>
                    </div>
                    <div style="font-size:14px;font-weight:600;color:var(--gold2);margin-bottom:16px">Class P3A —
                         Attendance · Thursday 15 May</div>
                    <div class="mock-row">
                         <div style="display:flex;align-items:center;gap:12px">
                              <div class="mock-avatar" style="background:rgba(14,165,160,0.2)"></div>
                              <div>
                                   <div class="mock-name">Aminah Nakato</div>
                                   <div class="mock-sub">Admission #P3-001</div>
                              </div>
                         </div>
                         <span class="mock-badge badge-present">Present</span>
                    </div>
                    <div class="mock-row">
                         <div style="display:flex;align-items:center;gap:12px">
                              <div class="mock-avatar" style="background:rgba(201,168,76,0.2)"></div>
                              <div>
                                   <div class="mock-name">Ibrahim Ssemwogerere</div>
                                   <div class="mock-sub">Admission #P3-002</div>
                              </div>
                         </div>
                         <span class="mock-badge badge-late">Late</span>
                    </div>
                    <div class="mock-row">
                         <div style="display:flex;align-items:center;gap:12px">
                              <div class="mock-avatar" style="background:rgba(139,92,246,0.2)"></div>
                              <div>
                                   <div class="mock-name">Fatuma Namutebi</div>
                                   <div class="mock-sub">Admission #P3-003</div>
                              </div>
                         </div>
                         <span class="mock-badge badge-present">Present</span>
                    </div>
                    <div class="mock-row">
                         <div style="display:flex;align-items:center;gap:12px">
                              <div class="mock-avatar" style="background:rgba(239,68,68,0.2)"></div>
                              <div>
                                   <div class="mock-name">Hassan Kibuuka</div>
                                   <div class="mock-sub">Admission #P3-004</div>
                              </div>
                         </div>
                         <span class="mock-badge badge-absent">Absent</span>
                    </div>
                    <div class="mock-row">
                         <div style="display:flex;align-items:center;gap:12px">
                              <div class="mock-avatar" style="background:rgba(34,197,94,0.2)"></div>
                              <div>
                                   <div class="mock-name">Zainab Nakirya</div>
                                   <div class="mock-sub">Admission #P3-005</div>
                              </div>
                         </div>
                         <span class="mock-badge badge-present">Present</span>
                    </div>
                    <div
                         style="margin-top:20px;padding:14px;background:rgba(201,168,76,0.06);border-radius:12px;display:flex;justify-content:space-between;align-items:center">
                         <span style="font-size:13px;color:var(--text-muted)">Session saved · 34/38 present</span>
                         <span style="font-size:13px;color:var(--green);font-weight:600">89.5%</span>
                    </div>
               </div>
          </div>
     </section>

     <!-- ATTENDANCE MODULE -->
     <section id="attendance" style="background:var(--navy2)">
          <div class="center fade-in">
               <div class="section-label">Module Spotlight</div>
               <div class="divider"></div>
               <h2 class="section-title">Attendance Management</h2>
               <p class="section-desc">Two independent attendance streams — one for students, one for staff — with
                    real-time dashboards, filters and downloadable reports.</p>
          </div>
          <div class="attendance-preview fade-in">
               <div class="att-card">
                    <div class="att-header">
                         <div class="att-title">Student Attendance</div>
                         <div class="att-date">Term II · Week 4</div>
                    </div>
                    <div class="att-donut">
                         <svg width="120" height="120" viewBox="0 0 120 120">
                              <circle cx="60" cy="60" r="48" fill="none" stroke="rgba(255,255,255,0.06)"
                                   stroke-width="14" />
                              <circle cx="60" cy="60" r="48" fill="none" stroke="#22c55e" stroke-width="14"
                                   stroke-dasharray="271 30" stroke-linecap="round" />
                              <circle cx="60" cy="60" r="48" fill="none" stroke="#f59e0b" stroke-width="14"
                                   stroke-dasharray="15 286" stroke-dashoffset="-271" stroke-linecap="round" />
                         </svg>
                         <div class="att-donut-label">
                              <div class="att-donut-num">91%</div>
                              <div class="att-donut-sub">Present</div>
                         </div>
                    </div>
                    <div class="att-legend">
                         <div class="att-leg-item">
                              <div class="att-leg-dot" style="background:#22c55e"></div>Present 91%
                         </div>
                         <div class="att-leg-item">
                              <div class="att-leg-dot" style="background:#f59e0b"></div>Late 5%
                         </div>
                         <div class="att-leg-item">
                              <div class="att-leg-dot" style="background:#ef4444"></div>Absent 4%
                         </div>
                    </div>
               </div>
               <div class="att-card">
                    <div class="att-header">
                         <div class="att-title">Teacher Check-in</div>
                         <div class="att-date">Today · 15 May 2026</div>
                    </div>
                    <div class="teacher-list">
                         <div class="teacher-row">
                              <div class="teacher-av" style="background:rgba(14,165,160,0.15);color:var(--teal2)">AK
                              </div>
                              <div style="flex:1">
                                   <div class="teacher-name">Ustadh Abdul Karim</div>
                                   <div class="teacher-time">In 07:42 AM</div>
                              </div>
                              <span class="mock-badge badge-present" style="font-size:10px">On Time</span>
                         </div>
                         <div class="teacher-row">
                              <div class="teacher-av" style="background:rgba(201,168,76,0.15);color:var(--gold2)">HN
                              </div>
                              <div style="flex:1">
                                   <div class="teacher-name">Hajjah Nadia</div>
                                   <div class="teacher-time">In 08:15 AM</div>
                              </div>
                              <span class="mock-badge badge-late" style="font-size:10px">Late</span>
                         </div>
                         <div class="teacher-row">
                              <div class="teacher-av" style="background:rgba(139,92,246,0.15);color:#a78bfa">MK</div>
                              <div style="flex:1">
                                   <div class="teacher-name">Mr. Musa Kiggundu</div>
                                   <div class="teacher-time">In 07:55 AM</div>
                              </div>
                              <span class="mock-badge badge-present" style="font-size:10px">On Time</span>
                         </div>
                         <div class="teacher-row">
                              <div class="teacher-av" style="background:rgba(239,68,68,0.15);color:#f87171">ZN</div>
                              <div style="flex:1">
                                   <div class="teacher-name">Ustadha Zainab Nsubuga</div>
                                   <div class="teacher-time">— Not arrived</div>
                              </div>
                              <span class="mock-badge badge-absent" style="font-size:10px">Absent</span>
                         </div>
                    </div>
                    <div style="margin-top:16px;font-size:12px;color:var(--text-muted);text-align:center">Gate-keeper
                         portal · Auto-timestamped</div>
               </div>
          </div>
     </section>

     <!-- TIMETABLE -->
     <section id="timetable" style="background:var(--navy)">
          <div class="fade-in" style="max-width:800px">
               <div class="section-label">Module Spotlight</div>
               <h2 class="section-title">Advanced Timetable Builder</h2>
               <p class="section-desc">Handle any school's complexity — multiple teachers, rooms and class groups — with
                    automatic conflict detection and one-click resolution.</p>
          </div>
          <div class="timetable-mock fade-in">
               <div class="tt-header">
                    <div class="tt-cell">Time</div>
                    <div class="tt-cell">Monday</div>
                    <div class="tt-cell">Tuesday</div>
                    <div class="tt-cell">Wednesday</div>
                    <div class="tt-cell">Thursday</div>
                    <div class="tt-cell">Friday</div>
               </div>
               <div class="tt-row">
                    <div class="tt-time">7:30–8:30</div>
                    <div class="tt-slot">
                         <div class="slot-gold">Quran Recitation<br><span style="font-size:10px;opacity:.7">Ust. Abdul
                                   Karim</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr.
                                   Kiggundu</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-gold">Quran Recitation<br><span style="font-size:10px;opacity:.7">Ust. Abdul
                                   Karim</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr.
                                   Kiggundu</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span>
                         </div>
                    </div>
               </div>
               <div class="tt-row">
                    <div class="tt-time">8:30–9:30</div>
                    <div class="tt-slot">
                         <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr.
                                   Kiggundu</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-purple">Islamic Studies<br><span style="font-size:10px;opacity:.7">Ustadha
                                   Zainab</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-gold">Arabic<br><span style="font-size:10px;opacity:.7">Hajjah Nadia</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-purple">Islamic Studies<br><span style="font-size:10px;opacity:.7">Ustadha
                                   Zainab</span></div>
                    </div>
               </div>
               <div class="tt-row">
                    <div class="tt-time">9:30–10:30</div>
                    <div class="tt-slot">
                         <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-blue">Science<br><span style="font-size:10px;opacity:.7">Mr. Sserunjogi</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-purple">Arabic<br><span style="font-size:10px;opacity:.7">Hajjah Nadia</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span>
                         </div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-blue">Science<br><span style="font-size:10px;opacity:.7">Mr. Sserunjogi</span>
                         </div>
                    </div>
               </div>
               <div class="tt-row">
                    <div class="tt-time">10:30–11:00</div>
                    <div class="tt-slot"
                         style="grid-column:span 5;text-align:center;color:var(--text-muted);font-size:12px;padding:14px">
                         ☕ Break</div>
               </div>
               <div class="tt-row">
                    <div class="tt-time">11:00–12:00</div>
                    <div class="tt-slot">
                         <div class="slot-purple">Social Studies<br><span style="font-size:10px;opacity:.7">Mr.
                                   Wasswa</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-gold">Tafseer<br><span style="font-size:10px;opacity:.7">Ust. Abdul
                                   Karim</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr.
                                   Kiggundu</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-purple">Social Studies<br><span style="font-size:10px;opacity:.7">Mr.
                                   Wasswa</span></div>
                    </div>
                    <div class="tt-slot">
                         <div class="slot-gold">Tafseer<br><span style="font-size:10px;opacity:.7">Ust. Abdul
                                   Karim</span></div>
                    </div>
               </div>
          </div>
     </section>

     <!-- PRICING -->
     <section id="pricing" class="pricing-bg">
          <div class="center fade-in">
               <div class="section-label">Termly Charges</div>
               <div class="divider"></div>
               <h2 class="section-title">Transparent, School-size Pricing</h2>
               <p class="section-desc">Charged per term based on your student population. Choose the system that fits
                    your school's curriculum.</p>
               <div class="pricing-tabs">
                    <button class="ptab active" onclick="switchPricing('english')">English</button>
                    <button class="ptab" onclick="switchPricing('arabic')">عربي</button>
               </div>
          </div>

          <div id="pricing-english" class="pricing-panel active fade-in">
               <div class="pricing-cards">
                    <div class="pricing-card">
                         <h3>Single System</h3>
                         <p class="arabic">النظام الواحد</p>
                         <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px">Best for schools running a
                              single curriculum — theology only or secular only.</p>
                         <table class="pricing-table">
                              <thead>
                                   <tr>
                                        <th>Students</th>
                                        <th>Per Term</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   <tr>
                                        <td>0 – 150</td>
                                        <td>Shs. 200,000</td>
                                   </tr>
                                   <tr>
                                        <td>151 – 300</td>
                                        <td>Shs. 300,000</td>
                                   </tr>
                                   <tr>
                                        <td>301 – 500</td>
                                        <td>Shs. 450,000</td>
                                   </tr>
                                   <tr>
                                        <td>501 – 800</td>
                                        <td>Shs. 650,000</td>
                                   </tr>
                                   <tr>
                                        <td>801 – 1,200</td>
                                        <td>Shs. 850,000</td>
                                   </tr>
                                   <tr>
                                        <td>1,201 – 1,500</td>
                                        <td>Shs. 1,250,000</td>
                                   </tr>
                                   <tr>
                                        <td>1,501 – 2,000</td>
                                        <td>Shs. 1,500,000</td>
                                   </tr>
                                   <tr>
                                        <td>2,001 – 2,800</td>
                                        <td>Shs. 1,750,000</td>
                                   </tr>
                                   <tr>
                                        <td>2,801 – 3,500</td>
                                        <td>Shs. 1,900,000</td>
                                   </tr>
                                   <tr>
                                        <td>3,500 – 5,000</td>
                                        <td>Shs. 2,150,000</td>
                                   </tr>
                              </tbody>
                         </table>
                         <div class="pricing-footer">
                              <button class="pricing-cta pricing-cta-outline"
                                   onclick="document.getElementById('contact').scrollIntoView({behavior:'smooth'})">Get
                                   a Quote</button>
                              <p class="pricing-note">Includes academic, finance & attendance modules</p>
                         </div>
                    </div>
                    <div class="pricing-card featured">
                         <div class="pricing-badge-top">MOST POPULAR</div>
                         <h3>Both Systems</h3>
                         <p class="arabic">النظامان معاً</p>
                         <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px">Full coverage — theology
                              and secular curricula running side by side in one school.</p>
                         <table class="pricing-table">
                              <thead>
                                   <tr>
                                        <th>Students</th>
                                        <th>Per Term</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   <tr>
                                        <td>0 – 150</td>
                                        <td>Shs. 350,000</td>
                                   </tr>
                                   <tr>
                                        <td>151 – 300</td>
                                        <td>Shs. 500,000</td>
                                   </tr>
                                   <tr>
                                        <td>301 – 500</td>
                                        <td>Shs. 750,000</td>
                                   </tr>
                                   <tr>
                                        <td>501 – 800</td>
                                        <td>Shs. 950,000</td>
                                   </tr>
                                   <tr>
                                        <td>801 – 1,200</td>
                                        <td>Shs. 1,150,000</td>
                                   </tr>
                                   <tr>
                                        <td>1,201 – 1,500</td>
                                        <td>Shs. 1,500,000</td>
                                   </tr>
                                   <tr>
                                        <td>1,501 – 2,000</td>
                                        <td>Shs. 1,750,000</td>
                                   </tr>
                                   <tr>
                                        <td>2,001 – 2,800</td>
                                        <td>Shs. 1,900,000</td>
                                   </tr>
                                   <tr>
                                        <td>2,801 – 3,500</td>
                                        <td>Shs. 2,150,000</td>
                                   </tr>
                                   <tr>
                                        <td>3,500 – 5,000</td>
                                        <td>Shs. 2,300,000</td>
                                   </tr>
                              </tbody>
                         </table>
                         <div class="pricing-footer">
                              <button class="pricing-cta pricing-cta-primary"
                                   onclick="document.getElementById('contact').scrollIntoView({behavior:'smooth'})">Get
                                   a Quote</button>
                              <p class="pricing-note">Includes all modules + theology support</p>
                         </div>
                    </div>
               </div>
          </div>

          <div id="pricing-arabic" class="pricing-panel fade-in" dir="rtl">
               <div class="pricing-cards">
                    <div class="pricing-card">
                         <h3 class="arabic">النظام الواحد</h3>
                         <p class="arabic" style="font-size:15px;color:var(--text-muted);margin-bottom:20px">مناسب
                              للمدارس التي تعمل بمنهج واحد — ديني أو علماني</p>
                         <table class="pricing-table">
                              <thead>
                                   <tr>
                                        <th style="text-align:right">عدد الطلاب</th>
                                        <th>الرسوم الفصلية</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   <tr>
                                        <td>٠ – ١٥٠</td>
                                        <td>٢٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٥١ – ٣٠٠</td>
                                        <td>٣٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٣٠١ – ٥٠٠</td>
                                        <td>٤٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٥٠١ – ٨٠٠</td>
                                        <td>٦٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٨٠١ – ١٢٠٠</td>
                                        <td>٨٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٢٠١ – ١٥٠٠</td>
                                        <td>١٬٢٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٥٠١ – ٢٠٠٠</td>
                                        <td>١٬٥٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٢٠٠١ – ٢٨٠٠</td>
                                        <td>١٬٧٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٢٨٠١ – ٣٥٠٠</td>
                                        <td>١٬٩٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٣٥٠٠ – ٥٠٠٠</td>
                                        <td>٢٬١٥٠٬٠٠٠ شلن</td>
                                   </tr>
                              </tbody>
                         </table>
                         <div class="pricing-footer">
                              <button class="pricing-cta pricing-cta-outline">اطلب عرض سعر</button>
                              <p class="pricing-note arabic">يشمل الإدارة الأكاديمية والمالية والحضور</p>
                         </div>
                    </div>
                    <div class="pricing-card featured">
                         <div class="pricing-badge-top">الأكثر طلباً</div>
                         <h3 class="arabic">النظامان معاً</h3>
                         <p class="arabic" style="font-size:15px;color:var(--text-muted);margin-bottom:20px">تغطية كاملة
                              — المنهجان الديني والعلماني في مدرسة واحدة</p>
                         <table class="pricing-table">
                              <thead>
                                   <tr>
                                        <th style="text-align:right">عدد الطلاب</th>
                                        <th>الرسوم الفصلية</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   <tr>
                                        <td>٠ – ١٥٠</td>
                                        <td>٣٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٥١ – ٣٠٠</td>
                                        <td>٥٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٣٠١ – ٥٠٠</td>
                                        <td>٧٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٥٠١ – ٨٠٠</td>
                                        <td>٩٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٨٠١ – ١٢٠٠</td>
                                        <td>١٬١٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٢٠١ – ١٥٠٠</td>
                                        <td>١٬٥٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>١٥٠١ – ٢٠٠٠</td>
                                        <td>١٬٧٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٢٠٠١ – ٢٨٠٠</td>
                                        <td>١٬٩٠٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٢٨٠١ – ٣٥٠٠</td>
                                        <td>٢٬١٥٠٬٠٠٠ شلن</td>
                                   </tr>
                                   <tr>
                                        <td>٣٥٠٠ – ٥٠٠٠</td>
                                        <td>٢٬٣٠٠٬٠٠٠ شلن</td>
                                   </tr>
                              </tbody>
                         </table>
                         <div class="pricing-footer">
                              <button class="pricing-cta pricing-cta-primary">اطلب عرض سعر</button>
                              <p class="pricing-note arabic">يشمل جميع الوحدات + دعم التعليم الشرعي</p>
                         </div>
                    </div>
               </div>
          </div>
     </section>

     <!-- FLYERS -->
     <section class="flyer-section">
          <div class="center fade-in">
               <div class="section-label">Official Flyers</div>
               <div class="divider"></div>
               <h2 class="section-title">Download Our Brochures</h2>
               <p class="section-desc">Share our official pricing flyers with school administrators and boards.</p>
          </div>
          <div class="flyer-grid fade-in">
               <div class="flyer-card">
                    <img src="/mnt/user-data/uploads/WhatsApp_Image_2026-05-15_at_7_45_00_AM__1_.jpeg"
                         alt="SMASA English Pricing Flyer"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div
                         style="display:none;height:320px;background:rgba(15,32,64,0.8);align-items:center;justify-content:center;flex-direction:column;gap:16px">
                         <div style="font-size:48px">📄</div>
                         <div style="font-size:16px;font-weight:600;color:var(--gold2)">English Pricing Flyer</div>
                         <div style="font-size:13px;color:var(--text-muted)">SMASA Comprehensive Academic System</div>
                    </div>
                    <div class="flyer-overlay">
                         <div class="flyer-label">🇬🇧 English Flyer</div>
                         <div class="flyer-sub">Single System & Both Systems — Termly Charges</div>
                    </div>
               </div>
               <div class="flyer-card">
                    <img src="/mnt/user-data/uploads/WhatsApp_Image_2026-05-15_at_7_45_00_AM.jpeg"
                         alt="SMASA Arabic Pricing Flyer"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div
                         style="display:none;height:320px;background:rgba(15,32,64,0.8);align-items:center;justify-content:center;flex-direction:column;gap:16px">
                         <div style="font-size:48px">📄</div>
                         <div style="font-size:16px;font-weight:600;color:var(--gold2)">Arabic Pricing Flyer</div>
                         <div style="font-size:13px;color:var(--text-muted)">نظام SMASA الأكاديمي الشامل</div>
                    </div>
                    <div class="flyer-overlay">
                         <div class="flyer-label arabic">🕌 النشرة العربية</div>
                         <div class="flyer-sub arabic" dir="rtl">الرسوم الفصلية — النظام الواحد والنظامان</div>
                    </div>
               </div>
          </div>
     </section>

     <!-- CONTACT -->
     <section id="contact" class="contact-bg">
          <div class="contact-grid fade-in">
               <div>
                    <div class="section-label">Get In Touch</div>
                    <h2 class="section-title">Ready to Transform Your School?</h2>
                    <p class="section-desc">Contact TechSate Company LTD today. Our team will walk you through a live
                         demo and answer every question.</p>
                    <div class="contact-info">
                         <div class="contact-item">
                              <div class="contact-icon">📍</div>
                              <div>
                                   <div class="contact-label">Visit Us</div>
                                   <div class="contact-val">Sky City Building, P.O Box 960248</div>
                                   <div style="font-size:13px;color:var(--text-muted)">Bwaise, Kawempe Division, Kampala
                                   </div>
                              </div>
                         </div>
                         <div class="contact-item">
                              <div class="contact-icon">📞</div>
                              <div>
                                   <div class="contact-label">Call Us</div>
                                   <div class="phones">
                                        <span class="phone-badge">+256 702 082 209</span>
                                        <span class="phone-badge">+256 702 859 495</span>
                                        <span class="phone-badge">+256 701 098 373</span>
                                   </div>
                              </div>
                         </div>
                         <div class="contact-item">
                              <div class="contact-icon">✉️</div>
                              <div>
                                   <div class="contact-label">Email</div>
                                   <div class="contact-val">techsatetechnologies@gmail.com</div>
                              </div>
                         </div>
                    </div>
               </div>
               <div class="contact-form">
                    <h3 style="font-size:20px;font-weight:600;color:var(--gold2);margin-bottom:24px">Request a Demo</h3>
                    <div class="form-grid-2">
                         <div class="form-group">
                              <label>School Name</label>
                              <input type="text" placeholder="e.g. Noor Islamic Institute">
                         </div>
                         <div class="form-group">
                              <label>Contact Person</label>
                              <input type="text" placeholder="Your full name">
                         </div>
                    </div>
                    <div class="form-grid-2">
                         <div class="form-group">
                              <label>Phone Number</label>
                              <input type="tel" placeholder="+256 7XX XXX XXX">
                         </div>
                         <div class="form-group">
                              <label>Email Address</label>
                              <input type="email" placeholder="school@example.com">
                         </div>
                    </div>
                    <div class="form-group">
                         <label>School Type</label>
                         <select>
                              <option>— Select —</option>
                              <option>Idaad & Thanawi (Islamic Secondary)</option>
                              <option>Primary Theology</option>
                              <option>Primary Secular</option>
                              <option>Both Primary Theology & Secular</option>
                         </select>
                    </div>
                    <div class="form-group">
                         <label>Student Population (approx.)</label>
                         <select>
                              <option>— Select range —</option>
                              <option>0 – 150</option>
                              <option>151 – 300</option>
                              <option>301 – 500</option>
                              <option>501 – 800</option>
                              <option>801 – 1,200</option>
                              <option>1,201 – 1,500</option>
                              <option>1,501 – 2,000</option>
                              <option>2,001+</option>
                         </select>
                    </div>
                    <div class="form-group">
                         <label>Message (optional)</label>
                         <textarea rows="3" placeholder="Any specific features or questions?"></textarea>
                    </div>
                    <button class="btn-primary"
                         style="width:100%;text-align:center;border:none;font-family:'DM Sans',sans-serif"
                         onclick="alert('Thank you! TechSate will contact you within 24 hours.')">Send Request
                         →</button>
               </div>
          </div>
     </section>

     <!-- FOOTER -->
     <footer>
          <div class="footer-top">
               <div class="footer-brand">
                    <div class="nav-logo">
                         <div class="nav-logo-icon">S</div>
                         <span class="nav-logo-text">SMASA</span>
                    </div>
                    <p>A comprehensive academic management platform by TechSate Company LTD — purpose-built for Uganda's
                         Islamic schools, fully bilingual and locally supported.</p>
                    <p class="arabic"
                         style="font-size:15px;color:var(--gold3);opacity:0.6;margin-top:12px;direction:rtl">نظام SMASA
                         الأكاديمي الشامل · شركة TechSate Limited</p>
               </div>
               <div class="footer-col">
                    <h4>Modules</h4>
                    <ul>
                         <li><a href="#modules">Academic Management</a></li>
                         <li><a href="#attendance">Attendance</a></li>
                         <li><a href="#timetable">Timetable</a></li>
                         <li><a href="#modules">Finance</a></li>
                         <li><a href="#modules">Exam Results</a></li>
                    </ul>
               </div>
               <div class="footer-col">
                    <h4>School Types</h4>
                    <ul>
                         <li><a href="#">Idaad & Thanawi</a></li>
                         <li><a href="#">Primary Theology</a></li>
                         <li><a href="#">Primary Secular</a></li>
                         <li><a href="#">Both Systems</a></li>
                    </ul>
               </div>
               <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                         <li><a href="#contact">Contact Us</a></li>
                         <li><a href="#pricing">Pricing</a></li>
                         <li><a href="https://github.com/huzaifa137/SMASA">GitHub</a></li>
                         <li><a href="#contact">Request Demo</a></li>
                    </ul>
               </div>
          </div>
          <div class="footer-bottom">
               <p>© 2026 TechSate Company LTD · Sky City Building, Kampala</p>
               <p style="color:var(--gold)">Built with ❤️ for Uganda's Islamic Schools</p>
          </div>
     </footer>

     <script>
          function switchPricing(tab) {
               document.querySelectorAll('.ptab').forEach(b => b.classList.remove('active'));
               document.querySelectorAll('.pricing-panel').forEach(p => p.classList.remove('active'));
               event.target.classList.add('active');
               document.getElementById('pricing-' + tab).classList.add('active');
          }

          const observer = new IntersectionObserver(entries => {
               entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible') } });
          }, { threshold: 0.12 });
          document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

          function countUp(el) {
               const target = parseInt(el.dataset.target);
               const duration = 2000;
               const step = target / 60;
               let current = 0;
               const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    el.textContent = Math.floor(current).toLocaleString();
                    if (current >= target) clearInterval(timer);
               }, duration / 60);
          }
          const counterObserver = new IntersectionObserver(entries => {
               entries.forEach(e => {
                    if (e.isIntersecting) {
                         e.target.querySelectorAll('.count-up').forEach(countUp);
                         counterObserver.unobserve(e.target);
                    }
               });
          }, { threshold: 0.5 });
          document.querySelectorAll('.hero-stats').forEach(el => counterObserver.observe(el));
     </script>
</body>

</html>