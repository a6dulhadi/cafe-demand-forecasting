<?php
require_once "../includes/auth.php";
requireRole("owner");

$pageTitle = "Upload Sales Data";

$success = "";
$error = "";

/*
EXPECTED CSV FORMAT (STRICT):
sale_date,item_name,category,quantity_sold,unit_price
*/

if (isset($_POST["upload"])) {

    if (!isset($_FILES["csv_file"]) || $_FILES["csv_file"]["error"] != 0) {
        $error = "Please select a valid CSV file.";
    } else {

        $fileName = $_FILES["csv_file"]["tmp_name"];
        $fileType = pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION);

        if (strtolower($fileType) != "csv") {
            $error = "Only CSV files are allowed.";
        } else {

            $file = fopen($fileName, "r");

            $header = fgetcsv($file);

            // STRICT FORMAT CHECK
            $expectedHeader = [
                "sale_date",
                "item_name",
                "category",
                "quantity_sold",
                "unit_price"
            ];

            if ($header !== $expectedHeader) {
                $error = "Invalid CSV format. Please use correct template.";
            } else {

                $rowCount = 0;
                $insertCount = 0;

                while (($row = fgetcsv($file)) !== false) {

                    $sale_date = $row[0];
                    $item_name = $row[1];
                    $category = $row[2];
                    $quantity_sold = (int)$row[3];
                    $unit_price = (float)$row[4];

                    $total_sales = $quantity_sold * $unit_price;

                    // find menu_item_id
                    $stmt = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE item_name = ?");
                    mysqli_stmt_bind_param($stmt, "s", $item_name);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $menu = mysqli_fetch_assoc($result);

                    $menu_item_id = $menu["id"] ?? null;

                    if ($menu_item_id) {

                        $insert = mysqli_prepare($conn, "
                            INSERT INTO sales_records
                            (sale_date, menu_item_id, item_name, category, quantity_sold, unit_price, total_sales)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");

                        mysqli_stmt_bind_param(
                            $insert,
                            "sissidd",
                            $sale_date,
                            $menu_item_id,
                            $item_name,
                            $category,
                            $quantity_sold,
                            $unit_price,
                            $total_sales
                        );

                        mysqli_stmt_execute($insert);
                        $insertCount++;
                    }

                    $rowCount++;
                }

                fclose($file);

                $success = "Upload completed. Rows: $rowCount | Inserted: $insertCount";
            }
        }
    }
}

include "../includes/header.php";
include "../includes/sidebar.php";
?>


<div class="topbar">
    <div>
        <h1>Upload Sales Data</h1>
        <p>Upload CSV file for QT Cafe demand forecasting system</p>
    </div>
</div>

<a href="download_template.php" class="btn btn-primary" style="margin-bottom:15px; display:inline-block;">
    📥 Download CSV Template
</a>

<div class="panel">

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <div class="form-group">
        <label>Select CSV File</label>
        <input type="file" name="csv_file" class="form-control" required>
    </div>

    <button type="submit" name="upload" class="btn btn-primary">
        Upload Sales Data
    </button>

</form>

<div class="panel">

    <h2>CSV Format Example</h2>

    <p><b>Required Format (STRICT):</b></p>

    <div class="alert alert-success">
        sale_date,item_name,category,quantity_sold,unit_price
    </div>

    <p><b>Example Data:</b></p>

    <div class="alert alert-success">
        2025-01-01,Iced Coffee,Drink,10,4.50<br>
        2025-01-01,Nasi Lemak,Food,5,5.00<br>
        2025-01-02,Milo Ais,Drink,8,3.50
    </div>

    <p><b>Rules:</b></p>
    <ul>
        <li>File must be CSV only (.csv)</li>
        <li>Column order must match exactly</li>
        <li>No missing values allowed</li>
        <li>item_name must exist in Menu Management</li>
    </ul>

</div>

<br>

<div class="alert alert-success">
    <b>Required CSV Format:</b><br><br>
    sale_date,item_name,category,quantity_sold,unit_price
</div>

</div>

<?php include "../includes/footer.php"; ?>