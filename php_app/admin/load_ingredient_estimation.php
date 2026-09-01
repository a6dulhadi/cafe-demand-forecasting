<?php

require_once "../includes/auth.php";
requireRole("admin");
require_once "../config/db.php";

// ── Main estimation query ──────────────────────────────────────────────────
$sql = "
    SELECT
        fr.forecast_month,
        fr.item_name,
        fr.predicted_quantity,
        fr.model_used,
        r.ingredient_id,
        r.quantity_required,
        i.ingredient_name,
        i.unit
    FROM forecast_results fr
    INNER JOIN recipes r
        ON fr.menu_item_id = r.menu_item_id
    INNER JOIN ingredients i
        ON r.ingredient_id = i.id
    ORDER BY
        fr.item_name,
        i.ingredient_name
";

$result     = mysqli_query($conn, $sql);
$hasData    = ($result && mysqli_num_rows($result) > 0);

// ── Shopping summary query ─────────────────────────────────────────────────
$summarySql = "
    SELECT
        i.ingredient_name,
        i.unit,
        SUM(fr.predicted_quantity * r.quantity_required) AS total_required
    FROM forecast_results fr
    INNER JOIN recipes r
        ON fr.menu_item_id = r.menu_item_id
    INNER JOIN ingredients i
        ON r.ingredient_id = i.id
    GROUP BY i.id, i.ingredient_name, i.unit
    ORDER BY i.ingredient_name ASC
";

$summaryResult = mysqli_query($conn, $summarySql);
$hasSummary    = ($summaryResult && mysqli_num_rows($summaryResult) > 0);

?>

<!-- ── Ingredient Requirements Table ──────────────────────────────────── -->
<table class="table">
    <tr>
        <th>Forecast</th>
        <th>Menu Item</th>
        <th>Ingredient</th>
        <th>Required Quantity</th>
        <th>Unit</th>
    </tr>

    <?php if ($hasData): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row["forecast_month"]); ?></td>
            <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["ingredient_name"]); ?></td>
            <td><?php echo number_format($row["predicted_quantity"] * $row["quantity_required"], 2); ?></td>
            <td><?php echo htmlspecialchars($row["unit"]); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td>July 2026</td><td>Nasi Lemak</td><td>Rice</td><td>60.00</td><td>kg</td></tr>
        <tr><td>July 2026</td><td>Chicken Chop</td><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
        <tr><td>July 2026</td><td>French Fries</td><td>Potato</td><td>38.00</td><td>kg</td></tr>
        <tr><td>July 2026</td><td>Milo Ais</td><td>Milk</td><td>18.00</td><td>Litre</td></tr>
        <tr><td>July 2026</td><td>Ice Cream</td><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
    <?php endif; ?>
</table>

<?php if (!$hasData): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — generate a prediction and configure recipes to see real results.</p>
<?php endif; ?>

<br>

<h3>Shopping Summary</h3>

<!-- ── Shopping Summary Table ─────────────────────────────────────────── -->
<table class="table">
    <tr>
        <th>Ingredient</th>
        <th>Total Required</th>
        <th>Unit</th>
    </tr>

    <?php if ($hasSummary): ?>
        <?php while ($s = mysqli_fetch_assoc($summaryResult)): ?>
        <tr>
            <td><?php echo htmlspecialchars($s["ingredient_name"]); ?></td>
            <td><?php echo number_format($s["total_required"], 2); ?></td>
            <td><?php echo htmlspecialchars($s["unit"]); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td>Rice</td><td>60.00</td><td>kg</td></tr>
        <tr><td>Chicken Breast</td><td>45.00</td><td>kg</td></tr>
        <tr><td>Potato</td><td>38.00</td><td>kg</td></tr>
        <tr><td>Milk</td><td>18.00</td><td>Litre</td></tr>
        <tr><td>Vanilla Mix</td><td>12.00</td><td>Litre</td></tr>
    <?php endif; ?>
</table>

<?php if (!$hasSummary): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data shown.</p>
<?php endif; ?>