<?php

require_once "../includes/auth.php";

requireRole("admin");

$pageTitle = "User Management";

include "../includes/header.php";
include "../includes/sidebar.php";

require_once "../config/db.php";

$message = "";
$error = "";

/* ---- Handle Add User ---- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    if ($_POST["action"] === "add") {
        $full_name = trim($_POST["full_name"] ?? "");
        $email     = trim($_POST["email"] ?? "");
        $password  = trim($_POST["password"] ?? "");
        $role      = trim($_POST["role"] ?? "");

        if ($full_name === "" || $email === "" || $password === "" || $role === "") {
            $error = "All fields are required.";
        } elseif (!in_array($role, ["owner", "staff"])) {
            $error = "Invalid role selected.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check duplicate email
            $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($checkStmt, "s", $email);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);

            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $error = "Email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $hashed, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "User '{$full_name}' added successfully.";
                } else {
                    $error = "Failed to add user: " . mysqli_error($conn);
                }
            }
        }
    }

    elseif ($_POST["action"] === "toggle_status") {
        $user_id    = intval($_POST["user_id"] ?? 0);
        $new_status = $_POST["new_status"] === "active" ? "active" : "inactive";

        if ($user_id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
            mysqli_stmt_execute($stmt);
            $message = "User status updated.";
        }
    }

    elseif ($_POST["action"] === "delete") {
        $user_id = intval($_POST["user_id"] ?? 0);
        if ($user_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $message = "User deleted.";
        }
    }

    elseif ($_POST["action"] === "reset_password") {
        $user_id      = intval($_POST["user_id"] ?? 0);
        $new_password = trim($_POST["new_password"] ?? "");
        if ($user_id > 0 && $new_password !== "") {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);
            mysqli_stmt_execute($stmt);
            $message = "Password reset successfully.";
        } else {
            $error = "Please enter a new password.";
        }
    }
}

/* ---- Load Users ---- */
$users = mysqli_query($conn, "
    SELECT id, full_name, email, role, status, created_at
    FROM users
    ORDER BY role, full_name
");

?>

<div class="topbar">
    <div>
        <h1>User Management</h1>
        <p>Manage Owner and Staff accounts. Admin accounts are managed separately.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        + Add User
    </button>
</div>

<?php if ($message !== ""): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if ($error !== ""): ?>
    <div class="alert alert-warning"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="panel">
    <table class="table">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?php echo htmlspecialchars($u["full_name"]); ?></td>
            <td><?php echo htmlspecialchars($u["email"]); ?></td>
            <td>
                <span class="badge badge-<?php echo $u["role"]; ?>">
                    <?php echo ucfirst($u["role"]); ?>
                </span>
            </td>
            <td>
                <span class="badge badge-<?php echo $u["status"] === "active" ? "active" : "inactive"; ?>">
                    <?php echo ucfirst($u["status"]); ?>
                </span>
            </td>
            <td><?php echo date("d M Y", strtotime($u["created_at"])); ?></td>
            <td>
                <?php if ($u["role"] !== "admin"): ?>
                <!-- Toggle Status -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="user_id" value="<?php echo $u["id"]; ?>">
                    <input type="hidden" name="new_status" value="<?php echo $u["status"] === "active" ? "inactive" : "active"; ?>">
                    <button type="submit" class="btn btn-sm <?php echo $u["status"] === "active" ? "btn-warning" : "btn-success"; ?>"
                        onclick="return confirm('<?php echo $u["status"] === "active" ? "Deactivate" : "Activate"; ?> this user?')">
                        <?php echo $u["status"] === "active" ? "Deactivate" : "Activate"; ?>
                    </button>
                </form>

                <!-- Reset Password -->
                <button class="btn btn-sm btn-secondary"
                    onclick="openResetModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['full_name'], ENT_QUOTES); ?>')">
                    Reset Password
                </button>

                <!-- Delete -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?php echo $u["id"]; ?>">
                    <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete user <?php echo htmlspecialchars($u["full_name"], ENT_QUOTES); ?>? This cannot be undone.')">
                        Delete
                    </button>
                </form>
                <?php else: ?>
                    <span style="color:#aaa; font-size:13px;">Protected</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- ===== Add User Modal ===== -->
<div id="addModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; padding:32px; width:420px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h2 style="margin-top:0;">Add New User</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required placeholder="e.g. Ahmad Bin Ali">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="user@cafe.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Min 6 characters" minlength="6">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <option value="">-- Select Role --</option>
                    <option value="owner">Owner</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Add User</button>
                <button type="button" class="btn btn-secondary" style="flex:1;"
                    onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Reset Password Modal ===== -->
<div id="resetModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; padding:32px; width:380px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h2 style="margin-top:0;">Reset Password</h2>
        <p id="resetUserName" style="color:#666;"></p>
        <form method="POST">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="resetUserId">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="Enter new password" minlength="6">
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Reset</button>
                <button type="button" class="btn btn-secondary" style="flex:1;"
                    onclick="document.getElementById('resetModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
.badge-admin    { background: #e8d5f5; color: #6a0dad; }
.badge-owner    { background: #d4edda; color: #155724; }
.badge-staff    { background: #cce5ff; color: #004085; }
.badge-active   { background: #d4edda; color: #155724; }
.badge-inactive { background: #f8d7da; color: #721c24; }

.btn-sm { padding: 5px 10px; font-size: 12px; margin: 2px; }
.btn-secondary { background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; padding: 10px 16px; }
.btn-secondary:hover { background: #5a6268; }
.btn-warning { background: #e0a800; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
.btn-warning:hover { background: #c69500; }
.btn-danger  { background: #c82333; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
.btn-danger:hover { background: #a71d2a; }
.btn-success { background: #218838; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
.btn-success:hover { background: #1e7e34; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-weight: bold; margin-bottom: 6px; }
.form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
</style>

<script>
function openResetModal(userId, userName) {
    document.getElementById("resetUserId").value = userId;
    document.getElementById("resetUserName").textContent = "User: " + userName;
    document.getElementById("resetModal").style.display = "flex";
}
</script>

<?php include "../includes/footer.php"; ?>