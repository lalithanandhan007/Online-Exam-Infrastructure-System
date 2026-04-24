<?php
/**
 * Reusable sidebar for Student pages
 * Variables expected: $pageTitle (string), $activePage (string)
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));
}
$nameInitial = strtoupper(substr($_SESSION['name'] ?? 'S', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Student Dashboard') ?> — ExamFlow</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
</head>
<body class="dashboard-body">
<div class="dashboard-layout">
<div class="overlay" id="sidebar-overlay"></div>

<!-- ── Sidebar ─────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand"><div class="brand-icon">🎓</div>ExamFlow</div>
  <div class="sidebar-user">
    <div class="user-avatar"><?= $nameInitial ?></div>
    <div class="user-name"><?= htmlspecialchars($_SESSION['name'] ?? '') ?></div>
    <div class="user-role">Student</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-title">Main</div>
    <a class="sidebar-link <?= ($activePage==='dashboard')?'active':'' ?>" href="<?= BASE_URL ?>/student/dashboard.php">
      <span class="link-icon">🏠</span> Dashboard
    </a>
    <a class="sidebar-link <?= ($activePage==='join_exam')?'active':'' ?>" href="<?= BASE_URL ?>/student/join_exam.php">
      <span class="link-icon">🔗</span> Join Exam
    </a>
    <div class="nav-section-title">Account</div>
    <a class="sidebar-link" href="<?= BASE_URL ?>/auth/logout.php">
      <span class="link-icon">🚪</span> Logout
    </a>
  </nav>
</aside>

<!-- ── Main ───────────────────────────────────────── -->
<div class="dashboard-main">
  <header class="topbar">
    <div class="d-flex align-center gap-2">
      <button class="sidebar-toggle" id="sidebar-toggle"><span></span><span></span><span></span></button>
      <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
    </div>
    <div class="topbar-right">
      <div class="topbar-avatar"><?= $nameInitial ?></div>
    </div>
  </header>
  <main class="dashboard-content">
