<?php

require_once "../includes/auth.php";
requireRole("owner");

$pageTitle = "Reports";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Reports</h1>
        <p>Business reports and forecasting information.</p>
    </div>
</div>

<div class="panel">
    <div id="reportContainer">
        <div style="text-align:center; padding:40px; color:#777;">
            Loading report...
        </div>
    </div>
</div>

<div class="panel">
    <a href="../admin/export_report.php" class="btn btn-success">
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
            "<div class='alert alert-warning'>Unable to load reports.</div>";
    });

</script>

<?php include "../includes/footer.php"; ?>