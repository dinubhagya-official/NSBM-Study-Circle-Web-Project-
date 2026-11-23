<?php
session_start();
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - NSBM Study Circle</title>
  <link rel="icon" type="image/png" href="images/logo-icon.png"> 

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-green: #22c55e;
      --primary-blue: #1d4ed8;
      --text-dark: #1e293b;
      --text-gray: #64748b;
      --border-color: #e2e8f0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    body { 
        background-color: transparent; 
        color: var(--text-dark); 
        zoom: 0.85;
    }

    #bg-video {
        position: fixed;
        right: 0;
        bottom: 0;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        z-index: -100;
        object-fit: cover;
    }

    .video-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(248, 250, 252, 0.6); 
        backdrop-filter: blur(3px); 
        z-index: -99;
    }

    .main-content {
        position: relative;
        z-index: 1;
    }

    .top-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 15px 60px; 
      background-color: #ffffff; 
      border-bottom: 1px solid #e2e8f0; 
      position: sticky; top: 0; z-index: 100;
    }
    
    .nav-brand { display: flex; align-items: center; gap: 20px; text-decoration: none; }
    
    .nav-brand img { 
        height: 65px; 
        width: auto; 
        object-fit: contain; 
    }
    
    .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
    
    .brand-main {
      font-size: 28px; font-weight: 800; letter-spacing: -0.5px;
      color: var(--primary-blue); 
    }
    
    .brand-sub { font-size: 17px; font-weight: 700; color: var(--primary-green); margin-top: 4px; }
    
    .nav-actions { display: flex; align-items: center; }
    
    .btn-login-register {
      background-color: var(--primary-green); color: #ffffff;
      padding: 12px 32px; border-radius: 50px; text-decoration: none;
      font-weight: 600; font-size: 16px; transition: 0.3s;
      box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .btn-login-register:hover { background-color: #16a34a; transform: translateY(-2px); }

    .hero { padding: 50px 60px 20px; display: flex; justify-content: center; }
    .hero-container {
      background: rgba(255, 255, 255, 0.9); 
      backdrop-filter: blur(15px); 
      border: 1px solid rgba(255, 255, 255, 0.8); 
      border-radius: 30px;
      padding: 60px; display: flex; align-items: center; justify-content: space-between;
      max-width: 1200px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.08); gap: 50px;
    }
    
    .hero-text h1 {
      font-size: 56px; font-weight: 800; line-height: 1.1; margin-bottom: 25px;
      background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #22c55e 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .hero-text h1 span { 
      background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .hero-text p { font-size: 20px; color: var(--text-dark); font-weight: 500; line-height: 1.6; margin-bottom: 35px; max-width: 480px; }
    
    .btn-primary {
      background-color: var(--primary-green); color: #ffffff; padding: 16px 36px;
      border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 18px;
      display: inline-block; transition: 0.3s; box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
    }
    .btn-primary:hover { background-color: #16a34a; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(34, 197, 94, 0.5); }
    
    .hero-image { flex: 1; display: flex; justify-content: flex-end; }
    .hero-image img { width: 100%; max-width: 550px; border-radius: 20px; object-fit: cover; box-shadow: 0 15px 35px rgba(0,0,0,0.2); animation: floatUpDown 3s ease-in-out infinite; }
    
    @keyframes floatUpDown {
      0% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
      100% { transform: translateY(0); }
    }

    .content-wrapper {
      max-width: 1200px;
      margin: 30px auto 60px;
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(15px);
      border-radius: 30px;
      padding: 60px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      border: 1px solid rgba(255, 255, 255, 0.8);
    }

    .how-it-works { display: flex; flex-direction: column; align-items: center; margin-bottom: 50px; }
    .section-title { font-size: 34px; font-weight: 800; color: var(--primary-blue); margin-bottom: 10px; text-align: center; }
    .section-subtitle { font-size: 18px; color: var(--text-gray); margin-bottom: 40px; text-align: center; font-weight: 500;}
    
    .steps-container { display: flex; justify-content: center; align-items: center; width: 100%; gap: 20px; }
    .step { display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1; transition: 0.3s;}
    .step:hover { transform: translateY(-5px); }
    
    .step-circle {
      width: 60px; height: 60px; background-color: #ffffff; border: 2px solid var(--primary-green);
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 24px; font-weight: 800; color: var(--primary-green); margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .step h4 { font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
    .step p { font-size: 15px; color: var(--text-gray); max-width: 220px; line-height: 1.5;}
    .step-line { width: 80px; height: 2px; background-color: #bbf7d0; margin-top: -40px; }

    .features { display: flex; justify-content: center; }
    .features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; width: 100%; }
    
    .feature-card {
      background: #ffffff; 
      border: 1px solid var(--border-color); border-radius: 20px;
      padding: 35px 20px; text-align: center; transition: all 0.3s ease;
      box-shadow: 0 5px 20px rgba(0,0,0,0.03);
    }
    .feature-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(34, 197, 94, 0.15); border-color: var(--primary-green); }
    .feature-icon { width: 70px; height: 70px; background: #eff6ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; transition: 0.3s; }
    .feature-card:hover .feature-icon { background: var(--primary-green); transform: scale(1.1);}
    .feature-icon svg { width: 32px; height: 32px; stroke: var(--primary-blue); fill: none; stroke-width: 2; transition: 0.3s; }
    .feature-card:hover .feature-icon svg { stroke: #ffffff; }
    .feature-card h3 { font-size: 17px; font-weight: 700; color: var(--text-dark); line-height: 1.4; }

    @media (max-width: 992px) {
      .hero-container { flex-direction: column; text-align: center; padding: 40px; }
      .hero-text p { margin: 0 auto 30px; }
      .hero-image { justify-content: center; margin-top: 30px; }
      .hero-text h1 { font-size: 42px; }
      .content-wrapper { padding: 30px 20px; margin: 20px; }
      .features-grid { grid-template-columns: repeat(2, 1fr); }
      .top-header { flex-direction: column; gap: 20px; padding: 20px; text-align: center; }
      .nav-brand { flex-direction: column; }
      .brand-text { text-align: center; }
      .brand-main { font-size: 24px; }
      .brand-sub { font-size: 16px; }
      .steps-container { flex-direction: column; gap: 40px; }
      .step-line { display: none; }
    }
    @media (max-width: 600px) { 
      .features-grid { grid-template-columns: 1fr; } 
      .hero-text h1 { font-size: 32px; } 
      body { zoom: 1; } /* ජංගම දුරකථනවල පෙනුම වෙනස් නොවී තිබීමට */
    }
  </style>
</head>
<body>

<video autoplay loop muted playsinline id="bg-video">
    <source src="images/bg-video.mp4" type="video/mp4">
</video>

<div class="video-overlay"></div>

<div class="main-content">

    <header class="top-header">
      <a href="index.php" class="nav-brand">
        <img src="images/nsbm-logo.png" alt="NSBM Logo">
        <div class="brand-text">
          <span class="brand-main">NSBM Green University StudyCircle</span>
          <span class="brand-sub">Faculty of Computing</span>
        </div>
      </a>
      <div class="nav-actions">
        <a href="login.php" class="btn-login-register">Login / Register</a>
      </div>
    </header>

    <section class="hero">
      <div class="hero-container">
        <div class="hero-text">
          <h1>Welcome to <br><span>StudyCircle</span></h1>
          <p>The Collaborative Platform for NSBM Computing Students</p>
          <a href="register.php" class="btn-primary">Join Now & Share Notes</a>
        </div>
        <div class="hero-image">
          <img src="images/students.jpg" alt="NSBM Students">
        </div>
      </div>
    </section>

    <div class="content-wrapper">
     
        <section class="how-it-works">
          <h2 class="section-title">How It Works</h2>
          <p class="section-subtitle">Get started with StudyCircle in 3 simple steps</p>
          <div class="steps-container">
            <div class="step"><div class="step-circle">1</div><h4>Create an Account</h4><p>Sign up using your NSBM campus email address.</p></div>
            <div class="step-line"></div>
            <div class="step"><div class="step-circle">2</div><h4>Upload or Find Notes</h4><p>Share your study materials or search for what you need.</p></div>
            <div class="step-line"></div>
            <div class="step"><div class="step-circle">3</div><h4>Learn Together</h4><p>Download resources, join groups, and ace your exams.</p></div>
          </div>
        </section>

        <section class="features">
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
              <h3>Upload & Share Notes</h3>
            </div>
            <div class="feature-card">
              <div class="feature-icon"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></div>
              <h3>Download Study Materials</h3>
            </div>
            <div class="feature-card">
              <div class="feature-icon"><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
              <h3>Ask Academic Questions</h3>
            </div>
            <div class="feature-card">
              <div class="feature-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
              <h3>Join Study Groups</h3>
            </div>
          </div>
        </section>
    </div> 

</div> 

</body>
</html>