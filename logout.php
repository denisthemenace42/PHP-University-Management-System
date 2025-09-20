<?php
require_once 'Auth.php';
Auth::logout();
header("Location: /login.php?logout=1");
exit;
?>
