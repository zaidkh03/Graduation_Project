<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 👇 Redirect logged-in users away from the login page
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
  header("Location: ../{$_SESSION['role']}/pages/dashboard.php");
  exit();
}

include '../db_connection.php';

$error = "";
$entered_id = ""; // To retain entered ID after failed login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $national_id = $_POST['national_id'];
  $password = $_POST['password'];
  $entered_id = htmlspecialchars($national_id); // Escape to avoid XSS

  $stmt = $conn->prepare("SELECT * FROM users WHERE national_id = ?");
  $stmt->bind_param("s", $national_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['related_id'] = $user['related_id'];


      // NEW: Harden session and cache
      session_regenerate_id(true);
      header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
      header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
      header("Pragma: no-cache");
      // Default name value
      $name = "User";

      // Prepare SQL based on role
      $query = "";
      switch ($user['role']) {
        case 'admin':
          $query = "SELECT name FROM admins WHERE id = ?";
          break;
        case 'teacher':
          $query = "SELECT name FROM teachers WHERE id = ?";
          break;
        case 'student':
          $query = "SELECT name FROM students WHERE id = ?";
          break;
        case 'parent':
          $query = "SELECT name FROM parents WHERE id = ?";
          break;
      }

      if (!empty($query)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
          $stmt->bind_param("i", $user['related_id']);
          $stmt->execute();
          $stmt->bind_result($fetched_name);
          if ($stmt->fetch()) {
            $name = $fetched_name; // Only overwrite if fetch is successful
          }
          $stmt->close();
        }
      }

      $_SESSION['name'] = $name;

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
          $error = "Unknown role.";
      }
      exit();
    } else {
      $error = "Incorrect password. Please try again.";
    }
  } else {
    $error = "No account found with that ID.";
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
  <style>
    .error-message {
      color: #b30000;
      background-color: #ffe6e6;
      border: 1px solid #ffcccc;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
    }

    .container {
      display: flex;
      height: 100vh;
    }

    .left-panel {
      flex: 1;
      background-color: #f0f4ff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }

    .overlay-content img.logo {
      width: 100px;
      margin-bottom: 10px;
    }

    .ishraf-title {
      font-size: 24px;
      font-weight: bold;
    }

    .right-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-form {
      width: 100%;
      max-width: 350px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .login-form input {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .login-form button {
      padding: 10px;
      border: none;
      border-radius: 20px;
      background-color: #333;
      color: white;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="left-panel">
      <div class="overlay-content">
        <img src="styles/logo.png" alt="Logo" class="logo" />
        <h1 class="ishraf-title">Madrasati</h1>
      </div>
    </div>
    <div class="right-panel">
      <form class="login-form" method="POST" action="login.php">
        <h2>Sign in</h2>
        <?php if (!empty($error)): ?>
          <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <input type="text" name="national_id" placeholder="Please Enter Your ID" required aria-label="National ID" value="<?php echo $entered_id; ?>" />
        <input type="password" name="password" placeholder="Please Enter Your Password" required aria-label="Password" />
        <button type="submit">Sign in</button>
        <!-- <a href="forgot-password.html" class="forgot-password">Forgot Password?</a> -->
      </form>
    </div>
  </div>
</body>

</html>