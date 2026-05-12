<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
 <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مركز الياسر التجاري</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --crimson:       #8B0D2E;
    --crimson-deep:  #6a0922;
    --crimson-dark:  #4a0618;
    --crimson-soft:  #b01238;
    --crimson-pale:  #f5e8ec;
    --crimson-ghost: rgba(139,13,46,0.07);
    --cream:         #f9f5ec;
    --cream-dark:    #f0e9da;
    --ink:           #1a0308;
    --text-muted:    #7a4455;
    --border:        rgba(139,13,46,0.15);
    --border-strong: rgba(139,13,46,0.35);
    --radius:        12px;
  }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'Tajawal', sans-serif;
    background: var(--cream);
    color: var(--ink);
    direction: rtl;
    overflow-x: hidden;
  }

  /* ─── NAV ─── */
  nav {
    position: fixed; top: 0; right: 0; left: 0; z-index: 100;
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 3rem;
    background: rgba(249,245,236,0.92);
    backdrop-filter: blur(16px);
    border-bottom: 0.5px solid var(--border);
  }
  .nav-logo {
    display: flex; align-items: center; gap: 0.75rem;
  }
  .nav-logo img {
    height: 44px; width: 44px; object-fit: contain; border-radius: 6px;
  }
  .nav-logo-text {
    font-size: 1.05rem; font-weight: 800; color: var(--crimson);
    line-height: 1.2;
  }
  .nav-logo-sub { font-size: 0.72rem; color: var(--text-muted); font-weight: 500; }
  .nav-links { display: flex; gap: 2rem; list-style: none; }
  .nav-links a {
    text-decoration: none; font-size: 0.9rem; font-weight: 500;
    color: var(--text-muted); transition: color 0.2s;
  }
  .nav-links a:hover { color: var(--crimson); }
  .nav-btn {
    background: var(--crimson); color: var(--cream);
    border: none; padding: 0.55rem 1.4rem;
    border-radius: 50px; font-family: 'Tajawal', sans-serif;
    font-size: 0.88rem; font-weight: 700; cursor: pointer;
    transition: background 0.2s, transform 0.15s;
  }
  .nav-btn:hover { background: var(--crimson-deep); transform: translateY(-1px); }

  /* ─── HERO ─── */
  .hero {
    min-height: 100vh;
    display: flex; flex-direction: column; justify-content: center; align-items: center;
    text-align: center;
    padding: 8rem 2rem 5rem;
    position: relative; overflow: hidden;
    background: var(--cream);
  }
  .hero-bg-circle {
    position: absolute; border-radius: 50%;
    background: var(--crimson-ghost);
  }
  .hero-bg-circle.c1 { width: 600px; height: 600px; top: -200px; left: -200px; }
  .hero-bg-circle.c2 { width: 400px; height: 400px; bottom: -100px; right: -100px; opacity: 0.6; }
  .hero-pattern {
    position: absolute; inset: 0; opacity: 0.03;
    background-image: repeating-linear-gradient(
      0deg, var(--crimson) 0, var(--crimson) 1px, transparent 0, transparent 40px
    ),
    repeating-linear-gradient(
      90deg, var(--crimson) 0, var(--crimson) 1px, transparent 0, transparent 40px
    );
  }
  .hero-content { position: relative; z-index: 1; max-width: 800px; }

  .hero-logo {
    margin-bottom: 2rem;
  }
  .hero-logo img {
    height: 140px; width: 140px; object-fit: contain;
    border-radius: 20px;
    border: 0.5px solid var(--border);
    background: white;
    padding: 10px;
    box-shadow: 0 8px 40px rgba(139,13,46,0.12);
  }

  .hero-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--crimson-ghost);
    border: 0.5px solid var(--border-strong);
    border-radius: 50px; padding: 0.4rem 1.1rem;
    font-size: 0.82rem; font-weight: 600; color: var(--crimson);
    margin-bottom: 1.5rem; letter-spacing: 0.02em;
  }
  .hero-badge::before {
    content: ''; display: inline-block;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--crimson);
  }
  .hero h1 {
    font-size: clamp(2.6rem, 6vw, 4.5rem);
    font-weight: 900; line-height: 1.12;
    color: var(--crimson-dark); margin-bottom: 1.2rem;
    letter-spacing: -0.02em;
  }
  .hero h1 .accent { color: var(--crimson); }
  .hero-sub {
    font-size: 1.1rem; line-height: 1.9; color: var(--text-muted);
    max-width: 580px; margin: 0 auto 2.5rem;
  }
  .hero-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
  .btn-primary {
    background: var(--crimson); color: var(--cream);
    border: none; padding: 0.9rem 2.4rem;
    border-radius: 50px; font-family: 'Tajawal', sans-serif;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    transition: all 0.2s; text-decoration: none; display: inline-block;
    box-shadow: 0 6px 24px rgba(139,13,46,0.28);
  }
  .btn-primary:hover { background: var(--crimson-deep); transform: translateY(-2px); box-shadow: 0 10px 32px rgba(139,13,46,0.35); }
  .btn-outline {
    background: transparent; color: var(--crimson);
    border: 1.5px solid var(--border-strong); padding: 0.9rem 2.4rem;
    border-radius: 50px; font-family: 'Tajawal', sans-serif;
    font-size: 1rem; font-weight: 600; cursor: pointer;
    transition: all 0.2s; text-decoration: none; display: inline-block;
  }
  .btn-outline:hover { background: var(--crimson-ghost); border-color: var(--crimson); }

  .hero-stats {
    display: flex; gap: 3.5rem; justify-content: center;
    margin-top: 4rem; padding-top: 3rem;
    border-top: 0.5px solid var(--border);
  }
  .stat { text-align: center; }
  .stat-num { font-size: 2.2rem; font-weight: 900; color: var(--crimson); line-height: 1; }
  .stat-label { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.35rem; font-weight: 500; }

  /* ─── SECTION WRAPPER ─── */
  .section-pad { padding: 5.5rem 2rem; }
  .container { max-width: 1100px; margin: 0 auto; }
  .section-label {
    font-size: 0.75rem; font-weight: 700; letter-spacing: 0.14em;
    color: var(--crimson); text-transform: uppercase; margin-bottom: 0.6rem;
    display: flex; align-items: center; gap: 0.6rem;
  }
  .section-label::before {
    content: ''; display: inline-block; width: 20px; height: 2px;
    background: var(--crimson); border-radius: 2px; flex-shrink: 0;
  }
  .section-title {
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    font-weight: 800; color: var(--crimson-dark); line-height: 1.2;
    margin-bottom: 1.2rem;
  }
  .section-body { font-size: 1.05rem; line-height: 1.95; color: var(--text-muted); }

  /* ─── ABOUT ─── */
  .about-bg { background: var(--cream); }
  .about-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }
  .about-card {
    background: var(--crimson); border-radius: 20px;
    padding: 2.5rem; color: white; position: relative; overflow: hidden;
  }
  .about-card::before {
    content: ''; position: absolute; top: -60px; left: -60px;
    width: 220px; height: 220px; border-radius: 50%;
    background: rgba(255,255,255,0.05);
  }
  .about-card::after {
    content: ''; position: absolute; bottom: -40px; right: -40px;
    width: 160px; height: 160px; border-radius: 50%;
    background: rgba(255,255,255,0.04);
  }
  .about-card-num {
    font-size: 4rem; font-weight: 900; color: rgba(255,255,255,0.9); line-height: 1;
    position: relative; z-index: 1;
  }
  .about-card-sub { font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-top: 0.3rem; position: relative; z-index: 1; }
  .about-card-divider { height: 0.5px; background: rgba(255,255,255,0.15); margin: 1.5rem 0; }
  .about-card-desc { font-size: 0.9rem; color: rgba(255,255,255,0.65); line-height: 1.8; position: relative; z-index: 1; }
  .about-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-top: 1.5rem; position: relative; z-index: 1; }
  .about-mini {
    background: rgba(255,255,255,0.09); border-radius: 10px;
    padding: 0.9rem; text-align: center;
    border: 0.5px solid rgba(255,255,255,0.12);
  }
  .about-mini-val { font-size: 1.1rem; font-weight: 800; color: white; }
  .about-mini-label { font-size: 0.72rem; color: rgba(255,255,255,0.5); margin-top: 0.2rem; }

  /* ─── PRODUCTS ─── */
  .products-bg { background: var(--cream-dark); }
  .products-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 3rem;
  }
  .product-card {
    background: white;
    border: 0.5px solid var(--border);
    border-radius: 16px; padding: 2rem;
    transition: all 0.3s; position: relative; overflow: hidden;
  }
  .product-card::before {
    content: ''; position: absolute; top: 0; right: 0;
    width: 80px; height: 80px;
    background: var(--crimson-ghost);
    border-radius: 0 16px 0 80px;
  }
  .product-card:hover {
    border-color: var(--border-strong);
    box-shadow: 0 8px 32px rgba(139,13,46,0.1);
    transform: translateY(-4px);
  }
  .product-icon {
    width: 52px; height: 52px; border-radius: 12px;
    background: var(--crimson-ghost); border: 0.5px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin-bottom: 1.3rem;
  }
  .product-name { font-size: 1.05rem; font-weight: 800; color: var(--crimson-dark); margin-bottom: 0.6rem; }
  .product-desc { font-size: 0.88rem; color: var(--text-muted); line-height: 1.75; }
  .product-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 1.2rem; }
  .tag {
    font-size: 0.72rem; padding: 0.25rem 0.65rem;
    border-radius: 50px; background: var(--crimson-ghost);
    color: var(--crimson); border: 0.5px solid var(--border);
    font-weight: 600;
  }

  /* ─── VISION ─── */
  .vision-bg { background: var(--cream); }
  .vision-layout { display: grid; grid-template-columns: 3fr 2fr; gap: 4rem; align-items: start; }
  .pillars { display: flex; flex-direction: column; gap: 1rem; }
  .pillar {
    padding: 1.4rem 1.6rem; border-radius: 12px;
    border: 0.5px solid var(--border); background: white;
    display: flex; gap: 1rem; align-items: flex-start;
    transition: all 0.2s;
  }
  .pillar:hover { border-color: var(--crimson-soft); box-shadow: 0 4px 16px rgba(139,13,46,0.08); }
  .pillar-dot {
    width: 8px; height: 8px; border-radius: 2px;
    background: var(--crimson); flex-shrink: 0; margin-top: 6px;
  }
  .pillar-text { font-size: 0.95rem; line-height: 1.75; color: var(--text-muted); }
  .pillar-text strong { color: var(--crimson-dark); font-weight: 700; }

  .vision-aside {
    background: var(--crimson); border-radius: 18px;
    padding: 2rem; position: sticky; top: 6rem; overflow: hidden;
  }
  .vision-aside::before {
    content: ''; position: absolute; bottom: -60px; left: -60px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(255,255,255,0.05);
  }
  .vision-aside-label {
    font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em;
    color: rgba(255,255,255,0.5); margin-bottom: 1.2rem;
    text-transform: uppercase; position: relative; z-index: 1;
  }
  .vision-aside-stat {
    padding: 1.2rem 0; border-bottom: 0.5px solid rgba(255,255,255,0.1);
    position: relative; z-index: 1;
  }
  .vision-aside-stat:last-child { border-bottom: none; padding-bottom: 0; }
  .vision-aside-num { font-size: 2rem; font-weight: 900; color: white; }
  .vision-aside-desc { font-size: 0.82rem; color: rgba(255,255,255,0.55); margin-top: 0.15rem; }

  /* ─── SCHEDULE ─── */
  .schedule-bg { background: var(--cream-dark); }
  .hours-card {
    background: var(--crimson); border-radius: 16px;
    padding: 1.8rem 2.5rem; display: flex; align-items: center; gap: 2rem;
    margin-bottom: 2.5rem;
  }
  .hours-icon {
    width: 52px; height: 52px; border-radius: 12px;
    background: rgba(255,255,255,0.12); border: 0.5px solid rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
  }
  .hours-label { font-size: 0.75rem; color: rgba(255,255,255,0.55); font-weight: 600; letter-spacing: 0.06em; margin-bottom: 0.3rem; }
  .hours-value { font-size: 1.2rem; font-weight: 800; color: white; }
  .hours-value span { color: rgba(255,255,255,0.75); font-weight: 500; }

  .schedule-table-wrap {
    border: 0.5px solid var(--border-strong); border-radius: 14px; overflow: hidden;
    background: white;
  }
  .schedule-header-row {
    display: grid; grid-template-columns: 130px 1fr;
    background: var(--crimson-dark);
    padding: 0.85rem 1.5rem;
    font-size: 0.78rem; font-weight: 700;
    color: rgba(255,255,255,0.6); letter-spacing: 0.06em;
  }
  .schedule-row {
    display: grid; grid-template-columns: 130px 1fr;
    border-bottom: 0.5px solid var(--border);
    transition: background 0.15s;
  }
  .schedule-row:hover { background: var(--crimson-pale); }
  .schedule-row:last-child { border-bottom: none; }
  .schedule-day {
    padding: 1.1rem 1.5rem;
    font-weight: 700; color: var(--crimson); font-size: 0.92rem;
    border-left: 0.5px solid var(--border);
    display: flex; align-items: center;
  }
  .schedule-areas {
    padding: 0.9rem 1.5rem;
    display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem;
  }
  .area-chip {
    background: var(--crimson-ghost);
    color: var(--crimson-dark);
    border: 0.5px solid var(--border);
    border-radius: 50px; padding: 0.22rem 0.7rem;
    font-size: 0.8rem; font-weight: 600;
  }

  /* ─── DOWNLOAD CTA ─── */
  .download-section {
    background: var(--crimson-dark); text-align: center; padding: 6rem 2rem;
    position: relative; overflow: hidden;
  }
  .download-section::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 80% at 50% 110%, rgba(139,13,46,0.6) 0%, transparent 70%);
  }
  .download-section::after {
    content: ''; position: absolute; top: -120px; left: 50%;
    transform: translateX(-50%);
    width: 500px; height: 500px; border-radius: 50%;
    background: rgba(255,255,255,0.03);
  }
  .download-section .container { position: relative; z-index: 1; }
  .download-section .section-label { justify-content: center; color: rgba(255,255,255,0.5); }
  .download-section .section-label::before { background: rgba(255,255,255,0.4); }
  .download-section h2 { font-size: 2.5rem; font-weight: 900; color: white; margin-bottom: 1rem; }
  .download-section p { font-size: 1.05rem; color: rgba(255,255,255,0.55); margin-bottom: 2.5rem; max-width: 500px; margin-left: auto; margin-right: auto; line-height: 1.8; }
  .download-btn {
    background: var(--cream); color: var(--crimson);
    border: none; padding: 1rem 3rem;
    border-radius: 50px; font-family: 'Tajawal', sans-serif;
    font-size: 1.05rem; font-weight: 800; cursor: pointer;
    transition: all 0.2s; display: inline-block;
    box-shadow: 0 6px 28px rgba(0,0,0,0.25);
  }
  .download-btn:hover { background: white; transform: translateY(-2px); box-shadow: 0 12px 36px rgba(0,0,0,0.3); }

  /* ─── FOOTER ─── */
  footer {
    background: var(--crimson-dark); color: rgba(255,255,255,0.35);
    padding: 2rem 3rem; text-align: center;
    font-size: 0.85rem; line-height: 1.8;
    border-top: 0.5px solid rgba(255,255,255,0.08);
  }
  footer span { color: rgba(255,255,255,0.65); }

  /* ─── DIVIDER ─── */
  .crimson-divider {
    height: 1px;
    background: linear-gradient(to left, transparent, var(--crimson), transparent);
  }

  @media (max-width: 900px) {
    nav { padding: 1rem 1.5rem; }
    .nav-links { display: none; }
    .hero-stats { gap: 2rem; }
    .about-layout, .vision-layout { grid-template-columns: 1fr; gap: 2.5rem; }
    .products-grid { grid-template-columns: 1fr; }
    .schedule-row, .schedule-header-row { grid-template-columns: 90px 1fr; }
    .hours-card { flex-direction: column; gap: 1rem; text-align: center; }
  }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">
    <img src="{{ asset('assets/img/favicon.png') }}" alt="شعار الياسر">
    <div>
      <div class="nav-logo-text">مركز الياسر</div>
      <div class="nav-logo-sub">التجاري — قدسيا، دمشق</div>
    </div>
  </div>
  <ul class="nav-links">
    <li><a href="#about">من نحن</a></li>
    <li><a href="#products">منتجاتنا</a></li>
    <li><a href="#vision">رؤيتنا</a></li>
    <li><a href="#schedule">التوصيل</a></li>
  </ul>
  <button class="nav-btn" onclick="alert('تطبيق الياسر — قريباً!')">⬇ تحميل التطبيق</button>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg-circle c1"></div>
  <div class="hero-bg-circle c2"></div>
  <div class="hero-pattern"></div>
  <div class="hero-content">
    <div class="hero-logo">
      <img src="{{ asset('assets/img/logo-ct-dark.png') }}" alt="شعار الياسر التجاري">
    </div>
    <div class="hero-badge">من قلب قدسيا — دمشق الشام</div>
    <h1>مركز <span class="accent">الياسر</span> التجاري</h1>
    <p class="hero-sub">
      شريكك الموثوق في المواد الغذائية والمنظفات — بجودة عالية، وأسعار تنافسية، وتوصيل يصل إليك.
    </p>
    <div class="hero-actions">
      <button class="btn-primary" onclick="alert('تطبيق الياسر — قريباً!')">⬇ حمّل التطبيق</button>
      <a href="#about" class="btn-outline">تعرّف علينا</a>
    </div>
    <div class="hero-stats">
      <div class="stat">
        <div class="stat-num">٣٠+</div>
        <div class="stat-label">عاماً من الخبرة</div>
      </div>
      <div class="stat">
        <div class="stat-num">٦</div>
        <div class="stat-label">أيام توصيل أسبوعياً</div>
      </div>
      <div class="stat">
        <div class="stat-num">١٠+</div>
        <div class="stat-label">منطقة تحت الخدمة</div>
      </div>
    </div>
  </div>
</section>

<div class="crimson-divider"></div>

<!-- ABOUT -->
<section id="about" class="section-pad about-bg">
  <div class="container">
    <div class="about-layout">
      <div>
        <div class="section-label">من نحن</div>
        <h2 class="section-title">شراكة تبنى على الثقة منذ ٣٠ عاماً</h2>
        <p class="section-body">
          منذ انطلاقتنا في دمشق الشام من قلب مدينة قدسيا، وضعنا في مركز الياسر التجاري هدفاً واحداً نصب أعيننا: أن نكون الشريك الموثوق لكل بيت ومحل تجاري في المنطقة. نحن متخصصون في بيع وتوزيع المواد الغذائية والمنظفات بأسلوب يجمع بين الجودة العالية والأسعار التنافسية، سواءً لزبائن المفرق أو لتجار الجملة.
        </p>
      </div>
      <div>
        <div class="about-card">
          <div class="about-card-num">٣٠</div>
          <div class="about-card-sub">عاماً من الثقة والخدمة المتواصلة</div>
          <div class="about-card-divider"></div>
          <div class="about-card-desc">
            سلسلة توريد متكاملة تضمن استمرارية توفر البضائع وجودة لا تُساوم عليها في كل طلبية.
          </div>
          <div class="about-card-grid">
            <div class="about-mini">
              <div class="about-mini-val">مفرق</div>
              <div class="about-mini-label">للأفراد والمنازل</div>
            </div>
            <div class="about-mini">
              <div class="about-mini-val">جملة</div>
              <div class="about-mini-label">للتجار والمحلات</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="crimson-divider"></div>

<!-- PRODUCTS -->
<section id="products" class="section-pad products-bg">
  <div class="container">
    <div class="section-label">ماذا نقدم</div>
    <h2 class="section-title">تشكيلة متكاملة لكل احتياجاتك</h2>
    <div class="products-grid">
      <div class="product-card">
        <div class="product-icon">🌾</div>
        <div class="product-name">المواد الغذائية</div>
        <div class="product-desc">أجود أنواع الأرز، السكر، البقوليات، والبهارات ذات النكهة الأصيلة.</div>
        <div class="product-tags">
          <span class="tag">أرز</span>
          <span class="tag">سكر</span>
          <span class="tag">بقوليات</span>
          <span class="tag">بهارات</span>
        </div>
      </div>
      <div class="product-card">
        <div class="product-icon">🥫</div>
        <div class="product-name">المعلبات والمشروبات</div>
        <div class="product-desc">تشكيلة متنوعة من المعلبات، المشروبات الغازية، المياه المعدنية، والقهوة والشاي.</div>
        <div class="product-tags">
          <span class="tag">معلبات</span>
          <span class="tag">مشروبات</span>
          <span class="tag">مياه معدنية</span>
          <span class="tag">قهوة وشاي</span>
        </div>
      </div>
      <div class="product-card">
        <div class="product-icon">🧴</div>
        <div class="product-name">المنظفات</div>
        <div class="product-desc">كافة مستلزمات العناية بالمنزل والنظافة الشخصية من كبرى العلامات التجارية.</div>
        <div class="product-tags">
          <span class="tag">عناية بالمنزل</span>
          <span class="tag">نظافة شخصية</span>
          <span class="tag">علامات كبرى</span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="crimson-divider"></div>

<!-- VISION -->
<section id="vision" class="section-pad vision-bg">
  <div class="container">
    <div class="vision-layout">
      <div>
        <div class="section-label">رؤيتنا</div>
        <h2 class="section-title">نطمح لأن نكون في كل حي وكل منزل</h2>
        <p class="section-body" style="margin-bottom: 2rem;">
          نطمح لتوسيع دائرة خدماتنا لتشمل أكبر عدد من المناطق، مع الالتزام التام بتأمين احتياجات زبائننا في أسرع وقت وأفضل حال.
        </p>
        <div class="pillars">
          <div class="pillar">
            <div class="pillar-dot"></div>
            <div class="pillar-text"><strong>خبرة ثلاثة عقود</strong> — مسيرة طويلة من العمل المتواصل مع أبناء المنطقة وبناء علاقات ثقة راسخة.</div>
          </div>
          <div class="pillar">
            <div class="pillar-dot"></div>
            <div class="pillar-text"><strong>سلسلة توريد متكاملة</strong> — تضمن استمرارية توفر البضائع في كل وقت وفي أفضل حال.</div>
          </div>
          <div class="pillar">
            <div class="pillar-dot"></div>
            <div class="pillar-text"><strong>تواصل مستمر مع الزبائن</strong> — عبر التطبيق الإلكتروني وسيارات التوزيع المنتشرة في المنطقة.</div>
          </div>
          <div class="pillar">
            <div class="pillar-dot"></div>
            <div class="pillar-text"><strong>توسع مستمر</strong> — نسعى لخدمة أكبر عدد من المناطق المحيطة بالمركز يوماً بعد يوم.</div>
          </div>
        </div>
      </div>
      <div class="vision-aside">
        <div class="vision-aside-label">بالأرقام</div>
        <div class="vision-aside-stat">
          <div class="vision-aside-num">٣٠ عاماً</div>
          <div class="vision-aside-desc">من الخبرة المتراكمة في السوق</div>
        </div>
        <div class="vision-aside-stat">
          <div class="vision-aside-num">٦ أيام</div>
          <div class="vision-aside-desc">توصيل أسبوعي للمناطق</div>
        </div>
        <div class="vision-aside-stat">
          <div class="vision-aside-num">١٠+</div>
          <div class="vision-aside-desc">منطقة تحت الخدمة حالياً</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="crimson-divider"></div>

<!-- SCHEDULE -->
<section id="schedule" class="section-pad schedule-bg">
  <div class="container">
    <div class="section-label">ساعات العمل والتوصيل</div>
    <h2 class="section-title">نوصّل إليك كل أسبوع</h2>

    <div class="hours-card">
      <div class="hours-icon">🕗</div>
      <div>
        <div class="hours-label">ساعات العمل الرسمية</div>
        <div class="hours-value">السبت — الخميس &nbsp;<span>من ٨ صباحاً حتى ٦ مساءً</span></div>
      </div>
    </div>

    <div class="schedule-table-wrap">
      <div class="schedule-header-row">
        <div>اليوم</div>
        <div>مناطق التوصيل</div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">السبت</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">دمر — السيل</span>
          <span class="area-chip">الشرقية</span>
          <span class="area-chip">جبل الرز</span>
        </div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">الأحد</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">ضاحية قدسيا</span>
        </div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">الإثنين</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">وادي المشاريع</span>
        </div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">الثلاثاء</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">دمر — السيل</span>
          <span class="area-chip">الشرقية</span>
          <span class="area-chip">جبل الرز</span>
        </div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">الأربعاء</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">دمر البلد</span>
          <span class="area-chip">رناكسة</span>
          <span class="area-chip">ضاحية قدسيا</span>
        </div>
      </div>
      <div class="schedule-row">
        <div class="schedule-day">الخميس</div>
        <div class="schedule-areas">
          <span class="area-chip">الهامة</span>
          <span class="area-chip">قدسيا</span>
          <span class="area-chip">وادي المشاريع</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DOWNLOAD CTA -->
<section class="download-section">
  <div class="container">
    <div class="section-label">تطبيق الياسر</div>
    <h2>اطلب بضغطة واحدة</h2>
    <p>حمّل تطبيق مركز الياسر التجاري واستمتع بتجربة طلب سهلة وتوصيل سريع إلى بابك.</p>
    <button class="download-btn" onclick="alert('تطبيق الياسر — قريباً!')">⬇ تحميل التطبيق الآن</button>
  </div>
</section>

<footer>
  <p>© ٢٠٢٤ <span>مركز الياسر التجاري</span> — قدسيا، دمشق الشام. جميع الحقوق محفوظة.</p>
</footer>

</body>
</html>
