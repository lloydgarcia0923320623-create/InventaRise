<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio | InventaRise</title>
    <style>
        :root {
            --bg: #061521;
            --bg-2: #0f2237;
            --panel: rgba(19, 38, 53, 0.9);
            --primary: #00c2ff;
            --accent: #7dd3fc;
            --text: #eaf7ff;
            --muted: #a9cfea;
            --success: #34d399;
            --warning: #fbbf24;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; font-family: Arial, sans-serif; background: linear-gradient(135deg, var(--bg), var(--bg-2)); color: var(--text);
        }
        a { color: inherit; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center; padding: 18px 0; border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; }
        .brand h1 { margin: 0; font-size: 1.6rem; }
        .nav { display: flex; gap: 12px; flex-wrap: wrap; }
        .nav a, .button {
            text-decoration: none; border: none; border-radius: 10px; padding: 10px 16px; cursor: pointer; font-weight: 700; display: inline-block;
        }
        .nav a { background: rgba(255,255,255,0.06); color: var(--text); }
        .button.primary { background: var(--primary); color: #021823; }
        .button.secondary { background: rgba(255,255,255,0.04); color: var(--text); }
        .hero {
            display: grid; grid-template-columns: 1.4fr 1fr; gap: 26px; align-items: center; padding: 50px 0 25px;
        }
        .eyebrow {
            display: inline-block; background: rgba(0,194,255,0.1); color: var(--accent); border: 1px solid rgba(125,211,252,0.2);
            padding: 7px 12px; border-radius: 999px; font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 18px;
        }
        h2 { font-size: clamp(2.2rem, 4vw, 4rem); line-height: 1.1; margin: 0 0 18px; }
        .lead { font-size: 1.1rem; color: var(--muted); max-width: 60ch; line-height: 1.7; }
        .hero-actions { display: flex; gap: 16px; margin-top: 26px; flex-wrap: wrap; }
        .stats { display: grid; grid-template-columns: repeat(2, minmax(170px, 1fr)); gap: 18px; margin-top: 28px; }
        .stat {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 18px; min-height: 120px;
        }
        .stat strong { font-size: 2rem; display: block; margin-bottom: 8px; }
        .panel {
            background: var(--panel); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.18);
        }
        .feature-grid, .stack-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-top: 24px;
        }
        .feature, .stack-item {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 20px;
        }
        .feature h3, .stack-item h3 { margin: 0 0 10px; }
        .feature p, .stack-item p, .list li { color: var(--muted); line-height: 1.7; }
        .section { padding-top: 34px; }
        .section h3 { font-size: clamp(1.7rem, 3vw, 2.5rem); margin-bottom: 0; }
        .list { margin: 18px 0 0; padding-left: 18px; }
        .badge-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
        .badge { background: rgba(0,194,255,0.1); color: var(--accent); border: 1px solid rgba(0,194,255,0.3); border-radius: 999px; padding: 7px 12px; font-size: 0.82rem; }
        .two-col { display: grid; grid-template-columns: 1.1fr 1fr; gap: 24px; }
        .highlight { color: var(--primary); }
        @media (max-width: 820px) { .hero, .two-col { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="topbar">
            <div class="brand">
                <img src="images/Logo.png" alt="InventaRise Logo">
                <h1>InventaRise</h1>
            </div>
            <nav class="nav">
                <a href="index.php">Home</a>
                <?php if ($loggedIn): ?>
                    <a href="home.php">Dashboard</a>
                    <a href="services.php">Services</a>
                <?php else: ?>
                    <a href="LogIn.php">Login</a>
                <?php endif; ?>
            </nav>
        </header>

        <section class="hero">
            <div>
                <div class="eyebrow">Portfolio Project</div>
                <h2>Inventory, delivery, and supplier operations built for modern micro-retail businesses.</h2>
                <p class="lead">InventaRise is a PHP + SQLite web application created to manage inventory, monitor delivery statuses, forecast low-stock risk, and keep supplier records organized in a single streamlined dashboard.</p>
                <div class="hero-actions">
                    <?php if ($loggedIn): ?>
                        <a class="button primary" href="services.php">Open Dashboard</a>
                    <?php else: ?>
                        <a class="button primary" href="LogIn.php">Open Demo</a>
                    <?php endif; ?>
                    <a class="button secondary" href="index.php">Project Landing Page</a>
                </div>
                <div class="stats">
                    <div class="stat"><strong>4</strong> core modules</div>
                    <div class="stat"><strong>1</strong> secure admin login</div>
                    <div class="stat"><strong>100%</strong> SQLite data storage</div>
                    <div class="stat"><strong>CSV</strong> export support</div>
                </div>
            </div>
            <div class="panel">
                <h3 style="margin-top:0;">Project Snapshot</h3>
                <ul class="list">
                    <li>Inventory tracking with quantity updates and low-stock warnings</li>
                    <li>Delivery scheduling and status monitoring</li>
                    <li>Supplier directory for procurement visibility</li>
                    <li>Analytics charts for monthly delivery, revenue, and stock trends</li>
                    <li>Clean dashboard UI for portfolio presentation</li>
                </ul>
                <div class="badge-row">
                    <span class="badge">PHP</span>
                    <span class="badge">SQLite</span>
                    <span class="badge">Chart.js</span>
                    <span class="badge">Responsive UI</span>
                </div>
            </div>
        </section>

        <section class="section">
            <h3>Key Features</h3>
            <div class="feature-grid">
                <div class="feature">
                    <h3>Inventory Control</h3>
                    <p>Add stock, adjust quantities, and flag items below reorder thresholds before they become a supply problem.</p>
                </div>
                <div class="feature">
                    <h3>Delivery Tracking</h3>
                    <p>Manage order progress from pending to in transit, delivered, or delayed with a single operational view.</p>
                </div>
                <div class="feature">
                    <h3>Supplier Management</h3>
                    <p>Track vendor details, contact info, and notes so procurement relationships stay organized and scalable.</p>
                </div>
                <div class="feature">
                    <h3>Business Insights</h3>
                    <p>Use analytics to review delivery frequency, revenue, and low-stock risk across product categories.</p>
                </div>
            </div>
        </section>

        <section class="section two-col">
            <div class="panel">
                <h3>Why this project stands out</h3>
                <ul class="list">
                    <li>Built around a practical real-world retail workflow instead of a generic demo.</li>
                    <li>Uses a lightweight SQLite backend for easy setup and portability.</li>
                    <li>Includes user authentication, dashboard navigation, and operational reporting.</li>
                    <li>Ready for portfolio presentation because it feels like a usable business app, not just a CRUD exercise.</li>
                </ul>
            </div>
            <div class="panel">
                <h3>Demo Access</h3>
                <ul class="list">
                    <li><span class="highlight">Username:</span> admin</li>
                    <li><span class="highlight">Password:</span> Admin123!</li>
                    <li><span class="highlight">Role:</span> Administrator</li>
                </ul>
                <p style="color: var(--muted); margin-top: 18px;">Use the dashboard to preview operations, low-stock alerts, delivery tracking, and supplier management workflow.</p>
            </div>
        </section>

        <section class="section">
            <h3>Technology Stack</h3>
            <div class="stack-grid">
                <div class="stack-item">
                    <h3>Application Layer</h3>
                    <p>PHP for authentication, data operations, routing, and dashboard logic.</p>
                </div>
                <div class="stack-item">
                    <h3>Database</h3>
                    <p>SQLite store with tables for users, inventory, deliveries, and suppliers.</p>
                </div>
                <div class="stack-item">
                    <h3>Frontend</h3>
                    <p>HTML, CSS, and JavaScript for a responsive, dashboard-style interface.</p>
                </div>
                <div class="stack-item">
                    <h3>Reporting</h3>
                    <p>Chart.js-powered analytics to present delivery and revenue performance.</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
