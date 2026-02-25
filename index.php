<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// OPTIONAL: show front page even if logged in (no auto-redirect)

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome | Barangay Information System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#ffcc00;
      --bg2:#ffb300;
      --bg3:#ff8f00;
      --ink:#1f2937;
      --glass: rgba(255,255,255,.22);
      --glass2: rgba(255,255,255,.14);
      --border: rgba(255,255,255,.35);
      --shadow: 0 20px 55px rgba(0,0,0,.22);
    }

    body{
      min-height: 100vh;
      margin:0;
      color: var(--ink);
      overflow-x:hidden;
      background: radial-gradient(1200px 700px at 20% 15%, rgba(255,255,255,.40), transparent 55%),
                  radial-gradient(900px 600px at 80% 30%, rgba(255,255,255,.25), transparent 60%),
                  linear-gradient(135deg, var(--bg1), var(--bg2) 45%, var(--bg3));
    }

    /* animated background blobs */
    .blob{
      position: fixed;
      z-index:-1;
      filter: blur(30px);
      opacity:.55;
      border-radius: 999px;
      animation: floaty 10s ease-in-out infinite;
    }
    .blob.b1{ width: 420px; height: 420px; left:-120px; top: 40px; background: rgba(255,255,255,.55); }
    .blob.b2{ width: 520px; height: 520px; right:-180px; top: 140px; background: rgba(255,255,255,.35); animation-duration: 13s; }
    .blob.b3{ width: 480px; height: 480px; left: 35%; bottom:-220px; background: rgba(0,0,0,.08); animation-duration: 15s; }
    @keyframes floaty{
      0%,100%{ transform: translateY(0) translateX(0) scale(1); }
      50%{ transform: translateY(-18px) translateX(10px) scale(1.03); }
    }

    .hero{
      min-height: 92vh;
      display:flex;
      align-items:center;
      padding: 36px 0;
    }

    .glass{
      background: var(--glass);
      border: 1px solid var(--border);
      backdrop-filter: blur(12px);
      border-radius: 22px;
      box-shadow: var(--shadow);
    }

    .title{
      font-weight: 800;
      letter-spacing: -.02em;
      line-height: 1.05;
      color: #111827;
    }

    .sub{
      font-size: 1.1rem;
      color: rgba(17,24,39,.82);
    }

    /* entrance animations */
    .reveal{
      opacity:0;
      transform: translateY(18px);
      animation: reveal .85s ease forwards;
    }
    .reveal.d2{ animation-delay: .12s; }
    .reveal.d3{ animation-delay: .24s; }
    .reveal.d4{ animation-delay: .36s; }
    @keyframes reveal{
      to{ opacity:1; transform: translateY(0); }
    }

    /* button styling + pulse */
    .btn-main{
      background: #111827;
      color: #fff;
      border: 0;
      border-radius: 14px;
      padding: 12px 18px;
      box-shadow: 0 14px 35px rgba(17,24,39,.22);
      transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
      position: relative;
      overflow: hidden;
    }
    .btn-main:hover{
      transform: translateY(-2px);
      box-shadow: 0 18px 45px rgba(17,24,39,.28);
      opacity: .96;
    }
    .btn-main:active{ transform: translateY(0); }

    .btn-main::after{
      content:"";
      position:absolute;
      inset:-2px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.22);
      opacity:.55;
      pointer-events:none;
    }

    .btn-outline{
      border-radius: 14px;
      padding: 12px 18px;
      border: 1px solid rgba(17,24,39,.35);
      color:#111827;
      background: rgba(255,255,255,.20);
      transition: transform .15s ease, background .15s ease;
    }
    .btn-outline:hover{
      transform: translateY(-2px);
      background: rgba(255,255,255,.30);
    }

    /* feature list with icons */
    .feature li{
      display:flex;
      gap:10px;
      align-items:flex-start;
      margin: 10px 0;
      color: rgba(17,24,39,.85);
    }
    .feature i{
      font-size: 1.15rem;
      margin-top: 2px;
      color: #111827;
      opacity:.9;
    }

    /* floating card effect */
    .float-card{
      animation: cardFloat 6.5s ease-in-out infinite;
    }
    @keyframes cardFloat{
      0%,100%{ transform: translateY(0); }
      50%{ transform: translateY(-10px); }
    }

    /* small stats chips */
    .chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.25);
      border: 1px solid rgba(255,255,255,.35);
      color: rgba(17,24,39,.88);
      font-weight: 600;
      font-size: .92rem;
    }

    /* responsive spacing */
    @media (max-width: 991px){
      .hero{ min-height: auto; padding: 26px 0 48px; }
      .title{ font-size: 2.3rem; }
    }
  </style>
</head>

<body>
  <div class="blob b1"></div>
  <div class="blob b2"></div>
  <div class="blob b3"></div>

  <section class="hero">
    <div class="container">
      <div class="row g-4 align-items-center">

        <!-- LEFT -->
        <div class="col-lg-7">
          <div class="reveal d1 mb-3">
            <span class="chip"><i class="bi bi-building"></i> Barangay Information System</span>
          </div>

          <h1 class="title display-4 reveal d2">
            Welcome to your<br> Barangay Services Portal
          </h1>

          <p class="sub mt-3 mb-4 reveal d3">
            Online document requests, announcements, and resident services — mabilis, malinaw, at may tracking via reference number.
          </p>

          <div class="d-flex flex-wrap gap-2 reveal d4">
            <a href="/BIS/views/login.php" class="btn btn-main btn-lg">
              <i class="bi bi-box-arrow-in-right me-2"></i> Proceed to Login
            </a>

           
            <!-- <a href="/BIS/views/register.php" class="btn btn-outline btn-lg">
              <i class="bi bi-person-plus me-2"></i> Register
            </a> -->

            <a href="#features" class="btn btn-outline btn-lg">
              <i class="bi bi-stars me-2"></i> View Features
            </a>
          </div>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-5">
          <div id="features" class="glass p-4 p-md-4 float-card reveal d3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0 fw-bold">System Features</h5>
              <span class="chip"><i class="bi bi-shield-lock"></i> Secure</span>
            </div>

            <ul class="feature list-unstyled mt-3 mb-0">
              <li><i class="bi bi-file-earmark-text"></i> Request barangay documents online</li>
              <li><i class="bi bi-hash"></i> Get reference number after submit</li>
              <li><i class="bi bi-search"></i> Track request status (Pending/Approved/Released)</li>
              <li><i class="bi bi-megaphone"></i> View announcements with attachments</li>
              <li><i class="bi bi-speedometer2"></i> Admin dashboards & monitoring</li>
            </ul>
          </div>
        </div>

        <section id="featuresSection" class="py-5">
  <div class="container">
    <div class="glass p-4 p-md-5">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h2 class="fw-bold mb-1">Features Overview</h2>
          <div style="color: rgba(17,24,39,.78);">Quick guide sa services ng BIS</div>
        </div>
        <a href="/BIS/views/login.php" class="btn btn-main">
          <i class="bi bi-box-arrow-in-right me-2"></i> Login
        </a>
      </div>

      <div class="row g-3">
        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Document Request</div>
            <div style="color: rgba(17,24,39,.78);">Request certificates and submit purpose online.</div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-hash me-2"></i>Reference Number</div>
            <div style="color: rgba(17,24,39,.78);">Auto-generated reference number after submit.</div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-search me-2"></i>Status Tracking</div>
            <div style="color: rgba(17,24,39,.78);">Track Pending / Approved / Released.</div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-megaphone me-2"></i>Announcements</div>
            <div style="color: rgba(17,24,39,.78);">Posts with images/files for residents.</div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-person-badge me-2"></i>Officials Directory</div>
            <div style="color: rgba(17,24,39,.78);">Manage barangay officials list.</div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="glass p-3 h-100">
            <div class="fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboards</div>
            <div style="color: rgba(17,24,39,.78);">Admin & resident dashboards for monitoring.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


      </div>
    </div>
  </section>

<script>
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click', (e)=>{
      const id = a.getAttribute('href');
      const el = document.querySelector(id);
      if(!el) return; 
      e.preventDefault();
      const top = el.getBoundingClientRect().top + window.pageYOffset - 20;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });
</script>

</body>
</html>
