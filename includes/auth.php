<?php
/**
 * Authentication Helper
 * Handles session guard and role-based access control
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in; redirect to login otherwise.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require a specific role; redirect to relevant dashboard otherwise.
 */
function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        $redirect = ($_SESSION['role'] === 'teacher')
            ? BASE_URL . '/teacher/dashboard.php'
            : BASE_URL . '/student/dashboard.php';
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Check if user is logged in (non-destructive).
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Return current user's role or null.
 */
function currentRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Return current user's id or null.
 */
function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Return current user's name or null.
 */
function currentUserName(): ?string {
    return $_SESSION['name'] ?? null;
}
