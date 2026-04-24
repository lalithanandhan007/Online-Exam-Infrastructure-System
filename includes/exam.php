<?php
/**
 * Exam Business Logic Helpers
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Generate a unique 6-character alphanumeric room code.
 */
function generateRoomCode(): string {
    $pdo = getDB();
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $stmt = $pdo->prepare('SELECT id FROM exam_rooms WHERE room_code = ?');
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

/**
 * Fetch an exam room by its room code.
 */
function getRoomByCode(string $code): array|false {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM exam_rooms WHERE room_code = ?');
    $stmt->execute([strtoupper(trim($code))]);
    return $stmt->fetch();
}

/**
 * Fetch an exam room by id.
 */
function getRoomById(int $id): array|false {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM exam_rooms WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all rooms created by a teacher.
 */
function getRoomsByTeacher(int $teacherId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM exam_rooms WHERE created_by = ? ORDER BY created_at DESC');
    $stmt->execute([$teacherId]);
    return $stmt->fetchAll();
}

/**
 * Check if student already attempted an exam.
 */
function getAttempt(int $roomId, int $studentId): array|false {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE exam_room_id = ? AND student_id = ?');
    $stmt->execute([$roomId, $studentId]);
    return $stmt->fetch();
}

/**
 * Get an attempt by its id.
 */
function getAttemptById(int $attemptId): array|false {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE id = ?');
    $stmt->execute([$attemptId]);
    return $stmt->fetch();
}

/**
 * Start (create) a new attempt and return its id.
 */
function startAttempt(int $roomId, int $studentId): int {
    $pdo = getDB();
    // Calculate total marks
    $stmt = $pdo->prepare('SELECT SUM(marks) AS total FROM questions WHERE exam_room_id = ?');
    $stmt->execute([$roomId]);
    $total = (int)($stmt->fetch()['total'] ?? 0);

    $stmt = $pdo->prepare(
        'INSERT INTO exam_attempts (exam_room_id, student_id, total_marks) VALUES (?, ?, ?)'
    );
    $stmt->execute([$roomId, $studentId, $total]);
    return (int)$pdo->lastInsertId();
}

/**
 * Return all attempts for a given exam room (for teacher view).
 */
function getAttemptsByRoom(int $roomId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'SELECT ea.*, u.name AS student_name, u.email
         FROM exam_attempts ea
         JOIN users u ON u.id = ea.student_id
         WHERE ea.exam_room_id = ?
         ORDER BY ea.submitted_at DESC'
    );
    $stmt->execute([$roomId]);
    return $stmt->fetchAll();
}

/**
 * Return all submitted attempts for a student.
 */
function getStudentResults(int $studentId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'SELECT ea.*, er.title, er.subject, er.room_code
         FROM exam_attempts ea
         JOIN exam_rooms er ON er.id = ea.exam_room_id
         WHERE ea.student_id = ? AND ea.status = "submitted"
         ORDER BY ea.submitted_at DESC'
    );
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}
