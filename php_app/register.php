<?php
require_once "config/app.php";
require_once "config/db.php";
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === "admin") header("Location: " . BASE_URL . "admin/dashboard.php");
    elseif ($_SESSION['role'] === "owner") header("Location: " . BASE_URL . "owner/dashboard.php");
    else header("Location: " . BASE_URL . "staff/dashboard.php");
    exit();
}

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = trim($_POST["password"] ?? "");
    $confirm   = trim($_POST["confirm_password"] ?? "");
    $role      = trim($_POST["role"] ?? "");

    if ($full_name === "" || $email === "" || $password === "" || $confirm === "" || $role === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!in_array($role, ["owner", "staff"])) {
        $error = "Invalid role selected.";
    } else {
        // Check duplicate email
        $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, "s", $email);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);

        if (mysqli_stmt_num_rows($chk) > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Status = active (Admin can deactivate if needed via User Management)
            $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
            mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $hashed, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! You can now <a href='login.php'>login here</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #3b2417, #b8793b);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }

        .card {
            background: #fff;
            border-radius: 18px;
            padding: 38px 36px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        .card h1 { color: #3b2417; font-size: 26px; text-align: center; margin-bottom: 4px; }
        .card .sub { text-align: center; color: #888; margin-bottom: 26px; font-size: 14px; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 6px; color: #333; font-size: 14px; }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus { border-color: #b8793b; }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #6f3f1f;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #4b2b16; }

        .alert-error {
            background: #ffe3e3; color: #a80000;
            padding: 12px 14px; border-radius: 10px;
            margin-bottom: 16px; font-size: 14px;
        }
        .alert-success {
            background: #d4edda; color: #155724;
            padding: 12px 14px; border-radius: 10px;
            margin-bottom: 16px; font-size: 14px;
        }
        .alert-success a { color: #155724; font-weight: bold; }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }
        .footer-links a { color: #6f3f1f; font-weight: bold; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }

        .role-note {
            background: #f8f4ef;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            color: #7a5c3a;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>🍵 QT Cafe</h1>
    <p class="sub">Create your account</p>

    <?php if ($error !== ""): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php else: ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="e.g. Ahmad Bin Ali" required
                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="your@email.com" required
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Minimum 6 characters" required minlength="6">
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter password" required>
        </div>

        <div class="form-group">
            <label>Register as</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="owner" <?php echo ($_POST['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner</option>
                <option value="staff" <?php echo ($_POST['role'] ?? '') === 'staff' ? 'selected' : ''; ?>>Staff</option>
            </select>
            <div class="role-note">
                ⚠️ Admin accounts are created by the system administrator only.
            </div>
        </div>

        <button type="submit" class="btn-submit">Create Account</button>
    </form>

    <?php endif; ?>

    <div class="footer-links">
        Already have an account? <a href="login.php">Login here</a>
        &nbsp;·&nbsp; <a href="index.php">← Back to Home</a>
    </div>
</div>

</body>
</html>