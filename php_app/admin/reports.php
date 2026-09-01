<?php

require_once "../includes/auth.php";

requireRole("admin");

$pageTitle = "Reports";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Reports</h1>
        <p>View and export forecasting and analysis reports.</p>
    </div>
</div>

<div class="panel">
    <div id="reportContainer">
        <p>Loading report...</p>
    </div>
</div>

<div class="panel">
    <a href="export_report.php" class="btn btn-success">
        📄 Download PDF Report
    </a>
</div>

<script>

fetch("load_reports.php?v=" + new Date().getTime())
    .then(r => r.text())
    .then(html => {
        document.getElementById("reportContainer").innerHTML = html;
    })
    .catch(() => {
        document.getElementById("reportContainer").innerHTML =
            "<div class='alert alert-warning'>Unable to load report.</div>";
    });

</script>

<?php include "../includes/footer.php"; ?>