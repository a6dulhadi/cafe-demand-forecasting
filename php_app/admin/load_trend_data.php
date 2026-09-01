<?php

require_once "../includes/auth.php";
requireRole(["admin", "staff"]);
require_once "../config/db.php";

header("Content-Type: application/json");

$monthlyResult = mysqli_query($conn, "
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

$monthlyLabels = [];
$monthlyValues = [];
$hasSalesData  = false;

if ($monthlyResult && mysqli_num_rows($monthlyResult) > 0) {
    $hasSalesData = true;
    while ($row = mysqli_fetch_assoc($monthlyResult)) {
        $monthlyLabels[] = $row["sales_month"];
        $monthlyValues[] = (int)$row["total_sales"];
    }
} else {
    // Dummy data — shown before any sales are uploaded
    $monthlyLabels = ["January 2026","February 2026","March 2026","April 2026","May 2026","June 2026"];
    $monthlyValues = [820, 745, 910, 880, 1020, 960];
}

$itemResult = mysqli_query($conn, "
    SELECT
        m.item_name,
        SUM(s.quantity_sold) AS total_sales
    FROM sales_records s
    INNER JOIN menu_items m
        ON s.menu_item_id = m.id
    GROUP BY
        m.id,
        m.item_name
    ORDER BY total_sales DESC
    LIMIT 10
");

$itemLabels = [];
$itemValues = [];

if ($itemResult && mysqli_num_rows($itemResult) > 0) {
    while ($row = mysqli_fetch_assoc($itemResult)) {
        $itemLabels[] = $row["item_name"];
        $itemValues[] = (int)$row["total_sales"];
    }
} else {
    // Dummy top-10 items
    $itemLabels = ["Nasi Lemak","Milo Ais","Chicken Chop","French Fries","Ice Cream"];
    $itemValues = [380, 290, 215, 178, 142];
}

echo json_encode([
    "monthly" => [
        "labels" => $monthlyLabels,
        "values" => $monthlyValues
    ],
    "items" => [
        "labels" => $itemLabels,
        "values" => $itemValues
    ],
    "is_sample" => !$hasSalesData
]);