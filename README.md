# WebAwesomeTodos

A small PHP + MySQL backed todo (task manager) with a WebAwesome-based frontend. Its purpose is to test the new Web Awesome Framework from the creators of Font Awesome

## Preview
<img width="822" height="677" alt="image" src="https://github.com/user-attachments/assets/28f7f36c-69a7-4e10-b471-cf3adb581e12" />

### Phase 2 Features
<img width="822" alt="Phase 2 - Task Management with Priorities, Due Dates, and Descriptions" src="https://github.com/user-attachments/assets/bcd9cf2a-78a2-4a0b-9a5a-64dc9430b341" />

## Features
- Add, edit, mark complete, and delete tasks
- Task descriptions/notes for detailed information
- Priority levels (Low, Medium, High) with visual indicators
- Due dates with calendar picker and smart date display (Today, Tomorrow, etc.)
- Sort tasks by priority, due date, or creation date
- Filter tasks by priority level
- Filter tasks (All / Open / Completed)
- Tag management: Create and assign tags to tasks
- Filter tasks by tags
- User authentication and registration
- Client-side validation with friendly error messages
- Persistent storage in MySQL (via PDO)
- Minimal modern UI using WebAwesome components and Font Awesome
- Dark-mode styling and responsive layout

## Quick setup
1. Create the database and tables (MySQL):
   
   **For new installations:**
   ```bash
   mysql -u root -p < schema.sql
   ```
   
   **For existing installations (migration from Phase 1):**
   ```bash
   mysql -u root -p < migrate_phase2.sql
   ```
   
   Or manually create the database and tables:
   ```bash
   mysql -u root -p
   ```
   Then run the SQL commands from `schema.sql` or `migrate_phase2.sql`

2. Configure DB credentials in `api.php`:
- $host, $db, $user, $pass, $charset (top of `api.php`).

3. Deploy files to a PHP-enabled server (or local dev stack like XAMPP, MAMP, Docker PHP image). Open `index.php` in your browser.

## API

### Authentication
- POST api.php?action=register
  - Register: JSON body { "username": "user", "password": "pass123" }
- POST api.php?action=login
  - Login: JSON body { "username": "user", "password": "pass123" }
- GET api.php?action=logout
  - Logout current user
- GET api.php?action=status
  - Check login status

### Tasks
- GET api.php
  - Returns JSON array of tasks: [{ id, title, description, due_date, priority, is_completed, tags }, ...]
- GET api.php?tag=TagName
  - Returns tasks filtered by tag name
- GET api.php?tag_id=123
  - Returns tasks filtered by tag ID
- POST api.php
  - Create: JSON body { "title": "Task title", "description": "Optional notes", "due_date": "2025-10-30", "priority": "high", "tags": ["Work", "Urgent"] }
- PUT api.php?action=toggle
  - Toggle complete: JSON body { "id": 123 }
- PUT api.php?action=edit
  - Edit task: JSON body { "id": 123, "title": "Updated title", "description": "Updated notes", "due_date": "2025-11-01", "priority": "medium", "tags": ["Work"] }
- DELETE api.php
  - Delete: JSON body { "id": 123 }

### Tags
- GET api.php?action=tags
  - Returns all tags for the current user

## Database Schema

The application requires 4 tables:
- **users**: User accounts with authentication
- **tasks**: Todo items linked to users with title, description, due_date, priority, completion status
- **tags**: Tag names per user
- **task_tags**: Junction table for many-to-many relationship between tasks and tags

See `schema.sql` for complete table definitions.

### Task Fields
- `id`: Unique task identifier
- `title`: Task title (required, max 255 chars)
- `description`: Optional task notes/description (text)
- `due_date`: Optional due date (date format: YYYY-MM-DD)
- `priority`: Task priority (enum: 'low', 'medium', 'high', default: 'medium')
- `is_completed`: Completion status (boolean)
- `created_at`: Timestamp of task creation
- `user_id`: Owner of the task


## Security notes
- Change DB credentials for production (do not use root with empty password).
- Passwords are hashed using bcrypt (password_hash/password_verify).
- Authentication required for all task operations.
- CSRF protection for all mutating operations (POST, PUT, DELETE).
- Client-side and server-side validation with friendly error messages.
- Input length validation to prevent database errors.

## Development ideas / roadmap
- ✅ ~~User authentication and authorization~~
- ✅ ~~Tagging/categories~~
- ✅ ~~Task editing with descriptions, due dates, and priorities~~
- ✅ ~~Priority filtering and sorting~~
- Full-text search across tasks
- Reordering via drag-and-drop
- Convert to a PWA (offline-first)

- ✅ ~~User authentication and authorization~~
- ✅ ~~Tagging/categories~~
- Add task editing and due dates
- Full-text search across tasks
- Reordering via drag-and-drop
- Convert to a PWA (offline-first)


