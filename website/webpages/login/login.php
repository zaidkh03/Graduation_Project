<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $national_id = $_POST['national_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE national_id = ?");
    $stmt->bind_param("s", $national_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Store login session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['related_id'] = $user['related_id'];

            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: ../student/pages/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: ../teacher/pages/dashboard.php");
                    break;
                case 'parent':
                    header("Location: ../parent/pages/dashboard.php");
                    break;
                case 'admin':
                    header("Location: ../admin/pages/dashboard.php");
                    break;
                default:
                    echo "Unknown role.";
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="styles/login.css">

</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="overlay-content">
        <img src="styles/logo.png" alt="Logo" class="logo" />
        <h1 class="ishraf-title">إشراف</h1>
      </div>
    </div>
    <div class="right-panel">
      <form class="login-form" method="POST" action="login.php">
        <h2>Sign in</h2>
        <input type="text" name="national_id" placeholder="Please Enter Your ID" required aria-label="National ID" />
        <input type="password" name="password" placeholder="Please Enter Your Password" required aria-label="Password" />
        <button type="submit">Sign in</button>
        <!-- <a href="forgot-password.html" class="forgot-password">Forgot Password?</a> -->
      </form>
    </div>
  </div>
</body>
</html>