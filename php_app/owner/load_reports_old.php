<?php

require_once "../includes/auth.php";

requireRole("owner");

require_once "../config/db.php";

/* ------------------------------------
BUSINESS SUMMARY
------------------------------------ */

$summary=mysqli_fetch_assoc(

mysqli_query(

$conn,

"

SELECT

MAX(forecast_month)

AS forecast_month,

MAX(model_used)

AS model_used,

COUNT(*)

AS total_prediction

FROM forecast_results

"

)

);

?>

<h3>Business Summary</h3>

<table class="table">

<tr>

<th>Forecast Period</th>

<td>

<?php echo htmlspecialchars($summary["forecast_month"] ?? "-"); ?>

</td>

</tr>

<tr>

<th>Best Model</th>

<td>

<?php echo htmlspecialchars($summary["model_used"] ?? "-"); ?>

</td>

</tr>

<tr>

<th>Total Predicted Menu Items</th>

<td>

<?php echo intval($summary["total_prediction"]); ?>

</td>

</tr>

</table>

<br>

<h3>Demand Prediction</h3>

<table class="table">

<tr>

<th>Menu Item</th>

<th>Forecast Month</th>

<th>Predicted Quantity</th>

</tr>

<?php

$result=mysqli_query(

$conn,

"

SELECT

item_name,

forecast_month,

predicted_quantity

FROM forecast_results

ORDER BY

predicted_quantity DESC

"

);

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?php echo htmlspecialchars($row["item_name"]); ?>

</td>

<td>

<?php echo htmlspecialchars($row["forecast_month"]); ?>

</td>

<td>

<?php echo number_format($row["predicted_quantity"]); ?>

</td>

</tr>

<?php

}

?>

</table>

<br>

<h3>Ingredient Estimation</h3>

<table class="table">

<tr>

<th>Menu Item</th>

<th>Ingredient</th>

<th>Required Quantity</th>

<th>Unit</th>

</tr>

<?php

$ingredientResult=mysqli_query(

$conn,

"

SELECT

fr.item_name,

i.ingredient_name,

(

fr.predicted_quantity

*

r.quantity_required

)

AS required_quantity,

i.unit

FROM forecast_results fr

INNER JOIN recipes r

ON fr.menu_item_id=r.menu_item_id

INNER JOIN ingredients i

ON r.ingredient_id=i.id

ORDER BY

fr.item_name,

i.ingredient_name

"

);

while($ingredient=mysqli_fetch_assoc($ingredientResult)){

?>

<tr>

<td>

<?php echo htmlspecialchars($ingredient["item_name"]); ?>

</td>

<td>

<?php echo htmlspecialchars($ingredient["ingredient_name"]); ?>

</td>

<td>

<?php echo number_format($ingredient["required_quantity"],2); ?>

</td>

<td>

<?php echo htmlspecialchars($ingredient["unit"]); ?>

</td>

</tr>

<?php

}

?>

</table>

<br>

<h3>Shopping Summary</h3>

<table class="table">

<tr>

<th>Ingredient</th>

<th>Total Required</th>

<th>Unit</th>

</tr>

<?php

$shoppingResult=mysqli_query(

$conn,

"

SELECT

i.ingredient_name,

SUM(

fr.predicted_quantity

*

r.quantity_required

)

AS total_required,

i.unit

FROM forecast_results fr

INNER JOIN recipes r

ON fr.menu_item_id=r.menu_item_id

INNER JOIN ingredients i

ON r.ingredient_id=i.id

GROUP BY

i.id

ORDER BY

i.ingredient_name

"

);

while($shopping=mysqli_fetch_assoc($shoppingResult)){

?>

<tr>

<td>

<?php echo htmlspecialchars($shopping["ingredient_name"]); ?>

</td>

<td>

<?php echo number_format($shopping["total_required"],2); ?>

</td>

<td>

<?php echo htmlspecialchars($shopping["unit"]); ?>

</td>

</tr>

<?php

}

?>

</table>

<br>

<h3>Training History</h3>

<table class="table">

<tr>

<th>Training Date</th>

<th>Best Model</th>

<th>Total Records</th>

</tr>

<?php

$historyResult=mysqli_query(

$conn,

"

SELECT

training_date,

best_model,

total_records

FROM training_history

ORDER BY

training_date DESC

LIMIT 10

"

);

while($history=mysqli_fetch_assoc($historyResult)){

?>

<tr>

<td>

<?php echo htmlspecialchars($history["training_date"]); ?>

</td>

<td>

<?php echo htmlspecialchars($history["best_model"]); ?>

</td>

<td>

<?php echo number_format($history["total_records"]); ?>

</td>

</tr>

<?php

}

?>

</table>