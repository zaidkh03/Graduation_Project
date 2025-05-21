<?php
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600); // remove session cookie

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");
header("Location: login.php");
exit();

exit();
