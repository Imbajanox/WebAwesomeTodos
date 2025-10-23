<?php
// =================================================================
// PHP KONFIGURATION & DATENBANKVERBINDUNG
// =================================================================
$host = '127.0.0.1'; 
$db   = 'task_manager_wa';
$user = 'root'; 
$pass = '';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     http_response_code(500);
     echo json_encode(['error' => 'Database connection failed.']);
     exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$request_method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';


// =================================================================
// 2. LOGIK: CRUD-Funktionen als API-Endpunkte
// =================================================================

switch ($request_method) {
    
    // A. READ (Aufgaben abrufen)
    case 'GET':
        $stmt = $pdo->query("SELECT id, title, is_completed FROM tasks ORDER BY is_completed ASC, created_at DESC");
        $tasks = $stmt->fetchAll();
        echo json_encode($tasks);
        break;

    // B. CREATE (Neue Aufgabe hinzufügen)
    case 'POST':
        if (isset($input['title']) && !empty(trim($input['title']))) {
            $title = trim($input['title']);
            $stmt = $pdo->prepare("INSERT INTO tasks (title, is_completed) VALUES (?, 0)");
            $stmt->execute([$title]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'title' => $title]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required.']);
        }
        break;

    // C. UPDATE (Status umschalten)
    case 'PUT':
        if ($action === 'toggle' && isset($input['id'])) {
            $id = $input['id'];

            $stmt = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            $current_status = $stmt->fetchColumn();
            $new_status = $current_status == 0 ? 1 : 0;

            $stmt = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            echo json_encode(['success' => true, 'is_completed' => $new_status]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid update request.']);
        }
        break;

    // D. DELETE (Aufgabe löschen)
    case 'DELETE':
        if (isset($input['id'])) {
            $id = $input['id'];
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for deletion.']);
        }
        break;
        
    default:
        http_response_code(405);d
        echo json_encode(['error' => 'Method not allowed.']);
        break;
}
?>