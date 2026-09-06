<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kindergarten Learning Journey — Preview</title>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Baloo+2:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  :root{
    --navy:#1c3f7c;
    --purple:#8a5fc7;
    --pink:#ec6ea8;
    --orange:#f2994a;
    --green:#4caf7d;
    --blue:#3aa8d8;
    --yellow:#f0b429;
    --paper:#fffdf7;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{
    font-family:'Baloo 2', sans-serif;
    background:#dfe3ea;
  }
  .toolbar{
    background:linear-gradient(135deg,#1c3f7c,#3aa8d8);
    color:#fff; padding:.75rem 1.5rem; display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; z-index:50; font-family:'Fredoka',sans-serif;
  }
  .toolbar b{font-size:1rem;}
  .toolbar small{opacity:.8; display:block;}
  .page-wrap{ display:flex; justify-content:center; padding:24px 0 60px; }
  .sheet{
    width:210mm; min-height:297mm;
    background:var(--paper);
    position:relative;
    padding:10mm 11mm 8mm;
    box-shadow:0 4px 30px rgba(0,0,0,.25);
    overflow:hidden;
    border-radius:6px;
  }
  @media print{
    body{background:#fff;}
    .toolbar{display:none;}
    .page-wrap{padding:0;}
    .sheet{box-shadow:none; width:210mm; min-height:297mm; border-radius:0;}
  }

  /* decorative floaters */
  .deco{position:absolute; opacity:.95;}
  .deco.star{color:#f0b429; font-size:20px;}
  .deco.heart{color:#ec6ea8; font-size:16px;}
  .deco.dot{color:#3aa8d8; font-size:14px;}

  /* HEADER */
  .header{ position:relative; display:flex; align-items:flex-start; justify-content:space-between; padding-bottom:6mm; }
  .logo-shield{
    width:70px; height:80px; background:var(--navy); color:#fff;
    clip-path: polygon(50% 0%, 100% 15%, 100% 60%, 50% 100%, 0% 60%, 0% 15%);
    display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
    font-size:8px; font-weight:700; line-height:1.1; padding:6px;
  }
  .logo-shield i{font-size:16px; margin-bottom:3px;}
  .header-center{ text-align:center; flex:1; padding:0 10px;}
  .school-name{
    font-family:'Fredoka',sans-serif; font-weight:700; color:var(--navy);
    font-size:26px; line-height:1.05; letter-spacing:.5px; text-transform:uppercase;
  }
  .banner{
    display:inline-block; margin-top:6px; background:var(--purple); color:#fff;
    padding:5px 22px; border-radius:20px; font-weight:700; font-size:13px; letter-spacing:.5px;
    box-shadow:0 3px 0 rgba(0,0,0,.12);
  }
  .academic-year{ color:var(--blue); font-weight:600; margin-top:6px; font-size:12px;}
  .side-art{ width:120px; text-align:center; }
  .side-art img{ max-width:100%; max-height:78px; object-fit:contain;}
  .rainbow-wrap{ position:absolute; right:6mm; top:-2mm; width:150px;}
  .rainbow-wrap img{width:100%;}
  .child-reading{ position:absolute; left:2mm; top:12mm; width:90px;}
  .teddy{ position:absolute; left:20mm; top:20mm; width:44px;}
  .blocks{ position:absolute; left:38mm; top:24mm; width:34px;}
  .girl-wave{ position:absolute; right:2mm; top:14mm; width:78px;}
  .palette{ position:absolute; right:34mm; top:24mm; width:40px;}

  .content{ position:relative; z-index:2; margin-top:30mm; }

  /* CHILD INFO BOX */
  .info-box{
    background:#fff; border:2px solid #eef1f6; border-radius:16px;
    box-shadow:0 3px 10px rgba(0,0,0,.06);
    padding:10px 18px 14px; margin-bottom:5mm;
  }
  .info-title{
    text-align:center; font-family:'Fredoka',sans-serif; font-weight:700; color:var(--navy);
    font-size:13px; letter-spacing:1px; text-transform:uppercase; margin-bottom:8px;
  }
  .info-grid{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px 22px; }
  .info-row{ display:flex; align-items:center; gap:8px; font-size:12px; color:#333; padding:4px 0; border-bottom:1px dotted #ccc;}
  .info-icon{
    width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:10px; flex:none;
  }
  .info-row .label{font-weight:600; color:#333; white-space:nowrap;}
  .info-row .value{flex:1; color:#555;}

  /* DEVELOPMENT JOURNEY */
  .dev-title{
    text-align:center; font-family:'Fredoka',sans-serif; font-weight:700; color:var(--navy);
    font-size:14px; letter-spacing:.5px; text-transform:uppercase; margin:4mm 0 3mm;
  }
  .dev-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:5mm;}
  .dev-card{
    background:#fff; border-radius:14px; padding:10px 8px 12px; text-align:center;
    box-shadow:0 2px 8px rgba(0,0,0,.07); border-top:4px solid var(--c);
  }
  .dev-card img{width:52px; height:52px; object-fit:contain; margin-bottom:4px;}
  .dev-card h4{ font-family:'Fredoka',sans-serif; font-size:10.5px; color:var(--c); text-transform:uppercase; line-height:1.2; margin-bottom:4px;}
  .dev-card p{font-size:9px; color:#555; line-height:1.3;}

  /* BOTTOM 4 PANELS */
  .bottom-grid{ display:grid; grid-template-columns:1.1fr 1fr 1.1fr 1fr; gap:8px; margin-bottom:4mm;}
  .panel{ background:#fff; border-radius:12px; padding:8px 10px; box-shadow:0 2px 8px rgba(0,0,0,.06); min-height:130px; position:relative;}
  .panel h5{ font-size:10px; font-family:'Fredoka',sans-serif; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:5px;}
  .panel h5 i{font-size:11px;}
  .lines .line{ border-bottom:1px dotted #bbb; height:14px; margin-bottom:6px;}
  .moment-item{ display:flex; gap:6px; align-items:center; margin-bottom:8px; font-size:9px;}
  .moment-icon{width:16px;height:16px;border-radius:50%; display:flex;align-items:center;justify-content:center;color:#fff;font-size:8px;flex:none;}
  .steps li{ list-style:none; display:flex; gap:6px; font-size:8.7px; margin-bottom:7px; align-items:flex-start;}
  .steps i{color:var(--blue); font-size:9px; margin-top:2px;}
  .message-text{font-size:9.3px; line-height:1.5; color:#444; text-align:center; font-style:italic;}

  /* FOOTER SIGNATURES */
  .sig-box{ background:#fff; border-radius:14px; padding:10px 14px; box-shadow:0 2px 8px rgba(0,0,0,.06); display:grid; grid-template-columns:repeat(4,1fr); gap:10px; position:relative; margin-bottom:6mm;}
  .sig-col{ text-align:center; font-size:10px;}
  .sig-col i{ color:var(--navy); font-size:14px; margin-bottom:6px; display:block;}
  .sig-line{ border-bottom:1px dotted #999; height:18px; margin-bottom:3px;}
  .sig-col span{ font-weight:600; color:#333;}

  .footer-art{ position:absolute; left:0; right:0; bottom:0; height:26mm; background-image:url('{{ asset('images/passslip/kindergarten/') }}/footer_landscape.png'); background-size:cover; background-position:bottom; opacity:.9; z-index:0;}
  .backpack-deco{ position:absolute; left:6mm; bottom:2mm; width:44px; z-index:1;}
  .pencils-deco{ position:absolute; right:8mm; bottom:2mm; width:44px; z-index:1;}
</style>
</head>
<body>
  <div class="toolbar">
    <div>
      <b>Kindergarten Learning Journey — Design Preview</b>
      <small>Standalone HTML preview (not wired to live data)</small>
    </div>
    <div>
      <button onclick="window.print()" style="padding:6px 14px;border:none;border-radius:6px;background:#fff;color:var(--navy);font-weight:700;cursor:pointer;">Print / Save PDF</button>
    </div>
  </div>

  <div class="page-wrap">
    <div class="sheet" id="sheet">

      <!-- decorative stars/hearts -->
      <i class="fa-solid fa-star deco star" style="left:5mm; top:2mm;"></i>
      <i class="fa-solid fa-star deco star" style="left:60mm; top:1mm; font-size:14px;"></i>
      <i class="fa-solid fa-heart deco heart" style="left:34mm; top:8mm;"></i>
      <i class="fa-regular fa-star deco star" style="right:5mm; top:34mm; font-size:14px;"></i>

      <div class="header">
        <div class="logo-shield">
          <i class="fa-solid fa-book-open"></i>
          YOUR LOGO HERE
        </div>

        <div class="header-center">
          <div class="school-name">Victory Christian<br>Nursery School</div>
          <div class="banner"><i class="fa-solid fa-star"></i> KINDERGARTEN LEARNING JOURNEY <i class="fa-solid fa-star"></i></div>
          <div class="academic-year">Academic Year: {{ $academic_year ?? '20XX – 20XX' }}</div>
        </div>

        <div class="side-art"></div>
      </div>

      <!-- floating illustration assets -->
      <img class="child-reading" src="{{ asset('images/passslip/kindergarten/') }}/child_reading.png" alt="">
      <img class="teddy" src="{{ asset('images/passslip/kindergarten/') }}/teddy_bear.png" alt="">
      <img class="blocks" src="{{ asset('images/passslip/kindergarten/') }}/alphabet_blocks.png" alt="">
      <img class="girl-wave" src="{{ asset('images/passslip/kindergarten/') }}/girl_waving.png" alt="">
      <img class="palette" src="{{ asset('images/passslip/kindergarten/') }}/paint_palette.png" alt="">
      <div class="rainbow-wrap"><img src="{{ asset('images/passslip/kindergarten/') }}/rainbow_clouds.png" alt=""></div>

      <div class="content">

        <!-- CHILD'S INFORMATION -->
        <div class="info-box">
          <div class="info-title"><i class="fa-solid fa-leaf"></i> Child's Information <i class="fa-solid fa-leaf"></i></div>
          <div class="info-grid">
            <div class="info-row"><div class="info-icon" style="background:var(--blue)"><i class="fa-solid fa-user"></i></div><span class="label">Child's Name:</span><span class="value">{{ $child_name ?? '' }}</span></div>
            <div class="info-row"><div class="info-icon" style="background:var(--orange)"><i class="fa-solid fa-book"></i></div><span class="label">Stream:</span><span class="value">{{ $stream ?? '' }}</span></div>
            <div class="info-row"><div class="info-icon" style="background:var(--green)"><i class="fa-solid fa-calendar-days"></i></div><span class="label">Term:</span><span class="value">{{ $term ?? '' }}</span></div>

            <div class="info-row"><div class="info-icon" style="background:var(--pink)"><i class="fa-solid fa-user-large"></i></div><span class="label">Class:</span><span class="value">{{ $class_name ?? '' }}</span></div>
            <div class="info-row"><div class="info-icon" style="background:var(--purple)"><i class="fa-solid fa-clock"></i></div><span class="label">Attendance:</span><span class="value">{{ $attendance ?? '' }}</span></div>
            <div class="info-row"><div class="info-icon" style="background:var(--pink)"><i class="fa-solid fa-chalkboard-teacher"></i></div><span class="label">Teacher:</span><span class="value">{{ $teacher ?? '' }}</span></div>

            <div class="info-row"><div class="info-icon" style="background:var(--green)"><i class="fa-solid fa-cake-candles"></i></div><span class="label">Date of Birth:</span><span class="value">{{ $dob ?? '' }}</span></div>
            <div class="info-row" style="grid-column:span 2;"></div>
          </div>
        </div>

        <!-- DEVELOPMENT JOURNEY -->
        <div class="dev-title"><i class="fa-solid fa-seedling"></i> My Development Journey <i class="fa-solid fa-seedling"></i></div>
        <div class="dev-grid">
          <div class="dev-card" style="--c:#4caf7d"><img src="{{ asset('images/passslip/kindergarten/') }}/social_emotional.png"><h4>Social &amp; Emotional Development</h4><p>Growing positive relationships and understanding feelings.</p></div>
          <div class="dev-card" style="--c:#f2994a"><img src="{{ asset('images/passslip/kindergarten/') }}/thinking_discovery.png"><h4>Thinking &amp; Discovery</h4><p>Shows curiosity, explores, and enjoys learning new things.</p></div>
          <div class="dev-card" style="--c:#ec6ea8"><img src="{{ asset('images/passslip/kindergarten/') }}/language_communication.png"><h4>Language &amp; Communication</h4><p>Enjoys stories, expresses ideas, and is developing confidence.</p></div>
          <div class="dev-card" style="--c:#8a5fc7"><img src="{{ asset('images/passslip/kindergarten/') }}/creativity_expression.png"><h4>Creativity &amp; Expression</h4><p>Enjoys art, imagination, and expressing ideas in many ways.</p></div>

          <div class="dev-card" style="--c:#3aa8d8"><img src="{{ asset('images/passslip/kindergarten/') }}/physical_development.png"><h4>Physical Development</h4><p>Developing strength, coordination and healthy movement habits.</p></div>
          <div class="dev-card" style="--c:#4caf7d"><img src="{{ asset('images/passslip/kindergarten/') }}/cooperation_independence.png"><h4>Cooperation &amp; Independence</h4><p>Works well with others and is becoming more independent.</p></div>
          <div class="dev-card" style="--c:#f0b429"><img src="{{ asset('images/passslip/kindergarten/') }}/music_movement.png"><h4>Music &amp; Movement</h4><p>Enjoys singing, rhythm, movement and creative musical activities.</p></div>
          <div class="dev-card" style="--c:#4caf7d"><img src="{{ asset('images/passslip/kindergarten/') }}/exploring_world.png"><h4>Exploring The World</h4><p>Shows interest in nature, people, places and the world.</p></div>
        </div>

        <!-- FOUR BOTTOM PANELS -->
        <div class="bottom-grid">
          <div class="panel">
            <h5 style="color:var(--navy)"><i class="fa-solid fa-pen"></i> Teacher's Observation</h5>
            <div class="lines">
              <div class="line"></div><div class="line"></div><div class="line"></div>
              <div class="line"></div><div class="line"></div>
            </div>
          </div>

          <div class="panel">
            <h5 style="color:var(--pink)"><i class="fa-solid fa-heart"></i> Child's Special Moments</h5>
            <div class="moment-item"><div class="moment-icon" style="background:var(--pink)"><i class="fa-solid fa-star"></i></div><div class="line" style="flex:1"></div></div>
            <div class="moment-item"><div class="moment-icon" style="background:var(--purple)"><i class="fa-solid fa-balloon"></i></div><div class="line" style="flex:1"></div></div>
            <div class="moment-item"><div class="moment-icon" style="background:var(--yellow)"><i class="fa-solid fa-trophy"></i></div><div class="line" style="flex:1"></div></div>
          </div>

          <div class="panel">
            <h5 style="color:var(--green)"><i class="fa-solid fa-seedling"></i> Next Steps In Learning</h5>
            <ul class="steps">
              <li><i class="fa-solid fa-magnifying-glass"></i> Keep exploring and asking wonderful questions.</li>
              <li><i class="fa-solid fa-comments"></i> Continue building confidence in expressing ideas.</li>
              <li><i class="fa-solid fa-user-check"></i> Develop independence and responsibility.</li>
              <li><i class="fa-solid fa-star"></i> Keep practicing, trying and believing in yourself.</li>
            </ul>
          </div>

          <div class="panel" style="display:flex; flex-direction:column; justify-content:center;">
            <h5 style="color:var(--purple); justify-content:center;"><i class="fa-solid fa-envelope"></i> Teacher's Message</h5>
            <div class="message-text">
              You are a wonderful learner with a bright future!
              Keep shining, keep smiling, and keep growing.
              We are proud of you!
            </div>
          </div>
        </div>

        <!-- SIGNATURES -->
        <div class="sig-box">
          <div class="sig-col"><i class="fa-solid fa-pen-nib"></i><div class="sig-line"></div><span>Class Teacher</span><br><small>Signature</small></div>
          <div class="sig-col"><i class="fa-solid fa-award"></i><div class="sig-line"></div><span>Head Teacher</span><br><small>Signature</small></div>
          <div class="sig-col"><i class="fa-solid fa-people-roof"></i><div class="sig-line"></div><span>Parent / Guardian</span><br><small>Signature</small></div>
          <div class="sig-col"><i class="fa-regular fa-calendar"></i><div class="sig-line"></div><span>Date</span></div>
        </div>

      </div>

      <div class="footer-art"></div>
      <img class="backpack-deco" src="{{ asset('images/passslip/kindergarten/') }}/backpack.png" alt="">
      <img class="pencils-deco" src="{{ asset('images/passslip/kindergarten/') }}/pencils.png" alt="">
    </div>
  </div>
</body>
</html>