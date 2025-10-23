# WebAwesomeTodos

A small PHP + MySQL backed todo (task manager) with a WebAwesome-based frontend. Its purpose is to test the new Web Awesome Framework from the creators of Font Awesome

## Features
- Add, mark complete, and delete tasks
- Filter tasks (All / Open / Completed)
- Persistent storage in MySQL (via PDO)
- Minimal modern UI using WebAwesome components and Font Awesome
- Dark-mode styling and responsive layout

## Quick setup
1. Create the database and table (MySQL):
```sql
CREATE DATABASE IF NOT EXISTS task_manager_wa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_manager_wa;
CREATE TABLE tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  is_completed TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

2. Configure DB credentials in `api.php`:
- $host, $db, $user, $pass, $charset (top of `api.php`).

3. Deploy files to a PHP-enabled server (or local dev stack like XAMPP, MAMP, Docker PHP image). Open `index.php` in your browser.

## API
- GET api.php
  - Returns JSON array of tasks: [{ id, title, is_completed }, ...]
- POST api.php
  - Create: JSON body { "title": "Task title" }
- PUT api.php?action=toggle
  - Toggle complete: JSON body { "id": 123 }
- DELETE api.php
  - Delete: JSON body { "id": 123 }

## Security notes
- Change DB credentials for production (do not use root with empty password).
- Add authentication/authorization if you plan to expose the app publicly.
- Server-side validation and error handling are minimal — expand as required.

## Development ideas / roadmap
- Add task editing and due dates
- Tagging/categories and search
- Reordering via drag-and-drop
- Convert to a PWA (offline-first) or add user accounts + sync (Firebase or simple API)


<img width="822" height="677" alt="image" src="https://github.com/user-attachments/assets/28f7f36c-69a7-4e10-b471-cf3adb581e12" />
