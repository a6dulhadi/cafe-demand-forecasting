<?php

require_once "../includes/auth.php";
requireRole(["admin", "owner"]);
require_once "../config/db.php";

$summary = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_predictions,
           MAX(forecast_month) AS forecast_month,
           MAX(model_used) AS model_used
    FROM forecast_results
"));

$hasPrediction = ($summary && intval($summary["total_predictions"]) > 0);

$predictions = [];
if ($hasPrediction) {
    $predResult = mysqli_query($conn, "
        SELECT item_name, forecast_month, predicted_quantity, model_used
        FROM forecast_results ORDER BY predicted_quantity DESC
    ");
    while ($row = mysqli_fetch_assoc($predResult)) $predictions[] = $row;
} else {
    $predictions = [
        ["item_name"=>"Nasi Lemak",    "forecast_month"=>"July 2026", "predicted_quantity"=>120, "model_used"=>"Random Forest"],
        ["item_name"=>"Milo Ais",      "forecast_month"=>"July 2026", "predicted_quantity"=>98,  "model_used"=>"Random Forest"],
        ["item_name"=>"Chicken Chop",  "forecast_month"=>"July 2026", "predicted_quantity"=>84,  "model_used"=>"Random Forest"],
        ["item_name"=>"French Fries",  "forecast_month"=>"July 2026", "predicted_quantity"=>73,  "model_used"=>"Random Forest"],
        ["item_name"=>"Ice Cream",     "forecast_month"=>"July 2026", "predicted_quantity"=>65,  "model_used"=>"Random Forest"],
    ];
}

$ingredients = [];
if ($hasPrediction) {
    $ingResult = mysqli_query($conn, "
        SELECT fr.item_name, i.ingredient_name,
               (fr.predicted_quantity * r.quantity_required) AS required_quantity, i.unit
        FROM forecast_results fr
        INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
        INNER JOIN ingredients i ON r.ingredient_id = i.id
        ORDER BY fr.item_name, i.ingredient_name
    ");
    while ($row = mysqli_fetch_assoc($ingResult)) $ingredients[] = $row;
} else {
    $ingredients = [
        ["item_name"=>"Nasi Lemak",   "ingredient_name"=>"Rice",           "required_quantity"=>60.00, "unit"=>"kg"],
        ["item_name"=>"Chicken Chop", "ingredient_name"=>"Chicken Breast",  "required_quantity"=>45.00, "unit"=>"kg"],
        ["item_name"=>"French Fries", "ingredient_name"=>"Potato",          "required_quantity"=>38.00, "unit"=>"kg"],
        ["item_name"=>"Milo Ais",     "ingredient_name"=>"Milk",            "required_quantity"=>18.00, "unit"=>"Litre"],
        ["item_name"=>"Ice Cream",    "ingredient_name"=>"Vanilla Mix",     "required_quantity"=>12.00, "unit"=>"Litre"],
    ];
}

$shopping = [];
if ($hasPrediction) {
    $shopResult = mysqli_query($conn, "
        SELECT i.ingredient_name,
               SUM(fr.predicted_quantity * r.quantity_required) AS total_required, i.unit
        FROM forecast_results fr
        INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
        INNER JOIN ingredients i ON r.ingredient_id = i.id
        GROUP BY i.id, i.ingredient_name, i.unit
        ORDER BY i.ingredient_name
    ");
    while ($row = mysqli_fetch_assoc($shopResult)) $shopping[] = $row;
} else {
    $shopping = [
        ["ingredient_name"=>"Rice",           "total_required"=>60.00, "unit"=>"kg"],
        ["ingredient_name"=>"Chicken Breast",  "total_required"=>45.00, "unit"=>"kg"],
        ["ingredient_name"=>"Potato",          "total_required"=>38.00, "unit"=>"kg"],
        ["ingredient_name"=>"Milk",            "total_required"=>18.00, "unit"=>"Litre"],
        ["ingredient_name"=>"Vanilla Mix",     "total_required"=>12.00, "unit"=>"Litre"],
    ];
}

$generatedOn = date("d M Y H:i");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>QT Cafe Forecasting Report</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size:13px; color:#333; padding:30px; background:#fff; }
.report-header { text-align:center; margin-bottom:30px; border-bottom:2px solid #333; padding-bottom:15px; }
.report-header h1 { font-size:20px; margin-bottom:4px; }
.report-header p { font-size:12px; color:#666; }
h2 { font-size:14px; margin:24px 0 8px; border-bottom:1px solid #ccc; padding-bottom:4px; }
table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:12px; }
th { background:#f0f0f0; border:1px solid #ccc; padding:7px 10px; text-align:left; font-weight:bold; }
td { border:1px solid #ccc; padding:6px 10px; }
tr:nth-child(even) td { background:#fafafa; }
.sample-note { color:#888; font-style:italic; font-size:11px; margin-bottom:10px; }
.print-btn { display:inline-block; margin-bottom:20px; padding:10px 24px; background:#2a78d6; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; }
.back-btn { display:inline-block; margin-bottom:20px; margin-right:10px; padding:10px 24px; background:#888; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; text-decoration:none; }
.report-footer { margin-top:40px; text-align:center; font-size:11px; color:#999; border-top:1px solid #ccc; padding-top:12px; }
@media print {
    .no-print { display:none !important; }
    body { padding:10px; }
}
</style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
    <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <span style="font-size:12px; color:#888; margin-left:10px;">Use browser Print → Save as PDF</span>
</div>

<div class="report-header">
    <h1>QT Cafe — Demand Forecasting Report</h1>
    <p>Generated on <?php echo $generatedOn; ?></p>
    <?php if (!$hasPrediction): ?>
    <p style="color:#c08000; margin-top:6px; font-size:12px;">⚠ Sample data shown — generate a prediction to see real results.</p>
    <?php endif; ?>
</div>

<h2>Business Summary</h2>
<table>
<tr><th>Cafe Name</th><td>QT Cafe</td></tr>
<tr><th>Forecast Period</th><td><?php echo $hasPrediction ? htmlspecialchars($summary["forecast_month"]) : "July 2026"; ?></td></tr>
<tr><th>Best Model Used</th><td><?php echo $hasPrediction ? htmlspecialchars($summary["model_used"]) : "Random Forest"; ?></td></tr>
<tr><th>Total Predicted Menu Items</th><td><?php echo $hasPrediction ? intval($summary["total_predictions"]) : 5; ?></td></tr>
</table>

<h2>Demand Prediction</h2>
<table>
<tr><th>Menu Item</th><th>Forecast Month</th><th>Predicted Quantity</th><th>Model Used</th></tr>
<?php foreach ($predictions as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($row["forecast_month"]); ?></td>
    <td><?php echo number_format($row["predicted_quantity"]); ?></td>
    <td><?php echo htmlspecialchars($row["model_used"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php if (!$hasPrediction): ?><p class="sample-note">* Sample data</p><?php endif; ?>

<h2>Ingredient Estimation</h2>
<table>
<tr><th>Menu Item</th><th>Ingredient</th><th>Required Quantity</th><th>Unit</th></tr>
<?php foreach ($ingredients as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($row["ingredient_name"]); ?></td>
    <td><?php echo number_format($row["required_quantity"], 2); ?></td>
    <td><?php echo htmlspecialchars($row["unit"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php if (!$hasPrediction): ?><p class="sample-note">* Sample data</p><?php endif; ?>

<h2>Shopping Summary</h2>
<table>
<tr><th>Ingredient</th><th>Total Required</th><th>Unit</th></tr>
<?php foreach ($shopping as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row["ingredient_name"]); ?></td>
    <td><?php echo number_format($row["total_required"], 2); ?></td>
    <td><?php echo htmlspecialchars($row["unit"]); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php if (!$hasPrediction): ?><p class="sample-note">* Sample data</p><?php endif; ?>

<div class="report-footer">
    QT Cafe Demand Forecasting System &mdash; <?php echo $generatedOn; ?> &mdash; Abdul Hadi Bin Haron &mdash; Universiti Selangor
</div>

</body>
</html>