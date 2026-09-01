<?php

require_once "../includes/auth.php";

requireRole("admin");

$pageTitle = "Menu Popularity Analysis";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Menu Popularity Analysis</h1>
        <p>Identify top and lowest performing menu items based on historical sales data.</p>
    </div>
</div>

<div class="panel">
    <h2>Top 10 Best Selling Items</h2>
    <div id="topItems">
        <p>Loading...</p>
    </div>
</div>

<div class="panel">
    <h2>Top 10 Lowest Selling Items</h2>
    <div id="lowItems">
        <p>Loading...</p>
    </div>
</div>

<div class="panel">
    <h2>Sales by Category</h2>
    <div id="categoryChart">
        <canvas id="catChart" role="img" aria-label="Bar chart of total sales quantity by menu category"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<script>

loadPopularity();

function loadPopularity() {

    fetch("load_popularity.php")
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const tables = doc.querySelectorAll("table");

            if (tables[0]) {
                document.getElementById("topItems").innerHTML = tables[0].outerHTML;
            }
            if (tables[1]) {
                document.getElementById("lowItems").innerHTML = tables[1].outerHTML;
            }
        })
        .catch(() => {
            document.getElementById("topItems").innerHTML =
                "<div class='alert alert-warning'>Unable to load popularity data.</div>";
        });

    fetch("load_category_sales.php")
        .then(response => response.json())
        .then(data => {
            if (!data.labels || data.labels.length === 0) return;

            const wrapper = document.getElementById("categoryChart");
            wrapper.style.position = "relative";
            wrapper.style.height = "300px";

            new Chart(document.getElementById("catChart"), {
                type: "bar",
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Total Quantity Sold",
                        data: data.values,
                        backgroundColor: "#2a78d6",
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: "#52514e" },
                            grid: { color: "#e1e0d9" }
                        },
                        x: {
                            ticks: {
                                color: "#52514e",
                                autoSkip: false
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        })
        .catch(() => {
            document.getElementById("categoryChart").innerHTML =
                "<p style='color:#888'>No category data available yet.</p>";
        });
}

</script>

<?php include "../includes/footer.php"; ?>