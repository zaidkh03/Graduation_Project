<?php

include 'connect/connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Madrasati Login</title>
  <link rel="stylesheet" href="../login/loginstyls/log.css" />   
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="overlay-content">
        <img src="logo3.png" alt="Logo" class="logo" />
        <h1 class="ishraf-title">Madrasati</h1>
      </div>
    </div>
    <div class="right-panel">
      <form class="login-form" method="post" action="login.php">
        <h2>Sign in</h2>
        <input type="text" name="user_id" placeholder="Please Enter Your ID" required />
        <input type="password" name="password" placeholder="Please Enter Your Password" required />
        <button type="submit">Sign in</button>
      </form>
    </div>
  </div>
</body>
</html>
