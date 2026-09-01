<?php

require_once "../includes/auth.php";

requireRole("staff");

$pageTitle = "Reports";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Reports</h1>
        <p>View current demand forecast and menu information.</p>
    </div>
</div>

<div class="panel">
    <h2>Demand Prediction</h2>
    <div id="predictionContainer">
        <p>Loading...</p>
    </div>
</div>

<div class="panel">
    <h2>Monthly Sales Trend</h2>
    <div style="position:relative; height:280px;">
        <canvas id="staffTrendChart" role="img" aria-label="Line chart of monthly sales trend"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<script>

fetch("load_reports.php?v=" + new Date().getTime())
    .then(r => r.text())
    .then(html => {
        document.getElementById("predictionContainer").innerHTML = html;
    })
    .catch(() => {
        document.getElementById("predictionContainer").innerHTML =
            "<div class='alert alert-warning'>Unable to load prediction data.</div>";
    });

fetch("../admin/load_trend_data.php")
    .then(r => r.json())
    .then(data => {
        if (!data.monthly || data.monthly.labels.length === 0) return;

        new Chart(document.getElementById("staffTrendChart"), {
            type: "line",
            data: {
                labels: data.monthly.labels,
                datasets: [{
                    label: "Total Quantity Sold",
                    data: data.monthly.values,
                    borderColor: "#2a78d6",
                    backgroundColor: "rgba(42,120,214,0.08)",
                    borderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: "#52514e" },
                        grid: { color: "#e1e0d9" }
                    },
                    x: {
                        ticks: { color: "#52514e", maxRotation: 45 },
                        grid: { display: false }
                    }
                }
            }
        });
    })
    .catch(() => {});

</script>

<?php include "../includes/footer.php"; ?>