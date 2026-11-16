<?php $pageTitle = $pageTitle ?? APP_NAME; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="SMEIC helps admins, students, parents, and canteen staff manage university canteen orders, wallets, and notifications in one place.">
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="landing-page">
    <div class="landing-bg"></div>
    <div class="landing-orb landing-orb-a"></div>
    <div class="landing-orb landing-orb-b"></div>

    <header class="landing-nav">
        <a href="<?= e(url('index.php')) ?>" class="landing-brand" aria-label="SMEIC home">
            <span class="landing-mark">SC</span>
            <span>
                <span class="landing-brand-kicker">University canteen platform</span>
                <span class="landing-brand-name"><?= e(APP_NAME) ?></span>
            </span>
        </a>

        <div class="landing-nav-actions">
            <a href="#features" class="btn btn-ghost btn-sm landing-nav-link">Explore</a>
            <a href="<?= e(url('index.php?action=login')) ?>" class="btn btn-primary btn-sm landing-login-btn">Login</a>
        </div>
    </header>

    <main class="landing-shell">
        <section class="landing-hero">
            <div class="landing-copy">
                <span class="landing-eyebrow">Unified canteen operations</span>
                <h1>Everything a university canteen needs, in one clean dashboard.</h1>
                <p class="landing-lead">
                    SMEIC connects admins, students, parents, and canteen staff with order tracking,
                    wallet transfers, menu management, and instant notifications built for daily university use.
                </p>

                <div class="landing-actions">
                    <a href="<?= e(url('index.php?action=login')) ?>" class="btn btn-primary btn-lg">Login to continue</a>
                    <a href="#roles" class="btn btn-secondary btn-lg">See user roles</a>
                </div>

                <div class="landing-mini-cards">
                    <div class="mini-card">
                        <strong>Simple start</strong>
                        <p>Open the landing page, choose login, and use your assigned role to enter the system.</p>
                    </div>
                    <div class="mini-card">
                        <strong>Fast actions</strong>
                        <p>Students order, parents top up wallets, and canteen staff process pickups from one flow.</p>
                    </div>
                </div>

                <div class="landing-stats">
                    <div class="landing-stat">
                        <strong>4</strong>
                        <span>Roles supported</span>
                    </div>
                    <div class="landing-stat">
                        <strong>3 min</strong>
                        <span>Pickup reminder flow</span>
                    </div>
                    <div class="landing-stat">
                        <strong>1</strong>
                        <span>Wallet and order system</span>
                    </div>
                </div>
            </div>

            <div class="landing-visual">
                <div class="smeic-visual">
                    <div class="smeic-visual-head">
                        <div>
                            <span class="hero-pill">University canteen at a glance</span>
                            <h2>Order, prepare, pickup.</h2>
                        </div>
                        <span class="hero-chip">Simple flow</span>
                    </div>

                    <div class="smeic-flow">
                        <div class="smeic-step smeic-step-order">
                            <span class="smeic-step-mark">1</span>
                            <strong>Student orders</strong>
                            <p>Choose food, add to cart, and pay from wallet balance.</p>
                        </div>
                        <div class="smeic-flow-line">
                            <span></span>
                        </div>
                        <div class="smeic-step smeic-step-kitchen">
                            <span class="smeic-step-mark">2</span>
                            <strong>Canteen prepares</strong>
                            <p>Staff accept the order, prepare it, and mark it ready.</p>
                        </div>
                        <div class="smeic-flow-line">
                            <span></span>
                        </div>
                        <div class="smeic-step smeic-step-pickup">
                            <span class="smeic-step-mark">3</span>
                            <strong>Student picks up</strong>
                            <p>The order is collected quickly with live status tracking.</p>
                        </div>
                    </div>

                    <div class="smeic-footnote">
                        <span>SMEIC keeps the whole canteen process in one place.</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="landing-section">
            <div class="section-heading">
                <span class="section-kicker">Why SMEIC</span>
                <h2>Built for the actual university canteen workflow.</h2>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <div class="feature-icon">01</div>
                    <h3>Admin control</h3>
                    <p>Track students, orders, revenue, top-ups, audit logs, and reports from one dashboard.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">02</div>
                    <h3>Student ordering</h3>
                    <p>Browse menu items, manage cart, check out from wallet balance, and follow order status live.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">03</div>
                    <h3>Parent wallet</h3>
                    <p>Transfer funds to children, review transactions, and receive notifications without extra steps.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">04</div>
                    <h3>Canteen speed</h3>
                    <p>Accept orders, mark ready items, manage specials, and scan pickup codes with minimal clicks.</p>
                </article>
            </div>
        </section>

        <section id="roles" class="landing-section landing-role-section">
            <div class="section-heading">
                <span class="section-kicker">Roles</span>
                <h2>One login portal, four focused experiences.</h2>
            </div>
            <div class="role-showcase">
                <div class="role-tile role-admin">
                    <span>Admin</span>
                    <p>Oversight, reporting, and system management.</p>
                </div>
                <div class="role-tile role-student">
                    <span>Student</span>
                    <p>Menu, cart, checkout, and pickup tracking.</p>
                </div>
                <div class="role-tile role-parent">
                    <span>Parent</span>
                    <p>Top-ups, transfers, preferences, and child orders.</p>
                </div>
                <div class="role-tile role-canteen">
                    <span>Canteen</span>
                    <p>Orders, menu updates, earnings, and pickups.</p>
                </div>
            </div>
        </section>

        <section class="landing-section landing-about" id="about">
            <div class="info-panel">
                <span class="section-kicker">About this system</span>
                <h2>SMEIC keeps university canteen operations organized and traceable.</h2>
                <p>
                    It replaces paper slips, manual wallet updates, and scattered messages with a single web app.
                    Each role sees only what it needs: admins manage the system, students order food, parents move money,
                    and canteen staff keep the kitchen moving.
                </p>
                <ul class="info-list">
                    <li><strong>A</strong><span>Admin dashboards show revenue, orders, students, reports, and audit logs.</span></li>
                    <li><strong>S</strong><span>Students use wallet balance to place orders and track status in real time.</span></li>
                    <li><strong>P</strong><span>Parents can top up and transfer funds while monitoring child activity.</span></li>
                    <li><strong>C</strong><span>Canteen staff can accept, prepare, mark ready, and complete orders quickly.</span></li>
                </ul>
            </div>
            <div class="step-panel">
                <span class="section-kicker">How it works</span>
                <h2>From login to pickup in four clear steps.</h2>
                <ul class="step-list">
                    <li><strong>1</strong><span>Choose your role and login from the button in the top corner.</span></li>
                    <li><strong>2</strong><span>Browse menu items, review your balance, and place an order.</span></li>
                    <li><strong>3</strong><span>The canteen accepts the order, prepares it, and marks it ready.</span></li>
                    <li><strong>4</strong><span>The student picks it up using the order flow or token verification.</span></li>
                </ul>
            </div>
        </section>

        <section class="landing-section landing-cta">
            <div>
                <span class="section-kicker">Ready to begin</span>
                <h2>Open the login page and continue with your role.</h2>
                <p>Use the secure login button in the corner to access your dashboard.</p>
            </div>
            <a href="<?= e(url('index.php?action=login')) ?>" class="btn btn-primary btn-lg">Login now</a>
        </section>
    </main>
</body>
</html>