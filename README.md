# InventaRise

<p align="center">
  <img src="web/my_website/images/Logo.png" alt="InventaRise Logo" width="120" />
</p>

<p align="center">
  <a href="web/my_website/portfolio.php">Project Showcase</a> ·
  <a href="web/my_website/">Application Source</a>
</p>

<p align="center">
  <a href="https://render.com/deploy?repo=https://github.com/lloydgarcia0923320623-create/InventaRise"><img src="https://render.com/images/deploy-to-render-button.svg" alt="Deploy InventaRise to Render" /></a>
</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white" />
  <img alt="SQLite" src="https://img.shields.io/badge/SQLite-Database-003B57?logo=sqlite&logoColor=white" />
  <img alt="Frontend" src="https://img.shields.io/badge/Frontend-HTML%2FCSS%2FJS-0A84FF" />
  <img alt="Charts" src="https://img.shields.io/badge/Charts-Chart.js-F58220" />
</p>

InventaRise is a polished inventory, delivery, and supplier management system built for micro-retail operations. It helps businesses track stock levels, monitor delivery progress, manage supplier records, and view operational insights from a single dashboard.

## Why this project is in my portfolio
This project demonstrates practical full-stack web development skills in a business context. It combines backend logic, database design, authentication, and polished UI work in a way that feels close to a real operations dashboard for a startup or retail business.

## What I built
- Admin authentication and secure session-based login
- Inventory tracking with quantity updates and low-stock warnings
- Delivery scheduling and status monitoring
- Supplier management and contact tracking
- Reporting analytics for smart operational decisions
- CSV export for stock reporting
- Modern dashboard-style interface for portfolio presentation

## Business use case
InventaRise is designed for small retail and supply-chain environments where teams need to:
- monitor stock movement in real time
- prepare for restocking before shortages happen
- coordinate deliveries and drivers
- keep supplier records organized
- review performance with quick insights and reports

## Tech stack
- PHP
- SQLite
- HTML/CSS/JavaScript
- Chart.js
- Responsive dashboard UI

## Project structure
- `web/my_website/` — main application files
- `web/my_website/portfolio.php` — portfolio showcase page
- `web/my_website/helpers/` — database and business logic helpers
- `web/my_website/css/` — styling for the app UI
- `README.md` — GitHub portfolio summary

## Demo credentials
- Username: `admin`
- Password: `Admin123!`

These credentials are for a local demo database only. Do not use them on a publicly hosted deployment.

## Run locally
1. Install PHP 8.0 or newer with the PDO SQLite extension enabled.
2. From the project folder, run `web\start.bat` on Windows. The included PHP runtime is intentionally excluded from GitHub; the script uses it if present, otherwise it uses PHP from your PATH.
3. Open `http://localhost:8000/`.

Example:
```bash
php -S localhost:8000 -t web/my_website
```

The SQLite databases are created automatically in `web/my_website/db/` the first time the app runs. Local database files are excluded from version control.

## Deploy a demo
Use the Deploy to Render button above and approve the blueprint in your Render account. The free demo uses SQLite on ephemeral storage: records may reset when the service restarts or redeploys, and the free instance may take a short time to wake. Use persistent storage and production-grade credentials before using it for real business data.

## Portfolio highlights
- Real-world operational dashboard experience
- Database-driven application with secure authentication
- UI design work focused on clarity, motion, and presentation quality
- Business-focused feature set suitable for a software portfolio

## Notes
This project is intended to feel like a real-world SaaS-style operations tool rather than a simple demo. It is suitable for GitHub portfolio presentation, academic submission, or stakeholder review.
