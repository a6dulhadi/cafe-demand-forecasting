<?php
session_start();

require_once "config/app.php";
require_once "config/db.php";

$error = "";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === "admin") {
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === "owner") {
        header("Location: " . BASE_URL . "owner/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === "staff") {
        header("Location: " . BASE_URL . "staff/dashboard.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if ($user['status'] !== "active") {
                $error = "Your account is inactive. Please contact admin.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === "admin") {
                    header("Location: " . BASE_URL . "admin/dashboard.php");
                } elseif ($user['role'] === "owner") {
                    header("Location: " . BASE_URL . "owner/dashboard.php");
                } elseif ($user['role'] === "staff") {
                    header("Location: " . BASE_URL . "staff/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "index.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo APP_NAME; ?></title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #3b2417, #b8793b);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 420px;
            background: #ffffff;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }

        .login-card h1 {
            margin: 0;
            color: #3b2417;
            text-align: center;
            font-size: 28px;
        }

        .login-card p {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
        }

        input:focus {
            border-color: #b8793b;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #6f3f1f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #4b2b16;
        }

        .error {
            background: #ffe3e3;
            color: #a80000;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        .demo {
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            line-height: 1.6;
            background: #f8f4ef;
            padding: 12px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h1>QT Cafe</h1>
    <p>Menu Demand Forecasting System</p>

    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    
</div>

</body>
</html>