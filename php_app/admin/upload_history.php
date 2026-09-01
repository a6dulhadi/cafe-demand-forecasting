<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Upload History";

include "../includes/header.php";
include "../includes/sidebar.php";

/*
---------------------------------------
UPLOAD HISTORY
---------------------------------------
*/

$sql = "
SELECT
s.*,
u.full_name
FROM sales_uploads s
LEFT JOIN users u
ON s.uploaded_by = u.id
ORDER BY s.uploaded_at DESC
";

$result = mysqli_query($conn, $sql);

/*
---------------------------------------
SUMMARY CARDS
---------------------------------------
*/

$totalUploads = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM sales_uploads
    ")
)['total'];

$totalRows = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COALESCE(SUM(total_rows),0) total
        FROM sales_uploads
    ")
)['total'];

$totalImported = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COALESCE(SUM(valid_rows),0) total
        FROM sales_uploads
    ")
)['total'];

$totalSkipped = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COALESCE(SUM(invalid_rows),0) total
        FROM sales_uploads
    ")
)['total'];
?>

<div class="topbar">

<div>

<h1>Upload History</h1>

<p>

View all CSV uploads submitted by cafe owners.

</p>

</div>

</div>

<div class="card-grid">

<div class="card">

<h3>Total Uploads</h3>

<div class="number">

<?php echo number_format($totalUploads); ?>

</div>

</div>

<div class="card">

<h3>Total Rows</h3>

<div class="number">

<?php echo number_format($totalRows); ?>

</div>

</div>

<div class="card">

<h3>Imported Rows</h3>

<div class="number">

<?php echo number_format($totalImported); ?>

</div>

</div>

<div class="card">

<h3>Skipped Rows</h3>

<div class="number">

<?php echo number_format($totalSkipped); ?>

</div>

</div>

</div>

<div class="panel">

<h2>CSV Upload History</h2>

<table class="table">

<thead>

<tr>

<th>#</th>

<th>CSV File</th>

<th>Uploaded By</th>

<th>Total Rows</th>

<th>Imported</th>

<th>Skipped</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php

$count = 1;

if(mysqli_num_rows($result)>0):

while($row=mysqli_fetch_assoc($result)):

$statusColor = "#28a745";

if($row['upload_status']=="partial"){
    $statusColor = "#ffc107";
}

if($row['upload_status']=="fail"){
    $statusColor = "#dc3545";
}

?>

<tr>

<td>

<?php echo $count++; ?>

</td>

<td>

<?php echo htmlspecialchars($row['file_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo number_format($row['total_rows']); ?>

</td>

<td>

<?php echo number_format($row['valid_rows']); ?>

</td>

<td>

<?php echo number_format($row['invalid_rows']); ?>

</td>

<td>

<span
style="
background:<?php echo $statusColor;?>;
color:#fff;
padding:6px 12px;
border-radius:20px;
font-size:13px;
font-weight:bold;
">

<?php echo ucfirst($row['upload_status']); ?>

</span>

</td>

<td>

<?php

echo date(
"d M Y h:i A",
strtotime($row['uploaded_at'])
);

?>

</td>

</tr>

<?php

endwhile;

else:

?>

<tr>

<td colspan="8"
style="
text-align:center;
padding:40px;
">

No upload history found.

</td>

</tr>

<?php endif; ?>

</tbody>

<tfoot>

<tr>

<th colspan="3">

TOTAL

</th>

<th>

<?php echo number_format($totalRows); ?>

</th>

<th>

<?php echo number_format($totalImported); ?>

</th>

<th>

<?php echo number_format($totalSkipped); ?>

</th>

<th colspan="2">

<?php echo number_format($totalUploads); ?>

Uploads

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

}

.table th{

background:#6f3f1f;
color:white;
padding:14px;
text-align:left;

}

.table td{

padding:14px;
border-bottom:1px solid #eee;

}

.table tr:hover{

background:#faf6f2;

}

tfoot th{

background:#f5f5f5;
color:#333;

}

</style>

</div>

<?php include "../includes/footer.php"; ?>