<?php
session_start();

session_unset();
session_destroy();

header("location: /mah-portal/public/login.php");
exit;
?>