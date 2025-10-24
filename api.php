<?php
// =================================================================
// PHP KONFIGURATION & DATENBANKVERBINDUNG
// =================================================================
session_start();

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

/**
 * Überprüft, ob ein Benutzer angemeldet ist und gibt die user_id zurück.
 * @return int|null Die ID des angemeldeten Benutzers oder null.
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// =================================================================
// 1. LOGIK: Authentifizierung (Registration, Login, Logout)
// =================================================================

if ($request_method === 'POST') {
    switch ($action) {
        case 'register':
            if (isset($input['username'], $input['password'])) {
                $username = trim($input['username']);
                $password = $input['password'];
                
                if (empty($username) || strlen($password) < 6) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username and a password (min 6 chars) are required.']);
                    exit;
                }

                // Passwort hashen
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                    $stmt->execute([$username, $password_hash]);
                    
                    // Automatisch einloggen nach der Registrierung
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    
                    echo json_encode(['success' => true, 'message' => 'Registration successful.', 'user_id' => $_SESSION['user_id']]);
                } catch (\PDOException $e) {
                    // Fehler: Wahrscheinlich ist der Benutzername bereits vergeben (UNIQUE Constraint)
                    if ($e->getCode() == 23000) {
                        http_response_code(409); // Conflict
                        echo json_encode(['error' => 'Username already exists.']);
                    } else {
                        http_response_code(500);
                        echo json_encode(['error' => 'Registration failed.']);
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password are required.']);
            }
            exit; // Beende den POST-Block, da die Authentifizierung abgeschlossen ist
            
        case 'login':
            if (isset($input['username'], $input['password'])) {
                $username = trim($input['username']);
                $password = $input['password'];

                $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login erfolgreich
                    $_SESSION['user_id'] = $user['id'];
                    echo json_encode(['success' => true, 'message' => 'Login successful.', 'user_id' => $user['id']]);
                } else {
                    http_response_code(401); // Unauthorized
                    echo json_encode(['error' => 'Invalid username or password.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password are required.']);
            }
            exit;

        default:
            // Wenn keine Auth-Action, lass den Code unten die Task-Logik verarbeiten
            break;
    }
} elseif ($request_method === 'GET' && $action === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout successful.']);
    exit;
} elseif ($request_method === 'GET' && $action === 'status') {
    // Endpunkt zur Überprüfung des Login-Status
    $user_id = get_current_user_id();
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        echo json_encode(['is_logged_in' => true, 'user_id' => $user_id, 'username' => $user['username']]);
    } else {
        echo json_encode(['is_logged_in' => false]);
    }
    exit;
}

// =================================================================
// 2. LOGIK: Autorisierungs-Check für Task-CRUD
// =================================================================

// Stoppe die Ausführung, wenn der Benutzer nicht eingeloggt ist und eine Task-Operation versucht wird.
$user_id = get_current_user_id();
if (!$user_id) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Authentication required for task operations.']);
    exit;
}

// =================================================================
// 3. LOGIK: CRUD-Funktionen als API-Endpunkte
// =================================================================

switch ($request_method) {
    
    // A. READ (Aufgaben abrufen)
    case 'GET':
        // WICHTIG: Filtere nach der user_id
        $stmt = $pdo->prepare("SELECT id, title, is_completed FROM tasks WHERE user_id = ? ORDER BY is_completed ASC, created_at DESC");
        $stmt->execute([$user_id]);
        $tasks = $stmt->fetchAll();
        echo json_encode($tasks);
        break;

    // B. CREATE (Neue Aufgabe hinzufügen)
    case 'POST':
        if (isset($input['title']) && !empty(trim($input['title']))) {
            $title = trim($input['title']);
            // WICHTIG: Füge die user_id beim Erstellen ein
            $stmt = $pdo->prepare("INSERT INTO tasks (title, is_completed, user_id) VALUES (?, 0, ?)");
            $stmt->execute([$title, $user_id]);
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

            // WICHTIG: Stelle sicher, dass die Aufgabe dem angemeldeten Benutzer gehört
            $stmt = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $current_status = $stmt->fetchColumn();

            if ($current_status !== false) { // Finde die Aufgabe und sie gehört dem Benutzer
                $new_status = $current_status == 0 ? 1 : 0;
                $stmt = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_status, $id, $user_id]);
                echo json_encode(['success' => true, 'is_completed' => $new_status]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found or unauthorized.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid update request.']);
        }
        break;

    // D. DELETE (Aufgabe löschen)
    case 'DELETE':
        if (isset($input['id'])) {
            $id = $input['id'];
            // WICHTIG: Stelle sicher, dass die Aufgabe dem angemeldeten Benutzer gehört
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);

            if ($stmt->rowCount() > 0) { // Überprüfe, ob eine Zeile gelöscht wurde
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found or unauthorized.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for deletion.']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        break;
}
?>