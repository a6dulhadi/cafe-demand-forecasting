<?php

require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Model Comparison";

include "../includes/header.php";
include "../includes/sidebar.php";

require_once "../config/db.php";

$hasHistory = false;
$histCheck = mysqli_query($conn, "SELECT COUNT(*) AS total FROM training_history");
if ($histCheck) {
    $row = mysqli_fetch_assoc($histCheck);
    $hasHistory = intval($row["total"]) > 0;
}

$hasModels = false;
$modelCheck = mysqli_query($conn, "SELECT COUNT(*) AS total FROM model_results");
if ($modelCheck) {
    $row = mysqli_fetch_assoc($modelCheck);
    $hasModels = intval($row["total"]) > 0;
}

// Fetch model data for chart (PHP side)
$chartModels = [];
$chartQuery = mysqli_query($conn, "SELECT * FROM model_results ORDER BY r2_score DESC");
if ($chartQuery && mysqli_num_rows($chartQuery) > 0) {
    while ($m = mysqli_fetch_assoc($chartQuery)) {
        $chartModels[] = $m;
    }
} else {
    // Sample fallback
    $chartModels = [
        ["model_name" => "Random Forest",    "mae" => 3.21, "rmse" => 4.56, "r2_score" => 0.9120, "is_best_model" => 1],
        ["model_name" => "Decision Tree",    "mae" => 5.43, "rmse" => 7.12, "r2_score" => 0.8340, "is_best_model" => 0],
        ["model_name" => "Linear Regression","mae" => 8.76, "rmse" => 11.23,"r2_score" => 0.7210, "is_best_model" => 0],
    ];
}
$chartJson = json_encode($chartModels);
?>

<div class="topbar">
    <div>
        <h1>Model Comparison</h1>
        <p>Compare machine learning models and train the forecasting system.</p>
    </div>
</div>

<!-- ── Current Model Status ── -->
<div class="panel">
    <h2>Current Model Status</h2>
    <div id="modelStatus">
        <?php
        if ($hasHistory) {
            $latest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM training_history ORDER BY training_date DESC LIMIT 1"));
            echo "<table class='table'>
                <tr><td><b>Last Training</b></td><td>" . htmlspecialchars($latest["training_date"]) . "</td></tr>
                <tr><td><b>Total Records</b></td><td>" . number_format($latest["total_records"]) . "</td></tr>
                <tr><td><b>Training Records</b></td><td>" . number_format($latest["training_records"]) . "</td></tr>
                <tr><td><b>Testing Records</b></td><td>" . number_format($latest["testing_records"]) . "</td></tr>
                <tr><td><b>Best Model</b></td><td><strong>" . htmlspecialchars($latest["best_model"]) . "</strong></td></tr>
            </table>";
        } else {
            echo "<table class='table'>
                <tr><td><b>Last Training</b></td><td>2026-07-01 09:00:00</td></tr>
                <tr><td><b>Total Records</b></td><td>1,250</td></tr>
                <tr><td><b>Training Records</b></td><td>1,000</td></tr>
                <tr><td><b>Testing Records</b></td><td>250</td></tr>
                <tr><td><b>Best Model</b></td><td><strong>Random Forest</strong></td></tr>
            </table>
            <p style='color:#888; font-style:italic; font-size:13px;'>* Sample data — click Train Models to generate real results.</p>";
        }
        ?>
    </div>
</div>

<!-- ── Dataset Summary ── -->
<div class="panel">
    <h2>Dataset Summary</h2>
    <div id="datasetSummary">
        <?php
        $salesCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales_records"))["total"] ?? 0;
        $menuCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu_items"))["total"] ?? 0;
        echo "<table class='table'>
            <tr><th>Total Sales Records</th><td>" . number_format($salesCount > 0 ? $salesCount : 1250) . ($salesCount == 0 ? " <em style='color:#aaa;font-size:12px;'>(sample)</em>" : "") . "</td></tr>
            <tr><th>Total Menu Items</th><td>" . ($menuCount > 0 ? $menuCount : 5) . ($menuCount == 0 ? " <em style='color:#aaa;font-size:12px;'>(sample)</em>" : "") . "</td></tr>
        </table>";
        ?>
    </div>
</div>

<!-- ── Training History ── -->
<div class="panel">
    <h2>Training History</h2>
    <table class="table">
        <tr><th>Date</th><th>Best Model</th><th>Records</th></tr>
        <?php
        $hist = mysqli_query($conn, "SELECT * FROM training_history ORDER BY training_date DESC LIMIT 10");
        if ($hist && mysqli_num_rows($hist) > 0) {
            while ($h = mysqli_fetch_assoc($hist)) {
                echo "<tr>
                    <td>" . htmlspecialchars($h["training_date"]) . "</td>
                    <td>" . htmlspecialchars($h["best_model"]) . "</td>
                    <td>" . number_format($h["total_records"]) . "</td>
                </tr>";
            }
        } else {
            echo "<tr><td>2026-07-01 09:00:00</td><td>Random Forest</td><td>1,250</td></tr>";
            echo "<tr><td colspan='3' style='color:#aaa; font-style:italic; font-size:12px;'>Sample data — train models to see real history.</td></tr>";
        }
        ?>
    </table>
</div>

<!-- ── Train Button ── -->
<div class="panel">
    <button id="trainButton" class="btn btn-primary">🚀 Train Models</button>
    <span id="trainStatus" style="margin-left:12px; color:#888; font-size:14px;"></span>
</div>

<!-- ── Model Comparison Table (colour-coded) ── -->
<div class="panel">
    <h2>Model Comparison
        <span class="mc-legend">
            <span class="mc-dot mc-best"></span> Best &nbsp;
            <span class="mc-dot mc-good"></span> Good &nbsp;
            <span class="mc-dot mc-low"></span> Lower
        </span>
    </h2>

    <table class="table mc-table" id="comparisonTable">
        <tr>
            <th>Model</th>
            <th>MAE <span class="metric-tip" data-tip="Mean Absolute Error — average prediction error. Lower is better.">ℹ</span></th>
            <th>RMSE <span class="metric-tip" data-tip="Root Mean Squared Error — penalises large errors more. Lower is better.">ℹ</span></th>
            <th>R² <span class="metric-tip" data-tip="R-squared — how well the model explains variance. Closer to 1.0 is better.">ℹ</span></th>
            <th>Rank</th>
        </tr>
        <?php
        $rankCounter = 1;
        foreach ($chartModels as $m) {
            $isBest = $m["is_best_model"] == 1;
            $r2 = floatval($m["r2_score"]);

            if ($isBest) {
                $rowClass = "mc-row-best";
                $rankBadge = "<span class='mc-badge mc-badge-best'>⭐ Best</span>";
            } elseif ($r2 >= 0.80) {
                $rowClass = "mc-row-good";
                $rankBadge = "<span class='mc-badge mc-badge-good'>#" . $rankCounter . "</span>";
            } else {
                $rowClass = "mc-row-low";
                $rankBadge = "<span class='mc-badge mc-badge-low'>#" . $rankCounter . "</span>";
            }
            $rankCounter++;

            // R² bar
            $r2Pct = round($r2 * 100);
            $barColor = $isBest ? "#1a7fbd" : ($r2 >= 0.80 ? "#e07bb5" : "#aaa");

            echo "<tr class='$rowClass'>
                <td><strong>" . htmlspecialchars($m["model_name"]) . "</strong></td>
                <td>" . number_format(floatval($m["mae"]), 4) . "</td>
                <td>" . number_format(floatval($m["rmse"]), 4) . "</td>
                <td>
                    <div style='display:flex;align-items:center;gap:8px;'>
                        <span>" . number_format($r2, 4) . "</span>
                        <div class='r2-bar-bg'><div class='r2-bar-fill' style='width:{$r2Pct}%;background:{$barColor};'></div></div>
                    </div>
                </td>
                <td>$rankBadge</td>
            </tr>";
        }
        ?>
    </table>
</div>

<!-- ── Bar Chart ── -->
<div class="panel">
    <h2>Performance Chart</h2>
    <p style="font-size:13px;color:#888;margin-bottom:16px;">Visual comparison of all three models across MAE, RMSE, and R² metrics.</p>
    <div style="position:relative;height:320px;">
        <canvas id="modelChart"></canvas>
    </div>
    <p style="font-size:12px;color:#aaa;margin-top:10px;font-style:italic;">Note: MAE and RMSE — lower is better. R² — higher is better (max 1.0).</p>
</div>

<!-- ── Why This Model Wins ── -->
<div class="panel">
    <h2>Why <span id="bestModelName">Random Forest</span> is Recommended</h2>
    <div id="whyPanel">
        <div class="why-grid">
            <div class="why-card why-blue">
                <div class="why-icon">🌲</div>
                <h4>Ensemble Learning</h4>
                <p>Random Forest builds many decision trees and averages their results. This reduces overfitting compared to a single Decision Tree.</p>
            </div>
            <div class="why-card why-pink">
                <div class="why-icon">📉</div>
                <h4>Lower Error Rates</h4>
                <p>It achieves the lowest MAE and RMSE scores, meaning its predictions are closest to the actual sales values in the test set.</p>
            </div>
            <div class="why-card why-teal">
                <div class="why-icon">📊</div>
                <h4>Highest R² Score</h4>
                <p>The R² score close to 1.0 means the model explains most of the variance in demand — it captures real sales patterns well.</p>
            </div>
        </div>

        <details class="why-details" style="margin-top:18px;">
            <summary>Compare with other models</summary>
            <div style="margin-top:12px;">
                <p><strong>vs Decision Tree:</strong> Decision Tree uses a single tree and tends to overfit — it memorises training data but struggles with new data. Random Forest fixes this by averaging many trees.</p>
                <p style="margin-top:8px;"><strong>vs Linear Regression:</strong> Linear Regression assumes a straight-line relationship between features and demand, which may miss non-linear patterns in real cafe sales data.</p>
            </div>
        </details>
    </div>
</div>

<!-- ── Recommendation ── -->
<div class="panel">
    <h2>Recommendation</h2>
    <div id="recommendation">
        <?php
        if ($hasHistory) {
            $latest = mysqli_fetch_assoc(mysqli_query($conn, "SELECT recommendation, best_model FROM training_history ORDER BY training_date DESC LIMIT 1"));
            echo "<p>" . htmlspecialchars($latest["recommendation"] ?? "No recommendation available.") . "</p>";
        } else {
            echo "<p>Based on sample data, <strong>Random Forest</strong> is recommended as it achieves the highest R² score (0.9120) with the lowest error rates. Train the models with real sales data to get an accurate recommendation.</p>";
        }
        ?>
    </div>
</div>

<!-- ── Tooltip container ── -->
<div id="mc-tooltip" class="mc-tooltip-box" style="display:none;"></div>

<style>
/* ── Metric tooltip ── */
.metric-tip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    background: #1a7fbd;
    color: #fff;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    margin-left: 4px;
    vertical-align: middle;
    user-select: none;
}
.mc-tooltip-box {
    position: fixed;
    background: #1e2a3a;
    color: #fff;
    padding: 8px 13px;
    border-radius: 8px;
    font-size: 13px;
    max-width: 260px;
    line-height: 1.5;
    z-index: 9999;
    pointer-events: none;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

/* ── Legend ── */
.mc-legend {
    font-size: 12px;
    font-weight: 400;
    color: #888;
    margin-left: 14px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mc-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.mc-dot.mc-best { background: #cce8f7; border: 2px solid #1a7fbd; }
.mc-dot.mc-good { background: #fce4f2; border: 2px solid #e07bb5; }
.mc-dot.mc-low  { background: #f5f5f5; border: 2px solid #bbb; }

/* ── Table row colours ── */
.mc-row-best { background: #e8f4fc !important; border-left: 4px solid #1a7fbd; }
.mc-row-good { background: #fdf0f8 !important; border-left: 4px solid #e07bb5; }
.mc-row-low  { background: #fafafa !important; border-left: 4px solid #ccc; }

/* ── Badges ── */
.mc-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}
.mc-badge-best { background: #cce8f7; color: #0d5e8e; border: 1px solid #1a7fbd; }
.mc-badge-good { background: #fce4f2; color: #a0477a; border: 1px solid #e07bb5; }
.mc-badge-low  { background: #f0f0f0; color: #666;    border: 1px solid #ccc; }

/* ── R² bar ── */
.r2-bar-bg {
    flex: 1;
    height: 8px;
    background: #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
    min-width: 60px;
}
.r2-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s ease;
}

/* ── Why cards ── */
.why-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 12px;
}
.why-card {
    padding: 18px;
    border-radius: 12px;
    text-align: center;
}
.why-card h4 { margin: 10px 0 6px; font-size: 15px; }
.why-card p  { font-size: 13px; line-height: 1.6; margin: 0; }
.why-icon    { font-size: 26px; }
.why-blue  { background: #e8f4fc; border: 1px solid #a8d4ee; color: #0d4f76; }
.why-pink  { background: #fce4f2; border: 1px solid #f0b0d8; color: #7a2f5a; }
.why-teal  { background: #e4f7f2; border: 1px solid #8fd8c8; color: #1a5f50; }

.why-details summary {
    cursor: pointer;
    color: #1a7fbd;
    font-weight: 600;
    font-size: 14px;
}
.why-details p { font-size: 13px; color: #555; margin: 0; }

@media (max-width: 768px) {
    .why-grid { grid-template-columns: 1fr; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const API_URL = "http://127.0.0.1:5000";

// ── Train button ──
document.getElementById("trainButton").addEventListener("click", async function () {
    if (!confirm("Retraining will overwrite previous models.\n\nDo you want to continue?")) return;
    const btn = this;
    const status = document.getElementById("trainStatus");
    btn.disabled = true;
    btn.innerHTML = "⏳ Training Models...";
    status.textContent = "Connecting to Python API...";
    try {
        let response = await fetch(API_URL + "/train", { method: "POST" });
        let result = await response.json();
        if (result.success) {
            status.textContent = "✅ Training complete! Reloading...";
            setTimeout(() => location.reload(), 1500);
        } else {
            alert("Training failed: " + result.message);
            btn.disabled = false;
            btn.innerHTML = "🚀 Train Models";
            status.textContent = "";
        }
    } catch (error) {
        alert("Unable to connect to Python API (port 5000). Make sure Flask is running.");
        btn.disabled = false;
        btn.innerHTML = "🚀 Train Models";
        status.textContent = "";
    }
});

// ── Tooltips ──
const tooltip = document.getElementById("mc-tooltip");
document.querySelectorAll(".metric-tip").forEach(el => {
    el.addEventListener("mouseenter", e => {
        tooltip.textContent = el.dataset.tip;
        tooltip.style.display = "block";
    });
    el.addEventListener("mousemove", e => {
        tooltip.style.left = (e.clientX + 14) + "px";
        tooltip.style.top  = (e.clientY - 10) + "px";
    });
    el.addEventListener("mouseleave", () => {
        tooltip.style.display = "none";
    });
});

// ── Bar Chart ──
const rawModels = <?php echo $chartJson; ?>;
const labels  = rawModels.map(m => m.model_name);
const maeData  = rawModels.map(m => parseFloat(m.mae));
const rmseData = rawModels.map(m => parseFloat(m.rmse));
const r2Data   = rawModels.map(m => parseFloat(m.r2_score));

const ctx = document.getElementById("modelChart").getContext("2d");
new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [
            {
                label: "MAE (lower = better)",
                data: maeData,
                backgroundColor: "rgba(26, 127, 189, 0.75)",
                borderColor: "#1a7fbd",
                borderWidth: 1.5,
                borderRadius: 6,
            },
            {
                label: "RMSE (lower = better)",
                data: rmseData,
                backgroundColor: "rgba(224, 123, 181, 0.75)",
                borderColor: "#e07bb5",
                borderWidth: 1.5,
                borderRadius: 6,
            },
            {
                label: "R² × 10 (higher = better)",
                data: r2Data.map(v => v * 10),
                backgroundColor: "rgba(34, 197, 164, 0.75)",
                borderColor: "#22c5a4",
                borderWidth: 1.5,
                borderRadius: 6,
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
                    label: function(ctx) {
                        if (ctx.datasetIndex === 2) {
                            return " R²: " + (ctx.raw / 10).toFixed(4);
                        }
                        return " " + ctx.dataset.label.split(" ")[0] + ": " + ctx.raw.toFixed(4);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: "rgba(0,0,0,0.05)" }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// Update best model name dynamically
const bestModel = rawModels.find(m => m.is_best_model == 1);
if (bestModel) {
    document.getElementById("bestModelName").textContent = bestModel.model_name;
}
</script>

<?php include "../includes/footer.php"; ?>