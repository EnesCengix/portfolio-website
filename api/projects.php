<?php
// ─── api/projects.php — Returns all projects as JSON ─────────
header('Content-Type: application/json');

require_once '../db.php';

try {
    $stmt = $pdo->query(
        'SELECT id, title, description, github_link, created_at
         FROM projects
         ORDER BY created_at DESC'
    );
    $projects = $stmt->fetchAll();

    echo json_encode([
        'success'  => true,
        'projects' => $projects
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load projects.'
    ]);
}
