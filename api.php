<?php
require 'db.php';

header('Content-Type: application/json');

// Fonction pour calculer la priorité simple
function calculatePriority($importance, $due_date) {
    $now = new DateTime();
    $due = $due_date ? new DateTime($due_date) : null;
    if ($due) {
        $interval = $now->diff($due);
        $daysLeft = (int)$interval->format('%r%a');
        if ($daysLeft < 0) $daysLeft = 0;
    } else {
        $daysLeft = 10;
    }
    return $importance * (10 - $daysLeft);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Récupérer toutes les tâches
    $stmt = $pdo->query('SELECT * FROM tasks');
    $tasks = $stmt->fetchAll();

    // Calculer la priorité pour chaque tâche
    foreach ($tasks as &$task) {
        $task['priority_score'] = calculatePriority($task['importance'], $task['due_date']);
    }

    // Trier par priorité décroissante
    usort($tasks, function($a, $b) {
        return $b['priority_score'] <=> $a['priority_score'];
    });

    echo json_encode($tasks);
    exit;
}

if ($method === 'POST') {
    // Ajouter une tâche
    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'] ?? '';
    $importance = (int)($data['importance'] ?? 1);
    $due_date = $data['due_date'] ?? null;

    if (!$title) {
        http_response_code(400);
        echo json_encode(['error' => 'Title is required']);
        exit;
    }

    $priority_score = calculatePriority($importance, $due_date);

    $stmt = $pdo->prepare('INSERT INTO tasks (title, importance, due_date, priority_score) VALUES (?, ?, ?, ?)');
    $stmt->execute([$title, $importance, $due_date, $priority_score]);

    echo json_encode(['message' => 'Task added']);
    exit;
}

if ($method === 'PUT') {
    // Mettre à jour une tâche
    parse_str(file_get_contents("php://input"), $put_vars);
    $id = $put_vars['id'] ?? null;
    $title = $put_vars['title'] ?? null;
    $importance = isset($put_vars['importance']) ? (int)$put_vars['importance'] : null;
    $due_date = $put_vars['due_date'] ?? null;
    $completed = isset($put_vars['completed']) ? (int)$put_vars['completed'] : null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    // Récupérer tâche existante
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([$id]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(404);
        echo json_encode(['error' => 'Task not found']);
        exit;
    }

    $title = $title ?? $task['title'];
    $importance = $importance ?? $task['importance'];
    $due_date = $due_date ?? $task['due_date'];
    $completed = $completed ?? $task['completed'];

    $priority_score = calculatePriority($importance, $due_date);

    $stmt = $pdo->prepare('UPDATE tasks SET title = ?, importance = ?, due_date = ?, completed = ?, priority_score = ? WHERE id = ?');
    $stmt->execute([$title, $importance, $due_date, $completed, $priority_score, $id]);

    echo json_encode(['message' => 'Task updated']);
    exit;
}

if ($method === 'DELETE') {
    // Supprimer une tâche
    parse_str(file_get_contents("php://input"), $delete_vars);
    $id = $delete_vars['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['message' => 'Task deleted']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
