<?php

require_once "../includes/auth.php";
requireRole("admin");
require_once "../config/db.php";

$pageTitle = "Ingredient Estimation";

include "../includes/header.php";
include "../includes/sidebar.php";

?>

<div class="topbar">
    <div>
        <h1>Ingredient Estimation</h1>
        <p>Ingredient requirements based on demand forecast and configured recipes.</p>
    </div>
</div>

<div class="panel">
    <h2>Forecast Ingredient Requirements</h2>
    <div id="ingredientEstimationTable">
        <p>Loading...</p>
    </div>
</div>

<div class="panel">
    <h2>Shopping Summary</h2>
    <div id="shoppingSummaryTable">
        <p>Loading...</p>
    </div>
</div>

<script>
loadIngredientEstimation();

function loadIngredientEstimation() {
    fetch("load_ingredient_estimation.php")
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            // Check for alert/warning message
            const alert = doc.querySelector(".alert");
            if (alert) {
                document.getElementById("ingredientEstimationTable").innerHTML = alert.outerHTML;
                document.getElementById("shoppingSummaryTable").innerHTML = "";
                return;
            }

            const tables = doc.querySelectorAll("table");
            const notes  = doc.querySelectorAll("p");
            const heading = doc.querySelector("h3");

            if (tables[0]) {
                let html1 = '<div class="table-responsive">' + tables[0].outerHTML + '</div>';
                if (notes[0]) html1 += notes[0].outerHTML;
                document.getElementById("ingredientEstimationTable").innerHTML = html1;
            } else {
                document.getElementById("ingredientEstimationTable").innerHTML =
                    "<p style='color:#888'>No data available.</p>";
            }

            if (tables[1]) {
                let html2 = '<div class="table-responsive">' + tables[1].outerHTML + '</div>';
                if (notes[1]) html2 += notes[1].outerHTML;
                document.getElementById("shoppingSummaryTable").innerHTML = html2;
            } else {
                document.getElementById("shoppingSummaryTable").innerHTML =
                    "<p style='color:#888'>No summary available.</p>";
            }
        })
        .catch(() => {
            document.getElementById("ingredientEstimationTable").innerHTML =
                "<div class='alert alert-warning'>Unable to load ingredient estimation data.</div>";
            document.getElementById("shoppingSummaryTable").innerHTML = "";
        });
}
</script>

<?php include "../includes/footer.php"; ?>