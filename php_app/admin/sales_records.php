<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Sales Records";

/* ---------------------------------
   SEARCH & FILTER
---------------------------------- */

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(item_name LIKE ?)";
    $params[] = "%{$search}%";
    $types .= "s";
}

if ($category !== "") {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

$whereSQL = "";

if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* ---------------------------------
   DASHBOARD TOTALS
---------------------------------- */

$totalRecords = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM sales_records")
)['total'];

$totalRevenue = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COALESCE(SUM(total_sales),0) total
        FROM sales_records
    ")
)['total'];

$totalQty = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COALESCE(SUM(quantity_sold),0) total
        FROM sales_records
    ")
)['total'];

$totalMenu = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(DISTINCT menu_item_id) total
        FROM sales_records
    ")
)['total'];

/* ---------------------------------
   LOAD SALES RECORDS
---------------------------------- */

$sql = "
SELECT *
FROM sales_records
{$whereSQL}
ORDER BY sale_date DESC, id DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/* ---------------------------------
   CATEGORY FILTER
---------------------------------- */

$categoryResult = mysqli_query($conn,"
SELECT DISTINCT category
FROM menu_items
ORDER BY category ASC
");

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">

    <div>

        <h1>Sales Records</h1>

        <p>
            View all uploaded sales records imported
            from CSV files.
        </p>

    </div>

    <div class="user-info">

        <strong>

            <?php
            echo htmlspecialchars($_SESSION['full_name']);
            ?>

        </strong>

        <br>

        <span class="badge">

            <?php
            echo strtoupper($_SESSION['role']);
            ?>

        </span>

    </div>

</div>

<div class="card-grid">

    <div class="card">

        <h3>Total Records</h3>

        <div class="number">

            <?php echo number_format($totalRecords); ?>

        </div>

    </div>

    <div class="card">

        <h3>Total Revenue</h3>

        <div class="number">

            RM <?php echo number_format($totalRevenue,2); ?>

        </div>

    </div>

    <div class="card">

        <h3>Total Quantity Sold</h3>

        <div class="number">

            <?php echo number_format($totalQty); ?>

        </div>

    </div>

    <div class="card">

        <h3>Menu Items Sold</h3>

        <div class="number">

            <?php echo number_format($totalMenu); ?>

        </div>

    </div>

</div>

<div class="panel">

<h2>Search Sales Records</h2>

<form method="GET">

<div
style="
display:grid;
grid-template-columns:2fr 1fr auto;
gap:15px;
align-items:end;
">

<div>

<label>Search Menu Item</label>

<input
type="text"
name="search"
class="form-control"
placeholder="Coffee, Burger..."
value="<?php echo htmlspecialchars($search); ?>"
>

</div>

<div>

<label>Category</label>

<select
name="category"
class="form-control"
>

<option value="">All Categories</option>

<?php
while($cat=mysqli_fetch_assoc($categoryResult)):
?>

<option
value="<?php echo $cat['category'];?>"

<?php
if($category==$cat['category'])
echo "selected";
?>

>

<?php
echo htmlspecialchars($cat['category']);
?>

</option>

<?php endwhile; ?>

</select>

</div>

<div>

<button
class="btn btn-primary"
style="margin-top:25px;"
>

Search

</button>

</div>

</div>

</form>

</div>

<div class="panel">

<h2>Sales Record List</h2>

<table class="table">

<thead>

<tr>

<th>#</th>

<th>Date</th>

<th>Menu Item</th>

<th>Category</th>

<th>Quantity</th>

<th>Unit Price</th>

<th>Total Sales</th>

</tr>

</thead>

<tbody>

<?php

$count = 1;

if(mysqli_num_rows($result) > 0):

while($row = mysqli_fetch_assoc($result)):

?>

<tr>

<td>

<?php echo $count++; ?>

</td>

<td>

<?php echo date("d M Y", strtotime($row['sale_date'])); ?>

</td>

<td>

<?php echo htmlspecialchars($row['item_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['category']); ?>

</td>

<td>

<?php echo number_format($row['quantity_sold']); ?>

</td>

<td>

RM <?php echo number_format($row['unit_price'],2); ?>

</td>

<td>

<strong>

RM <?php echo number_format($row['total_sales'],2); ?>

</strong>

</td>

</tr>

<?php

endwhile;

else:

?>

<tr>

<td colspan="7" style="text-align:center;padding:40px;">

No sales records found.

</td>

</tr>

<?php endif; ?>

</tbody>

<tfoot>

<tr>

<th colspan="4">

TOTAL

</th>

<th>

<?php

echo number_format($totalQty);

?>

</th>

<th>

-

</th>

<th>

RM <?php echo number_format($totalRevenue,2); ?>

</th>

</tr>

</tfoot>

</table>

</div>

<style>

.table{

width:100%;

border-collapse:collapse;

margin-top:20px;

background:#fff;

}

.table th{

background:#6f3f1f;

color:#fff;

padding:14px;

text-align:left;

font-size:14px;

}

.table td{

padding:14px;

border-bottom:1px solid #eee;

font-size:14px;

}

.table tr:hover{

background:#faf6f2;

}

.form-control{

width:100%;

padding:11px;

border:1px solid #ddd;

border-radius:8px;

font-size:14px;

outline:none;

}

.form-control:focus{

border-color:#6f3f1f;

}

.btn{

padding:11px 20px;

border:none;

border-radius:8px;

cursor:pointer;

font-size:14px;

}

.btn-primary{

background:#6f3f1f;

color:#fff;

}

.btn-primary:hover{

opacity:.9;

}

tfoot th{

background:#f5f5f5;

color:#333;

font-size:15px;

}

</style>

</div>

<?php include "../includes/footer.php"; ?>