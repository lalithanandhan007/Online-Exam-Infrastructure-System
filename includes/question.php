<?php
/**
 * Question Business Logic Helpers
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Get all questions for an exam room (shuffled or ordered).
 */
function getQuestions(int $roomId, bool $shuffle = false): array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE exam_room_id = ?');
    $stmt->execute([$roomId]);
    $questions = $stmt->fetchAll();

    foreach ($questions as &$q) {
        if ($q['question_type'] === 'mcq') {
            $q['options'] = getOptions($q['id'], $shuffle);
        }
    }
    unset($q);

    if ($shuffle) {
        shuffle($questions);
    }

    return $questions;
}

/**
 * Get MCQ options for a question (optionally shuffled).
 */
function getOptions(int $questionId, bool $shuffle = false): array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM options WHERE question_id = ?');
    $stmt->execute([$questionId]);
    $options = $stmt->fetchAll();
    if ($shuffle) {
        shuffle($options);
    }
    return $options;
}

/**
 * Get the correct option for a given question.
 */
function getCorrectOption(int $questionId): array|false {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM options WHERE question_id = ? AND is_correct = 1 LIMIT 1');
    $stmt->execute([$questionId]);
    return $stmt->fetch();
}

/**
 * Count questions in a room.
 */
function countQuestions(int $roomId): int {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions WHERE exam_room_id = ?');
    $stmt->execute([$roomId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Add a question to a room.
 * Returns the new question id.
 */
function addQuestion(int $roomId, string $text, string $type, int $marks): int {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO questions (exam_room_id, question_text, question_type, marks) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$roomId, $text, $type, $marks]);
    return (int)$pdo->lastInsertId();
}

/**
 * Add an option to a question.
 */
function addOption(int $questionId, string $text, bool $isCorrect): void {
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)');
    $stmt->execute([$questionId, $text, $isCorrect ? 1 : 0]);
}

/**
 * Delete a question (and its options cascade).
 */
function deleteQuestion(int $questionId, int $teacherId): bool {
    $pdo = getDB();
    // Verify ownership via exam_rooms
    $stmt = $pdo->prepare(
        'SELECT q.id FROM questions q
         JOIN exam_rooms er ON er.id = q.exam_room_id
         WHERE q.id = ? AND er.created_by = ?'
    );
    $stmt->execute([$questionId, $teacherId]);
    if (!$stmt->fetch()) {
        return false;
    }
    $pdo->prepare('DELETE FROM questions WHERE id = ?')->execute([$questionId]);
    return true;
}
