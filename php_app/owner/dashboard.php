<?php
require_once "../includes/auth.php";
requireRole("owner");

$pageTitle = "Owner Dashboard";

$totalUploads  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_uploads"))['total'] ?? 0;
$totalSales    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_records"))['total'] ?? 0;
$totalForecasts= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM forecast_results"))['total'] ?? 0;

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Owner Dashboard</h1>
        <p>Upload sales data and review forecasting reports for QT Cafe.</p>
    </div>
    <div class="user-info">
        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong><br>
        <span class="badge"><?php echo strtoupper($_SESSION['role']); ?></span>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h3>Uploaded Datasets</h3>
        <div class="number"><?php echo $totalUploads; ?></div>
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
        <h3>Reports</h3>
        <div class="number"><?php echo $totalForecasts > 0 ? 1 : 0; ?></div>
    </div>
</div>

<div class="panel">
    <h2>Owner Overview</h2>
    <p>The owner role focuses on uploading historical sales records and reviewing
    system-generated reports. Uploaded sales data is used to support menu demand
    prediction and business planning.</p>
</div>

<div class="panel">
    <h2>Owner Quick Actions</h2>
    <div class="quick-links">
        <a href="<?php echo BASE_URL; ?>owner/upload_sales.php" class="quick-link">
            <h4>📤 Upload Sales Data</h4>
            <p>Upload CSV files containing QT Cafe historical sales records.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>owner/reports.php" class="quick-link">
            <h4>📄 View Reports</h4>
            <p>Review demand forecasting reports and menu sales summaries.</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/export_report.php" class="quick-link">
            <h4>🖨️ Download PDF Report</h4>
            <p>Export forecasting results as a printable PDF report.</p>
        </a>
    </div>
</div>

<style>
    body {
        background: #E5F5E0 !important;
    }
</style>

<?php include "../includes/footer.php"; ?>