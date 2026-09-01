<?php

require_once "../includes/auth.php";

requireRole("admin");

require_once "../config/db.php";

header("Content-Type: application/json");

$result = mysqli_query($conn, "
    SELECT
        m.category,
        SUM(s.quantity_sold) AS total_sales
    FROM sales_records s
    INNER JOIN menu_items m
        ON s.menu_item_id = m.id
    GROUP BY m.category
    ORDER BY total_sales DESC
");

$labels = [];
$values = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row["category"];
        $values[] = (int)$row["total_sales"];
    }
}

echo json_encode([
    "labels" => $labels,
    "values" => $values
]);