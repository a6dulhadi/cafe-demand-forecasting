<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Menu Management";

$success = "";
$error = "";
$editMode = false;
$editData = [
    "id" => "",
    "item_name" => "",
    "category" => "",
    "price" => "",
    "status" => "active"
];

/* ADD MENU */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_menu"])) {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $status = trim($_POST["status"] ?? "active");

    if ($item_name === "" || $category === "" || $price === "") {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid number.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE item_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $item_name);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "This menu item already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO menu_items (item_name, category, price, status)
                VALUES (?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "ssds", $item_name, $category, $price, $status);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Menu added successfully.";
            } else {
                $error = "Failed to add menu.";
            }
        }
    }
}

/* UPDATE MENU */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_menu"])) {
    $id = intval($_POST["id"] ?? 0);
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $status = trim($_POST["status"] ?? "active");

    if ($id <= 0 || $item_name === "" || $category === "" || $price === "") {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Price must be a valid number.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM menu_items WHERE item_name = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check, "si", $item_name, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Another menu with this name already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "
                UPDATE menu_items
                SET item_name = ?, category = ?, price = ?, status = ?
                WHERE id = ?
            ");
            mysqli_stmt_bind_param($stmt, "ssdsi", $item_name, $category, $price, $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Menu updated successfully.";
            } else {
                $error = "Failed to update menu.";
            }
        }
    }
}

/* DELETE MENU */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Menu deleted successfully.";
        } else {
            $error = "Unable to delete menu. It may already be linked to sales or recipe data.";
        }
    }
}

/* LOAD EDIT DATA */
if (isset($_GET["edit"])) {
    $editId = intval($_GET["edit"]);

    if ($editId > 0) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM menu_items WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $editId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $editMode = true;
            $editData = mysqli_fetch_assoc($result);
        }
    }
}

/* FETCH MENU */
$menuResult = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY created_at DESC");

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Menu Management</h1>
        <p>Add and manage QT Cafe menu used for sales analysis and demand prediction.</p>
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
    <h2><?php echo $editMode ? "Edit Menu " : "Add Menu "; ?></h2>

    <form method="POST">
        <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Menu Name</label>
                <input type="text" name="item_name" class="form-control"
                       value="<?php echo htmlspecialchars($editData['item_name']); ?>"
                       placeholder="Example: Iced Coffee" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" class="form-control" required>
                    <option value="">Select category</option>
                    <?php
                    $categories = ["Food", "Drink", "Dessert", "Snack", "Other"];
                    foreach ($categories as $cat):
                    ?>
                        <option value="<?php echo $cat; ?>"
                            <?php echo ($editData['category'] === $cat) ? "selected" : ""; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Price (RM)</label>
                <input type="number" step="0.01" name="price" class="form-control"
                       value="<?php echo htmlspecialchars($editData['price']); ?>"
                       placeholder="Example: 5.00" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="active" <?php echo ($editData['status'] === "active") ? "selected" : ""; ?>>Active</option>
                    <option value="inactive" <?php echo ($editData['status'] === "inactive") ? "selected" : ""; ?>>Inactive</option>
                </select>
            </div>
        </div>

        <?php if ($editMode): ?>
            <button type="submit" name="update_menu" class="btn btn-primary">Update Menu</button>
            <a href="menu_management.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_menu" class="btn btn-primary">Add Menu </button>
        <?php endif; ?>
    </form>
</div>

<div class="panel">
    <h2>Menu List</h2>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Menu</th>
                    <th>Category</th>
                    <th>Price (RM)</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($menuResult && mysqli_num_rows($menuResult) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($menuResult)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["category"]); ?></td>
                            <td><?php echo number_format($row["price"], 2); ?></td>
                            <td>
                                <span class="<?php echo $row["status"] === "active" ? "status-active" : "status-inactive"; ?>">
                                    <?php echo ucfirst($row["status"]); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="menu_management.php?edit=<?php echo $row["id"]; ?>" class="btn btn-warning">Edit</a>
                                    <a href="menu_management.php?delete=<?php echo $row["id"]; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this menu?');">
                                       Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No menu found. Add the first menu above.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>