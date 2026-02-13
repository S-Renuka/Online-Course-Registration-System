<?php
session_start();
include '../db/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<style>
body { font-family: Arial; background:#f4f6f8; display:flex; justify-content:center; align-items:center; height:100vh; }
.login-box { background:white; padding:30px; width:350px; border-radius:10px; box-shadow:0 0 10px #ccc; }
h2 { text-align:center; color:#004d99; }
input { width:100%; padding:10px; margin:10px 0; }
button { width:100%; padding:10px; background:#004d99; color:white; border:none; border-radius:5px; }
.error { color:red; text-align:center; }
</style>
</head>

<body>
<div class="login-box">
<h2>🔐 Admin Login</h2>

<?php if ($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST">
<input type="text" name="username" placeholder="Admin Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>
