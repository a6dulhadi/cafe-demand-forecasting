<?php
require_once "../includes/auth.php";
requireRole("owner");

$pageTitle = "Upload Sales Data";

$success = "";
$error = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST["upload"])) {

    if (!isset($_FILES["csv_file"]) || $_FILES["csv_file"]["error"] != 0) {

        $error = "Please select a valid CSV file.";

    } else {

        $extension = strtolower(pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION));

        if ($extension != "csv") {

            $error = "Only CSV files are allowed.";

        } else {

            $tempFile     = $_FILES["csv_file"]["tmp_name"];
            $originalFile = $_FILES["csv_file"]["name"];

            // ── Fix 4: Duplicate file guard ──────────────────────────────
            $dupCheck = mysqli_prepare($conn,
                "SELECT id FROM sales_uploads WHERE file_name = ? AND upload_status = 'success' LIMIT 1"
            );
            mysqli_stmt_bind_param($dupCheck, "s", $originalFile);
            mysqli_stmt_execute($dupCheck);
            mysqli_stmt_store_result($dupCheck);

            if (mysqli_stmt_num_rows($dupCheck) > 0) {
                $error = "This file has already been uploaded successfully. Rename it if this is new data.";
            } else {

                $file = fopen($tempFile, "r");

                if ($file === false) {

                    $error = "Unable to open uploaded CSV.";

                } else {

                    // ── Header validation ────────────────────────────────
                    $header = fgetcsv($file);

                    $expectedHeader = ["sale_date", "item_name", "category", "quantity_sold", "unit_price"];

                    // Remove empty columns
                    $header = array_filter($header, function ($v) { return trim($v) !== ""; });
                    $header = array_values($header);
                    $header = array_map("trim", $header);

                    // Remove UTF-8 BOM
                    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

                    // Lowercase
                    $header = array_map("strtolower", $header);

                    if ($header != $expectedHeader) {

                        fclose($file);
                        $error = "Invalid CSV format. Please use the downloadable template.";

                    } else {

                        $totalRows   = 0;
                        $validRows   = 0;
                        $invalidRows = 0;
                        $skipReasons = []; // collect reasons for skipped rows

                        // ── Create upload history record ─────────────────
                        $history = mysqli_prepare($conn,
                            "INSERT INTO sales_uploads (uploaded_by, file_name, total_rows, valid_rows, invalid_rows, upload_status)
                             VALUES (?, ?, 0, 0, 0, 'success')"
                        );
                        mysqli_stmt_bind_param($history, "is", $_SESSION["user_id"], $originalFile);
                        mysqli_stmt_execute($history);
                        $uploadID = mysqli_insert_id($conn);

                        // ── Process rows ─────────────────────────────────
                        while (($row = fgetcsv($file)) !== false) {

                            $totalRows++;

                            // Fix 3: allow trailing commas — strip empty trailing columns
                            $row = array_filter($row, function ($v) { return trim($v) !== ""; });
                            $row = array_values($row);

                            if (count($row) < 5) {
                                $invalidRows++;
                                $skipReasons[] = "Row $totalRows: missing columns (only " . count($row) . " found).";
                                continue;
                            }

                            // Fix 1: accept multiple date formats
                            $sale_date = trim($row[0]);
                            $parsed    = false;
                            foreach (["Y-m-d", "d/m/Y", "m/d/Y", "n/j/Y", "Y/m/d", "d-m-Y"] as $fmt) {
                                $date = DateTime::createFromFormat($fmt, $sale_date);
                                if ($date && $date->format($fmt) === $sale_date) {
                                    $sale_date = $date->format("Y-m-d");
                                    $parsed    = true;
                                    break;
                                }
                            }
                            if (!$parsed) {
                                $invalidRows++;
                                $skipReasons[] = "Row $totalRows: unrecognised date format \"" . htmlspecialchars(trim($row[0])) . "\".";
                                continue;
                            }

                            $item_name    = trim($row[1]);
                            $category     = trim($row[2]);
                            $quantity_sold = (int)$row[3];
                            $unit_price   = (float)$row[4];

                            // Fix 2: block zero or negative quantity
                            if (empty($item_name) || empty($category) || $quantity_sold <= 0 || $unit_price < 0) {
                                $invalidRows++;
                                $skipReasons[] = "Row $totalRows: invalid data (empty name/category, zero/negative quantity, or negative price).";
                                continue;
                            }

                            $total_sales = $quantity_sold * $unit_price;

                            // ── Match item against Menu Management ───────
                            $menuQuery = mysqli_prepare($conn,
                                "SELECT id FROM menu_items WHERE LOWER(TRIM(item_name)) = LOWER(TRIM(?)) LIMIT 1"
                            );
                            mysqli_stmt_bind_param($menuQuery, "s", $item_name);
                            mysqli_stmt_execute($menuQuery);
                            $menuResult = mysqli_stmt_get_result($menuQuery);

                            if (mysqli_num_rows($menuResult) == 0) {
                                $invalidRows++;
                                $skipReasons[] = "Row $totalRows: \"" . htmlspecialchars($item_name) . "\" not found in Menu Management.";
                                continue;
                            }

                            $menu   = mysqli_fetch_assoc($menuResult);
                            $menuID = $menu["id"];

                            // ── Insert sales record ───────────────────────
                            $insert = mysqli_prepare($conn,
                                "INSERT INTO sales_records (upload_id, sale_date, menu_item_id, item_name, category, quantity_sold, unit_price, total_sales)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            mysqli_stmt_bind_param($insert, "isissidd",
                                $uploadID, $sale_date, $menuID, $item_name,
                                $category, $quantity_sold, $unit_price, $total_sales
                            );

                            if (mysqli_stmt_execute($insert)) {
                                $validRows++;
                            } else {
                                $invalidRows++;
                                $skipReasons[] = "Row $totalRows: database insert failed.";
                            }
                        }

                        fclose($file);

                        // ── Update upload history ─────────────────────────
                        $status = "success";
                        if ($invalidRows > 0 && $validRows > 0)  $status = "partial";
                        elseif ($validRows == 0)                  $status = "failed";

                        $updateHistory = mysqli_prepare($conn,
                            "UPDATE sales_uploads SET total_rows = ?, valid_rows = ?, invalid_rows = ?, upload_status = ? WHERE id = ?"
                        );
                        mysqli_stmt_bind_param($updateHistory, "iiisi",
                            $totalRows, $validRows, $invalidRows, $status, $uploadID
                        );
                        mysqli_stmt_execute($updateHistory);

                        $_SESSION["upload_success"] = [
                            "file"        => $originalFile,
                            "rows"        => $totalRows,
                            "valid"       => $validRows,
                            "invalid"     => $invalidRows,
                            "status"      => $status,
                            "skipReasons" => $skipReasons,
                        ];

                        header("Location: upload_sales.php");
                        exit();

                    } // end header check
                } // end fopen check
            } // end duplicate check
        } // end extension check
    } // end file check
} // end POST

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<?php if (isset($_SESSION["upload_success"])): ?>
    <?php
        $upload = $_SESSION["upload_success"];
        unset($_SESSION["upload_success"]);
    ?>
    <div class="alert alert-<?php echo $upload['status'] === 'failed' ? 'error' : 'success'; ?>">
        <strong>Upload <?php echo ucfirst($upload['status']); ?></strong><br><br>
        File : <b><?php echo htmlspecialchars($upload["file"]); ?></b><br>
        Total Rows : <b><?php echo number_format($upload["rows"]); ?></b><br>
        Successfully Imported : <b><?php echo number_format($upload["valid"]); ?></b><br>
        Skipped : <b><?php echo number_format($upload["invalid"]); ?></b>
    </div>

    <?php if (!empty($upload["skipReasons"])): ?>
        <div class="panel" style="border-left: 4px solid #e07bb5;">
            <h3 style="color:#a0477a; margin-bottom:10px;">⚠️ Skipped Rows (<?php echo count($upload["skipReasons"]); ?>)</h3>
            <ul style="font-size:13px; color:#555; line-height:2;">
                <?php foreach ($upload["skipReasons"] as $reason): ?>
                    <li><?php echo $reason; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php if ($error !== ""): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="topbar">
    <div>
        <h1>Upload Sales Data</h1>
        <p>Upload monthly sales data for demand forecasting.</p>
    </div>
</div>

<a href="download_template.php" class="btn btn-primary" style="margin-bottom:15px; display:inline-block;">
    📥 Download CSV Template
</a>

<div class="panel">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Select CSV File</label>
            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Upload Sales Data</button>
    </form>
</div>

<div class="panel">
    <h2>Required Item in CSV</h2>
    <div class="alert alert-success">
        sale_date, item_name, category, quantity_sold, unit_price
    </div>

    <p><strong>Example</strong></p>
    <div class="alert alert-success">
        2025-01-01, Iced Coffee, Drink, 10, 4.50<br>
        2025-01-01, Nasi Lemak, Food, 5, 5.00<br>
        2025-01-02, Milo Ais, Drink, 8, 3.50
    </div>

    <ul>
        <li>Notes</li>
        <li>CSV files only.</li>
        <li>Accepted date formats: <b>2025-01-05</b>, <b>05/01/2025</b>, <b>01/05/2025</b>, <b>2025/01/05</b>, <b>1/5/2025</b>.</li>
        <li>Menu item name must match an existing item in Menu Management.</li>
        <li>Quantity must be 1 or more. Unit price must not be negative.</li>
        <li>The same file cannot be uploaded twice — rename it if it contains new data.</li>
        <li>Every upload is recorded in Upload History.</li>
    </ul>
</div>

<?php include "../includes/footer.php"; ?>