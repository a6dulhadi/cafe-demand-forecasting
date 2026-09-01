<?php
require_once "../includes/auth.php";
requireRole("staff");

$pageTitle = "Staff Dashboard";

$totalMenu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu_items WHERE status='active'"))['total'] ?? 0;
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_records"))['total'] ?? 0;
$totalForecasts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM forecast_results"))['total'] ?? 0;
$totalIngredients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM ingredients"))['total'] ?? 0;

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Staff Dashboard</h1>
        <p>View QT Cafe menu trends, reports, and forecasting summaries.</p>
    </div>
    <div class="user-info">
        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong><br>
        <span class="badge"><?php echo strtoupper(htmlspecialchars($_SESSION['role'])); ?></span>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h3>Active Menu Items</h3>
        <div class="number"><?php echo $totalMenu; ?></div>
    </div>

    <div class="card">
        <h3>Sales Records</h3>
        <div class="number"><?php echo $totalSales; ?></div>
    </div>

    <div class="card">
        <h3>Forecast Results</h3>
        <div class="number"><?php echo $totalForecasts; ?></div>
    </div>

    <div class="card">
        <h3>Ingredients</h3>
        <div class="number"><?php echo $totalIngredients; ?></div>
    </div>
</div>

<div class="panel">
    <h2>Staff Overview</h2>
    <p>
        The staff role is designed for viewing dashboard information, reports,
        and menu trend summaries without modifying system data.
    </p>
</div>

<div class="panel">
    <h2>Staff Quick Actions</h2>

    <div class="quick-links">
        <div class="quick-link">
            <h4>View Dashboard</h4>
            <p>Monitor overall menu demand and sales trend summaries.</p>
        </div>

        <div class="quick-link">
            <h4>View Reports</h4>
            <p>Access forecasting and menu performance reports.</p>
        </div>

        
</div>

<style>
    body {
        background: #FFB3D9 !important;
    }
</style>

<?php include "../includes/footer.php"; ?>