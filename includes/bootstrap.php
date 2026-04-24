<?php
/**
 * Bootstrap / Constants
 * Include this at the top of every page.
 */

// Detect base URL dynamically for XAMPP compatibility
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = dirname($_SERVER['SCRIPT_NAME']);

// Walk up until we reach the project root (folder named "Online Examination" or "online_exam")
// We store BASE_URL relative to the project root
define('BASE_URL', rtrim($protocol . '://' . $host . dirname(dirname(__FILE__)), '/'));
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/exam.php';
require_once ROOT_PATH . '/includes/question.php';
