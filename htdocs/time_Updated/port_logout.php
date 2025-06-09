<?php

session_start();

// Unset only port login session variables
unset($_SESSION['port_user']);
unset($_SESSION['port_name']);

header("Location: port_login.php");
exit();