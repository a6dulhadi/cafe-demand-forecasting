<?php
require_once "includes/auth.php";

// If already logged in, go to dashboard
if (isLoggedIn()) {
    redirectByRole($_SESSION['role']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QT Cafe - Demand Forecasting System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #3b2417 0%, #7a4520 50%, #b8793b 100%);
            display: flex;
            flex-direction: column;
        }

        /* NAV */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 48px;
            background: rgba(0,0,0,0.25);
        }

        .nav-logo { color: #fff; font-size: 22px; font-weight: bold; letter-spacing: 1px; }
        .nav-logo span { color: #f5c87a; }

        .nav-links { display: flex; gap: 12px; }

        .btn-nav-login {
            padding: 9px 24px;
            border: 2px solid #fff;
            background: transparent;
            color: #fff;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-nav-login:hover { background: #fff; color: #3b2417; }

        .btn-nav-register {
            padding: 9px 24px;
            border: 2px solid #f5c87a;
            background: #f5c87a;
            color: #3b2417;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-nav-register:hover { background: #e6b85e; border-color: #e6b85e; }

        /* HERO */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 24px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(245,200,122,0.2);
            border: 1px solid #f5c87a;
            color: #f5c87a;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 13px;
            letter-spacing: 1px;
            margin-bottom: 24px;
        }

        .hero h1 {
            color: #fff;
            font-size: 52px;
            font-weight: bold;
            line-height: 1.15;
            margin-bottom: 10px;
        }

        .hero h1 span { color: #f5c87a; }

        .hero p {
            color: rgba(255,255,255,0.82);
            font-size: 18px;
            max-width: 560px;
            margin: 16px auto 40px;
            line-height: 1.6;
        }

        .hero-buttons { display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; }

        .btn-hero-primary {
            padding: 14px 36px;
            background: #f5c87a;
            color: #3b2417;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-hero-primary:hover { background: #e6b85e; transform: translateY(-2px); }

        .btn-hero-secondary {
            padding: 14px 36px;
            background: rgba(255,255,255,0.12);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.22); transform: translateY(-2px); }

        /* FEATURES */
        .features {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px 40px 60px;
        }

        .feature-card {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            padding: 24px 28px;
            width: 220px;
            text-align: center;
            color: #fff;
        }

        .feature-card .icon { font-size: 32px; margin-bottom: 10px; }
        .feature-card h3 { font-size: 15px; margin-bottom: 6px; color: #f5c87a; }
        .feature-card p { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.5; }

        /* FOOTER */
        footer {
            text-align: center;
            padding: 18px;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-logo">
        <img src="assets/images/logo.jpg" alt="QT Cafe Logo" style="height: 100px; margin-right: 80px; vertical-align: middle;">
        QT <span>Cafe</span>
    </div>
    <div class="nav-links">
        <a href="register.php" class="btn-nav-register">Register</a>
        <a href="login.php" class="btn-nav-login">Login</a>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <div class="hero-badge">🍵 QT Cafe · LOT G-5, Jalan 1a/6, Taman Setapak Indah, 53100 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur</div>
    <h1>Smart Menu<br><span>Demand Forecasting</span></h1>
    <p>A machine learning–based decision support system to forecast café menu demand, analyse trends, and plan ingredients efficiently.</p>
    <div class="hero-buttons">
        <a href="register.php" class="btn-hero-primary">Register</a>
        <a href="login.php" class="btn-hero-secondary">Login to System</a>
    </div>
</div>

<!-- FEATURES -->
<div class="features">
    <div class="feature-card">
        <div class="icon">📈</div>
        <h3>Demand Prediction</h3>
        <p>ML-powered forecasts based on historical sales data.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🍽️</div>
        <h3>Menu Trend Analysis</h3>
        <p>Identify top-performing menu items over time.</p>
    </div>
    <div class="feature-card">
        <div class="icon">🛒</div>
        <h3>Ingredient Estimation</h3>
        <p>Auto-calculate ingredient needs from predicted demand.</p>
    </div>
    <div class="feature-card">
        <div class="icon">📊</div>
        <h3>Reports & Export</h3>
        <p>Download PDF reports for business planning.</p>
    </div>
</div>

<!-- FOOTER -->
<footer>
    &copy; <?php echo date("Y"); ?> QT Cafe Demand Forecasting System &nbsp;·&nbsp; Abdul Hadi Bin Haron &nbsp;·&nbsp; Bachelor of Computer Science
</footer>

</body>
</html>