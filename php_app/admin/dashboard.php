<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Admin Dashboard";

$totalMenu       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu_items"))['total'] ?? 0;
$totalIngredients= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM ingredients"))['total'] ?? 0;
$totalSales      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_records"))['total'] ?? 0;
$totalForecasts  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM forecast_results"))['total'] ?? 0;

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Admin Dashboard</h1>
        <p>Manage QT Cafe forecasting operations and analytics.</p>
    </div>
    <div class="user-info">
        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong><br>
        <span class="badge"><?php echo strtoupper($_SESSION['role']); ?></span>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h3>Total Menu Items</h3>
        <div class="number"><?php echo $totalMenu; ?></div>
    </div>
    <div class="card">
        <h3>Total Ingredients</h3>
        <div class="number"><?php echo $totalIngredients; ?></div>
    </div>
    <div class="card">
        <h3>Sales Records</h3>
        <div class="number"><?php echo $totalSales; ?></div>
    </div>
    <div class="card">
        <h3>Forecast Results</h3>
        <div class="number"><?php echo $totalForecasts; ?></div>
    </div>
</div>

<div class="panel">
    <h2>System Overview</h2>
    <p>QT Cafe Forecasting System helps cafe users upload historical sales data,
    compare machine learning models, forecast future menu demand, analyse menu
    popularity, estimate ingredient requirements, and export reports.</p>
</div>

<div class="panel">
    <h2>Admin Quick Actions</h2>
    <div class="quick-links">
        <a href="<?php echo BASE_URL; ?>admin/menu_management.php" class="quick-link">
            <h4>🍽️ Menu Management</h4>
            <p>Add, edit, and manage QT Cafe menu items.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/ingredient_management.php" class="quick-link">
            <h4>🧂 Ingredients & Recipes</h4>
            <p>Manage ingredient usage rules for each menu item.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/demand_prediction.php" class="quick-link">
            <h4>📈 Demand Prediction</h4>
            <p>Generate forecast results using machine learning models.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/model_comparison.php" class="quick-link">
            <h4>🤖 Model Comparison</h4>
            <p>Compare Decision Tree, Random Forest, and Linear Regression.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/ingredient_estimation.php" class="quick-link">
            <h4>🛒 Ingredient Estimation</h4>
            <p>Estimate ingredient needs based on predicted demand.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/reports.php" class="quick-link">
            <h4>📄 Reports</h4>
            <p>View and export forecasting reports.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/user_management.php" class="quick-link">
            <h4>👥 User Management</h4>
            <p>Add and manage Owner and Staff accounts.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/menu_trend.php" class="quick-link">
            <h4>📊 Menu Trend</h4>
            <p>Visual analysis of monthly sales trends.</p>
        </a>
    </div>
</div>

<style>
.quick-link {
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.15s, box-shadow 0.15s;
}
.quick-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(80,50,20,0.13);
}
</style>

<?php include "../includes/footer.php"; ?>