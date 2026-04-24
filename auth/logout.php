<?php
/**
 * Logout — destroys session and redirects to home
 */
session_start();
session_unset();
session_destroy();
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
header('Location: ' . $base . '/auth/login.php');
exit;
