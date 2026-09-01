<?php

require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Menu Trend Analysis";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Menu Trend Analysis</h1>
        <p>Visual analysis of monthly sales trends over time.</p>
    </div>
</div>

<div class="panel">
    <h2>Monthly Sales Trend</h2>
    <div style="position:relative; height:320px;">
        <canvas id="trendChart" role="img" aria-label="Line chart of monthly total sales quantity"></canvas>
    </div>
    <div id="trendSampleNote"></div>
</div>

<div class="panel">
    <h2>Monthly Sales Data Table</h2>
    <div id="trendTable">
        <p>Loading...</p>
    </div>
</div>

<div class="panel">
    <h2>Sales by Menu Item (Top 10)</h2>
    <div style="position:relative; height:360px;">
        <canvas id="itemChart" role="img" aria-label="Bar chart of top 10 menu items by total quantity sold"></canvas>
    </div>
    <div id="itemSampleNote"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<script>

loadTrend();

function loadTrend() {

    fetch("load_trend_data.php")
        .then(r => r.json())
        .then(data => {

            const isSample = data.is_sample === true;
            const sampleHtml = "<p style='color:#888; font-style:italic; font-size:13px; margin-top:10px;'>* Sample data — upload sales records to see real trend.</p>";

            // ── Line chart: Monthly Sales ──────────────────────────────────
            if (data.monthly && data.monthly.labels.length > 0) {

                new Chart(document.getElementById("trendChart"), {
                    type: "line",
                    data: {
                        labels: data.monthly.labels,
                        datasets: [{
                            label: "Total Quantity Sold",
                            data: data.monthly.values,
                            borderColor: "#2a78d6",
                            backgroundColor: "rgba(42,120,214,0.08)",
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: "#2a78d6",
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
                                ticks: {
                                    color: "#52514e",
                                    autoSkip: false,
                                    maxRotation: 45
                                },
                                grid: { display: false }
                            }
                        }
                    }
                });

                if (isSample) {
                    document.getElementById("trendSampleNote").innerHTML = sampleHtml;
                }

                // ── Data Table ─────────────────────────────────────────────
                let tableHtml = "<table class='table'><tr><th>Month</th><th>Total Quantity Sold</th></tr>";
                data.monthly.labels.forEach((label, i) => {
                    tableHtml += `<tr><td>${label}</td><td>${data.monthly.values[i].toLocaleString()}</td></tr>`;
                });
                tableHtml += "</table>";
                if (isSample) {
                    tableHtml += sampleHtml;
                }
                document.getElementById("trendTable").innerHTML = tableHtml;

            } else {
                document.getElementById("trendTable").innerHTML =
                    "<div class='alert alert-info'>No sales data available. Please upload sales records first.</div>";
            }

            // ── Bar chart: Top 10 Items ────────────────────────────────────
            if (data.items && data.items.labels.length > 0) {
                new Chart(document.getElementById("itemChart"), {
                    type: "bar",
                    indexAxis: "y",
                    data: {
                        labels: data.items.labels,
                        datasets: [{
                            label: "Total Quantity Sold",
                            data: data.items.values,
                            backgroundColor: "#1baf7a",
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { color: "#52514e" },
                                grid: { color: "#e1e0d9" }
                            },
                            y: {
                                ticks: { color: "#52514e" },
                                grid: { display: false }
                            }
                        }
                    }
                });

                if (isSample) {
                    document.getElementById("itemSampleNote").innerHTML = sampleHtml;
                }
            }

        })
        .catch(() => {
            document.getElementById("trendTable").innerHTML =
                "<div class='alert alert-warning'>Unable to load trend data.</div>";
        });
}

</script>

<?php include "../includes/footer.php"; ?>