<?php
session_start();
require_once '../class/auth/Auth.php';
use Class\Auth\Auth;
Auth::logout();
header('Location: index.php');
exit;
?>
