<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/db.php";

/* ------------------------------------
REPORT SUMMARY
------------------------------------ */

$summary = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT
            COUNT(*) AS total_predictions,
            MAX(forecast_month) AS forecast_month,
            MAX(model_used) AS model_used
        FROM forecast_results
    ")
);

$hasPrediction = ($summary && intval($summary["total_predictions"]) > 0);

?>

<h3>Business Summary</h3>

<table class="table">
<tr>
    <th>Cafe Name</th>
    <td>QT Cafe</td>
</tr>
<tr>
    <th>Forecast Period</th>
    <td><?php echo $hasPrediction ? htmlspecialchars($summary["forecast_month"]) : "July 2026"; ?></td>
</tr>
<tr>
    <th>Best Model Used</th>
    <td><?php echo $hasPrediction ? htmlspecialchars($summary["model_used"]) : "Random Forest"; ?></td>
</tr>
<tr>
    <th>Total Predicted Menu Items</th>
    <td><?php echo $hasPrediction ? intval($summary["total_predictions"]) : 5; ?></td>
</tr>
</table>

<?php if (!$hasPrediction): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data shown. Generate a prediction to see real results.</p>
<?php endif; ?>

<br>

<h3>Demand Prediction</h3>

<?php

if ($hasPrediction) {
    $result = mysqli_query($conn, "
        SELECT
            item_name,
            forecast_month,
            predicted_quantity,
            model_used
        FROM forecast_results
        ORDER BY predicted_quantity DESC
    ");
    if ($result && mysqli_num_rows($result) > 0):
?>
<table class="table">
<tr>
    <th>Menu Item</th>
    <th>Forecast Month</th>
    <th>Predicted Quantity</th>
    <th>Model Used</th>
</tr>
<?php
        while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($row["forecast_month"]); ?></td>
    <td><?php echo number_format($row["predicted_quantity"]); ?></td>
    <td><?php echo htmlspecialchars($row["model_used"]); ?></td>
</tr>
<?php
        }
?>
</table>
<?php
    else:
        echo "<div class='alert alert-info'>No prediction data yet.</div>";
    endif;
} else {
    // Dummy data fallback
?>
<table class="table">
<tr>
    <th>Menu Item</th>
    <th>Forecast Month</th>
    <th>Predicted Quantity</th>
    <th>Model Used</th>
</tr>
<tr><td>Nasi Lemak</td><td>July 2026</td><td>120</td><td>Random Forest</td></tr>
<tr><td>Milo Ais</td><td>July 2026</td><td>98</td><td>Random Forest</td></tr>
<tr><td>Chicken Chop</td><td>July 2026</td><td>84</td><td>Random Forest</td></tr>
<tr><td>French Fries</td><td>July 2026</td><td>73</td><td>Random Forest</td></tr>
<tr><td>Ice Cream</td><td>July 2026</td><td>65</td><td>Random Forest</td></tr>
</table>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data. Generate a prediction to see real results.</p>
<?php
}
?>

<br>

<h3>Ingredient Estimation</h3>

<?php

if ($hasPrediction) {
    $ingredientResult = mysqli_query($conn, "
        SELECT
            fr.item_name,
            i.ingredient_name,
            (fr.predicted_quantity * r.quantity_required) AS required_quantity,
            i.unit
        FROM forecast_results fr
        INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
        INNER JOIN ingredients i ON r.ingredient_id = i.id
        ORDER BY fr.item_name, i.ingredient_name
    ");
    if ($ingredientResult && mysqli_num_rows($ingredientResult) > 0):
?>
<table class="table">
<tr>
    <th>Menu Item</th>
    <th>Ingredient</th>
    <th>Required Quantity</th>
    <th>Unit</th>
</tr>
<?php
        while ($ingredient = mysqli_fetch_assoc($ingredientResult)) {
?>
<tr>
    <td><?php echo htmlspecialchars($ingredient["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($ingredient["ingredient_name"]); ?></td>
    <td><?php echo number_format($ingredient["required_quantity"], 2); ?></td>
    <td><?php echo htmlspecialchars($ingredient["unit"]); ?></td>
</tr>
<?php
        }
?>
</table>
<?php
    else:
        echo "<div class='alert alert-info'>No ingredient estimation available. Configure recipes first.</div>";
    endif;
} else {
?>
<table class="table">
<tr>
    <th>Menu Item</th>
    <th>Ingredient</th>
    <th>Required Quantity</th>
    <th>Unit</th>
</tr>
<tr><td>Nasi Lemak</td><td>Rice</td><td>60.00</td><td>kg</td></tr>
<tr><td>Chicken Chop</td><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
<tr><td>French Fries</td><td>Potato</td><td>38.00</td><td>kg</td></tr>
<tr><td>Milo Ais</td><td>Milk</td><td>18.00</td><td>Litre</td></tr>
<tr><td>Ice Cream</td><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
</table>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data. Configure recipes and generate a prediction to see real results.</p>
<?php
}
?>

<br>

<h3>Shopping Summary</h3>

<?php

if ($hasPrediction) {
    $shoppingResult = mysqli_query($conn, "
        SELECT
            i.ingredient_name,
            SUM(fr.predicted_quantity * r.quantity_required) AS total_required,
            i.unit
        FROM forecast_results fr
        INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
        INNER JOIN ingredients i ON r.ingredient_id = i.id
        GROUP BY i.id, i.ingredient_name, i.unit
        ORDER BY i.ingredient_name
    ");
    if ($shoppingResult && mysqli_num_rows($shoppingResult) > 0):
?>
<table class="table">
<tr>
    <th>Ingredient</th>
    <th>Total Required</th>
    <th>Unit</th>
</tr>
<?php
        while ($shopping = mysqli_fetch_assoc($shoppingResult)) {
?>
<tr>
    <td><?php echo htmlspecialchars($shopping["ingredient_name"]); ?></td>
    <td><?php echo number_format($shopping["total_required"], 2); ?></td>
    <td><?php echo htmlspecialchars($shopping["unit"]); ?></td>
</tr>
<?php
        }
?>
</table>
<?php
    else:
        echo "<div class='alert alert-info'>No shopping summary available yet.</div>";
    endif;
} else {
?>
<table class="table">
<tr>
    <th>Ingredient</th>
    <th>Total Required</th>
    <th>Unit</th>
</tr>
<tr><td>Rice</td><td>60.00</td><td>kg</td></tr>
<tr><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
<tr><td>Potato</td><td>38.00</td><td>kg</td></tr>
<tr><td>Milk</td><td>18.00</td><td>Litre</td></tr>
<tr><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
</table>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data. Generate a prediction to see real results.</p>
<?php
}
?>