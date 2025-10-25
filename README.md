# WebAwesomeTodos

A small PHP + MySQL backed todo (task manager) with a WebAwesome-based frontend. Its purpose is to test the new Web Awesome Framework from the creators of Font Awesome
## Preview
<img width="822" height="677" alt="image" src="https://github.com/user-attachments/assets/28f7f36c-69a7-4e10-b471-cf3adb581e12" />

## Features
- Add, mark complete, and delete tasks
- Filter tasks (All / Open / Completed)
- Tag management: Create and assign tags to tasks
- Filter tasks by tags
- User authentication and registration
- Persistent storage in MySQL (via PDO)
- Minimal modern UI using WebAwesome components and Font Awesome
- Dark-mode styling and responsive layout

## Quick setup
1. Create the database and tables (MySQL):
   
   The easiest way is to import the complete schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
   
   Or manually create the database and tables:
   ```bash
   mysql -u root -p
   ```
   Then run the SQL commands from `schema.sql`

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
  - Returns JSON array of tasks: [{ id, title, is_completed, tags }, ...]
- GET api.php?tag=TagName
  - Returns tasks filtered by tag name
- GET api.php?tag_id=123
  - Returns tasks filtered by tag ID
- POST api.php
  - Create: JSON body { "title": "Task title", "tags": ["Work", "Urgent"] }
- PUT api.php?action=toggle
  - Toggle complete: JSON body { "id": 123 }
- DELETE api.php
  - Delete: JSON body { "id": 123 }

### Tags
- GET api.php?action=tags
  - Returns all tags for the current user

## Database Schema

The application requires 4 tables:
- **users**: User accounts with authentication
- **tasks**: Todo items linked to users
- **tags**: Tag names per user
- **task_tags**: Junction table for many-to-many relationship between tasks and tags

See `schema.sql` for complete table definitions.

## Security notes
- Change DB credentials for production (do not use root with empty password).
- Passwords are hashed using bcrypt (password_hash/password_verify).
- Authentication required for all task operations.
- Server-side validation and error handling are minimal — expand as required.

## Development ideas / roadmap
- ✅ ~~User authentication and authorization~~
- ✅ ~~Tagging/categories~~
- Add task editing and due dates
- Full-text search across tasks
- Reordering via drag-and-drop
- Convert to a PWA (offline-first)


