<?php

require_once "../includes/auth.php";
requireRole("admin");
require_once "../config/db.php";

$trendResult = mysqli_query($conn, "
    SELECT
        DATE_FORMAT(sale_date, '%M %Y') AS sales_month,
        YEAR(sale_date) AS yr,
        MONTH(sale_date) AS mo,
        SUM(quantity_sold) AS total_sales
    FROM sales_records
    GROUP BY
        YEAR(sale_date),
        MONTH(sale_date),
        DATE_FORMAT(sale_date, '%M %Y')
    ORDER BY
        YEAR(sale_date),
        MONTH(sale_date)
");

$hasData = ($trendResult && mysqli_num_rows($trendResult) > 0);

?>

<h3>Monthly Sales Trend</h3>

<table class="table">
<tr>
    <th>Month</th>
    <th>Total Quantity Sold</th>
</tr>

<?php if ($hasData): ?>
    <?php while ($trend = mysqli_fetch_assoc($trendResult)): ?>
    <tr>
        <td><?php echo htmlspecialchars($trend["sales_month"]); ?></td>
        <td><?php echo number_format($trend["total_sales"]); ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td>January 2026</td><td>820</td></tr>
    <tr><td>February 2026</td><td>745</td></tr>
    <tr><td>March 2026</td><td>910</td></tr>
    <tr><td>April 2026</td><td>880</td></tr>
    <tr><td>May 2026</td><td>1,020</td></tr>
    <tr><td>June 2026</td><td>960</td></tr>
<?php endif; ?>
</table>

<?php if (!$hasData): ?>
<p style="color:#888; font-style:italic; font-size:13px;">* Sample data — upload sales records to see real monthly trend.</p>
<?php endif; ?>