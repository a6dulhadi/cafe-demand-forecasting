<?php

require_once "../includes/auth.php";
requireRole("admin");
require_once "../config/db.php";

$result = mysqli_query($conn, "
    SELECT forecast_month, item_name, predicted_quantity, model_used
    FROM forecast_results
    ORDER BY predicted_quantity DESC, item_name ASC
");

$hasData = ($result && mysqli_num_rows($result) > 0);
?>

<table class="table">
<tr>
    <th>Forecast Period</th>
    <th>Menu Item</th>
    <th>Predicted Quantity</th>
    <th>Status</th>
    <th>Model</th>
</tr>

<?php if ($hasData): ?>
    <?php while ($row = mysqli_fetch_assoc($result)):
        $qty = (int)$row["predicted_quantity"];
        if ($qty === 0)     { $badge = "badge-danger";  $label = "Low Demand"; }
        elseif ($qty < 50)  { $badge = "badge-warning"; $label = "Moderate Demand"; }
        else                { $badge = "badge-success"; $label = "High Demand"; }
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row["forecast_month"]); ?></td>
        <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
        <td><?php echo number_format($qty); ?></td>
        <td><span class="badge <?php echo $badge; ?>"><?php echo $label; ?></span></td>
        <td><?php echo htmlspecialchars($row["model_used"]); ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td>July 2026</td><td>Nasi Lemak</td><td>120</td><td><span class="badge badge-success">High Demand</span></td><td>Random Forest</td></tr>
    <tr><td>July 2026</td><td>Milo Ais</td><td>98</td><td><span class="badge badge-success">High Demand</span></td><td>Random Forest</td></tr>
    <tr><td>July 2026</td><td>Chicken Chop</td><td>84</td><td><span class="badge badge-success">High Demand</span></td><td>Random Forest</td></tr>
    <tr><td>July 2026</td><td>French Fries</td><td>73</td><td><span class="badge badge-success">High Demand</span></td><td>Random Forest</td></tr>
    <tr><td>July 2026</td><td>Ice Cream</td><td>65</td><td><span class="badge badge-success">High Demand</span></td><td>Random Forest</td></tr>
<?php endif; ?>
</table>

<?php if (!$hasData): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — click Generate New Prediction to see real results.</p>
<?php endif; ?>