<?php
// =================================================================
// PHP KONFIGURATION & DATENBANKVERBINDUNG
// =================================================================

// Configure secure session cookie parameters
// Note: 'secure' flag is set based on HTTPS detection for local development compatibility
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',  // Only send over HTTPS when available
    'httponly' => true,    // Prevent JavaScript access to session cookie
    'samesite' => 'Strict' // CSRF protection via SameSite
]);

session_start();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Special endpoint to get CSRF token
if ($request_method === 'GET' && $action === 'csrf') {
    echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);
    exit;
}

/**
 * Überprüft, ob ein Benutzer angemeldet ist und gibt die user_id zurück.
 * @return int|null Die ID des angemeldeten Benutzers oder null.
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Validates CSRF token for mutating requests (POST, PUT, DELETE)
 * @return bool True if token is valid or not required, false otherwise
 */
function validate_csrf_token() {
    // Only validate for mutating requests
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
        return true; // GET requests don't need CSRF validation
    }
    
    // Check for CSRF token in header
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    
    return hash_equals($session_token, $token);
}

/**
 * Validates input length to prevent database errors
 * @param string $value The value to validate
 * @param int $maxLength Maximum allowed length
 * @param string $fieldName Field name for error message
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_input_length($value, $maxLength, $fieldName) {
    $trimmed = trim($value);
    if (mb_strlen($trimmed) > $maxLength) {
        return [
            'valid' => false, 
            'error' => "$fieldName exceeds maximum length of $maxLength characters."
        ];
    }
    return ['valid' => true, 'error' => null];
}



// =================================================================
// TAG-VERWALTUNGSFUNKTIONEN
// =================================================================
/**
 * Liefert tag-id zurück (existierend) oder legt Tag an und liefert ID.
 * Returns null if tag is invalid (empty or too long).
 */
function get_or_create_tag(PDO $pdo, int $user_id, string $tag_name) {
    $tag_name = trim($tag_name);
    if ($tag_name === '') return null;
    
    // Validate tag name length (max 100 chars per schema) - silently skip if too long
    if (mb_strlen($tag_name) > 100) {
        return null; // Skip tags that are too long
    }

    // Versuche vorhandenen Tag zu finden
    $stmt = $pdo->prepare("SELECT id FROM tags WHERE user_id = ? AND name = ?");
    $stmt->execute([$user_id, $tag_name]);
    $row = $stmt->fetchColumn();
    if ($row !== false) return (int)$row;

    // Anlegen
    $stmt = $pdo->prepare("INSERT INTO tags (user_id, name) VALUES (?, ?)");
    $stmt->execute([$user_id, $tag_name]);
    return (int)$pdo->lastInsertId();
}

/**
 * Liefert Array von Tags (name,id) für einen Task
 */
function get_tags_for_task(PDO $pdo, int $task_id) {
    $stmt = $pdo->prepare("SELECT tags.id, tags.name FROM tags JOIN task_tags tt ON tags.id = tt.tag_id WHERE tt.task_id = ?");
    $stmt->execute([$task_id]);
    return $stmt->fetchAll();
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

if ($request_method === 'GET' && $action === 'tags') {
    $user_id = get_current_user_id();
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, name FROM tags WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$user_id]);
    $tags = $stmt->fetchAll();
    echo json_encode($tags);
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

// CSRF validation for mutating requests (POST, PUT, DELETE)
if (!validate_csrf_token()) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'CSRF token validation failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export']) && $_GET['export'] === 'ics') {
    // Filter-Parameter
    $filter = $_GET['filter'] ?? 'all';            // all | open | completed
    $priority = $_GET['priority'] ?? 'all';        // low | medium | high | all
    $tag = isset($_GET['tag']) ? trim($_GET['tag']) : null; // optionaler Tag-Name

    // Auth prüfen (wie im restlichen api.php)
    if (empty($user_id)) {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    // SQL ähnlich wie im normalen GET
    $sql = "
        SELECT t.id, t.title, t.description, t.due_date, t.priority, t.is_completed, t.created_at
        FROM tasks t
        LEFT JOIN task_tags tt ON t.id = tt.task_id
        LEFT JOIN tags ON tags.id = tt.tag_id
        WHERE t.user_id = ?
    ";
    $params = [$user_id];

    if ($filter === 'open') {
        $sql .= " AND t.is_completed = 0";
    } elseif ($filter === 'completed') {
        $sql .= " AND t.is_completed = 1";
    }
    if ($priority !== 'all') {
        $sql .= " AND t.priority = ?";
        $params[] = $priority;
    }
    if ($tag) {
        $sql .= " AND tags.name = ?";
        $params[] = $tag;
    }

    $sql .= " GROUP BY t.id ORDER BY t.due_date IS NULL, t.due_date ASC, t.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Helfer: RFC-konformes Escaping für TEXT
    $ical_escape = function(string $text): string {
        // RFC5545: BACKSLASH, SEMICOLON, COMMA und NEWLINE escapen
        $text = (string)$text;
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);
        $text = str_replace(";", "\\;", $text);
        $text = str_replace(",", "\\,", $text);
        return $text;
    };

    // Helfer: Zeilen auf max 75 octets falten (RFC 5545)
    $ical_fold = function(string $line): string {
        $max = 75; // max octets before folding
        $result = '';
        // Wir arbeiten auf Byte-Ebene, CRLF wird später angehängt
        while (strlen($line) > $max) {
            $part = substr($line, 0, $max);
            $result .= $part . "\r\n" . ' ';
            $line = substr($line, $max);
        }
        $result .= $line;
        return $result;
    };

    // Meta
    $prodid = '-//WebAwesomeTodos//EN';
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $dtstamp = $now->format('Ymd').'T'.$now->format('His').'Z';

    $raw_lines = [];
    $raw_lines[] = 'BEGIN:VCALENDAR';
    $raw_lines[] = 'VERSION:2.0';
    $raw_lines[] = 'PRODID:'.$prodid;
    $raw_lines[] = 'CALSCALE:GREGORIAN';
    $raw_lines[] = 'METHOD:PUBLISH';
    $raw_lines[] = 'X-WR-CALNAME:WebAwesomeTodos';

    foreach ($rows as $task) {
        // Nur Tasks mit due_date als YYYY-MM-DD exportieren (ganztägige Events).
        if (empty($task['due_date'])) continue;

        $due = DateTime::createFromFormat('Y-m-d', $task['due_date'], new DateTimeZone('UTC'));
        if (!$due) continue;

        $dtstart = $due->format('Ymd'); // all-day event
        $dtendObj = clone $due;
        $dtendObj->modify('+1 day'); // DTEND ist exklusiv für DATE
        $dtend = $dtendObj->format('Ymd');

        // Eindeutige UID: task-id + timestamp + host (lokal)
        $uid = 'task-' . $task['id'] . '-' . $now->format('YmdHis') . '@webawesometodos.local';

        $summary = $ical_escape($task['title'] ?? '');
        $description = $ical_escape($task['description'] ?? '');
        $status = ($task['is_completed'] == 1) ? 'COMPLETED' : 'CONFIRMED';

        $raw_lines[] = 'BEGIN:VEVENT';
        $raw_lines[] = 'UID:'.$uid;
        $raw_lines[] = 'DTSTAMP:'.$dtstamp;
        $raw_lines[] = 'DTSTART;VALUE=DATE:'.$dtstart;
        $raw_lines[] = 'DTEND;VALUE=DATE:'.$dtend;
        $raw_lines[] = 'SUMMARY:'.$summary;
        if ($description !== '') {
            $raw_lines[] = 'DESCRIPTION:'.$description;
        }
        // Status und ggf. COMPLETED-Zeitstempel
        $raw_lines[] = 'STATUS:'.$status;
        if ($task['is_completed'] == 1) {
            // Wenn Task erledigt, setze COMPLETED per DTSTAMP (UTC)
            $raw_lines[] = 'COMPLETED:'.$dtstamp;
        }
        // Optional: Quelle/URL oder Notiz mit Priorität / Tags (kann ergänzt werden)
        $raw_lines[] = 'END:VEVENT';
    }

    $raw_lines[] = 'END:VCALENDAR';

    // Folding aller Linien nach RFC 5545 (max 75 octets pro Zeile)
    $folded = array_map($ical_fold, $raw_lines);
    $ical = implode("\r\n", $folded) . "\r\n";

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="webawesometodos_export.ics"');
    echo $ical;
    exit;
}

// =================================================================
// 3. LOGIK: CRUD-Funktionen als API-Endpunkte
// =================================================================

switch ($request_method) {
    
    // A. READ (Aufgaben abrufen)
    case 'GET':
        $tag_id = isset($_GET['tag_id']) ? (int)$_GET['tag_id'] : null;
        $tag_name = isset($_GET['tag']) ? trim($_GET['tag']) : null;

        if ($tag_id) {
            $stmt = $pdo->prepare("
                SELECT t.id, t.title, t.description, t.due_date, t.priority, t.is_completed,
                       GROUP_CONCAT(tags.name SEPARATOR ',') AS tags
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags ON tags.id = tt.tag_id
                WHERE t.user_id = ? AND t.id IN (SELECT task_id FROM task_tags WHERE tag_id = ?)
                GROUP BY t.id
                ORDER BY t.is_completed ASC, t.created_at DESC
            ");
            $stmt->execute([$user_id, $tag_id]);
        } elseif ($tag_name) {
            $stmt = $pdo->prepare("
                SELECT t.id, t.title, t.description, t.due_date, t.priority, t.is_completed,
                       GROUP_CONCAT(tags.name SEPARATOR ',') AS tags
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags ON tags.id = tt.tag_id
                WHERE t.user_id = ? AND t.id IN (
                    SELECT tt2.task_id FROM task_tags tt2 JOIN tags tg ON tg.id = tt2.tag_id WHERE tg.user_id = ? AND tg.name = ?
                )
                GROUP BY t.id
                ORDER BY t.is_completed ASC, t.created_at DESC
            ");
            $stmt->execute([$user_id, $user_id, $tag_name]);
        } else {
            $stmt = $pdo->prepare("
                SELECT t.id, t.title, t.description, t.due_date, t.priority, t.is_completed,
                       GROUP_CONCAT(tags.name SEPARATOR ',') AS tags
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags ON tags.id = tt.tag_id
                WHERE t.user_id = ?
                GROUP BY t.id
                ORDER BY t.is_completed ASC, t.created_at DESC
            ");
            $stmt->execute([$user_id]);
        }

        $rows = $stmt->fetchAll();
        // map tags string to array
        $tasks = array_map(function($r){
            $r['tags'] = $r['tags'] ? array_values(array_filter(array_map('trim', explode(',', $r['tags'])))) : [];
            return $r;
        }, $rows);

        echo json_encode($tasks);
        break;

    // B. CREATE (Neue Aufgabe hinzufügen)
    case 'POST':
        if (isset($input['title']) && !empty(trim($input['title']))) {
            $title = trim($input['title']);
            $description = isset($input['description']) ? trim($input['description']) : null;
            $due_date = isset($input['due_date']) ? trim($input['due_date']) : null;
            $priority = isset($input['priority']) ? trim($input['priority']) : 'medium';
            $tags_input = $input['tags'] ?? []; // optional: array of tag names
            
            // Validate title length (max 255 chars per schema)
            $titleValidation = validate_input_length($title, 255, 'Title');
            if (!$titleValidation['valid']) {
                http_response_code(400);
                echo json_encode(['error' => $titleValidation['error']]);
                break;
            }
            
            // Validate priority
            if (!in_array($priority, ['low', 'medium', 'high'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Priority must be low, medium, or high.']);
                break;
            }
            
            // Validate due_date format if provided
            if ($due_date && !empty($due_date)) {
                $date = \DateTime::createFromFormat('Y-m-d', $due_date);
                if (!$date || $date->format('Y-m-d') !== $due_date) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Due date must be in YYYY-MM-DD format.']);
                    break;
                }
            } else {
                $due_date = null;
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO tasks (title, description, due_date, priority, is_completed, user_id) VALUES (?, ?, ?, ?, 0, ?)");
                $stmt->execute([$title, $description, $due_date, $priority, $user_id]);
                $task_id = (int)$pdo->lastInsertId();

                // Tags verarbeiten (falls vorhanden)
                if (is_array($tags_input) && count($tags_input) > 0) {
                    $insertRelation = $pdo->prepare("INSERT IGNORE INTO task_tags (task_id, tag_id) VALUES (?, ?)");
                    foreach ($tags_input as $tname) {
                        $tname = trim((string)$tname);
                        if ($tname === '') continue;
                        // Note: Invalid tags (too long) are silently skipped by get_or_create_tag
                        $tag_id = get_or_create_tag($pdo, $user_id, $tname);
                        if ($tag_id) {
                            $insertRelation->execute([$task_id, $tag_id]);
                        }
                    }
                }

                $pdo->commit();
                echo json_encode(['success' => true, 'id' => $task_id, 'title' => $title]);
            } catch (\Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create task.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required.']);
        }
        break;

    // C. UPDATE (Status umschalten oder Task bearbeiten)
    case 'PUT':
        if ($action === 'toggle' && isset($input['id'])) {
            $id = $input['id'];

            $stmt = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $current_status = $stmt->fetchColumn();

            if ($current_status !== false) {
                $new_status = $current_status == 0 ? 1 : 0;
                $stmt = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_status, $id, $user_id]);
                echo json_encode(['success' => true, 'is_completed' => $new_status]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found or unauthorized.']);
            }
        } elseif ($action === 'edit' && isset($input['id'])) {
            // Edit task endpoint
            $id = $input['id'];
            
            // Verify task exists and belongs to user
            $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found or unauthorized.']);
                break;
            }
            
            // Collect and validate fields
            $title = isset($input['title']) ? trim($input['title']) : null;
            $description = isset($input['description']) ? trim($input['description']) : null;
            $due_date = isset($input['due_date']) ? trim($input['due_date']) : null;
            $priority = isset($input['priority']) ? trim($input['priority']) : null;
            $tags_input = $input['tags'] ?? null;
            
            // Title is required
            if (empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'Title is required.']);
                break;
            }
            
            // Validate title length
            $titleValidation = validate_input_length($title, 255, 'Title');
            if (!$titleValidation['valid']) {
                http_response_code(400);
                echo json_encode(['error' => $titleValidation['error']]);
                break;
            }
            
            // Validate priority
            if ($priority !== null && !in_array($priority, ['low', 'medium', 'high'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Priority must be low, medium, or high.']);
                break;
            }
            
            // Validate due_date format if provided
            if ($due_date && !empty($due_date)) {
                $date = \DateTime::createFromFormat('Y-m-d', $due_date);
                if (!$date || $date->format('Y-m-d') !== $due_date) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Due date must be in YYYY-MM-DD format.']);
                    break;
                }
            } else {
                $due_date = null;
            }
            
            $pdo->beginTransaction();
            try {
                // Update task fields
                $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $description, $due_date, $priority, $id, $user_id]);
                
                // Update tags if provided
                if (is_array($tags_input)) {
                    // Remove existing tags
                    $stmt = $pdo->prepare("DELETE FROM task_tags WHERE task_id = ?");
                    $stmt->execute([$id]);
                    
                    // Add new tags
                    if (count($tags_input) > 0) {
                        $insertRelation = $pdo->prepare("INSERT IGNORE INTO task_tags (task_id, tag_id) VALUES (?, ?)");
                        foreach ($tags_input as $tname) {
                            $tname = trim((string)$tname);
                            if ($tname === '') continue;
                            $tag_id = get_or_create_tag($pdo, $user_id, $tname);
                            if ($tag_id) {
                                $insertRelation->execute([$id, $tag_id]);
                            }
                        }
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'id' => $id]);
            } catch (\Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update task.']);
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
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);

            if ($stmt->rowCount() > 0) {
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