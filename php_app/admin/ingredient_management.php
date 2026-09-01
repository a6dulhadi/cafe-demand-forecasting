<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Ingredient Management";

$success = "";
$error = "";
$editMode = false;

$editData = [
    "id" => "",
    "ingredient_name" => "",
    "unit" => "",
    "current_stock" => "",
    "minimum_stock" => ""
];

/* ADD INGREDIENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_ingredient"])) {
    $ingredient_name = trim($_POST["ingredient_name"] ?? "");
    $unit = trim($_POST["unit"] ?? "");
    $current_stock = trim($_POST["current_stock"] ?? "0");
    $minimum_stock = trim($_POST["minimum_stock"] ?? "0");

    if ($ingredient_name === "" || $unit === "") {
        $error = "Please fill in ingredient name and unit.";
    } elseif (!is_numeric($current_stock) || !is_numeric($minimum_stock)) {
        $error = "Stock values must be valid numbers.";
    } elseif ($current_stock < 0 || $minimum_stock < 0) {
        $error = "Stock values cannot be negative.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM ingredients WHERE ingredient_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $ingredient_name);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "This ingredient already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO ingredients (ingredient_name, unit, current_stock, minimum_stock)
                VALUES (?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "ssdd", $ingredient_name, $unit, $current_stock, $minimum_stock);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Ingredient added successfully.";
            } else {
                $error = "Failed to add ingredient.";
            }
        }
    }
}

/* UPDATE INGREDIENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_ingredient"])) {
    $id = intval($_POST["id"] ?? 0);
    $ingredient_name = trim($_POST["ingredient_name"] ?? "");
    $unit = trim($_POST["unit"] ?? "");
    $current_stock = trim($_POST["current_stock"] ?? "0");
    $minimum_stock = trim($_POST["minimum_stock"] ?? "0");

    if ($id <= 0 || $ingredient_name === "" || $unit === "") {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($current_stock) || !is_numeric($minimum_stock)) {
        $error = "Stock values must be valid numbers.";
    } elseif ($current_stock < 0 || $minimum_stock < 0) {
        $error = "Stock values cannot be negative.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM ingredients WHERE ingredient_name = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check, "si", $ingredient_name, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Another ingredient with this name already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "
                UPDATE ingredients
                SET ingredient_name = ?, unit = ?, current_stock = ?, minimum_stock = ?
                WHERE id = ?
            ");

            mysqli_stmt_bind_param($stmt, "ssddi", $ingredient_name, $unit, $current_stock, $minimum_stock, $id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Ingredient updated successfully.";
            } else {
                $error = "Failed to update ingredient.";
            }
        }
    }
}

/* DELETE INGREDIENT */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM ingredients WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Ingredient deleted successfully.";
        } else {
            $error = "Unable to delete ingredient. It may already be linked to recipe or forecast data.";
        }
    }
}

/* LOAD EDIT DATA */
if (isset($_GET["edit"])) {
    $editId = intval($_GET["edit"]);

    if ($editId > 0) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM ingredients WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $editId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $editMode = true;
            $editData = mysqli_fetch_assoc($result);
        }
    }
}

/* FETCH INGREDIENTS */
$ingredientResult = mysqli_query($conn, "SELECT * FROM ingredients ORDER BY created_at DESC");

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Ingredient Management</h1>
        <p>Manage ingredients used by QT Cafe menu items for future ingredient estimation.</p>
    </div>
    <div class="user-info">
        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong><br>
        <span class="badge"><?php echo strtoupper(htmlspecialchars($_SESSION['role'])); ?></span>
    </div>
</div>

<?php if ($success !== ""): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error !== ""): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="panel">
    <h2><?php echo $editMode ? "Edit Ingredient" : "Add New Ingredient"; ?></h2>

    <form method="POST">
        <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Ingredient Name</label>
                <input type="text" name="ingredient_name" class="form-control"
                       value="<?php echo htmlspecialchars($editData['ingredient_name']); ?>"
                       placeholder="Example: Coffee Powder" required>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit" class="form-control" required>
                    <option value="">Select unit</option>
                    <?php
                    $units = ["g", "kg", "ml", "L", "pcs", "pack", "cup", "tbsp", "tsp"];
                    foreach ($units as $unit):
                    ?>
                        <option value="<?php echo $unit; ?>"
                            <?php echo ($editData['unit'] === $unit) ? "selected" : ""; ?>>
                            <?php echo $unit; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Stock</label>
                <input type="number" step="0.01" name="current_stock" class="form-control"
                       value="<?php echo htmlspecialchars($editData['current_stock']); ?>"
                       placeholder="Example: 1000" required>
            </div>

            <div class="form-group">
                <label>Minimum Stock</label>
                <input type="number" step="0.01" name="minimum_stock" class="form-control"
                       value="<?php echo htmlspecialchars($editData['minimum_stock']); ?>"
                       placeholder="Example: 200" required>
            </div>
        </div>

        <?php if ($editMode): ?>
            <button type="submit" name="update_ingredient" class="btn btn-primary">Update Ingredient</button>
            <a href="ingredient_management.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_ingredient" class="btn btn-primary">Add Ingredient</button>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <h2>Ingredient List</h2>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Ingredient</th>
                    <th>Unit</th>
                    <th>Current Stock</th>
                    <th>Minimum Stock</th>
                    <th>Stock Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($ingredientResult && mysqli_num_rows($ingredientResult) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($ingredientResult)): ?>
                        <?php
                        $stockStatus = "Sufficient";
                        $stockClass = "status-active";

                        if ($row["current_stock"] <= $row["minimum_stock"]) {
                            $stockStatus = "Low Stock";
                            $stockClass = "status-inactive";
                        }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row["ingredient_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["unit"]); ?></td>
                            <td><?php echo number_format($row["current_stock"], 2); ?></td>
                            <td><?php echo number_format($row["minimum_stock"], 2); ?></td>
                            <td>
                                <span class="<?php echo $stockClass; ?>">
                                    <?php echo $stockStatus; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="ingredient_management.php?edit=<?php echo $row["id"]; ?>" class="btn btn-warning">Edit</a>
                                    <a href="ingredient_management.php?delete=<?php echo $row["id"]; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this ingredient?');">
                                       Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No ingredients found. Add the first ingredient above.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>