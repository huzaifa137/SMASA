<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
  <title>SMASA – Comprehensive Academic System</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" href="{{ URL::asset('assets/images/brand/logo.png') }}" type="image/x-icon" />
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
      max-width: 580px
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
      font-size: clamp(38px, 5.5vw, 68px);
      font-weight: 900;
      line-height: 1.05;
      margin-bottom: 20px
    }

    .hero h1 span {
      color: var(--gold2)
    }

    .hero-arabic {
      font-size: clamp(18px, 2.5vw, 26px);
      color: var(--gold3);
      opacity: .7;
      margin-bottom: 24px;
      line-height: 1.6;
      direction: rtl
    }

    .hero-desc {
      font-size: 17px;
      color: var(--text-muted);
      line-height: 1.8;
      max-width: 520px;
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
      box-shadow: 0 12px 30px rgba(201, 168, 76, .4)
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
      background: rgba(201, 168, 76, .08);
      border-color: var(--gold2)
    }

    .hero-stats {
      display: flex;
      gap: 48px;
      margin-top: 48px;
      padding-top: 36px;
      border-top: 1px solid rgba(201, 168, 76, .15)
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

    /* ── HERO CAROUSEL (right side) ── */
    .hero-carousel-wrap {
      position: absolute;
      right: 4%;
      top: 50%;
      transform: translateY(-50%);
      width: clamp(320px, 40vw, 540px);
      z-index: 3
    }

    .hero-carousel {
      position: relative;
      border-radius: 24px;
      overflow: hidden;
      border: 2px solid rgba(201, 168, 76, 0.3);
      box-shadow: 0 32px 80px rgba(0, 0, 0, 0.5)
    }

    .carousel-track {
      display: flex;
      transition: transform .7s cubic-bezier(.4, 0, .2, 1)
    }

    .carousel-slide {
      min-width: 100%;
      position: relative
    }

    .carousel-slide img {
      width: 100%;
      height: 380px;
      object-fit: cover;
      display: block
    }

    .carousel-slide-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 24px;
      background: linear-gradient(transparent, rgba(10, 22, 40, 0.92))
    }

    .carousel-slide-label {
      font-size: 15px;
      font-weight: 600;
      color: var(--gold2)
    }

    .carousel-slide-sub {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 4px
    }

    .carousel-dots {
      position: absolute;
      bottom: 16px;
      right: 16px;
      display: flex;
      gap: 6px;
      z-index: 5
    }

    .cdot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.35);
      border: none;
      cursor: pointer;
      transition: all .25s;
      padding: 0
    }

    .cdot.active {
      background: var(--gold2);
      width: 20px;
      border-radius: 4px
    }

    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 5;
      background: rgba(10, 22, 40, 0.7);
      border: 1px solid rgba(201, 168, 76, 0.3);
      color: var(--gold2);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .2s
    }

    .carousel-btn:hover {
      background: rgba(201, 168, 76, 0.2)
    }

    .carousel-btn.prev {
      left: 12px
    }

    .carousel-btn.next {
      right: 12px
    }

    /* Gold accent tag on carousel */
    .carousel-accent {
      position: absolute;
      top: 16px;
      left: 16px;
      background: linear-gradient(135deg, var(--gold), var(--gold2));
      color: var(--navy);
      font-size: 11px;
      font-weight: 700;
      padding: 5px 14px;
      border-radius: 100px;
      z-index: 5;
      letter-spacing: .06em;
      text-transform: uppercase
    }

    /* ── TRUST BAR ── */
    .trust-bar {
      background: rgba(15, 32, 64, 0.6);
      border-top: 1px solid rgba(201, 168, 76, 0.1);
      border-bottom: 1px solid rgba(201, 168, 76, 0.1);
      padding: 28px 5%
    }

    .trust-bar-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 32px;
      flex-wrap: wrap
    }

    .trust-item {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 14px;
      color: var(--text-muted)
    }

    .trust-icon {
      font-size: 22px
    }

    .trust-label {
      font-weight: 500;
      color: var(--white)
    }

    /* ── SECTION SHELL ── */
    section {
      padding: 100px 5%
    }

    .section-label {
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .12em;
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
      background: rgba(22, 42, 80, .6);
      border: 1px solid rgba(201, 168, 76, .12);
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
      background: linear-gradient(135deg, rgba(201, 168, 76, .04) 0%, transparent 60%);
      opacity: 0;
      transition: .3s
    }

    .module-card:hover {
      border-color: rgba(201, 168, 76, .35);
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
      background: rgba(14, 165, 160, .15);
      color: var(--teal2)
    }

    .tag-core {
      background: rgba(201, 168, 76, .15);
      color: var(--gold2)
    }

    .tag-pro {
      background: rgba(139, 92, 246, .15);
      color: #a78bfa
    }

    /* ── PHOTO GRID (school life) ── */
    .photo-section {
      background: var(--navy);
      padding: 100px 5%
    }

    .photo-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      grid-template-rows: 280px 280px;
      gap: 16px;
      margin-top: 60px;
      border-radius: 20px;
      overflow: hidden
    }

    .photo-cell {
      position: relative;
      overflow: hidden;
      border-radius: 16px
    }

    .photo-cell.tall {
      grid-row: span 2
    }

    .photo-cell img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .5s ease
    }

    .photo-cell:hover img {
      transform: scale(1.05)
    }

    .photo-cell-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(10, 22, 40, .85) 0%, transparent 55%);
      opacity: 0;
      transition: .4s
    }

    .photo-cell:hover .photo-cell-overlay {
      opacity: 1
    }

    .photo-cell-label {
      position: absolute;
      bottom: 16px;
      left: 16px;
      right: 16px;
      font-size: 13px;
      font-weight: 600;
      color: var(--gold2);
      opacity: 0;
      transition: .4s;
      transform: translateY(6px)
    }

    .photo-cell:hover .photo-cell-label {
      opacity: 1;
      transform: translateY(0)
    }

    .photo-tag-abs {
      position: absolute;
      top: 12px;
      left: 12px;
      background: linear-gradient(135deg, var(--gold), var(--gold2));
      color: var(--navy);
      font-size: 10px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 100px;
      letter-spacing: .05em
    }

    /* ── FEATURES SPLIT ── */
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
      background: rgba(201, 168, 76, .15);
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
      background: rgba(15, 32, 64, .8);
      border: 1px solid rgba(201, 168, 76, .2);
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
      background: rgba(255, 255, 255, .06);
      border-radius: 5px
    }

    .mock-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0;
      border-bottom: 1px solid rgba(255, 255, 255, .05)
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
      background: rgba(34, 197, 94, .15);
      color: #4ade80
    }

    .badge-late {
      background: rgba(234, 179, 8, .15);
      color: #fbbf24
    }

    .badge-absent {
      background: rgba(239, 68, 68, .15);
      color: #f87171
    }

    /* ── IMPACT NUMBERS ── */
    .impact-section {
      background: linear-gradient(135deg, var(--navy2) 0%, var(--navy3) 100%);
      padding: 80px 5%;
      position: relative;
      overflow: hidden
    }

    .impact-glow {
      position: absolute;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(201, 168, 76, .06) 0%, transparent 70%);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none
    }

    .impact-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 2px;
      background: rgba(201, 168, 76, .08);
      border-radius: 20px;
      overflow: hidden
    }

    .impact-cell {
      background: rgba(10, 22, 40, .7);
      padding: 40px 24px;
      text-align: center;
      position: relative
    }

    .impact-num {
      font-family: 'Playfair Display', serif;
      font-size: 48px;
      font-weight: 900;
      color: var(--gold2);
      line-height: 1
    }

    .impact-unit {
      font-size: 22px;
      color: var(--gold);
      font-family: 'Playfair Display', serif
    }

    .impact-label {
      font-size: 14px;
      color: var(--text-muted);
      margin-top: 10px;
      line-height: 1.5
    }

    .impact-sub {
      font-size: 12px;
      color: var(--gold);
      margin-top: 6px;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase
    }

    /* ── TESTIMONIAL CAROUSEL ── */
    .testimonial-section {
      background: var(--navy2);
      padding: 100px 5%
    }

    .tcarousel-wrap {
      position: relative;
      margin-top: 60px;
      overflow: hidden
    }

    .tcarousel-track {
      display: flex;
      transition: transform .6s cubic-bezier(.4, 0, .2, 1)
    }

    .tcard {
      min-width: calc(50% - 16px);
      margin-right: 32px;
      background: rgba(15, 32, 64, .7);
      border: 1px solid rgba(201, 168, 76, .15);
      border-radius: 24px;
      padding: 36px;
      position: relative
    }

    .tcard-quote {
      font-size: 48px;
      color: var(--gold);
      opacity: .3;
      font-family: 'Playfair Display', serif;
      line-height: 1;
      margin-bottom: 16px
    }

    .tcard-text {
      font-size: 15px;
      color: var(--white);
      line-height: 1.8;
      margin-bottom: 24px
    }

    .tcard-author {
      display: flex;
      align-items: center;
      gap: 14px
    }

    .tcard-av {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      overflow: hidden;
      flex-shrink: 0
    }

    .tcard-av img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .tcard-name {
      font-size: 14px;
      font-weight: 600;
      color: var(--gold2)
    }

    .tcard-role {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 2px
    }

    .tcarousel-nav {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 32px
    }

    .tnav-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 1px solid rgba(201, 168, 76, .3);
      background: transparent;
      color: var(--gold2);
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .2s
    }

    .tnav-btn:hover {
      background: rgba(201, 168, 76, .1)
    }

    /* ── HOW IT WORKS ── */
    .how-section {
      background: var(--navy);
      padding: 100px 5%
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
      margin-top: 60px;
      position: relative
    }

    .steps-grid::before {
      content: '';
      position: absolute;
      top: 36px;
      left: 12%;
      right: 12%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(201, 168, 76, .3) 20%, rgba(201, 168, 76, .3) 80%, transparent)
    }

    .step-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 0 16px;
      position: relative;
      z-index: 2
    }

    .step-num {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(15, 32, 64, .9);
      border: 2px solid rgba(201, 168, 76, .4);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: var(--gold2);
      margin-bottom: 20px;
      position: relative
    }

    .step-num::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 1px solid rgba(201, 168, 76, .1)
    }

    .step-img {
      width: 100%;
      height: 140px;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 16px;
      border: 1px solid rgba(201, 168, 76, .1)
    }

    .step-img img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .step-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 8px
    }

    .step-desc {
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.6
    }

    /* ── ATTENDANCE PREVIEW ── */
    .attendance-preview {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-top: 48px
    }

    .att-card {
      background: rgba(15, 32, 64, .7);
      border: 1px solid rgba(201, 168, 76, .15);
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

    /* ── TIMETABLE ── */
    .timetable-mock {
      background: rgba(10, 22, 40, .8);
      border: 1px solid rgba(201, 168, 76, .2);
      border-radius: 20px;
      overflow: hidden;
      margin-top: 48px
    }

    .tt-header {
      display: grid;
      grid-template-columns: 80px repeat(5, 1fr);
      background: rgba(201, 168, 76, .08);
      border-bottom: 1px solid rgba(201, 168, 76, .15)
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
      border-bottom: 1px solid rgba(255, 255, 255, .04)
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
      background: rgba(14, 165, 160, .12);
      color: var(--teal2);
      border-radius: 6px;
      padding: 8px 4px;
      width: 100%
    }

    .slot-gold {
      background: rgba(201, 168, 76, .12);
      color: var(--gold2);
      border-radius: 6px;
      padding: 8px 4px;
      width: 100%
    }

    .slot-purple {
      background: rgba(139, 92, 246, .12);
      color: #a78bfa;
      border-radius: 6px;
      padding: 8px 4px;
      width: 100%
    }

    .slot-green {
      background: rgba(34, 197, 94, .12);
      color: #4ade80;
      border-radius: 6px;
      padding: 8px 4px;
      width: 100%
    }

    /* ── FLAT-RATE PRICING (UGX 1,500 per student per term) ── */
    .flat-price {
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      gap: 32px;
      margin-top: 40px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto
    }

    .flat-price-main,
    .flat-calc {
      background: rgba(22, 42, 80, .9);
      border: 1px solid rgba(201, 168, 76, .5);
      border-radius: 24px;
      padding: 44px 36px 36px;
      position: relative;
      overflow: hidden
    }

    .flat-calc {
      background: rgba(15, 32, 64, .7);
      border-color: rgba(201, 168, 76, .2);
      padding-top: 36px
    }

    .flat-price-main h3 {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: var(--gold2);
      margin: 6px 0 14px
    }

    .flat-amount {
      font-family: 'Playfair Display', serif;
      font-size: 64px;
      font-weight: 700;
      line-height: 1.05;
      color: var(--white)
    }

    .flat-cur {
      font-size: 22px;
      font-weight: 600;
      color: var(--gold2);
      margin-right: 6px
    }

    [dir=rtl] .flat-cur {
      margin-right: 0;
      margin-left: 6px
    }

    .flat-sub {
      color: var(--text-muted);
      font-size: 15px;
      margin: 6px 0 24px
    }

    .flat-list {
      list-style: none;
      padding: 0;
      margin: 0
    }

    .flat-list li {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 12px
    }

    .flat-list i {
      color: var(--teal2);
      margin-top: 4px
    }

    .flat-calc h4 {
      font-size: 18px;
      font-weight: 600;
      color: var(--gold2);
      margin-bottom: 20px
    }

    .flat-calc label {
      display: block;
      font-size: 13px;
      color: var(--text-muted);
      margin-bottom: 8px
    }

    .flat-calc input[type=number] {
      width: 100%;
      background: rgba(255, 255, 255, .05);
      border: 1px solid rgba(255, 255, 255, .1);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 18px;
      color: var(--white);
      font-family: 'DM Sans', sans-serif
    }

    .flat-calc input[type=number]:focus {
      outline: none;
      border-color: rgba(201, 168, 76, .5)
    }

    .flat-calc input[type=range] {
      width: 100%;
      margin: 18px 0 22px;
      accent-color: var(--gold)
    }

    .calc-result {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      background: rgba(201, 168, 76, .08);
      border: 1px solid rgba(201, 168, 76, .2);
      border-radius: 14px;
      padding: 18px;
      margin-bottom: 22px
    }

    .calc-k {
      display: block;
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 4px
    }

    .calc-v {
      font-size: 22px;
      font-weight: 700;
      color: var(--gold2)
    }

    .calc-v-sm {
      font-size: 17px;
      color: var(--white)
    }

    .flat-calc .pricing-note {
      text-align: center
    }

    @media (max-width: 860px) {
      .flat-price {
        grid-template-columns: 1fr
      }

      .flat-amount {
        font-size: 52px
      }
    }

    /* ── PRICING ── */
    .pricing-bg {
      background: linear-gradient(180deg, var(--navy) 0%, var(--navy2) 100%)
    }

    .pricing-tabs {
      display: flex;
      gap: 0;
      background: rgba(255, 255, 255, .05);
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
      background: rgba(15, 32, 64, .7);
      border: 1px solid rgba(201, 168, 76, .15);
      border-radius: 24px;
      padding: 36px;
      position: relative;
      overflow: hidden
    }

    .pricing-card.featured {
      border-color: rgba(201, 168, 76, .5);
      background: rgba(22, 42, 80, .9)
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
      background: rgba(255, 255, 255, .03)
    }

    .pricing-table th:first-child {
      text-align: left
    }

    .pricing-table td {
      padding: 10px 12px;
      font-size: 14px;
      color: var(--white);
      border-bottom: 1px solid rgba(255, 255, 255, .05)
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
      border: 1px solid rgba(201, 168, 76, .35);
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

    /* ── DEMO FORM STATES ── */
    .demo-alert {
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13px;
      line-height: 1.6;
      margin-bottom: 18px
    }

    .demo-alert-ok {
      background: rgba(34, 197, 94, .12);
      border: 1px solid rgba(34, 197, 94, .4);
      color: #86efac
    }

    .demo-alert-err {
      background: rgba(239, 68, 68, .1);
      border: 1px solid rgba(239, 68, 68, .4);
      color: #fca5a5
    }

    .form-group input.invalid,
    .form-group select.invalid {
      border-color: rgba(239, 68, 68, .7)
    }

    #demoSubmit[disabled] {
      opacity: .7;
      cursor: wait
    }

    .demo-success {
      text-align: center;
      padding: 24px 8px
    }

    .demo-success-icon {
      font-size: 54px;
      color: #22c55e;
      margin-bottom: 12px
    }

    .demo-success h4 {
      font-size: 22px;
      color: var(--gold2);
      margin-bottom: 10px
    }

    .demo-success p {
      color: var(--text-muted);
      font-size: 14px;
      line-height: 1.7
    }

    .demo-ref {
      margin: 14px 0 22px
    }

    .demo-ref strong {
      color: var(--white);
      letter-spacing: .06em
    }

    /* ── CONTACT ── */
    .contact-bg {
      background: linear-gradient(135deg, var(--navy2) 0%, rgba(10, 22, 40, .95) 100%)
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
      background: rgba(201, 168, 76, .12);
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
      background: rgba(15, 32, 64, .6);
      border: 1px solid rgba(201, 168, 76, .15);
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
      background: rgba(255, 255, 255, .05);
      border: 1px solid rgba(255, 255, 255, .1);
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
      border-color: rgba(201, 168, 76, .5)
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

    .phones {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 6px
    }

    .phone-badge {
      background: rgba(201, 168, 76, .1);
      border: 1px solid rgba(201, 168, 76, .2);
      color: var(--gold2);
      font-size: 13px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 8px
    }

    /* ── CONTACT IMAGE STRIP ── */
    .contact-img-strip {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 32px
    }

    .contact-img-strip img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 12px;
      border: 1px solid rgba(201, 168, 76, .15)
    }

    /* ── FOOTER ── */
    footer {
      background: var(--navy);
      border-top: 1px solid rgba(201, 168, 76, .1);
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
      border-top: 1px solid rgba(255, 255, 255, .06)
    }

    .footer-bottom p {
      font-size: 13px;
      color: var(--text-muted)
    }

    /* ── IMAGE FALLBACK SVG BACKGROUNDS ── */
    .img-african-classroom {
      background: linear-gradient(135deg, #1a3a2a 0%, #2d5a3d 50%, #1a3a2a 100%)
    }

    .img-african-students {
      background: linear-gradient(135deg, #1a2a3a 0%, #2a3d5a 50%, #1a2a3a 100%)
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

    .count-up {
      display: inline-block
    }

    /* ── RESPONSIVE ── */
    @media(max-width:1100px) {
      .hero-carousel-wrap {
        display: none
      }

      .hero-content {
        max-width: 100%
      }

      .hero {
        min-height: auto;
        padding-top: 120px;
        padding-bottom: 60px
      }
    }

    @media(max-width:900px) {

      .features-split,
      .contact-grid {
        grid-template-columns: 1fr
      }

      .photo-grid {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: auto
      }

      .photo-cell.tall {
        grid-row: span 1
      }

      .attendance-preview {
        grid-template-columns: 1fr
      }

      .footer-top {
        grid-template-columns: 1fr 1fr
      }

      .nav-links {
        display: none
      }

      .impact-grid {
        grid-template-columns: 1fr 1fr
      }

      .steps-grid {
        grid-template-columns: 1fr 1fr
      }

      .steps-grid::before {
        display: none
      }

      .tcard {
        min-width: calc(100% - 0px);
        margin-right: 0
      }
    }

    @media(max-width:600px) {
      .photo-grid {
        grid-template-columns: 1fr
      }

      .impact-grid {
        grid-template-columns: 1fr 1fr
      }

      .form-grid-2 {
        grid-template-columns: 1fr
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
      <li><a href="#attendance">Attendance</a></li>
      <li><a href="#timetable">Timetable</a></li>
      <li><a href="#pricing">Pricing</a></li>
      <li><a href="{{ url('/users/privacy-policy') }}">Privacy Policy</a></li>

      <li><a href="#contact">Contact</a></li>
    </ul>
    <button class="nav-cta" onclick="window.location.href='/users/login'">
      <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Login
    </button>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid-lines"></div>
    <div class="hero-glow"></div>
    <div class="hero-content fade-in">
      <div class="hero-badge"><span class="hero-badge-dot"></span>TechSate Software Company LTD — School Management
      </div>
      <h1>SMASA<br><span>Comprehensive</span><br>Academic System</h1>
      <!-- <div class="hero-arabic arabic">نظام SMASA الأكاديمي الشامل</div> -->
      <p class="hero-desc">A powerful, multilingual school management platform built for both Uganda's Islamic and non
        Islamic primary and secondary institutions — handling academics, finance, staff, attendance, timetables and
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

    <!-- HERO IMAGE CAROUSEL -->
    <div class="hero-carousel-wrap fade-in">
      <div class="carousel-accent">
        <i class="fas fa-flag-checkered" style="margin-right: 6px;"></i>
        Uganda's #1 School Management Platform
      </div>

      <div class="hero-carousel">
        <div class="carousel-track" id="heroTrack">

          <div class="carousel-slide">
            <img src="/homepagepics/1.png" alt="Students in classroom">
            <div class="carousel-slide-overlay">
              <div class="carousel-slide-label">
                <i class="fas fa-book-open" style="margin-right: 8px;"></i>
                Modern Academic Management
              </div>
              <div class="carousel-slide-sub">
                Full student lifecycle — enrollment to graduation
              </div>
            </div>
          </div>

          <div class="carousel-slide">
            <img src="/homepagepics/2.jfif" alt="School attendance">
            <div class="carousel-slide-overlay">
              <div class="carousel-slide-label">
                <i class="fas fa-clipboard-list" style="margin-right: 8px;"></i>
                Real-Time Attendance Tracking
              </div>
              <div class="carousel-slide-sub">
                Students & staff — dual attendance streams
              </div>
            </div>
          </div>

          <div class="carousel-slide">
            <img src="/homepagepics/3.jfif" alt="Islamic school learning">
            <div class="carousel-slide-overlay">
              <div class="carousel-slide-label">
                <i class="fas fa-mosque" style="margin-right: 8px;"></i>
                Theology + Secular Curricula
              </div>
              <div class="carousel-slide-sub">
                Idaad, Thanawi, Primary Theology & Secular
              </div>
            </div>
          </div>

          <div class="carousel-slide">
            <img src="/homepagepics/4.jfif" alt="Teacher with students">
            <div class="carousel-slide-overlay">
              <div class="carousel-slide-label">
                <i class="fas fa-chalkboard-user" style="margin-right: 8px;"></i>
                Staff & Teacher Management
              </div>
              <div class="carousel-slide-sub">
                Profiles, workloads, check-ins, payroll
              </div>
            </div>
          </div>

          <div class="carousel-slide">
            <img src="/homepagepics/5.jfif" alt="Exam results">
            <div class="carousel-slide-overlay">
              <div class="carousel-slide-label">
                <i class="fas fa-chart-line" style="margin-right: 8px;"></i>
                Exam Results & Analytics
              </div>
              <div class="carousel-slide-sub">
                Auto-graded report cards, division calculations
              </div>
            </div>
          </div>

        </div>

        <button class="carousel-btn prev" id="heroPrev">&#8249;</button>
        <button class="carousel-btn next" id="heroNext">&#8250;</button>
        <div class="carousel-dots" id="heroDots"></div>
      </div>
    </div>
  </section>

  <!-- TRUST BAR -->
  <div class="trust-bar">
    <div class="trust-bar-inner">
      <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-shield-alt"></i></span>
        <div>
          <div class="trust-label">Role-Based Access</div>
          <div>Super Admin · Teacher · Gatekeeper · Student</div>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-wifi"></i></span>
        <div>
          <div class="trust-label">Offline-Tolerant</div>
          <div>Works in low-connectivity rural schools</div>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-globe-africa"></i></span>
        <div>
          <div class="trust-label">Bilingual Platform</div>
          <div>Full English + Arabic RTL support</div>
        </div>
      </div>
      <div class="trust-item">
        <span class="trust-icon"><i class="fas fa-mobile-alt"></i></span>
        <div>
          <div class="trust-label">Any Device</div>
          <div>Browser-based — no installs needed</div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODULES -->
  <section id="modules" class="modules-bg">
    <div class="center fade-in">
      <div class="section-label">What's Inside</div>
      <div class="divider"></div>
      <h2 class="section-title">Everything Your School Needs</h2>
      <p class="section-desc">From student enrollment to exam analytics, library to parent portal — SMASA covers every corner of school
        administration in one unified platform.</p>
    </div>
    <div class="modules-grid fade-in">
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academic Management</h3>
        <p>Manage student enrollment, classes, streams, subjects, and academic years. Full support for Idaad, Thanawi,
          Primary Theology and Secular curricula.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(14,165,160,.12)"><i class="fas fa-clipboard-list"></i></div>
        <h3>Attendance Tracking</h3>
        <p>Dual attendance system: class-level student attendance by teachers and school-gate teacher attendance with
          arrival/departure timestamps and reports.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(139,92,246,.12)"><i class="fas fa-calendar-alt"></i></div>
        <h3>Timetable Generator</h3>
        <p>Advanced, conflict-aware timetable builder that handles any school complexity. Assign teachers, rooms and
          subjects with a clean drag-and-drop interface.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-coins"></i></div>
        <h3>Finance Management</h3>
        <p>Track school fees, generate receipts, handle outstanding balances and produce term-by-term financial reports
          for administrators and parents.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(34,197,94,.12)"><i class="fas fa-chalkboard-user"></i></div>
        <h3>Staff Management</h3>
        <p>Maintain complete teacher profiles, assign subjects and classes, track workloads, manage credentials and
          payroll integration.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(239,68,68,.12)"><i class="fas fa-chart-simple"></i></div>
        <h3>Reports & Analytics</h3>
        <p>Comprehensive dashboards showing student performance, class averages, attendance trends, and financial KPIs
          with export to Excel and PDF.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(14,165,160,.12)"><i class="fas fa-envelope"></i></div>
        <h3>Communication</h3>
        <p>Send SMS and email notifications to parents and staff. Broadcast announcements, result slips and fee
          reminders with one click.</p>
        <span class="module-tag tag-pro">Pro Feature</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-mosque"></i></div>
        <h3>Theology Support</h3>
        <p>Native Arabic interface, RTL text, Islamic calendar integration and grading systems aligned with Ugandan
          Muslim school requirements.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(139,92,246,.12)"><i class="fas fa-pen-to-square"></i></div>
        <h3>Exam Management</h3>
        <p>Schedule exams, upload results, auto-grade with configurable grading rubrics and generate class-wide report
          cards with division calculations.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-book-open"></i></div>
        <h3>Subjects &amp; Curriculum</h3>
        <p>Custom subjects, A-Level combinations, O-Level electives and the NLSC school curriculum, set up once and reused across classes and exams.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(139,92,246,.12)"><i class="fas fa-file-lines"></i></div>
        <h3>Report Cards</h3>
        <p>Printable report cards in English and Arabic, O-Level and A-Level layouts, NLSC assessments and custom designs assigned per school.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(34,197,94,.12)"><i class="fas fa-arrow-trend-up"></i></div>
        <h3>Student Promotion</h3>
        <p>Promote whole classes at the end of the year with suggested paths, review the plan before approving, export it and revert a batch if needed.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(14,165,160,.12)"><i class="fas fa-file-import"></i></div>
        <h3>Bulk Import &amp; Data Tools</h3>
        <p>Import students, teachers and photos in bulk, and merge duplicate student records so your data stays clean from day one.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(14,165,160,.12)"><i class="fas fa-qrcode"></i></div>
        <h3>Card Scan &amp; Gate Arrival</h3>
        <p>Scan ID cards at the gate to log student and staff arrival in seconds, with a live scanning hub and arrival reports.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-id-card"></i></div>
        <h3>Student &amp; Teacher ID Cards</h3>
        <p>Design, generate and verify professional ID cards for students and teachers, ready to print and scan.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(139,92,246,.12)"><i class="fas fa-book-open-reader"></i></div>
        <h3>Library Management</h3>
        <p>Catalogue books, manage members, borrowings, reservations and book requests, track fines and view library reports.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(34,197,94,.12)"><i class="fas fa-user-group"></i></div>
        <h3>Parent Portal</h3>
        <p>Parents log in with their phone number to follow results, attendance and fee balances for each child, even across schools.</p>
        <span class="module-tag tag-new">New Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(239,68,68,.12)"><i class="fas fa-bell"></i></div>
        <h3>Notifications</h3>
        <p>Broadcast announcements to staff and schools as in-app and push notifications, with a personal inbox for every user.</p>
        <span class="module-tag tag-pro">Pro Feature</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(201,168,76,.12)"><i class="fas fa-file-invoice-dollar"></i></div>
        <h3>Billing &amp; Invoices</h3>
        <p>See your SMASA termly invoices, payments and receipts in one place, so there are never any billing surprises.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(14,165,160,.12)"><i class="fas fa-shield-halved"></i></div>
        <h3>User Rights &amp; Permissions</h3>
        <p>Create roles and decide exactly which modules and features each staff member can open, view, edit or delete.</p>
        <span class="module-tag tag-core">Core Module</span>
      </div>
      <div class="module-card">
        <div class="module-icon" style="background:rgba(139,92,246,.12)"><i class="fas fa-list-check"></i></div>
        <h3>Audit Trail</h3>
        <p>A tamper-evident record of who did what, when and from where, so every change in your school is accountable.</p>
        <span class="module-tag tag-pro">Security</span>
      </div>
    </div>
  </section>

  <!-- SCHOOL LIFE PHOTO GRID -->
  <section class="photo-section">
    <div class="center fade-in">
      <div class="section-label">School Life</div>
      <div class="divider"></div>
      <h2 class="section-title">Built for Real Ugandan Schools</h2>
      <p class="section-desc">SMASA was designed in Uganda, for Uganda — understanding the unique blend of Islamic
        theology, secular education and local context.</p>
    </div>
    <div class="photo-grid fade-in">

      <!-- Top middle -->
      <div class="photo-cell">
        <img src="/homepagepics/1.png" alt="Students in classroom" alt="Teacher in classroom" loading="lazy"
          onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#162a50,#0a1628)'">
        <div class="photo-tag-abs">Teaching</div>
        <div class="photo-cell-overlay"></div>
        <div class="photo-cell-label">Teacher attendance tracked from the gate</div>
      </div>
      <!-- Top right -->
      <div class="photo-cell">
        <img src="/homepagepics/6.png" alt="Students in classroom" alt="Exam preparation" loading="lazy"
          onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#162a50,#0f2040)'">
        <div class="photo-tag-abs">Exams</div>
        <div class="photo-cell-overlay"></div>
        <div class="photo-cell-label">Auto-graded results — divisions calculated instantly</div>
      </div>
      <!-- Bottom middle -->
      <div class="photo-cell">
        <img src="/homepagepics/7.png" alt="Islamic school education" loading="lazy"
          onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#1a2a3a,#162a50)'">
        <div class="photo-tag-abs">Theology</div>
        <div class="photo-cell-overlay"></div>
        <div class="photo-cell-label">Full Arabic + Islamic curriculum support</div>
      </div>
      <!-- Bottom right -->
      <div class="photo-cell">
        <img src="/homepagepics/4.jfif" alt="School library reading" loading="lazy"
          onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#0a1628,#162a50)'">
        <div class="photo-tag-abs">Learning</div>
        <div class="photo-cell-overlay"></div>
        <div class="photo-cell-label">Reports & analytics drive better outcomes</div>
      </div>
    </div>
  </section>

  <!-- IMPACT NUMBERS -->
  <section class="impact-section">
    <div class="impact-glow"></div>
    <div class="center fade-in" style="margin-bottom:60px">
      <div class="section-label">Our Impact</div>
      <div class="divider"></div>
      <h2 class="section-title">Numbers That Speak</h2>
    </div>
    <div class="impact-grid fade-in">
      <div class="impact-cell">
        <div class="impact-num"><span class="count-up" data-target="200">0</span><span class="impact-unit">+</span>
        </div>
        <div class="impact-label">Schools<br>Onboarded</div>
        <div class="impact-sub">Across Uganda</div>
      </div>
      <div class="impact-cell">
        <div class="impact-num"><span class="count-up" data-target="5000">0</span><span class="impact-unit">+</span>
        </div>
        <div class="impact-label">Active<br>Students</div>
        <div class="impact-sub">Managed daily</div>
      </div>
      <div class="impact-cell">
        <div class="impact-num"><span class="count-up" data-target="12">0</span></div>
        <div class="impact-label">Core<br>Modules</div>
        <div class="impact-sub">One platform</div>
      </div>
      <div class="impact-cell">
        <div class="impact-num"><span class="count-up" data-target="98">0</span><span class="impact-unit">%</span></div>
        <div class="impact-label">Uptime<br>Reliability</div>
        <div class="impact-sub">Offline-tolerant</div>
      </div>
    </div>
  </section>

  <!-- FEATURES SPLIT -->
  <section id="features" style="background:var(--navy)">
    <div class="features-split fade-in">
      <div>
        <div class="section-label">Why SMASA</div>
        <h2 class="section-title">Built for Uganda's Schools</h2>
        <p class="section-desc">Not a generic school system — SMASA was engineered from the ground up for the unique
          curriculum, language and compliance needs of Islamic institutions in Uganda.</p>
        <ul class="features-list">
          <li class="feature-item">
            <div class="feature-check">✓</div>
            <div>
              <h4>Bilingual Interface (English + Arabic)</h4>
              <p>Full RTL Arabic support with Naskh typography for all academic records, report cards and
                communications.</p>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-check">✓</div>
            <div>
              <h4>Multi-curriculum Grading</h4>
              <p>Separate grading engines for Idaad, Thanawi, Primary Theology and Secular — each with its own rubrics
                and division classes.</p>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-check">✓</div>
            <div>
              <h4>School-product Aware</h4>
              <p>The system auto-configures classes, subjects and reports based on whether the school runs theology,
                secular or a combined programme.</p>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-check">✓</div>
            <div>
              <h4>Role-based Access Control</h4>
              <p>Separate portals for Super Admins, School Admins, Teachers, Students and Gate-keepers — each seeing
                only what they need.</p>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-check">✓</div>
            <div>
              <h4>Offline-tolerant Design</h4>
              <p>Core data entry works with intermittent connectivity and syncs automatically — critical for schools in
                rural Uganda.</p>
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
        <div style="font-size:14px;font-weight:600;color:var(--gold2);margin-bottom:16px">
          School — Attendance · {{ now()->format('l d F Y') }}
        </div>
        <div class="mock-row">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="teacher-av" style="background:rgba(14,165,160,.15);color:var(--teal2)">AN</div>
            <div>
              <div class="mock-name">Aminah Nakato</div>
              <div class="mock-sub">Admission #P3-001</div>
            </div>
          </div>
          <span class="mock-badge badge-present">Present</span>
        </div>
        <div class="mock-row">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="teacher-av" style="background:rgba(201,168,76,.15);color:var(--gold2)">IS</div>
            <div>
              <div class="mock-name">Ibrahim Ssemwogerere</div>
              <div class="mock-sub">Admission #P3-002</div>
            </div>
          </div>
          <span class="mock-badge badge-late">Late</span>
        </div>
        <div class="mock-row">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="teacher-av" style="background:rgba(139,92,246,.15);color:#a78bfa">FM</div>
            <div>
              <div class="mock-name">Fatuma Namutebi</div>
              <div class="mock-sub">Admission #P3-003</div>
            </div>
          </div>
          <span class="mock-badge badge-present">Present</span>
        </div>
        <div class="mock-row">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="teacher-av" style="background:rgba(239,68,68,.15);color:#f87171">HK</div>
            <div>
              <div class="mock-name">Hassan Kasagga</div>
              <div class="mock-sub">Admission #P3-004</div>
            </div>
          </div>
          <span class="mock-badge badge-absent">Absent</span>
        </div>
        <div class="mock-row">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="teacher-av" style="background:rgba(14,165,160,.15);color:var(--teal2)">ZN</div>
            <div>
              <div class="mock-name">Zainab Nakirya</div>
              <div class="mock-sub">Admission #P3-005</div>
            </div>
          </div>
          <span class="mock-badge badge-present">Present</span>
        </div>
        <div
          style="margin-top:20px;padding:14px;background:rgba(201,168,76,.06);border-radius:12px;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13px;color:var(--text-muted)">Session saved · 34/38 present</span>
          <span style="font-size:13px;color:var(--green);font-weight:600">89.5%</span>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="how-section">
    <div class="center fade-in">
      <div class="section-label">Getting Started</div>
      <div class="divider"></div>
      <h2 class="section-title">Up & Running in 4 Steps</h2>
      <p class="section-desc">We handle the entire setup — your school is live within days, not months.</p>
    </div>
    <div class="steps-grid fade-in">
      <div class="step-item">
        <div class="step-num">1</div>
        <div class="step-img">
          <img src="/homepagepics/10.png" alt="Onboarding" loading="lazy"
            onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#0f2040,#162a50)'">
        </div>
        <div class="step-title">School Onboarding</div>
        <div class="step-desc">We configure your school profile, curriculum type and academic year structure.</div>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <div class="step-img">
          <img src="/homepagepics/11.png" alt="Student enrollment" loading="lazy"
            onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#162a50,#0a1628)'">
        </div>
        <div class="step-title">Import Students & Staff</div>
        <div class="step-desc">Bulk upload existing records or enter data through the guided enrollment wizard.</div>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <div class="step-img">
          <img src="/homepagepics/12.png" alt="Training session" loading="lazy"
            onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#0a1628,#162a50)'">
        </div>
        <div class="step-title">Staff Training</div>
        <div class="step-desc">TechSate team delivers on-site training in Luganda, English or Arabic as needed.</div>
      </div>
      <div class="step-item">
        <div class="step-num">4</div>
        <div class="step-img">
          <img src="/homepagepics/13.png" alt="Live school management" loading="lazy"
            onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#162a50,#0f2040)'">
        </div>
        <div class="step-title">Go Live!</div>
        <div class="step-desc">Your school manages attendance, exams, fees and timetables — all from one dashboard.
        </div>
      </div>
    </div>
  </section>

  <!-- ATTENDANCE -->
  <section id="attendance" style="background:var(--navy2)">
    <div class="center fade-in">
      <div class="section-label">Module Spotlight</div>
      <div class="divider"></div>
      <h2 class="section-title">Attendance Management</h2>
      <p class="section-desc">Two independent attendance streams — one for students, one for staff — with real-time
        dashboards, filters and downloadable reports.</p>
    </div>
    <div class="attendance-preview fade-in">
      <div class="att-card">
        <div class="att-header">
          <div class="att-title">Student Attendance</div>
          <div class="att-date">Term II · Week 4</div>
        </div>
        <div class="att-donut">
          <svg width="120" height="120" viewBox="0 0 120 120">
            <circle cx="60" cy="60" r="48" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="14" />
            <circle cx="60" cy="60" r="48" fill="none" stroke="#22c55e" stroke-width="14" stroke-dasharray="271 30"
              stroke-linecap="round" />
            <circle cx="60" cy="60" r="48" fill="none" stroke="#f59e0b" stroke-width="14" stroke-dasharray="15 286"
              stroke-dashoffset="-271" stroke-linecap="round" />
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
            <div class="teacher-av" style="background:rgba(14,165,160,.15);color:var(--teal2)">AK</div>
            <div style="flex:1">
              <div class="teacher-name">Ustadh Abdul Karim</div>
              <div class="teacher-time">In 07:42 AM</div>
            </div>
            <span class="mock-badge badge-present" style="font-size:10px">On Time</span>
          </div>
          <div class="teacher-row">
            <div class="teacher-av" style="background:rgba(201,168,76,.15);color:var(--gold2)">HN</div>
            <div style="flex:1">
              <div class="teacher-name">Hajjah Nadia</div>
              <div class="teacher-time">In 08:15 AM</div>
            </div>
            <span class="mock-badge badge-late" style="font-size:10px">Late</span>
          </div>
          <div class="teacher-row">
            <div class="teacher-av" style="background:rgba(139,92,246,.15);color:#a78bfa">MK</div>
            <div style="flex:1">
              <div class="teacher-name">Mr. Musa Kiggundu</div>
              <div class="teacher-time">In 07:55 AM</div>
            </div>
            <span class="mock-badge badge-present" style="font-size:10px">On Time</span>
          </div>
          <div class="teacher-row">
            <div class="teacher-av" style="background:rgba(239,68,68,.15);color:#f87171">ZN</div>
            <div style="flex:1">
              <div class="teacher-name">Ustadha Zainab Nsubuga</div>
              <div class="teacher-time">— Not arrived</div>
            </div>
            <span class="mock-badge badge-absent" style="font-size:10px">Absent</span>
          </div>
        </div>
        <div style="margin-top:16px;font-size:12px;color:var(--text-muted);text-align:center">Gate-keeper portal ·
          Auto-timestamped</div>
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
          <div class="slot-gold">Quran Recitation<br><span style="font-size:10px;opacity:.7">Ust. Abdul Karim</span>
          </div>
        </div>
        <div class="tt-slot">
          <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr. Kiggundu</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-gold">Quran Recitation<br><span style="font-size:10px;opacity:.7">Ust. Abdul Karim</span>
          </div>
        </div>
        <div class="tt-slot">
          <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr. Kiggundu</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span></div>
        </div>
      </div>
      <div class="tt-row">
        <div class="tt-time">8:30–9:30</div>
        <div class="tt-slot">
          <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr. Kiggundu</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-purple">Islamic Studies<br><span style="font-size:10px;opacity:.7">Ustadha Zainab</span>
          </div>
        </div>
        <div class="tt-slot">
          <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-gold">Arabic<br><span style="font-size:10px;opacity:.7">Hajjah Nadia</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-purple">Islamic Studies<br><span style="font-size:10px;opacity:.7">Ustadha Zainab</span>
          </div>
        </div>
      </div>
      <div class="tt-row">
        <div class="tt-time">9:30–10:30</div>
        <div class="tt-slot">
          <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-blue">Science<br><span style="font-size:10px;opacity:.7">Mr. Sserunjogi</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-purple">Arabic<br><span style="font-size:10px;opacity:.7">Hajjah Nadia</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-green">English<br><span style="font-size:10px;opacity:.7">Ms. Nakato</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-blue">Science<br><span style="font-size:10px;opacity:.7">Mr. Sserunjogi</span></div>
        </div>
      </div>
      <div class="tt-row">
        <div class="tt-time">10:30–11:00</div>
        <div class="tt-slot"
          style="grid-column:span 5;text-align:center;color:var(--text-muted);font-size:12px;padding:14px">☕ Break</div>
      </div>
      <div class="tt-row">
        <div class="tt-time">11:00–12:00</div>
        <div class="tt-slot">
          <div class="slot-purple">Social Studies<br><span style="font-size:10px;opacity:.7">Mr. Wasswa</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-gold">Tafseer<br><span style="font-size:10px;opacity:.7">Ust. Abdul Karim</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-blue">Mathematics<br><span style="font-size:10px;opacity:.7">Mr. Kiggundu</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-purple">Social Studies<br><span style="font-size:10px;opacity:.7">Mr. Wasswa</span></div>
        </div>
        <div class="tt-slot">
          <div class="slot-gold">Tafseer<br><span style="font-size:10px;opacity:.7">Ust. Abdul Karim</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="testimonial-section">
    <div class="center fade-in">
      <div class="section-label">Testimonials</div>
      <div class="divider"></div>
      <h2 class="section-title">What School Leaders Say</h2>
      <p class="section-desc">Headteachers and principals from across Uganda share their experience with SMASA.</p>
    </div>
    <div class="tcarousel-wrap fade-in">
      <div class="tcarousel-track" id="tTrack">
        <div class="tcard">
          <div class="tcard-quote">"</div>
          <div class="tcard-text">Before SMASA, we were doing everything in exercise books and spreadsheets. Now our
            entire school — 340 students, 28 teachers — runs from one dashboard. The attendance module alone saves us
            two hours every morning.</div>
          <div class="tcard-author">
            <div class="tcard-av"><img src="/testimonial/3.jpg" alt="Headteacher"
                onerror="this.parentElement.style.background='rgba(201,168,76,0.2)'"></div>
            <div>
              <div class="tcard-name">Ustadh Mahmoud Ssentamu</div>
              <div class="tcard-role">Headteacher — Al-Zoor Islamic Primary, Kampala</div>
            </div>
          </div>
        </div>
        <div class="tcard">
          <div class="tcard-quote">"</div>
          <div class="tcard-text">The Arabic RTL support is exceptional. We run both Thanawi and secular classes and
            SMASA handles both curricula perfectly — separate grading rubrics, separate report cards. Our parents love
            getting results via SMS.</div>
          <div class="tcard-author">
            <div class="tcard-av"><img src="/testimonial/1.jpg" alt="Principal"
                onerror="this.parentElement.style.background='rgba(14,165,160,0.2)'"></div>
            <div>
              <div class="tcard-name">Hajjah Fatuma Nakirye</div>
              <div class="tcard-role">Principal — Madrasa Nusrat ul-Islam, Wakiso</div>
            </div>
          </div>
        </div>
        <div class="tcard">
          <div class="tcard-quote">"</div>
          <div class="tcard-text">Our internet is unreliable but SMASA keeps working. Teachers mark attendance offline,
            it syncs when the connection comes back. TechSate's support team is always reachable — even on weekends
            during exam season.</div>
          <div class="tcard-author">
            <div class="tcard-av"><img src="/testimonial/2.jpg" alt="School Admin"
                onerror="this.parentElement.style.background='rgba(139,92,246,0.2)'"></div>
            <div>
              <div class="tcard-name">Mr. Robert Wasswa</div>
              <div class="tcard-role">School Administrator — Jinjaz Muslim Secondary</div>
            </div>
          </div>
        </div>
        <div class="tcard">
          <div class="tcard-quote">"</div>
          <div class="tcard-text">The fee management module transformed how we collect school fees. Outstanding balances
            are visible instantly, receipts are generated automatically, and the termly financial report saves our
            bursar an entire week's work.</div>
          <div class="tcard-author">
            <div class="tcard-av"><img src="/testimonial/4.jpg" alt="Bursar"
                onerror="this.parentElement.style.background='rgba(201,168,76,0.2)'"></div>
            <div>
              <div class="tcard-name">Sister Amina Nalwoga</div>
              <div class="tcard-role">Bursar — Kibuli Muslimz Primary School</div>
            </div>
          </div>
        </div>
      </div>
      <div class="tcarousel-nav">
        <button class="tnav-btn" id="tPrev">&#8249;</button>
        <button class="tnav-btn" id="tNext">&#8250;</button>
      </div>
    </div>
  </section>

  <!-- PRICING -->
  <section id="pricing" class="pricing-bg">
    <div class="center fade-in">
      <div class="section-label">Termly Charges</div>
      <div class="divider"></div>
      <h2 class="section-title">Transparent, Simple Pricing</h2>
      <p class="section-desc">One flat rate for every school. You pay per student, per term — no tiers, no hidden fees,
        and every module included.</p>
      <div class="pricing-tabs">
        <button class="ptab active" onclick="switchPricing('english',event)">English</button>
        <button class="ptab" onclick="switchPricing('arabic',event)">عربي</button>
      </div>
    </div>

    <!-- ENGLISH -->
    <div id="pricing-english" class="pricing-panel active fade-in">
      <div class="flat-price">
        <div class="flat-price-main">
          <div class="pricing-badge-top">ONE SIMPLE RATE</div>
          <h3>Per Student, Per Term</h3>
          <div class="flat-amount"><span class="flat-cur">UGX</span> 1,500</div>
          <p class="flat-sub">per student, per term</p>
          <ul class="flat-list">
            <li><i class="fas fa-circle-check"></i> Every module included, with nothing locked behind a higher tier</li>
            <li><i class="fas fa-circle-check"></i> Idaad, Thanawi, Primary Theology and Secular curricula</li>
            <li><i class="fas fa-circle-check"></i> Report cards in English and Arabic</li>
            <li><i class="fas fa-circle-check"></i> Pay only for the students you actually have</li>
          </ul>
        </div>
        <div class="flat-calc">
          <h4>Estimate your school's fee</h4>
          <label for="calc-students-en">Number of students</label>
          <input type="number" id="calc-students-en" min="0" step="1" value="500" inputmode="numeric"
            oninput="updateCalc('en')">
          <input type="range" id="calc-range-en" min="0" max="5000" step="10" value="500"
            oninput="document.getElementById('calc-students-en').value=this.value;updateCalc('en')">
          <div class="calc-result">
            <div>
              <span class="calc-k">Per term</span>
              <span class="calc-v" id="calc-term-en">UGX 750,000</span>
            </div>
            <div>
              <span class="calc-k">Per year (3 terms)</span>
              <span class="calc-v calc-v-sm" id="calc-year-en">UGX 2,250,000</span>
            </div>
          </div>
          <button class="pricing-cta pricing-cta-primary"
            onclick="document.getElementById('contact').scrollIntoView({behavior:'smooth'})">Request a Demo</button>
          <p class="pricing-note">Billed termly on your enrolled student count</p>
        </div>
      </div>
    </div>

    <!-- ARABIC -->
    <div id="pricing-arabic" class="pricing-panel fade-in" dir="rtl">
      <div class="flat-price">
        <div class="flat-price-main">
          <div class="pricing-badge-top">سعر واحد بسيط</div>
          <h3 class="arabic">لكل طالب، في كل فصل</h3>
          <div class="flat-amount arabic">١٬٥٠٠ <span class="flat-cur">شلن أوغندي</span></div>
          <p class="flat-sub arabic">لكل طالب في الفصل الدراسي</p>
          <ul class="flat-list arabic">
            <li><i class="fas fa-circle-check"></i> جميع الوحدات مشمولة دون أي رسوم إضافية</li>
            <li><i class="fas fa-circle-check"></i> يدعم المناهج الشرعية والعلمانية معاً</li>
            <li><i class="fas fa-circle-check"></i> تقارير الدرجات بالعربية والإنجليزية</li>
            <li><i class="fas fa-circle-check"></i> تدفع فقط عن عدد طلابك الفعليين</li>
          </ul>
        </div>
        <div class="flat-calc">
          <h4 class="arabic">قدّر رسوم مدرستك</h4>
          <label for="calc-students-ar" class="arabic">عدد الطلاب</label>
          <input type="number" id="calc-students-ar" min="0" step="1" value="500" inputmode="numeric"
            oninput="updateCalc('ar')">
          <input type="range" id="calc-range-ar" min="0" max="5000" step="10" value="500"
            oninput="document.getElementById('calc-students-ar').value=this.value;updateCalc('ar')">
          <div class="calc-result">
            <div>
              <span class="calc-k arabic">لكل فصل</span>
              <span class="calc-v arabic" id="calc-term-ar">٧٥٠٬٠٠٠ شلن</span>
            </div>
            <div>
              <span class="calc-k arabic">لكل سنة (٣ فصول)</span>
              <span class="calc-v calc-v-sm arabic" id="calc-year-ar">٢٬٢٥٠٬٠٠٠ شلن</span>
            </div>
          </div>
          <button class="pricing-cta pricing-cta-primary arabic"
            onclick="document.getElementById('contact').scrollIntoView({behavior:'smooth'})">اطلب عرضاً تجريبياً</button>
          <p class="pricing-note arabic">تُحتسب الرسوم كل فصل حسب عدد الطلاب المسجلين</p>
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
        <p class="section-desc">Contact TechSate Software Company LTD today. Our team will walk you through a live demo
          and
          answer every question.</p>
        <!-- Image strip above contact info -->
        <div class="contact-img-strip" style="margin-top:28px">
          <img src="/homepagepics/9.jfif" alt="School management demo" loading="lazy"
            onerror="this.style.display='none'">
          <img src="/homepagepics/8.png" alt="Students in Uganda school" loading="lazy"
            onerror="this.style.display='none'">
        </div>
        <div class="contact-info">
          <div class="contact-item">
            <div class="contact-icon"><i class="fas fa-location-dot"></i></div>
            <div>
              <div class="contact-label">Visit Us</div>
              <div class="contact-val">Sky City Building, P.O Box 960248</div>
              <div style="font-size:13px;color:var(--text-muted)">Bwaise, Kawempe Division, Kampala</div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
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
            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
            <div>
              <div class="contact-label">Email</div>
              <div class="contact-val">techsatetechnologies@gmail.com</div>
            </div>
          </div>
        </div>
      </div>
      <div class="contact-form" id="demoFormWrap">
        <h3 style="font-size:20px;font-weight:600;color:var(--gold2);margin-bottom:24px">Request a Demo</h3>

        @if(session('demo_success'))
          <div class="demo-alert demo-alert-ok">{{ session('demo_success') }}</div>
        @endif
        @if($errors->any())
          <div class="demo-alert demo-alert-err">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
          </div>
        @endif

        <form id="demoForm" method="POST" action="{{ route('demo.request') }}" novalidate>
          @csrf
          {{-- honeypot: hidden from people, bots fill it --}}
          <div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden" aria-hidden="true">
            <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="demo-alert demo-alert-err" id="demoErrors" style="display:none" role="alert"></div>

          <div class="form-grid-2">
            <div class="form-group"><label for="df-school">School Name *</label><input id="df-school" type="text"
                name="school_name" value="{{ old('school_name') }}" maxlength="191" required
                placeholder="e.g. Noor Islamic Institute"></div>
            <div class="form-group"><label for="df-person">Contact Person *</label><input id="df-person" type="text"
                name="contact_person" value="{{ old('contact_person') }}" maxlength="191" required
                placeholder="Your full name"></div>
          </div>
          <div class="form-grid-2">
            <div class="form-group"><label for="df-phone">Phone Number *</label><input id="df-phone" type="tel"
                name="phone" value="{{ old('phone') }}" maxlength="40" required placeholder="+256 7XX XXX XXX"></div>
            <div class="form-group"><label for="df-email">Email Address *</label><input id="df-email" type="email"
                name="email" value="{{ old('email') }}" maxlength="191" required placeholder="school@example.com">
            </div>
          </div>
          <div class="form-group">
            <label for="df-type">School Type *</label>
            <select id="df-type" name="school_type" required>
              <option value="">— Select —</option>
              @foreach(config('demo_requests.school_types') as $key => $label)
                <option value="{{ $key }}" {{ old('school_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="df-range">Student Population (approx.) *</label>
            <select id="df-range" name="student_range" required>
              <option value="">— Select range —</option>
              @foreach(config('demo_requests.student_ranges') as $key => $label)
                <option value="{{ $key }}" {{ old('student_range') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group"><label for="df-msg">Message (optional)</label><textarea id="df-msg" name="message"
              rows="3" maxlength="2000"
              placeholder="Any specific features or questions?">{{ old('message') }}</textarea></div>
          <button type="submit" id="demoSubmit" class="btn-primary"
            style="width:100%;text-align:center;border:none;font-family:'DM Sans',sans-serif">Send Request →</button>
        </form>

        <div class="demo-success" id="demoSuccess" style="display:none" role="status">
          <div class="demo-success-icon"><i class="fas fa-circle-check"></i></div>
          <h4>Request received!</h4>
          <p id="demoSuccessMsg">Thank you! TechSate will contact you within 24 hours.</p>
          <p class="demo-ref">Your reference: <strong id="demoRef"></strong></p>
          <button type="button" class="pricing-cta pricing-cta-outline" onclick="resetDemoForm()">Send another request</button>
        </div>
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
        <p>A comprehensive academic management platform by TechSate Software Company LTD — purpose-built for Uganda's
          Islamic
          schools, fully bilingual and locally supported.</p>
        <p class="arabic" style="font-size:15px;color:var(--gold3);">TechSate Software Company Limited</p>
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
          <li><a href="#">Secondary</a></li>
          <li><a href="#">Both Secular and Theology</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <ul>
          <li><a href="#contact">Contact Us</a></li>
          <li><a href="#pricing">Pricing</a></li>
          <li><a href="#attendance">Attendance</a></li>
          <li><a href="#contact">Request Demo</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 TechSate Software Company LTD · Sky City Building, Kampala</p>
      <p style="color:var(--gold)">Built with <i class="fas fa-heart" style="color:var(--gold2);"></i> for Uganda's
        Islamic and Non Islamic Schools</p>
    </div>
  </footer>

  <script>
    /* ── HERO CAROUSEL ── */
    (function () {
      const track = document.getElementById('heroTrack');
      const dotsWrap = document.getElementById('heroDots');
      if (!track) return;
      const slides = track.children;
      const total = slides.length;
      let cur = 0, timer;

      // build dots
      for (let i = 0; i < total; i++) {
        const d = document.createElement('button');
        d.className = 'cdot' + (i === 0 ? ' active' : '');
        d.onclick = () => { goTo(i); resetTimer(); };
        dotsWrap.appendChild(d);
      }

      function goTo(n) {
        cur = (n + total) % total;
        track.style.transform = 'translateX(-' + cur + '00%)';
        document.querySelectorAll('.cdot').forEach((d, i) => d.classList.toggle('active', i === cur));
      }
      function resetTimer() { clearInterval(timer); timer = setInterval(() => goTo(cur + 1), 4500); }

      document.getElementById('heroPrev').onclick = () => { goTo(cur - 1); resetTimer(); };
      document.getElementById('heroNext').onclick = () => { goTo(cur + 1); resetTimer(); };
      resetTimer();
    })();

    /* ── TESTIMONIAL CAROUSEL ── */
    (function () {
      const track = document.getElementById('tTrack');
      if (!track) return;
      let cur = 0;
      const cards = track.children;
      const total = cards.length;
      const visible = window.innerWidth < 700 ? 1 : 2;

      function goTo(n) {
        const max = total - visible;
        cur = Math.max(0, Math.min(n, max));
        const w = track.parentElement.offsetWidth;
        const cardW = (w - 32) / visible;
        track.style.transform = 'translateX(-' + (cur * (cardW + 32)) + 'px)';
      }
      // Set card widths
      function setWidths() {
        const w = track.parentElement.offsetWidth;
        const vis = window.innerWidth < 700 ? 1 : 2;
        const cardW = (w - (vis - 1) * 32) / vis;
        Array.from(cards).forEach(c => { c.style.minWidth = cardW + 'px'; c.style.marginRight = '32px'; });
      }
      setWidths();
      document.getElementById('tPrev').onclick = () => goTo(cur - 1);
      document.getElementById('tNext').onclick = () => goTo(cur + 1);
      window.addEventListener('resize', () => { setWidths(); goTo(0); });
    })();

    /* ── PRICING TABS ── */
    function switchPricing(tab, e) {
      document.querySelectorAll('.ptab').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.pricing-panel').forEach(p => p.classList.remove('active'));
      if (e && e.target) e.target.classList.add('active');
      const panel = document.getElementById('pricing-' + tab);
      if (panel) panel.classList.add('active');
    }

    /* ── PRICING CALCULATOR: UGX 1,500 per student per term ── */
    const RATE_PER_STUDENT = 1500;
    function updateCalc(lang) {
      const input = document.getElementById('calc-students-' + lang);
      const range = document.getElementById('calc-range-' + lang);
      const students = Math.max(0, Math.floor(Number(input.value) || 0));
      if (Number(range.max) >= students) range.value = students;
      const term = students * RATE_PER_STUDENT;
      const year = term * 3;
      if (lang === 'ar') {
        const f = n => n.toLocaleString('ar-EG') + ' شلن';
        document.getElementById('calc-term-ar').textContent = f(term);
        document.getElementById('calc-year-ar').textContent = f(year);
      } else {
        const f = n => 'UGX ' + n.toLocaleString('en-US');
        document.getElementById('calc-term-en').textContent = f(term);
        document.getElementById('calc-year-en').textContent = f(year);
      }
    }

    /* ── REQUEST A DEMO ── */
    (function () {
      const form = document.getElementById('demoForm');
      if (!form) return;
      const btn = document.getElementById('demoSubmit');
      const errBox = document.getElementById('demoErrors');
      const okBox = document.getElementById('demoSuccess');

      function showErrors(list) {
        errBox.innerHTML = '';
        list.forEach(m => { const d = document.createElement('div'); d.textContent = m; errBox.appendChild(d); });
        errBox.style.display = 'block';
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errBox.style.display = 'none';
        form.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));

        // quick client-side check (the server validates again)
        const problems = [];
        [['school_name', 'Please enter your school name.'], ['contact_person', 'Please tell us who we should speak to.'],
         ['phone', 'Please enter a phone number we can reach you on.'], ['email', 'Please enter your email address.'],
         ['school_type', 'Please select your school type.'], ['student_range', 'Please select your approximate student population.']
        ].forEach(([name, msg]) => {
          const el = form.elements[name];
          if (!el.value.trim()) { problems.push(msg); el.classList.add('invalid'); }
        });
        if (form.elements.email.value && !/^\S+@\S+\.\S+$/.test(form.elements.email.value)) {
          problems.push('That email address does not look right.'); form.elements.email.classList.add('invalid');
        }
        if (problems.length) { showErrors(problems); return; }

        const original = btn.textContent;
        btn.disabled = true; btn.textContent = 'Sending…';
        try {
          const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
            credentials: 'same-origin'
          });
          const data = await res.json().catch(() => ({}));
          if (res.ok && data.ok) {
            document.getElementById('demoSuccessMsg').textContent = data.message;
            document.getElementById('demoRef').textContent = data.reference;
            form.style.display = 'none';
            okBox.style.display = 'block';
            form.reset();
          } else if (res.status === 422 && data.errors) {
            showErrors(Object.values(data.errors).flat());
            Object.keys(data.errors).forEach(k => form.elements[k] && form.elements[k].classList.add('invalid'));
          } else if (res.status === 419) {
            showErrors(['This page has been open for a while. Please refresh it and send the request again.']);
          } else if (res.status === 429) {
            showErrors(['Too many requests from this device. Please wait a few minutes or call us on +256 702 082 209.']);
          } else {
            showErrors(['Sorry, something went wrong. Please try again or call us on +256 702 082 209.']);
          }
        } catch (err) {
          showErrors(['We could not reach the server. Please check your connection and try again.']);
        } finally {
          btn.disabled = false; btn.textContent = original;
        }
      });
    })();
    function resetDemoForm() {
      document.getElementById('demoSuccess').style.display = 'none';
      document.getElementById('demoForm').style.display = 'block';
    }

    /* ── SCROLL FADE-IN ── */
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

    /* ── COUNT-UP ── */
    function countUp(el) {
      const target = parseInt(el.dataset.target);
      const step = target / 60;
      let cur = 0;
      const t = setInterval(() => {
        cur = Math.min(cur + step, target);
        el.textContent = Math.floor(cur).toLocaleString();
        if (cur >= target) clearInterval(t);
      }, 2000 / 60);
    }
    const cObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.querySelectorAll('.count-up').forEach(countUp);
          cObs.unobserve(e.target);
        }
      });
    }, { threshold: 0.4 });
    document.querySelectorAll('.hero-stats,.impact-grid').forEach(el => cObs.observe(el));
  </script>

  @auth
<script src="{{ asset('js/push-init.js') }}"></script>
@endauth

</body>

</html>