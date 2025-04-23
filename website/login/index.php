<?php

include 'connect/connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ISHRAF Login</title>
  <style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  
  body, html {
    height: 100%;
    width: 100%;
  }
  
  .container {
    display: flex;
    height: 100vh;
    width: 100%;
  }
  
  .left-panel {
    width: 50%;
    background-image: url('ishraf.png');
    background-size: cover;
    background-position: center;
    position: relative;
  }
  
  .overlay-content {
    position: absolute;
    top: 50%;
    left: 50px;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
  }
  
  .ishraf-title {
    font-size: 36px;
    font-weight: bold;
    color: #000;
  }
  
  .right-panel {
    width: 50%;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .login-form {
    width: 80%;
    max-width: 400px;
  }
  
  .login-form h2 {
    margin-bottom: 30px;
    font-size: 24px;
    color: #222;
  }
  
  .login-form input {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
  }
  
  .login-form button {
    width: 100%;
    padding: 12px;
    background-color: #2e2e2e;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 16px;
  }
  
  .login-form button:hover {
    background-color: #444;
  }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="overlay-content">
        <img src="logo3.png" alt="Logo" class="logo" />
        <h1 class="ishraf-title">إشراف</h1>
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
