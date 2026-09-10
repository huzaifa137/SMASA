<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kindergarten Learning Journey — Design 2 Preview</title>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Poppins:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  :root{
    --navy:#173a63;
    --gold:#c9973f;
    --cream:#fdfaf0;
    --line:#d8cfb8;
  }
  *{box-sizing:border-box;}
  body{
    font-family:'Poppins',sans-serif;
    background:#dde1e7;
    margin:0; padding:0;
    color:#2b2b2b;
  }
  .toolbar{
    background:linear-gradient(135deg,#173a63,#2d5a94);
    color:#fff; padding:.75rem 2rem; display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; z-index:100;
  }
  .toolbar strong{font-size:.95rem;}
  .toolbar small{display:block; opacity:.75; font-size:.7rem;}
  .tbtn{background:var(--gold); color:#fff; border:none; padding:.5rem 1.1rem; border-radius:7px; font-weight:600; cursor:pointer;}

.page-wrap{max-width:850px; margin:1.5rem auto; padding:0 12px;}
.sheet{
  /* Fluid on screen — shrinks to fit narrow containers like the
     "Customize this design" live-preview iframe — but never grows
     past true A4 width. The @media print block further down pins
     this back to an exact 210mm regardless of viewport, so printed
     output is unaffected. */
  width:100%; max-width:210mm; min-height:297mm;
  margin:0 auto;
  background:var(--cream);
    position:relative;
    padding:7mm 9mm 4mm;
    box-shadow:0 4px 30px rgba(0,0,0,.25);
    border:2px solid #b9c9da;
    overflow:hidden;
  }

  /* ===== HERO HEADER OVERLAY (dynamic: logo, school name, motto, journey caption) ===== */
  .hero-logo{
    position:absolute; left:0; top:0; width:30.4mm; height:33.28mm; z-index:6;
    background:linear-gradient(160deg,#1d4278 0%,#0f2947 75%);
    clip-path: polygon(0 0, 100% 0, 100% 80%, 50% 100%, 0 80%);
    border:1px dashed rgba(255,255,255,.4);
    box-sizing:border-box;
    padding:3.52mm 2.24mm 5.12mm;
    display:flex; flex-direction:column; align-items:center; justify-content:flex-start;
    text-align:center; color:#fff; overflow:hidden;
  }
  .hero-logo-icon{ position:relative; width:14.72mm; height:14.72mm; margin-bottom:0.96mm; flex:none;}
  .hero-logo-icon .fa-star{ position:absolute; top:-0.64mm; left:50%; transform:translateX(-50%); font-size:3.84mm; color:#e9c477;}
  .hero-logo-icon .fa-shield-halved{ position:absolute; top:2.24mm; left:50%; transform:translateX(-50%); font-size:11.52mm; background:linear-gradient(160deg,#f0d49a,#a9762e); -webkit-background-clip:text; background-clip:text; color:transparent;}
  .hero-logo-icon .fa-book-open{ position:absolute; top:5.44mm; left:50%; transform:translateX(-50%); font-size:4.48mm; color:#fff;}
  .hero-logo-icon .fa-wheat-awn{ position:absolute; bottom:0; font-size:4.16mm; color:#d3ac67;}
  .hero-logo-icon .fa-wheat-awn.left{ left:-0.64mm; transform:scaleX(-1) rotate(8deg);}
  .hero-logo-icon .fa-wheat-awn.right{ right:-0.64mm; transform:rotate(8deg);}
  .hero-logo-title{ font-family:'Fredoka',sans-serif; font-weight:600; font-size:2.3mm; letter-spacing:.25px; line-height:1.2;}
  .hero-logo-sub{ font-family:'Poppins',sans-serif; font-weight:400; font-size:1.6mm; letter-spacing:.4px; opacity:.85; margin-top:0.32mm;}

  .hero-logo--custom .hero-logo-badge{
    width:16.64mm; height:16.64mm; border-radius:50%; background:#fff; margin-bottom:1.28mm;
    display:flex; align-items:center; justify-content:center; overflow:hidden;
    box-shadow:0 1px 4px rgba(0,0,0,.25); flex:none;
  }
  .hero-logo--custom .hero-logo-badge img{ width:100%; height:100%; object-fit:contain; padding:0.96mm; box-sizing:border-box;}

  .hero-header-text{ position:absolute; left:33.86mm; top:0.64mm; width:67.2mm; text-align:center; z-index:6;}
  .school-name-dynamic{
    font-family:'Fredoka',sans-serif; font-weight:700; font-size:4.74mm; line-height:1.18;
    color:var(--navy); text-transform:uppercase; letter-spacing:.2px;
    overflow-wrap:break-word;
  }
  .school-motto-dynamic{
    font-family:'Playfair Display', 'Poppins', serif; font-style:italic; font-weight:500;
    font-size:2.82mm; color:#4a4a4a; margin-top:1.41mm;
    display:flex; align-items:center; justify-content:center; gap:1.41mm;
  }
  .school-motto-dynamic .ln{ width:5.76mm; height:1px; background:#b7a262; flex:none;}
  .school-motto-dynamic .dot{ width:1.02mm; height:1.02mm; border-radius:50%; background:var(--gold); flex:none;}

  .hero-ribbon-caption{
    position:absolute; left:32.64mm; top:38.72mm; width:67.84mm; height:8mm; z-index:6;
    display:flex; align-items:center; justify-content:center; padding:0 3.84mm; box-sizing:border-box;
  }
  .hero-ribbon-caption span{
    font-family:'Playfair Display', 'Poppins', serif; font-style:italic; font-weight:500;
    font-size:2.5mm; line-height:1.15; color:#fff; white-space:nowrap;
    text-shadow:0 1px 2px rgba(40,20,70,.25);
  }

  .plane-deco{ position:absolute; right:33.28mm; top:5.12mm; width:10.24mm; z-index:5;}
  .plane-deco img{width:100%;}

  .hero-wrap{ margin-bottom:2mm; position:relative;}
  .hero-wrap img{width:100%; display:block;}

  /* ===== MIDDLE: profile + who-i-am + areas (left) | timeline (right) ===== */
  .mid-grid{ display:grid; grid-template-columns: 64% 33%; gap:3%; margin-top:1mm; align-items:start;}
  .left-col{ min-width:0; }
  .right-col{ min-width:0; }

  .profile-row{ display:flex; gap:4mm; margin-bottom:1mm;}
  .profile-card{ flex:0 0 30mm; }
  .profile-card img{width:100%; display:block;}

  .pc{
    position:relative;
    background:var(--cream);
    border:1.5px dashed #c7b98f;
    border-radius:10px;
    padding:4mm 2.8mm 2mm;
    text-align:center;
  }
  .pc-ribbon{
    position:absolute; top:-2.6mm; left:50%; transform:translateX(-50%);
    width:88%;
    background:var(--navy); color:#fff;
    font-family:'Fredoka',sans-serif; font-weight:600; font-size:8.5px; letter-spacing:.6px;
    padding:2.6px 2px 3.2px; text-align:center;
    clip-path: polygon(6% 0, 94% 0, 100% 55%, 94% 100%, 6% 100%, 0 55%);
  }
  .pc-avatar-wrap{ position:relative; margin:2.2mm auto 1.3mm; width:19mm; height:19mm; }
  .pc-avatar{
    width:100%; height:100%; border-radius:50%;
    background:#e3e3e3; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.15);
    display:flex; align-items:center; justify-content:center; overflow:hidden;
  }
  .pc-avatar i{ font-size:11mm; color:#a9a9a9; }
  .pc-deco{ position:absolute; font-size:9px; }
  .pc-deco-left{ left:-2.5mm; bottom:0; color:#8fae5c; transform:rotate(-15deg); }
  .pc-deco-right{ right:-3mm; bottom:1mm; color:#e08a4c; transform:rotate(10deg); }
  .pc-fields{ list-style:none; margin:0; padding:0; text-align:left; }
  .pc-fields li{
    display:flex; align-items:baseline; gap:1.3mm;
    padding:0.7mm 0; border-bottom:1px dotted #cfc4a6;
    font-size:7.6px; color:#2b2b2b;
  }
  .pc-fields li:last-child{ border-bottom:none; }
  .pc-fields i{ color:var(--navy); font-size:7.5px; width:3mm; text-align:center; flex:none; }
  .pc-label{ font-weight:500; white-space:nowrap; }
  .pc-label:after{ content:":"; margin-right:1mm; }
  .pc-value{ flex:1; min-width:0; }
  .pc-heart{ margin-top:1.2mm; }
  .pc-heart i{ color:#c9a8d4; font-size:9px; }
  .whoiam{ flex:1; padding-top:2mm;}
  .whoiam-title{ text-align:center; font-family:'Fredoka',sans-serif; font-weight:600; color:var(--navy); font-size:13px; letter-spacing:1px; margin-bottom:1mm;}
  .whoiam img{width:100%; display:block;}
  .whoiam-caption{ text-align:center; font-size:9.5px; font-style:italic; color:#555; margin-top:1.5mm;}

  .whoiam-badges{ display:flex; gap:1.6mm; }
  .wb{
    flex:1; min-width:0;
    border-radius:50% 50% 5px 5px;
    border:1.3px solid var(--wb-color);
    background:linear-gradient(to bottom, var(--wb-bg) 0%, var(--wb-bg) 68%, #fffdf7 68%, #fffdf7 100%);
    text-align:center;
    padding:10mm 1mm 7mm;
    display:flex; flex-direction:column; align-items:center; justify-content:flex-start;
  }
  .wb i{ font-size:28px; color:var(--wb-color); margin-bottom:5mm; }
  .wb span{ font-family:'Fredoka',sans-serif; font-weight:600; font-size:7.6px; letter-spacing:.3px; color:var(--wb-text); }
  .wb-green{ --wb-bg:#dce8cf; --wb-color:#7fa65c; --wb-text:#4f7a3d; }
  .wb-red{ --wb-bg:#f7d9d9; --wb-color:#d65a5a; --wb-text:#c73e3e; }
  .wb-yellow{ --wb-bg:#faf0cf; --wb-color:#e0a83c; --wb-text:#c98a1d; }
  .wb-purple{ --wb-bg:#e6def2; --wb-color:#8f6bc7; --wb-text:#6b4fa0; }
  .wb-blue{ --wb-bg:#d9ecf2; --wb-color:#4fa0c7; --wb-text:#3d7ab5; }
  .wb-orange{ --wb-bg:#fbe4cf; --wb-color:#e08a3c; --wb-text:#d97b2b; }

  .dev-title{ text-align:center; font-family:'Fredoka',sans-serif; font-weight:600; color:var(--navy); font-size:13px; letter-spacing:1px; margin:2.5mm 0 1mm;}

  /* ===== AREAS OF DEVELOPMENT — card grid (icon image + dynamic HTML text) ===== */
  .dev-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:2.2mm;}
  .dev-card{
    position:relative;
    border-radius:9px;
    border:1.4px solid var(--dc-color, #c7c2b3);
    padding:2mm 1.6mm 2mm;
    text-align:center;
    display:flex; flex-direction:column; align-items:center;
  }
  .dev-card--dashed{ border-style:dashed; }
  .dev-card img{ width:82%; display:block; margin:0 auto 1mm; }
  .dev-card-title{
    margin:0; font-family:'Fredoka',sans-serif; font-weight:600; color:var(--navy);
    font-size:6.6px; letter-spacing:.2px; line-height:1.25; text-transform:uppercase;
  }
  .dev-card-sub{
    margin:0.8mm 0 0; font-family:'Poppins',sans-serif; font-weight:400; color:#333;
    font-size:5.7px; line-height:1.35;
  }
  .dev-card-divider{ margin-top:1.2mm; display:flex; align-items:center; justify-content:center; gap:1mm; width:100%; }
  .dev-card-divider .dcd-line{ height:1px; flex:1; background:var(--dc-color, #c7c2b3); max-width:7mm; }
  .dev-card-divider i{ font-size:6px; color:var(--dc-color, #c7c2b3); flex:none; }

  .dev-card--lang{ --dc-color:#8c8c86; }
  .dev-card--social{ --dc-color:#f4a19c; }
  .dev-card--cognitive{ --dc-color:#e9b95c; }
  .dev-card--creative{ --dc-color:#dcc27f; }
  .dev-card--physical{ --dc-color:#b9b9ae; }
  .dev-card--approach{ --dc-color:#a9c78a; }
  .dev-card--music{ --dc-color:#dcc27f; }
  .dev-card--world{ --dc-color:#a9c78a; }

  /* ===== TERM & FEES INFORMATION =====
     Same visual language as the rest of the page (Fredoka headings,
     navy/cream palette, the same rounded colour-badge pattern used by
     the "Who I Am" badges) — values are ready to be swapped for real
     $termEndsOn / $nextTermStartsOn / $feesBalance / $nextTermFees
     variables instead of the placeholders shown here. */
  .term-fees-wrap{ margin-top:1.5mm;}
  .term-fees-title{ text-align:center; font-family:'Fredoka',sans-serif; font-weight:600; color:var(--navy); font-size:13px; letter-spacing:1px; margin:1.5mm 0 1.6mm;}
  .term-fees-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:2.2mm;}
  .term-fee-item{
    display:flex; align-items:center; gap:2mm;
    background:#fffdf7; border:1px solid var(--line); border-radius:8px;
    padding:2mm 2.4mm;
  }
  .term-fee-icon{
    width:8mm; height:8mm; border-radius:50%; flex:none;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:8.5px; background:var(--tf-color, var(--navy));
  }
  .term-fee-content{ display:flex; flex-direction:column; min-width:0; }
  .term-fee-label{ font-family:'Fredoka',sans-serif; font-size:6px; font-weight:600; color:#948f7e; text-transform:uppercase; letter-spacing:.2px; line-height:1.2;}
  .term-fee-value{ font-family:'Fredoka',sans-serif; font-size:8.4px; font-weight:600; color:var(--navy); margin-top:0.6mm; line-height:1.2;}
  .tf-blue{ --tf-color:#4fa0c7; }
  .tf-green{ --tf-color:#7fa65c; }
  .tf-gold{ --tf-color:#e0a83c; }
  .tf-purple{ --tf-color:#8f6bc7; }

  /* ===== MY LEARNING JOURNEY (timeline) =====
     Heading is now a proper pointed ribbon (matches the "MY PROFILE"
     ribbon language) with real breathing room before the first item —
     it no longer sits jammed against the first icon. Each entry's
     heading + description used to be baked into the PNG together with
     three blank dotted lines; the lines are gone and the text is now
     real HTML, ready to be looped/bound to dynamic data later. Only
     the circular icon + connecting vine stay as artwork. */
  .timeline{ display:flex; flex-direction:column; margin-bottom:3mm;}
  .tl-title-wrap{ text-align:center; margin-bottom:5mm;}
  .tl-title{
    display:inline-block; background:var(--navy); color:#fff; font-family:'Fredoka',sans-serif; font-size:9.5px; font-weight:600;
    padding:5px 15px 5px 11px; letter-spacing:.5px; text-transform:uppercase;
    clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%);
  }
  .tl-item{ display:flex; align-items:flex-start; gap:2mm;}
  .tl-item + .tl-item{ margin-top:-7mm; }
  .tl-item img{ width:22mm; flex:none; display:block;}
  .tl-item-text{ padding-top:4mm; min-width:0;}
  .tl-item-title{ margin:0 0 0.7mm; font-family:'Fredoka',sans-serif; font-weight:600; font-size:9px; letter-spacing:.3px; text-transform:uppercase;}
  .tl-item-desc{ margin:0; font-family:'Poppins',sans-serif; font-size:7.2px; color:#444; line-height:1.35;}

  /* ===== FOOTER =====
     Signature row now sits inside its own card (matches the dev-card /
     term-fee-item visual language) so it reads as a clean, intentional
     block rather than dotted lines floating on the page. Each slot has
     a real blank sig-space above the dotted rule for a physical
     signature, or to later bind a captured signature image / the real
     $issueDate value into that space dynamically. */
  .footer{ position:relative; margin-top:3mm;}
  .sig-box{
    background:#fffdf7; border:1px solid var(--line); border-radius:10px;
    padding:3.5mm 5mm 3mm; box-shadow:0 2px 8px rgba(0,0,0,.05);
  }
  .sig-row{ display:grid; grid-template-columns:repeat(4,1fr); gap:4mm; text-align:center;}
  .sig-slot{ display:flex; flex-direction:column; align-items:center;}
  .sig-slot i{ color:var(--navy); font-size:12px; margin-bottom:2.5px; opacity:.8;}
  .sig-slot .sig-space{ width:100%; min-height:7mm;}
  .sig-slot .sig-rule{ width:88%; border-top:1px dotted #999;}
  .sig-slot .sig-label{ margin-top:1.5px; padding-top:2px; font-size:9px; color:#333; line-height:1.3;}
  .sig-slot .sig-label b{ font-size:9.5px; display:block; color:var(--navy);}
  .footer-tagline{ text-align:center; font-style:italic; font-size:9.5px; color:#666; margin-top:3.5mm;}

  @media print{
    @page{ size:A4; margin:0; }
    body{ background:#fff; }
    .toolbar{ display:none !important; }
    .page-wrap{ margin:0; max-width:100%; }
    .sheet{ box-shadow:none; border:none; width:210mm; min-height:297mm; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <div>
      <strong><i class="fas fa-seedling"></i> Kindergarten Learning Journey — Design 2 Preview</strong>
      <small>Standalone HTML preview (not wired to live data)</small>
    </div>
    <button class="tbtn" onclick="window.print()"><i class="fas fa-print"></i> Print / Save PDF</button>
  </div>

  <div class="page-wrap">
    <div class="sheet">

      <div class="mid-grid">
        <div class="left-col">
          <div class="hero-wrap">
            <img src="{{ asset('images/passslip/kindergarten2/') }}/hero_learning_children.png" alt="">

            @if(!empty($schoolLogoUrl))
              <div class="hero-logo hero-logo--custom">
                <div class="hero-logo-badge">
                  <img src="{{ $schoolLogoUrl }}" alt="{{ $schoolName ?? 'School' }} logo">
                </div>
                @if(!empty($schoolFoundedYear))
                  <div class="hero-logo-sub">SINCE {{ $schoolFoundedYear }}</div>
                @endif
              </div>
            @else
              <div class="hero-logo">
                <div class="hero-logo-icon">
                  <i class="fas fa-wheat-awn left"></i>
                  <i class="fas fa-shield-halved"></i>
                  <i class="fas fa-book-open"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-wheat-awn right"></i>
                </div>
                <div class="hero-logo-title">{{ $schoolLogoText ?? 'YOUR LOGO' }}</div>
                <div class="hero-logo-sub">SINCE {{ $schoolFoundedYear ?? date('Y') }}</div>
              </div>
            @endif

            <div class="hero-header-text">
              <div class="school-name-dynamic">{{ $schoolName ?? 'YOUR SCHOOL NAME' }}</div>
              <div class="school-motto-dynamic">
                <span class="dot"></span><span class="ln"></span>
                <span>{{ $schoolMotto ?? 'In God We Trust' }}</span>
                <span class="ln"></span><span class="dot"></span>
              </div>
            </div>

            <!-- <div class="hero-ribbon-caption">
              <span>{{ $journeyCaption ?? 'Celebrating growth, discovery & little achievements' }}</span>
            </div> -->
          </div>
          <div class="profile-row">
            <div class="profile-card">
              <div class="pc">
                <div class="pc-ribbon">MY PROFILE</div>
                <div class="pc-avatar-wrap">
                  <div class="pc-avatar"><i class="fas fa-child"></i></div>
                  <span class="pc-deco pc-deco-left"><i class="fas fa-leaf"></i></span>
                  <span class="pc-deco pc-deco-right"><i class="fas fa-spa"></i></span>
                </div>
                <ul class="pc-fields">
                  <li><i class="fas fa-user"></i><span class="pc-label">Name</span><span class="pc-value">{{ $student->name ?? '' }}</span></li>
                  <li><i class="fas fa-graduation-cap"></i><span class="pc-label">Class</span><span class="pc-value">{{ $student->class ?? '' }}</span></li>
                  <li><i class="fas fa-book-open"></i><span class="pc-label">Stream</span><span class="pc-value">{{ $student->stream ?? '' }}</span></li>
                </ul>
                <div class="pc-heart"><i class="fas fa-heart"></i></div>
              </div>
            </div>
            <div class="whoiam">
              <div class="whoiam-title">WHO I AM</div>
              <div class="whoiam-badges">
                <div class="wb wb-green"><i class="fas fa-magnifying-glass"></i><span>CURIOUS</span></div>
                <div class="wb wb-red"><i class="fas fa-heart"></i><span>KIND</span></div>
                <div class="wb wb-yellow"><i class="fas fa-star"></i><span>CONFIDENT</span></div>
                <div class="wb wb-purple"><i class="fas fa-paintbrush"></i><span>CREATIVE</span></div>
                <div class="wb wb-blue"><i class="fas fa-hands"></i><span>HELPFUL</span></div>
                <div class="wb wb-orange"><i class="fas fa-sun"></i><span>JOYFUL</span></div>
              </div>
              <div class="whoiam-caption">Characteristics I show every day in my own special way. ♡</div>
            </div>
          </div>
        </div>

        <div class="right-col">
          {{--
            MY LEARNING JOURNEY: only the circular icon + connecting vine
            stay as artwork now. The heading, per-item title and
            description are real HTML (previously baked into each PNG
            together with three blank dotted lines, which have been
            removed entirely). Ready to loop over a $learningJourney
            collection later instead of the six hardcoded rows below.
          --}}
          <div class="timeline">
            <div class="tl-title-wrap"><span class="tl-title">My Learning Journey</span></div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/discovery_magnifying_glass.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#4f8a3d">Discovering</h5>
                <p class="tl-item-desc">I explore the world around me with wonder and excitement.</p>
              </div>
            </div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/exploring_boat.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#2f6fa8">Exploring</h5>
                <p class="tl-item-desc">I investigate, ask questions and enjoy new experiences.</p>
              </div>
            </div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/creating_palette.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#c1701f">Creating</h5>
                <p class="tl-item-desc">I express my ideas through art, imagination and play.</p>
              </div>
            </div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/communicating_speech_bubbles.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#6b4fa0">Communicating</h5>
                <p class="tl-item-desc">I share my thoughts, listen to others and enjoy stories.</p>
              </div>
            </div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/connecting_friends.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#c1443c">Connecting</h5>
                <p class="tl-item-desc">I build friendships, show kindness and work together.</p>
              </div>
            </div>

            <div class="tl-item">
              <img src="{{ asset('images/passslip/kindergarten2/') }}/growing_plant.png" alt="">
              <div class="tl-item-text">
                <h5 class="tl-item-title" style="color:#3d7a3d">Growing</h5>
                <p class="tl-item-desc">I develop my skills and become more confident each day.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{--
        AREAS OF DEVELOPMENT: full sheet width now (previously confined to
        the 64% left column, which left a large empty gap on the right once
        the shorter timeline column ran out of items). Each card uses an
        icon-only image with the heading + description rebuilt as real HTML
        below it, ready to loop over a $developmentAreas collection later
        instead of the hardcoded text shown here.
      --}}
      <div class="dev-title">AREAS OF DEVELOPMENT</div>
      <div class="dev-grid">
        <div class="dev-card dev-card--lang">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/language_communication.png" alt="">
          <h4 class="dev-card-title">Language &amp;<br>Communication</h4>
          <p class="dev-card-sub">expressing ideas and<br>building vocabulary.</p>
          <div class="dev-card-divider"><i class="fas fa-leaf"></i></div>
        </div>

        <div class="dev-card dev-card--social">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/social_emotional.png" alt="">
          <h4 class="dev-card-title">Social &amp; Emotional<br>Development</h4>
          <p class="dev-card-sub">Shows empathy, builds positive<br>relationships and understands feelings.</p>
          <div class="dev-card-divider"><span class="dcd-line"></span><i class="fas fa-heart"></i><span class="dcd-line"></span></div>
        </div>

        <div class="dev-card dev-card--cognitive dev-card--dashed">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/cognitive_development.png" alt="">
          <h4 class="dev-card-title">Cognitive<br>Development</h4>
          <p class="dev-card-sub">Shows curiosity, problem-solving<br>skills and enjoys learning new concepts.</p>
          <div class="dev-card-divider"><span class="dcd-line"></span><i class="fas fa-star"></i><span class="dcd-line"></span></div>
        </div>

        <div class="dev-card dev-card--creative">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/creative_development.png" alt="">
          <h4 class="dev-card-title">Creative<br>Development</h4>
          <p class="dev-card-sub">Enjoys art, imagination, drama,<br>music and creative self-expression.</p>
          <div class="dev-card-divider"><i class="fas fa-seedling"></i></div>
        </div>

        <div class="dev-card dev-card--physical">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/physical_development.png" alt="">
          <h4 class="dev-card-title">Physical<br>Development</h4>
          <p class="dev-card-sub">Develops gross and fine motor<br>skills through active play and activities.</p>
          <div class="dev-card-divider"><i class="fas fa-seedling"></i></div>
        </div>

        <div class="dev-card dev-card--approach">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/approach_to_learning.png" alt="">
          <h4 class="dev-card-title">Approach to<br>Learning</h4>
          <p class="dev-card-sub">Shows independence, focus,<br>perseverance and positive learning habits.</p>
          <div class="dev-card-divider"><span class="dcd-line"></span><i class="fas fa-heart"></i><span class="dcd-line"></span></div>
        </div>

        <div class="dev-card dev-card--music">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/music_movement.png" alt="">
          <h4 class="dev-card-title">Music &amp;<br>Movement</h4>
          <p class="dev-card-sub">Enjoys singing, rhythm, dancing<br>and moving to express feelings.</p>
          <div class="dev-card-divider"><i class="fas fa-music"></i></div>
        </div>

        <div class="dev-card dev-card--world">
          <img src="{{ asset('images/passslip/kindergarten2/') }}/understanding_world.png" alt="">
          <h4 class="dev-card-title">Understanding<br>the World</h4>
          <p class="dev-card-sub">Explores nature, people, culture<br>and the world with interest.</p>
          <div class="dev-card-divider"><i class="fas fa-earth-americas"></i></div>
        </div>
      </div>

      {{--
        TERM & FEES INFORMATION: also full sheet width now, laid out as a
        single row of 4 (rather than 2x2) since there's room — styled with
        the same navy/cream/Fredoka language as the rest of the page. Swap
        the four placeholder values below for real $termEndsOn /
        $nextTermStartsOn / $feesBalance / $nextTermFees variables.
      --}}
      <div class="term-fees-wrap">
        <div class="term-fees-title">TERM &amp; FEES INFORMATION</div>
        <div class="term-fees-grid">
          <div class="term-fee-item">
            <span class="term-fee-icon tf-blue"><i class="fas fa-calendar-check"></i></span>
            <span class="term-fee-content">
              <span class="term-fee-label">This Term Ends On</span>
              <span class="term-fee-value">{{ $termEndsOn ?? '20 December 2026' }}</span>
            </span>
          </div>
          <div class="term-fee-item">
            <span class="term-fee-icon tf-green"><i class="fas fa-calendar-plus"></i></span>
            <span class="term-fee-content">
              <span class="term-fee-label">Next Term Starts On</span>
              <span class="term-fee-value">{{ $nextTermStartsOn ?? '05 January 2027' }}</span>
            </span>
          </div>
          <div class="term-fee-item">
            <span class="term-fee-icon tf-gold"><i class="fas fa-coins"></i></span>
            <span class="term-fee-content">
              <span class="term-fee-label">Fees Balance</span>
              <span class="term-fee-value">{{ $feesBalance ?? 'UGX 150,000' }}</span>
            </span>
          </div>
          <div class="term-fee-item">
            <span class="term-fee-icon tf-purple"><i class="fas fa-wallet"></i></span>
            <span class="term-fee-content">
              <span class="term-fee-label">Next Term Fees</span>
              <span class="term-fee-value">{{ $nextTermFees ?? 'UGX 500,000' }}</span>
            </span>
          </div>
        </div>
      </div>

      {{--
        SIGNATURE ROW: sits inside its own card now (instead of floating
        dotted lines) and each signatory gets a real blank sig-space
        above the dotted rule — room to physically sign when printed, or
        to later bind a captured signature image / the real $issueDate
        value into that space dynamically.
      --}}
      <div class="footer">
        <div class="sig-box">
          <div class="sig-row">
            <div class="sig-slot">
              <i class="fas fa-pen-nib"></i>
              <div class="sig-space"></div>
              <div class="sig-rule"></div>
              <div class="sig-label"><b>Class Teacher</b>Signature</div>
            </div>
            <div class="sig-slot">
              <i class="fas fa-award"></i>
              <div class="sig-space"></div>
              <div class="sig-rule"></div>
              <div class="sig-label"><b>Head Teacher</b>Signature</div>
            </div>
            <div class="sig-slot">
              <i class="fas fa-people-roof"></i>
              <div class="sig-space"></div>
              <div class="sig-rule"></div>
              <div class="sig-label"><b>Parent / Guardian</b>Signature</div>
            </div>
            <div class="sig-slot">
              <i class="fas fa-calendar-day"></i>
              <div class="sig-space"></div>
              <div class="sig-rule"></div>
              <div class="sig-label"><b>Date</b>{{ $issueDate ?? '' }}</div>
            </div>
          </div>
        </div>
        <div class="footer-tagline">Every child is a unique story of joy, hope and endless potential ♡</div>
      </div>

    </div>
  </div>
</body>
</html>