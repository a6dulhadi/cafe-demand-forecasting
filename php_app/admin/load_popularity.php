<?php

require_once "../includes/auth.php";
requireRole("admin");
require_once "../config/db.php";

$topResult = mysqli_query($conn, "
    SELECT m.item_name, m.category, SUM(s.quantity_sold) AS total_sales
    FROM sales_records s
    INNER JOIN menu_items m ON s.menu_item_id = m.id
    GROUP BY m.id, m.item_name, m.category
    ORDER BY total_sales DESC LIMIT 10
");
$hasData = ($topResult && mysqli_num_rows($topResult) > 0);
?>

<h3>Top Selling Menu Items</h3>
<table class="table">
<tr><th>Rank</th><th>Menu Item</th><th>Category</th><th>Total Quantity Sold</th></tr>
<?php if ($hasData):
    $rank = 1;
    while ($row = mysqli_fetch_assoc($topResult)): ?>
<tr>
    <td><?php echo $rank++; ?></td>
    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($row["category"]); ?></td>
    <td><?php echo number_format($row["total_sales"]); ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td>1</td><td>Nasi Lemak</td><td>Food</td><td>380</td></tr>
<tr><td>2</td><td>Milo Ais</td><td>Drinks</td><td>290</td></tr>
<tr><td>3</td><td>Chicken Chop</td><td>Food</td><td>215</td></tr>
<tr><td>4</td><td>French Fries</td><td>Snacks</td><td>178</td></tr>
<tr><td>5</td><td>Ice Cream</td><td>Desserts</td><td>142</td></tr>
<?php endif; ?>
</table>
<?php if (!$hasData): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — upload sales records to see real popularity ranking.</p>
<?php endif; ?>

<br>

<h3>Lowest Selling Menu Items</h3>
<table class="table">
<tr><th>Rank</th><th>Menu Item</th><th>Category</th><th>Total Quantity Sold</th></tr>
<?php
$lowResult = mysqli_query($conn, "
    SELECT m.item_name, m.category, SUM(s.quantity_sold) AS total_sales
    FROM sales_records s
    INNER JOIN menu_items m ON s.menu_item_id = m.id
    GROUP BY m.id, m.item_name, m.category
    ORDER BY total_sales ASC LIMIT 10
");
$hasLow = ($lowResult && mysqli_num_rows($lowResult) > 0);
if ($hasLow):
    $rank = 1;
    while ($row = mysqli_fetch_assoc($lowResult)): ?>
<tr>
    <td><?php echo $rank++; ?></td>
    <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($row["category"]); ?></td>
    <td><?php echo number_format($row["total_sales"]); ?></td>
</tr>
<?php endwhile;
else: ?>
<tr><td>1</td><td>Ice Cream</td><td>Desserts</td><td>142</td></tr>
<tr><td>2</td><td>French Fries</td><td>Snacks</td><td>178</td></tr>
<tr><td>3</td><td>Chicken Chop</td><td>Food</td><td>215</td></tr>
<tr><td>4</td><td>Milo Ais</td><td>Drinks</td><td>290</td></tr>
<tr><td>5</td><td>Nasi Lemak</td><td>Food</td><td>380</td></tr>
<?php endif; ?>
</table>
<?php if (!$hasLow): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — upload sales records to see real results.</p>
<?php endif; ?>