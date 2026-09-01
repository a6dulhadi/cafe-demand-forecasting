<?php
require_once "../includes/auth.php";
requireRole("admin");

$pageTitle = "Recipe Management";

$success = "";
$error = "";
$editMode = false;

$editData = [
    "id" => "",
    "menu_item_id" => "",
    "ingredient_id" => "",
    "quantity_required" => ""
];

/* FETCH MENU ITEMS */
$menuItems = [];
$menuQuery = mysqli_query($conn, "SELECT id, item_name, category FROM menu_items ORDER BY item_name ASC");
if ($menuQuery) {
    while ($row = mysqli_fetch_assoc($menuQuery)) {
        $menuItems[] = $row;
    }
}

/* FETCH INGREDIENTS */
$ingredients = [];
$ingredientQuery = mysqli_query($conn, "SELECT id, ingredient_name, unit FROM ingredients ORDER BY ingredient_name ASC");
if ($ingredientQuery) {
    while ($row = mysqli_fetch_assoc($ingredientQuery)) {
        $ingredients[] = $row;
    }
}

/* ADD RECIPE */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_recipe"])) {
    $menu_item_id = intval($_POST["menu_item_id"] ?? 0);
    $ingredient_id = intval($_POST["ingredient_id"] ?? 0);
    $quantity_required = trim($_POST["quantity_required"] ?? "");

    if ($menu_item_id <= 0 || $ingredient_id <= 0 || $quantity_required === "") {
        $error = "Please select menu item, ingredient, and quantity required.";
    } elseif (!is_numeric($quantity_required) || $quantity_required <= 0) {
        $error = "Quantity required must be a valid number greater than 0.";
    } else {
        $check = mysqli_prepare($conn, "
            SELECT id FROM recipes 
            WHERE menu_item_id = ? AND ingredient_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($check, "ii", $menu_item_id, $ingredient_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "This ingredient is already added to the selected menu item.";
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO recipes (menu_item_id, ingredient_id, quantity_required)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "iid", $menu_item_id, $ingredient_id, $quantity_required);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Recipe ingredient added successfully.";
            } else {
                $error = "Failed to add recipe ingredient.";
            }
        }
    }
}

/* UPDATE RECIPE */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_recipe"])) {
    $id = intval($_POST["id"] ?? 0);
    $menu_item_id = intval($_POST["menu_item_id"] ?? 0);
    $ingredient_id = intval($_POST["ingredient_id"] ?? 0);
    $quantity_required = trim($_POST["quantity_required"] ?? "");

    if ($id <= 0 || $menu_item_id <= 0 || $ingredient_id <= 0 || $quantity_required === "") {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($quantity_required) || $quantity_required <= 0) {
        $error = "Quantity required must be a valid number greater than 0.";
    } else {
        $check = mysqli_prepare($conn, "
            SELECT id FROM recipes 
            WHERE menu_item_id = ? AND ingredient_id = ? AND id != ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($check, "iii", $menu_item_id, $ingredient_id, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "This ingredient is already linked to the selected menu item.";
        } else {
            $stmt = mysqli_prepare($conn, "
                UPDATE recipes
                SET menu_item_id = ?, ingredient_id = ?, quantity_required = ?
                WHERE id = ?
            ");
            mysqli_stmt_bind_param($stmt, "iidi", $menu_item_id, $ingredient_id, $quantity_required, $id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Recipe ingredient updated successfully.";
            } else {
                $error = "Failed to update recipe ingredient.";
            }
        }
    }
}

/* DELETE RECIPE */
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM recipes WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Recipe ingredient deleted successfully.";
        } else {
            $error = "Failed to delete recipe ingredient.";
        }
    }
}

/* LOAD EDIT DATA */
if (isset($_GET["edit"])) {
    $editId = intval($_GET["edit"]);

    if ($editId > 0) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM recipes WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $editId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $editMode = true;
            $editData = mysqli_fetch_assoc($result);
        }
    }
}

/* FETCH RECIPE LIST */
$recipeResult = mysqli_query($conn, "
    SELECT 
        r.id,
        r.quantity_required,
        r.created_at,
        mi.item_name,
        mi.category,
        ing.ingredient_name,
        ing.unit
    FROM recipes r
    INNER JOIN menu_items mi ON r.menu_item_id = mi.id
    INNER JOIN ingredients ing ON r.ingredient_id = ing.id
    ORDER BY mi.item_name ASC, ing.ingredient_name ASC
");

include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="topbar">
    <div>
        <h1>Recipe Management</h1>
        <p>Connect QT Cafe menu items with ingredients to support future ingredient estimation.</p>
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

<?php if (count($menuItems) === 0 || count($ingredients) === 0): ?>
    <div class="alert alert-error">
        Please add menu items and ingredients first before creating recipes.
    </div>
<?php endif; ?>

<div class="panel">
    <h2><?php echo $editMode ? "Edit Recipe Ingredient" : "Add Recipe Ingredient"; ?></h2>

    <form method="POST">
        <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Menu Item</label>
                <select name="menu_item_id" class="form-control" required>
                    <option value="">Select menu item</option>
                    <?php foreach ($menuItems as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo ((int)$editData['menu_item_id'] === (int)$item['id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($item['item_name'] . " (" . $item['category'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Ingredient</label>
                <select name="ingredient_id" class="form-control" required>
                    <option value="">Select ingredient</option>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <option value="<?php echo $ingredient['id']; ?>"
                            <?php echo ((int)$editData['ingredient_id'] === (int)$ingredient['id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($ingredient['ingredient_name'] . " (" . $ingredient['unit'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity Required Per Menu Item</label>
                <input type="number" step="0.01" name="quantity_required" class="form-control"
                       value="<?php echo htmlspecialchars($editData['quantity_required']); ?>"
                       placeholder="Example: 20" required>
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <?php if ($editMode): ?>
                    <button type="submit" name="update_recipe" class="btn btn-primary">Update Recipe</button>
                    <a href="recipe_management.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_recipe" class="btn btn-primary">Add Recipe</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<div class="panel">
    <h2>Recipe List</h2>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Menu Item</th>
                    <th>Category</th>
                    <th>Ingredient</th>
                    <th>Quantity Per Item</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($recipeResult && mysqli_num_rows($recipeResult) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($recipeResult)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["category"]); ?></td>
                            <td><?php echo htmlspecialchars($row["ingredient_name"]); ?></td>
                            <td>
                                <?php echo number_format($row["quantity_required"], 2); ?>
                                <?php echo htmlspecialchars($row["unit"]); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="recipe_management.php?edit=<?php echo $row["id"]; ?>" class="btn btn-warning">Edit</a>
                                    <a href="recipe_management.php?delete=<?php echo $row["id"]; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this recipe ingredient?');">
                                       Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No recipe data found. Add recipe ingredients above.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>