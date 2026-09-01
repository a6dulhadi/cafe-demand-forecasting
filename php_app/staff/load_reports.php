<?php
require_once "../includes/auth.php";
requireRole("staff");
require_once "../config/db.php";

$predResult = mysqli_query($conn, "SELECT item_name, forecast_month, predicted_quantity FROM forecast_results ORDER BY predicted_quantity DESC");
$hasPred = ($predResult && mysqli_num_rows($predResult) > 0);
?>

<h3>Demand Prediction</h3>
<table class="table">
<tr><th>Menu Item</th><th>Forecast Month</th><th>Predicted Quantity</th></tr>
<?php if ($hasPred): while ($r = mysqli_fetch_assoc($predResult)): ?>
<tr>
    <td><?php echo htmlspecialchars($r["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($r["forecast_month"]); ?></td>
    <td><?php echo number_format($r["predicted_quantity"]); ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td>Nasi Lemak</td><td>July 2026</td><td>120</td></tr>
<tr><td>Milo Ais</td><td>July 2026</td><td>98</td></tr>
<tr><td>Chicken Chop</td><td>July 2026</td><td>84</td></tr>
<tr><td>French Fries</td><td>July 2026</td><td>73</td></tr>
<tr><td>Ice Cream</td><td>July 2026</td><td>65</td></tr>
<?php endif; ?>
</table>
<?php if (!$hasPred): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — waiting for admin to generate a prediction.</p>
<?php endif; ?>

<br>
<h3>Ingredient Estimation</h3>
<?php
$ingResult = mysqli_query($conn, "
    SELECT fr.item_name, i.ingredient_name,
           (fr.predicted_quantity * r.quantity_required) AS required_quantity, i.unit
    FROM forecast_results fr
    INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
    INNER JOIN ingredients i ON r.ingredient_id = i.id
    ORDER BY fr.item_name, i.ingredient_name
");
$hasIng = ($ingResult && mysqli_num_rows($ingResult) > 0);
?>
<table class="table">
<tr><th>Menu Item</th><th>Ingredient</th><th>Required Quantity</th><th>Unit</th></tr>
<?php if ($hasIng): while ($r = mysqli_fetch_assoc($ingResult)): ?>
<tr>
    <td><?php echo htmlspecialchars($r["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($r["ingredient_name"]); ?></td>
    <td><?php echo number_format($r["required_quantity"], 2); ?></td>
    <td><?php echo htmlspecialchars($r["unit"]); ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td>Nasi Lemak</td><td>Rice</td><td>60.00</td><td>kg</td></tr>
<tr><td>Chicken Chop</td><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
<tr><td>French Fries</td><td>Potato</td><td>38.00</td><td>kg</td></tr>
<tr><td>Milo Ais</td><td>Milk</td><td>18.00</td><td>Litre</td></tr>
<tr><td>Ice Cream</td><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
<?php endif; ?>
</table>
<?php if (!$hasIng): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data shown.</p>
<?php endif; ?>

<br>
<h3>Shopping Summary</h3>
<?php
$shopResult = mysqli_query($conn, "
    SELECT i.ingredient_name, SUM(fr.predicted_quantity * r.quantity_required) AS total_required, i.unit
    FROM forecast_results fr
    INNER JOIN recipes r ON fr.menu_item_id = r.menu_item_id
    INNER JOIN ingredients i ON r.ingredient_id = i.id
    GROUP BY i.id, i.ingredient_name, i.unit
    ORDER BY i.ingredient_name
");
$hasShop = ($shopResult && mysqli_num_rows($shopResult) > 0);
?>
<table class="table">
<tr><th>Ingredient</th><th>Total Required</th><th>Unit</th></tr>
<?php if ($hasShop): while ($r = mysqli_fetch_assoc($shopResult)): ?>
<tr>
    <td><?php echo htmlspecialchars($r["ingredient_name"]); ?></td>
    <td><?php echo number_format($r["total_required"], 2); ?></td>
    <td><?php echo htmlspecialchars($r["unit"]); ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td>Rice</td><td>60.00</td><td>kg</td></tr>
<tr><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
<tr><td>Potato</td><td>38.00</td><td>kg</td></tr>
<tr><td>Milk</td><td>18.00</td><td>Litre</td></tr>
<tr><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
<?php endif; ?>
</table>
<?php if (!$hasShop): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data shown.</p>
<?php endif; ?>