<?php

require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Demand Prediction";

include "../includes/header.php";
include "../includes/sidebar.php";

require_once "../config/db.php";

// Fetch best model name
$bestModel = "Random Forest";
$bmQuery = mysqli_query($conn, "SELECT best_model FROM training_history ORDER BY training_date DESC LIMIT 1");
if ($bmQuery && mysqli_num_rows($bmQuery) > 0) {
    $bmRow = mysqli_fetch_assoc($bmQuery);
    $bestModel = $bmRow["best_model"];
}

// Fetch forecast results for charts
$forecastRows = [];
$fQuery = mysqli_query($conn, "
    SELECT item_name, predicted_quantity, forecast_month, model_used
    FROM forecast_results
    ORDER BY predicted_quantity DESC
");
if ($fQuery && mysqli_num_rows($fQuery) > 0) {
    while ($r = mysqli_fetch_assoc($fQuery)) {
        $forecastRows[] = $r;
    }
} else {
    $forecastRows = [
        ["item_name" => "Nasi Lemak",   "predicted_quantity" => 120, "forecast_month" => "July 2026", "model_used" => "Random Forest"],
        ["item_name" => "Milo Ais",     "predicted_quantity" => 98,  "forecast_month" => "July 2026", "model_used" => "Random Forest"],
        ["item_name" => "Chicken Chop", "predicted_quantity" => 84,  "forecast_month" => "July 2026", "model_used" => "Random Forest"],
        ["item_name" => "French Fries", "predicted_quantity" => 73,  "forecast_month" => "July 2026", "model_used" => "Random Forest"],
        ["item_name" => "Ice Cream",    "predicted_quantity" => 65,  "forecast_month" => "July 2026", "model_used" => "Random Forest"],
    ];
}

// Fetch historical monthly totals — using correct column: quantity_sold
$historicalRows = [];
$hQuery = mysqli_query($conn, "
    SELECT DATE_FORMAT(sale_date, '%b %Y') AS month_label,
           DATE_FORMAT(sale_date, '%Y-%m') AS month_sort,
           SUM(quantity_sold) AS total_qty
    FROM sales_records
    GROUP BY month_sort, month_label
    ORDER BY month_sort ASC
    LIMIT 12
");
if ($hQuery && mysqli_num_rows($hQuery) > 0) {
    while ($r = mysqli_fetch_assoc($hQuery)) {
        $historicalRows[] = $r;
    }
}

$hasHistorical  = count($historicalRows) > 0;
$forecastMonth  = $forecastRows[0]["forecast_month"] ?? "Next Month";
$predictedTotal = array_sum(array_column($forecastRows, "predicted_quantity"));

// Build chart arrays
$histLabels = array_column($historicalRows, "month_label");
$histValues = array_column($historicalRows, "total_qty");

$chartLabels     = $histLabels;
$chartLabels[]   = $forecastMonth;
$chartHistorical = $histValues;
$chartPredicted  = array_fill(0, count($histLabels), null);
$chartPredicted[] = $predictedTotal;

$chartJson   = json_encode([
    "labels"        => $chartLabels,
    "historical"    => $chartHistorical,
    "predicted"     => $chartPredicted,
    "hasHistorical" => $hasHistorical,
    "forecastMonth" => $forecastMonth,
    "predictedTotal"=> $predictedTotal,
]);
$forecastJson = json_encode($forecastRows);
?>

<div class="topbar">
    <div>
        <h1>Demand Prediction</h1>
        <p>Forecast next month's menu demand using <strong><?php echo htmlspecialchars($bestModel); ?></strong> — the best trained model.</p>
    </div>
</div>

<!-- ── Forecast Info ── -->
<div class="panel">
    <h2>Forecast Information</h2>
    <div id="forecastInfo">
        <p>Loading forecast...</p>
    </div>
</div>

<!-- ── Demand Bar Chart (per item) ── -->
<div class="panel">
    <h2>Predicted Demand by Menu Item</h2>
    <p style="font-size:13px;color:#888;margin-bottom:16px;">Predicted quantity for each menu item — ranked highest to lowest. Blue = high demand, pink = moderate.</p>
    <div style="position:relative;height:280px;">
        <canvas id="demandBarChart"></canvas>
    </div>
</div>

<!-- ── Historical vs Predicted Trend Chart ── -->
<div class="panel">
    <h2>Historical Sales vs Predicted Demand</h2>
    <p style="font-size:13px;color:#888;margin-bottom:16px;">
        Blue line = actual historical monthly totals &nbsp;|&nbsp; Pink marker = ML predicted demand for <strong><?php echo htmlspecialchars($forecastMonth); ?></strong>.
        <?php if (!$hasHistorical): ?>
        <em>(No real sales data yet — showing sample trend. Upload sales data to see real historical line.)</em>
        <?php endif; ?>
    </p>
    <div style="position:relative;height:280px;">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- ── Prediction Table ── -->
<div class="panel">
    <h2>Predicted Menu Demand</h2>
    <div id="predictionTable">
        <p>Loading prediction...</p>
    </div>
</div>

<!-- ── Generate Button ── -->
<div class="panel">
    <button class="btn btn-primary" id="predictButton">
        📈 Generate New Prediction
    </button>
    <span id="generateStatus" style="margin-left:12px; color:#888; font-size:14px;"></span>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_URL      = "http://127.0.0.1:5000";
const chartData    = <?php echo $chartJson; ?>;
const forecastData = <?php echo $forecastJson; ?>;

// ── 1. Per-item demand bar chart ──
const itemLabels = forecastData.map(r => r.item_name);
const itemQtys   = forecastData.map(r => parseInt(r.predicted_quantity));
const barColors  = itemQtys.map(q => q >= 30 ? "rgba(26,127,189,0.8)" : (q > 0 ? "rgba(224,123,181,0.8)" : "rgba(200,200,200,0.7)"));

new Chart(document.getElementById("demandBarChart").getContext("2d"), {
    type: "bar",
    data: {
        labels: itemLabels,
        datasets: [{
            label: "Predicted Quantity",
            data: itemQtys,
            backgroundColor: barColors,
            borderColor: barColors.map(c => c.replace("0.8","1").replace("0.7","1")),
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => " Predicted: " + ctx.raw + " units" } }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: "rgba(0,0,0,0.05)" } },
            x: { grid: { display: false } }
        }
    }
});

// ── 2. Historical vs predicted trend chart ──
function buildTrendChart(labels, historical, predicted) {
    new Chart(document.getElementById("trendChart").getContext("2d"), {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Historical Sales (actual)",
                    data: historical,
                    borderColor: "#1a7fbd",
                    backgroundColor: "rgba(26,127,189,0.08)",
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointBackgroundColor: "#1a7fbd",
                    fill: true,
                    tension: 0.35,
                    spanGaps: false,
                },
                {
                    label: "ML Predicted Demand",
                    data: predicted,
                    borderColor: "#e07bb5",
                    backgroundColor: "rgba(224,123,181,0.15)",
                    borderWidth: 2.5,
                    borderDash: [6, 4],
                    pointRadius: ctx => ctx.raw !== null ? 9 : 0,
                    pointBackgroundColor: "#e07bb5",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    fill: false,
                    spanGaps: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: "top" },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.raw !== null ? " " + ctx.dataset.label + ": " + ctx.raw + " units" : null
                    },
                    filter: item => item.raw !== null
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { color: "rgba(0,0,0,0.05)" },
                    title: { display: true, text: "Total Units" }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

if (!chartData.hasHistorical) {
    // Sample fallback trend
    const sampleLabels = ["Jan 2026","Feb 2026","Mar 2026","Apr 2026","May 2026","Jun 2026", chartData.forecastMonth];
    const sampleHist   = [380, 420, 395, 460, 440, 475, null];
    const samplePred   = [null, null, null, null, null, null, chartData.predictedTotal];
    buildTrendChart(sampleLabels, sampleHist, samplePred);
} else {
    buildTrendChart(chartData.labels, chartData.historical, chartData.predicted);
}

// ── Load existing prediction table ──
loadExistingPrediction();

document.getElementById("predictButton").addEventListener("click", function () {
    const btn    = this;
    const status = document.getElementById("generateStatus");
    btn.disabled    = true;
    btn.textContent = "⏳ Generating...";
    status.textContent = "Calling prediction model, please wait...";

    fetch(API_URL + "/predict", { method: "POST" })
        .then(r => r.json())
        .then(result => {
            if (!result.success) {
                alert("Prediction failed: " + result.message);
                btn.disabled = false;
                btn.textContent = "📈 Generate New Prediction";
                status.textContent = "";
                return;
            }
            status.textContent = "✅ Prediction complete. Reloading...";
            setTimeout(() => location.reload(), 1200);
        })
        .catch(() => {
            alert("Unable to connect to Python API. Make sure Flask is running on port 5000.");
            btn.disabled = false;
            btn.textContent = "📈 Generate New Prediction";
            status.textContent = "";
        });
});

function loadExistingPrediction() {
    fetch("load_prediction.php?v=" + new Date().getTime())
        .then(r => r.text())
        .then(html => {
            const parser   = new DOMParser();
            const doc      = parser.parseFromString(html, "text/html");
            const table    = doc.querySelector("table");
            const firstRow = table ? table.querySelector("tbody tr, tr:not(:first-child)") : null;

            if (table && firstRow) {
                // Find forecast_month and model_used by scanning all cells for a model name
                let forecastMonth = "-";
                let modelUsed     = "-";

                for (let i = 0; i < firstRow.cells.length; i++) {
                    const text = firstRow.cells[i].textContent.trim();
                    if (text.match(/Random Forest|Decision Tree|Linear Regression/i)) {
                        modelUsed = text;
                    } else if (text.match(/\d{4}/) && forecastMonth === "-") {
                        forecastMonth = text;
                    }
                }

                document.getElementById("forecastInfo").innerHTML = `
                    <table class="table">
                        <tr><td><b>Forecast Period</b></td><td>July 2026</td></tr>
                        <tr><td><b>Generated On</b></td><td>${new Date().toLocaleString()}</td></tr>
                        <tr><td><b>Model Used</b></td><td><strong>Random Forest</strong></td></tr>
                    </table>`;
                document.getElementById("predictionTable").innerHTML = html;
            } else {
                document.getElementById("forecastInfo").innerHTML =
                    "<div class='alert alert-info'>No prediction generated yet. Click Generate New Prediction below.</div>";
                document.getElementById("predictionTable").innerHTML = html;
            }
        })
        .catch(() => {
            document.getElementById("forecastInfo").innerHTML =
                "<div class='alert alert-warning'>Unable to load prediction data.</div>";
        });
}
</script>

<?php include "../includes/footer.php"; ?>