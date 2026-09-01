<?php
$currentRole = $_SESSION['role'] ?? "";
$currentPage = basename($_SERVER['PHP_SELF']);

function activeClass($page, $current) {
    return $page === $current ? " active" : "";
}
?>

<div class="sidebar">
    <div class="logo-box">
    <img src="<?php echo BASE_URL; ?>assets/images/logo.jpg" alt="QT Cafe Logo" class="sidebar-logo">
    <h2>QT <span>Cafe</span></h2>
    <p>Forecasting System</p>
</div>

    <?php if ($currentRole === "admin"): ?>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php"
           class="<?php echo activeClass('dashboard.php', $currentPage); ?>">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>admin/menu_management.php"
           class="<?php echo activeClass('menu_management.php', $currentPage); ?>">Menu Management</a>
        <a href="<?php echo BASE_URL; ?>admin/ingredient_management.php"
           class="<?php echo activeClass('ingredient_management.php', $currentPage); ?>">Stock Ingredient</a>
        <a href="<?php echo BASE_URL; ?>admin/recipe_management.php"
           class="<?php echo activeClass('recipe_management.php', $currentPage); ?>">Ingredient Recipe</a>
        <a href="<?php echo BASE_URL; ?>admin/sales_records.php"
           class="<?php echo activeClass('sales_records.php', $currentPage); ?>">Sales Records</a>
         <a href="<?php echo BASE_URL; ?>admin/menu_trend.php"
           class="<?php echo activeClass('menu_trend.php', $currentPage); ?>">Menu Trend</a>
         <a href="<?php echo BASE_URL; ?>admin/popularity_analysis.php"
           class="<?php echo activeClass('popularity_analysis.php', $currentPage); ?>">Menu Popularity</a>
        <a href="<?php echo BASE_URL; ?>admin/upload_history.php"
           class="<?php echo activeClass('upload_history.php', $currentPage); ?>">Upload History</a>
        <a href="<?php echo BASE_URL; ?>admin/model_comparison.php"
           class="<?php echo activeClass('model_comparison.php', $currentPage); ?>">Model Comparison</a>
        <a href="<?php echo BASE_URL; ?>admin/demand_prediction.php"
           class="<?php echo activeClass('demand_prediction.php', $currentPage); ?>">Demand Prediction</a>
        <a href="<?php echo BASE_URL; ?>admin/ingredient_estimation.php"
           class="<?php echo activeClass('ingredient_estimation.php', $currentPage); ?>">Ingredient Estimation</a>
        <a href="<?php echo BASE_URL; ?>admin/reports.php"
           class="<?php echo activeClass('reports.php', $currentPage); ?>">Reports</a>
        <a href="<?php echo BASE_URL; ?>admin/user_management.php"
           class="<?php echo activeClass('user_management.php', $currentPage); ?>">User</a>

    <?php elseif ($currentRole === "owner"): ?>
        <a href="<?php echo BASE_URL; ?>owner/dashboard.php"
           class="<?php echo activeClass('dashboard.php', $currentPage); ?>">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>owner/upload_sales.php"
           class="<?php echo activeClass('upload_sales.php', $currentPage); ?>">Upload Sales Data</a>
        <a href="<?php echo BASE_URL; ?>owner/reports.php"
           class="<?php echo activeClass('reports.php', $currentPage); ?>">Reports</a>

    <?php elseif ($currentRole === "staff"): ?>
        <a href="<?php echo BASE_URL; ?>staff/dashboard.php"
           class="<?php echo activeClass('dashboard.php', $currentPage); ?>">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>staff/reports.php"
           class="<?php echo activeClass('reports.php', $currentPage); ?>">Reports</a>

    <?php endif; ?>

    <a class="logout-btn" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
</div>

<div class="main-content">