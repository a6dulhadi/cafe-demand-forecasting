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

<div class="report-card">

<h2>

<span>Business Summary</span>



</h2>

<table class="data-table">

<tr>

<th>Forecast Period</th>

<td>

<?php

if($summary["total_prediction"]==0){

    echo "July 2026";

}else{

    echo htmlspecialchars($summary["forecast_month"]);

}

?>

</td>

</tr>

<tr>

<th>Best Model</th>

<td>

<?php

if($summary["total_prediction"]==0){

    echo "Random Forest";

}else{

    echo htmlspecialchars($summary["model_used"]);

}

?>

</td>

</tr>

<tr>

<th>Total Predicted Menu Items</th>

<td>

<?php

if($summary["total_prediction"]==0){

    echo 5;

}else{

    echo intval($summary["total_prediction"]);

}

?>

</td>

</tr>

</table>


<hr class="report-divider">

<p class="sample-note">

Sample report 

</p>
<div style="height:40px;"></div>

</div>

<div class="report-card">

<h2>

<span>Demand Prediction</span>




</h2>

<table class="data-table">

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

if($summary["total_prediction"]==0){

?>

<tr>
    <td>Nasi Lemak</td>
    <td>July 2026</td>
    <td>120</td>
</tr>

<tr>
    <td>Milo Ais</td>
    <td>July 2026</td>
    <td>98</td>
</tr>

<tr>
    <td>Chicken Chop</td>
    <td>July 2026</td>
    <td>84</td>
</tr>

<tr>
    <td>French Fries</td>
    <td>July 2026</td>
    <td>73</td>
</tr>

<tr>
    <td>Ice Cream</td>
    <td>July 2026</td>
    <td>65</td>
</tr>

<?php

}else{

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo htmlspecialchars($row["item_name"]); ?></td>

<td><?php echo htmlspecialchars($row["forecast_month"]); ?></td>

<td><?php echo number_format($row["predicted_quantity"]); ?></td>

</tr>

<?php

}

}

?>

</table>
<?php

if($summary["total_prediction"]==0){

?>

<hr class="report-divider">

<p class="sample-note">

Sample forecast data

</p>
<div style="height:40px;"></div>
<?php

}
?>
</div>

<div class="report-card">

<h2>

<span>Ingredient Estimation</span>



</h2>

<table class="data-table">

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
if($summary["total_prediction"]==0){

?>

<tr>
    <td>Nasi Lemak</td>
    <td>Rice</td>
    <td>60.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>Chicken Chop</td>
    <td>Chicken Breast</td>
    <td>45.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>French Fries</td>
    <td>Potato</td>
    <td>38.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>Milo Ais</td>
    <td>Milk</td>
    <td>18.00</td>
    <td>Litre</td>
</tr>

<tr>
    <td>Ice Cream</td>
    <td>Vanilla Mix</td>
    <td>12.00</td>
    <td>Litre</td>
</tr>

<?php

}else{

    while($ingredient=mysqli_fetch_assoc($ingredientResult)){

?>

<tr>
    <td><?php echo htmlspecialchars($ingredient["item_name"]); ?></td>
    <td><?php echo htmlspecialchars($ingredient["ingredient_name"]); ?></td>
    <td><?php echo number_format($ingredient["required_quantity"],2); ?></td>
    <td><?php echo htmlspecialchars($ingredient["unit"]); ?></td>
</tr>

<?php

    }

}

?>
</table>

<?php

if($summary["total_prediction"]==0){

?>


<hr class="report-divider">

<p class="sample-note">

Sample ingredient estimation 

</p>
<div style="height:40px;"></div>
<?php

}

?>

</div>

<div class="report-card">

<h2>

<span>Shopping Summary</span>



</h2>

<table class="data-table">

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



if($summary["total_prediction"]==0){

?>

<tr>
    <td>Rice</td>
    <td>60.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>Chicken Breast</td>
    <td>45.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>Potato</td>
    <td>38.00</td>
    <td>kg</td>
</tr>

<tr>
    <td>Milk</td>
    <td>18.00</td>
    <td>Litre</td>
</tr>

<tr>
    <td>Vanilla Mix</td>
    <td>12.00</td>
    <td>Litre</td>
</tr>

<?php

}else{

while($shopping=mysqli_fetch_assoc($shoppingResult)){

?>

<tr>

<td><?php echo htmlspecialchars($shopping["ingredient_name"]); ?></td>

<td><?php echo number_format($shopping["total_required"],2); ?></td>

<td><?php echo htmlspecialchars($shopping["unit"]); ?></td>

</tr>

<?php

}

}

?>

</table>

<?php

if($summary["total_prediction"]==0){

?>


<hr class="report-divider">

<p class="sample-note">

Sample shopping list

</p>
<div style="height:40px;"></div>
<?php

}

?>

</div>

<div class="report-card">

<h2>

<span>Training History</span>



</h2>

<table class="data-table">

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



if($summary["total_prediction"]==0){

?>

<tr>
    <td>2026-07-01</td>
    <td>Random Forest</td>
    <td>1250</td>
</tr>

<?php

}else{

while($history=mysqli_fetch_assoc($historyResult)){

?>

<tr>

<td><?php echo htmlspecialchars($history["training_date"]); ?></td>

<td><?php echo htmlspecialchars($history["best_model"]); ?></td>

<td><?php echo number_format($history["total_records"]); ?></td>

</tr>

<?php

}

}

?>

</table>

<?php

if($summary["total_prediction"]==0){

?>


<hr class="report-divider">

<p class="sample-note">

Sample training record 

</p>
<div style="height:40px;"></div>
<?php

}

?>

</div>