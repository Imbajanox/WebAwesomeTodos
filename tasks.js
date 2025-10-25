// =================================================================
// 3. FRONTEND LOGIK (AJAX/FETCH) - ANGEPASST MIT FILTER/ZÄHLER
// =================================================================

const taskListContainer = document.getElementById('taskListContainer');
const addTaskForm = document.getElementById('addTaskForm');
const taskTitleInput = document.getElementById('taskTitle');
const taskDescriptionInput = document.getElementById('taskDescription');
const taskDueDateInput = document.getElementById('taskDueDate');
const taskPriorityInput = document.getElementById('taskPriority');
const addButton = document.getElementById('addButton');

const taskCounter = document.getElementById('taskCounter');
const filterAllButton = document.getElementById('filterAll');
const filterOpenButton = document.getElementById('filterOpen');
const filterCompletedButton = document.getElementById('filterCompleted');
const sortBySelect = document.getElementById('sortBy');
const filterPrioritySelect = document.getElementById('filterPriority');

// NEU: Auth-Elemente
const taskManagerView = document.getElementById('taskManagerView');
const authView = document.getElementById('authView');
const userInfo = document.getElementById('userInfo');
const usernameDisplay = document.getElementById('usernameDisplay');

const authTitle = document.getElementById('authTitle');
const authMessage = document.getElementById('authMessage');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const loginButton = document.getElementById('loginButton');
const registerButton = document.getElementById('registerButton');
const logoutButton = document.getElementById('logoutButton');
const toggleAuthViewLink = document.getElementById('toggleAuthView');

const taskTagsInput = document.getElementById('taskTags'); // NEU
const tagFilterBar = document.getElementById('tagFilterBar'); // NEU

let allTasks = [];
let currentFilter = 'all'; // Standardfilter
let currentSortBy = 'default';
let currentPriorityFilter = 'all';
let isLoggedIn = false;
let currentUsername = '';
let csrfToken = ''; // CSRF token for requests

// =================================================================
// CSRF TOKEN MANAGEMENT
// =================================================================

/**
 * Fetches CSRF token from the server
 * If this fails, user will get errors on mutating operations
 */
async function fetchCsrfToken() {
    try {
        const response = await fetch('api.php?action=csrf');
        if (!response.ok) {
            throw new Error('Failed to fetch CSRF token');
        }
        const data = await response.json();
        csrfToken = data.csrf_token || '';
        if (!csrfToken) {
            console.error('CSRF token is empty - mutating operations may fail');
        }
    } catch (error) {
        console.error('Failed to fetch CSRF token:', error);
        console.error('Mutating operations (create, update, delete) will fail without CSRF token');
    }
}

// =================================================================
// 1. ANZEIGE LOGIK (Views umschalten)
// =================================================================

/**
 * Schaltet zwischen der Login/Register-Ansicht und der Task-Manager-Ansicht um.
 * @param {boolean} loggedIn - true, wenn der Benutzer eingeloggt ist.
 * @param {string} username - Der Benutzername des eingeloggten Benutzers.
 */
async function updateView(loggedIn, username = '') {
    isLoggedIn = loggedIn;
    currentUsername = username;
    
    if (loggedIn) {
        authView.style.display = 'none';
        taskManagerView.style.display = 'block';
        userInfo.style.display = 'flex';
        usernameDisplay.textContent = username;
        await fetchCsrfToken(); // Fetch CSRF token for authenticated requests
        fetchTasks(); // Aufgaben nur laden, wenn eingeloggt
        fetchTagsAndRenderBar();
    } else {
        authView.style.display = 'block';
        taskManagerView.style.display = 'none';
        userInfo.style.display = 'none';
        taskListContainer.innerHTML = ''; // Aufgabenliste leeren
        allTasks = []; // Lokalen Cache leeren
        renderTasks(); // Zähler aktualisieren (auf 0)
    }
}

/**
 * Zeigt eine temporäre Nachricht im Auth-Bereich an.
 */
function showAuthMessage(message, variant = 'info') {
    authMessage.innerHTML = `<wa-callout variant="${variant}" style="margin-bottom: 0;">${message}</wa-callout>`;
    authMessage.style.display = 'block';
    setTimeout(() => {
        authMessage.style.display = 'none';
        authMessage.innerHTML = '';
    }, 4000);
}

/**
 * Zeigt eine freundliche Validierungsfehlermeldung an.
 */
function showValidationError(message) {
    // Create a temporary callout message
    const errorDiv = document.createElement('div');
    errorDiv.id = 'validationError';
    errorDiv.innerHTML = `<wa-callout variant="danger" closeable><i class="fas fa-exclamation-triangle" slot="icon"></i>${message}</wa-callout>`;
    errorDiv.style.marginBottom = '1rem';
    
    // Remove any existing error messages
    const existing = document.getElementById('validationError');
    if (existing) existing.remove();
    
    // Insert before the form
    addTaskForm.parentNode.insertBefore(errorDiv, addTaskForm);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) errorDiv.remove();
    }, 5000);
    
    // Allow manual close
    const callout = errorDiv.querySelector('wa-callout');
    if (callout) {
        callout.addEventListener('wa-hide', () => {
            if (errorDiv.parentNode) errorDiv.remove();
        });
    }
}


// =================================================================
// 2. AUTHENTIFIZIERUNG LOGIK (Login, Register, Logout)
// =================================================================

/**
 * Sendet Anmeldedaten an den Backend-Endpunkt.
 * @param {string} action - 'login' oder 'register'
 * @param {string} username 
 * @param {string} password 
 * @param {HTMLElement} buttonElement 
 */
async function authenticate(action, username, password, buttonElement) {
    if (!username || !password) return;

    buttonElement.setAttribute('loading', '');

    try {
        const response = await fetch(`api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();

        if (response.ok) {
            showAuthMessage(`Erfolgreich ${action === 'login' ? 'eingeloggt' : 'registriert'}!`, 'success');
            // Formulare leeren
            document.getElementById(`${action}Username`).value = '';
            document.getElementById(`${action}Password`).value = '';
            
            // Ansicht aktualisieren und Tasks laden
            updateView(true, username); 
        } else {
            const errorMessage = data.error || `Fehler bei der ${action}-Anfrage.`;
            showAuthMessage(errorMessage, 'danger');
        }

    } catch (error) {
        console.error(`${action} error:`, error);
        showAuthMessage('Ein Netzwerkfehler ist aufgetreten.', 'danger');
    } finally {
        buttonElement.removeAttribute('loading');
    }
}

// Event Listener für Login
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    authenticate('login', username, password, loginButton);
});

// Event Listener für Registrierung
registerForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const username = document.getElementById('registerUsername').value.trim();
    const password = document.getElementById('registerPassword').value;
    authenticate('register', username, password, registerButton);
});

// Event Listener für Logout
logoutButton.addEventListener('click', async () => {
    if (!confirm("Wirklich ausloggen?")) return;
    
    try {
        await fetch('api.php?action=logout'); // Session im Backend zerstören
        updateView(false); // Frontend-Status zurücksetzen
        showAuthMessage('Erfolgreich ausgeloggt.', 'info');
    } catch (error) {
        console.error('Logout error:', error);
    }
});

// Event Listener zum Umschalten zwischen Login/Register
toggleAuthViewLink.addEventListener('click', (e) => {
    e.preventDefault();
    if (loginForm.style.display !== 'none') {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        authTitle.textContent = 'Registrieren';
        toggleAuthViewLink.textContent = 'Bereits ein Konto? Hier anmelden.';
    } else {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        authTitle.textContent = 'Login';
        toggleAuthViewLink.textContent = 'Noch kein Konto? Hier registrieren.';
    }
    authMessage.style.display = 'none'; // Nachricht ausblenden beim Umschalten
});

/**
 * Überprüft beim Laden der Seite, ob der Benutzer bereits eingeloggt ist (Session Check).
 */
async function checkLoginStatus() {
    try {
        const response = await fetch('api.php?action=status');
        const data = await response.json();

        if (data.is_logged_in) {
            updateView(true, data.username);
        } else {
            updateView(false);
        }
    } catch (error) {
        console.error('Status check error:', error);
        updateView(false);
    }
}

function createTaskCard(task) {
    // 1. Haupt-Container: wa-card
    const card = document.createElement('wa-card');
    card.className = task.is_completed == 1 ? 'completed' : '';
    card.setAttribute('data-id', task.id);
    
    // Add priority class for styling
    if (task.priority) {
        card.classList.add(`priority-${task.priority}`);
    }

    // 2. Checkbox (Toggle) erstellen
    const checkbox = document.createElement('wa-checkbox');
    // Attribute 'name', 'size', 'value' entfernt, damit CSS die Kontrolle hat
    if (task.is_completed == 1) {
        checkbox.setAttribute('checked', '');
    }
    // Event Listener für Status-Update
    checkbox.addEventListener('change', () => toggleTask(task.id));

    // 3. Content container for title, description, and metadata
    const contentDiv = document.createElement('div');
    contentDiv.className = 'task-content';
    contentDiv.style.flex = '1';
    contentDiv.style.minWidth = '0';
    
    // Title
    const titleDiv = document.createElement('div');
    titleDiv.className = 'task-title';
    titleDiv.textContent = task.title;
    contentDiv.appendChild(titleDiv);
    
    // Description (if exists)
    if (task.description && task.description.trim()) {
        const descDiv = document.createElement('div');
        descDiv.className = 'task-description';
        descDiv.textContent = task.description;
        descDiv.style.fontSize = '0.85rem';
        descDiv.style.color = 'var(--wa-color-neutral-600)';
        descDiv.style.marginTop = '0.25rem';
        contentDiv.appendChild(descDiv);
    }
    
    // Metadata container (priority, due date, tags)
    const metaDiv = document.createElement('div');
    metaDiv.style.display = 'flex';
    metaDiv.style.gap = '0.5rem';
    metaDiv.style.marginTop = '0.5rem';
    metaDiv.style.flexWrap = 'wrap';
    metaDiv.style.alignItems = 'center';
    
    // Priority badge
    if (task.priority) {
        const priorityBadge = document.createElement('wa-badge');
        const priorityLabels = { low: 'Niedrig', medium: 'Mittel', high: 'Hoch' };
        const priorityVariants = { low: 'success', medium: 'warning', high: 'danger' };
        priorityBadge.textContent = priorityLabels[task.priority] || task.priority;
        priorityBadge.setAttribute('variant', priorityVariants[task.priority] || 'neutral');
        priorityBadge.style.fontSize = '0.7rem';
        metaDiv.appendChild(priorityBadge);
    }
    
    // Due date badge
    if (task.due_date) {
        const dueDateBadge = document.createElement('wa-badge');
        const dueDate = new Date(task.due_date + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const isOverdue = dueDate < today && task.is_completed == 0;
        
        dueDateBadge.innerHTML = `<i class="fas fa-calendar"></i>&nbsp;${formatDate(task.due_date)}`;
        dueDateBadge.setAttribute('variant', isOverdue ? 'danger' : 'info');
        dueDateBadge.style.fontSize = '0.7rem';
        metaDiv.appendChild(dueDateBadge);
    }
    
    // Tags
    if (Array.isArray(task.tags) && task.tags.length > 0) {
        task.tags.forEach(tn => {
            const badge = document.createElement('wa-badge');
            badge.textContent = tn;
            badge.setAttribute('variant', 'neutral');
            badge.style.fontSize = '0.7rem';
            metaDiv.appendChild(badge);
        });
    }
    
    if (metaDiv.children.length > 0) {
        contentDiv.appendChild(metaDiv);
    }

    // 4. Edit and Delete buttons
    const editButton = document.createElement('wa-button');
    editButton.innerHTML = '<i class="fas fa-pen"></i>';
    editButton.setAttribute('title', 'Bearbeiten');
    editButton.addEventListener('click', () => showEditDialog(task));
    
    const deleteButton = document.createElement('wa-button');
    deleteButton.innerHTML = '<i class="fas fa-trash-can"></i>'; 
    deleteButton.setAttribute('title', 'Löschen');
    deleteButton.addEventListener('click', () => showDeleteDialog(task.id, task.title));

    // 5. Aktionen-Gruppe erstellen
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'task-actions';
    actionsDiv.appendChild(editButton);
    actionsDiv.appendChild(deleteButton); 

    // 6. Alles in der gewünschten Reihenfolge zur Card hinzufügen: [Checkbox] | [Content] | [Actions]
    card.appendChild(checkbox);
    card.appendChild(contentDiv);
    card.appendChild(actionsDiv);
    
    return card;
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Heute';
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'Morgen';
    } else {
        return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
}

// ===================================
// ZWISCHEN-SCHRITT: Aufgaben rendern
// ===================================

/**
 * Filtert die Aufgaben basierend auf dem aktuellen Filter und rendert sie neu.
 */
function renderTasks() {
    const openTasksCount = allTasks.filter(t => t.is_completed == 0).length;

    // 1. Zähler aktualisieren (KORRIGIERT: Nutzung von &nbsp; für gesicherte Leerzeichen)
    if (taskCounter) {
        taskCounter.innerHTML = `Noch&nbsp;<span>${openTasksCount}</span>&nbsp;offene Aufgaben`;
        taskCounter.setAttribute('variant', openTasksCount > 0 ? 'warning' : 'info');
    }

    if (!isLoggedIn) {
        taskListContainer.innerHTML = '';
        return;
    }

    // 2. Aufgaben filtern
    let tasksToRender = [];
    if (currentFilter === 'open') {
        tasksToRender = allTasks.filter(task => task.is_completed == 0);
    } else if (currentFilter === 'completed') {
        tasksToRender = allTasks.filter(task => task.is_completed == 1);
    } else {
        tasksToRender = allTasks; // 'all'
    }
    
    // Filter by priority
    if (currentPriorityFilter !== 'all') {
        tasksToRender = tasksToRender.filter(task => task.priority === currentPriorityFilter);
    }
    
    // Sort tasks
    if (currentSortBy === 'priority') {
        const priorityOrder = { high: 0, medium: 1, low: 2 };
        tasksToRender.sort((a, b) => {
            const aPriority = priorityOrder[a.priority] ?? 1;
            const bPriority = priorityOrder[b.priority] ?? 1;
            return aPriority - bPriority;
        });
    } else if (currentSortBy === 'due_date') {
        tasksToRender.sort((a, b) => {
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return new Date(a.due_date) - new Date(b.due_date);
        });
    } else if (currentSortBy === 'created') {
        tasksToRender.sort((a, b) => b.id - a.id); // Newer first
    }
    // default: already sorted by backend (is_completed ASC, created_at DESC)

    // 3. DOM aktualisieren
    taskListContainer.innerHTML = '';
    
    if (tasksToRender.length === 0) {
        let message = 'Keine Aufgaben gefunden.';
        if (currentFilter === 'open') message = 'Alle Aufgaben erledigt! 🎉';
        if (currentFilter === 'completed') message = 'Keine abgeschlossenen Aufgaben.';
        if (currentPriorityFilter !== 'all') {
            // Escape HTML to prevent XSS
            const escapedPriority = escapeHtml(currentPriorityFilter);
            message = `Keine Aufgaben mit Priorität "${escapedPriority}" gefunden.`;
        }

        taskListContainer.innerHTML = `
            <wa-callout variant="info">
                <i class="fas fa-circle-info" slot="icon"></i>
                ${message}
            </wa-callout>
        `;
        return;
    }

    const taskList = document.createElement('div');
    taskList.className = 'task-list';

    tasksToRender.forEach(task => {
        taskList.appendChild(createTaskCard(task));
    });
    
    taskListContainer.appendChild(taskList); 
}

// ===================================
// A. READ: Aufgaben abrufen und rendern
// ===================================
async function fetchTasks() {
    if (!isLoggedIn) return

    taskListContainer.innerHTML = `<div class="loading"><wa-spinner size="large"></wa-spinner><p>Aufgaben werden geladen...</p></div>`;
    
    try {
        // Optionaler Tag-Filter: wenn currentTag gesetzt, senden wir tag param (verwende currentTagName)
        const url = currentTagName ? `api.php?tag=${encodeURIComponent(currentTagName)}` : 'api.php';
        const response = await fetch(url);
        const tasks = await response.json();
        allTasks = tasks;

        renderTasks();
    } catch (error) {
        taskListContainer.innerHTML = `<wa-callout variant="danger">Fehler beim Laden der Aufgaben.</wa-callout>`;
        console.error('Fetch error:', error);
    }
}

// ===================================
// FILTER LOGIK
// ===================================

let currentTagName = ''; // leer = kein Tag-Filter

async function fetchTagsAndRenderBar() {
    if (!isLoggedIn) return;
    try {
        const resp = await fetch('api.php?action=tags');
        if (!resp.ok) return;
        const tags = await resp.json();
        renderTagFilterBar(tags);
    } catch (e) {
        console.error('fetch tags error', e);
    }
}

function renderTagFilterBar(tags) {
    if (!tagFilterBar) return;
    tagFilterBar.innerHTML = '';

    const allBtn = document.createElement('wa-button');
    allBtn.textContent = 'Alle Tags';
    allBtn.setAttribute('size', 'small');
    allBtn.setAttribute('variant', currentTagName ? 'neutral' : 'primary');
    allBtn.addEventListener('click', () => {
        currentTagName = '';
        setActiveFilter('all', filterAllButton);
        fetchTasks();
    });
    tagFilterBar.appendChild(allBtn);

    tags.forEach(t => {
        const b = document.createElement('wa-button');
        b.textContent = t.name;
        b.setAttribute('size', 'small');
        b.setAttribute('variant', currentTagName === t.name ? 'primary' : 'neutral');
        b.addEventListener('click', () => {
            currentTagName = t.name;
            setActiveFilter(currentTagName, b);
            fetchTasks();
        });
        tagFilterBar.appendChild(b);
    });

    console.log('Tag filter bar rendered with tags:', tags);
}

let activeTagButton = null;

function setActiveFilter(filter, button) {
    if (!isLoggedIn) return

    // Wenn der neue Filter ein Tag-Filter ist:
    if (filter !== 'all' && !['open', 'completed'].includes(filter)) {
        // 1. Letzten aktiven TAG-Button auf neutral setzen (falls vorhanden)
        if (activeTagButton) {
            activeTagButton.setAttribute('variant', 'neutral');
            activeTagButton.removeAttribute('active');
        }
        
        // 2. Den neuen Tag-Button als aktiv speichern und markieren
        if (button) {
            button.setAttribute('variant', 'primary');
            button.setAttribute('active', '');
            activeTagButton = button;
        }
        // 3. Status-Filter-Buttons ignorieren (sie haben ihre eigene Logik)

    } else {
        // Wenn es ein Status-Filter ist ('all', 'open', 'completed'):
        
        // 1. Letzten aktiven TAG-Button auf neutral setzen, da der Status-Filter aktiv wird
        if (activeTagButton) {
            activeTagButton.setAttribute('variant', 'neutral');
            activeTagButton.removeAttribute('active');
            activeTagButton = null; // Tag-Button ist nicht mehr der aktive Filter
        }
        
        // 2. Alle Status-Buttons auf neutral setzen (wie in Ihrem Originalcode)
        [filterAllButton, filterOpenButton, filterCompletedButton].forEach(btn => {
             if (btn) btn.setAttribute('variant', 'neutral');
             if (btn) btn.removeAttribute('active');
        });

        if (button) {
            button.setAttribute('variant', 'primary');
            button.setAttribute('active', '');
        }
    }

    currentFilter = filter;
    renderTasks();
}

document.addEventListener('DOMContentLoaded', () => {
    checkLoginStatus();
    //fetchTasks();
    
    if (filterAllButton) filterAllButton.addEventListener('click', () => setActiveFilter('all', filterAllButton));
    if (filterOpenButton) filterOpenButton.addEventListener('click', () => setActiveFilter('open', filterOpenButton));
    if (filterCompletedButton) filterCompletedButton.addEventListener('click', () => setActiveFilter('completed', filterCompletedButton));
    
    // Sort and priority filter listeners
    if (sortBySelect) {
        sortBySelect.addEventListener('change', (e) => {
            currentSortBy = sortBySelect.value;
            renderTasks();
        });
    }
    
    if (filterPrioritySelect) {
        filterPrioritySelect.addEventListener('change', (e) => {
            currentPriorityFilter = filterPrioritySelect.value;
            renderTasks();
        });
    }
});

// ===================================
// B. CREATE: Neue Aufgabe hinzufügen
// ===================================
addTaskForm.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    if (!isLoggedIn) return;

    const title = taskTitleInput.value.trim();
    if (!title) {
        showValidationError('Bitte geben Sie einen Titel ein.');
        return;
    }
    
    if (title.length > 255) {
        showValidationError('Der Titel darf maximal 255 Zeichen lang sein.');
        return;
    }

    const description = taskDescriptionInput ? taskDescriptionInput.value.trim() : '';
    const dueDate = taskDueDateInput ? taskDueDateInput.value : '';
    const priority = taskPriorityInput ? taskPriorityInput.value : 'medium';
    const tagsRaw = taskTagsInput ? taskTagsInput.value : '';
    const tags = tagsRaw.split(',').map(t => t.trim()).filter(t => t.length > 0);

    addButton.setAttribute('loading', ''); 
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ 
                title: title, 
                description: description || null,
                due_date: dueDate || null,
                priority: priority,
                tags: tags 
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            taskTitleInput.value = '';
            if (taskDescriptionInput) taskDescriptionInput.value = '';
            if (taskDueDateInput) taskDueDateInput.value = '';
            if (taskPriorityInput) taskPriorityInput.value = 'medium';
            if (taskTagsInput) taskTagsInput.value = '';
            
            await fetchTagsAndRenderBar();
            if (currentFilter !== 'all') {
                setActiveFilter('all', filterAllButton); 
            } else {
                await fetchTasks();
            }
        } else {
            showValidationError(data.error || 'Fehler beim Hinzufügen der Aufgabe.');
        }
    } catch (error) {
        console.error('Add error:', error);
        showValidationError('Ein Netzwerkfehler ist aufgetreten.');
    } finally {
        addButton.removeAttribute('loading');
    }
});


// ===================================
// C. UPDATE: Status umschalten
// ===================================
async function toggleTask(id) {
    if (!isLoggedIn) return;

    try {
        const response = await fetch('api.php?action=toggle', {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ id: id })
        });

        if (response.ok) {
            const task = allTasks.find(t => t.id == id);
            if (task) {
                task.is_completed = task.is_completed == 1 ? 0 : 1; 
            }

            renderTasks(); 
        } else {
            showValidationError('Fehler beim Ändern des Status.');
        }
    } catch (error) {
        console.error('Toggle error:', error);
        showValidationError('Ein Netzwerkfehler ist aufgetreten.');
    }
}

// ===================================
// C2. UPDATE: Task bearbeiten
// ===================================
async function editTask(id, title, description, dueDate, priority, tags) {
    if (!isLoggedIn) return;

    try {
        const response = await fetch('api.php?action=edit', {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ 
                id: id,
                title: title,
                description: description || null,
                due_date: dueDate || null,
                priority: priority,
                tags: tags
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            await fetchTasks();
            await fetchTagsAndRenderBar();
        } else {
            showValidationError(data.error || 'Fehler beim Aktualisieren der Aufgabe.');
        }
    } catch (error) {
        console.error('Edit error:', error);
        showValidationError('Ein Netzwerkfehler ist aufgetreten.');
    }
}

// ===================================
// D. DELETE: Aufgabe löschen
// ===================================
async function deleteTask(id) {
    if (!isLoggedIn) return;

    try {
        const response = await fetch('api.php', {
            method: 'DELETE',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ id: id })
        });

        if (response.ok) {
            allTasks = allTasks.filter(t => t.id != id); 
            renderTasks(); 
        } else {
            alert('Fehler beim Löschen der Aufgabe.');
        }
    } catch (error) {
        console.error('Delete error:', error);
    }
}

// ===================================
// E. WA DIALOG (Modal) für Löschbestätigung
// ===================================

let deleteDialog = null; 
let currentTaskIdToDelete = null; 



// -----------------------------------
// Function to show the deletion dialog
// -----------------------------------

function showDeleteDialog(id, title) {
    const dialogId = 'deleteDialog';
    let dialog = document.getElementById(dialogId);
    
    currentTaskIdToDelete = id;
	//console.log(currentTaskIdToDelete);
    if (!dialog) {
        dialog = document.createElement('wa-dialog');
        dialog.id = dialogId;
        dialog.setAttribute('label', 'Aufgabe löschen');
        document.body.appendChild(dialog);
    }
    
    dialog.innerHTML = `
        <p>Möchtest du die Aufgabe "<strong>${title}</strong>" wirklich löschen?</p>
        <wa-button slot="footer" id="cancelDeleteButton" data-dialog="close">Abbrechen</wa-button>
        <wa-button slot="footer" variant="danger" id="confirmDeleteButton" data-dialog="close">Wirklich löschen</wa-button>
    `;

    customElements.whenDefined('wa-dialog').then(() => {
        deleteDialog = dialog;

        const confirmButton = dialog.querySelector('#confirmDeleteButton');
        const cancelButton = dialog.querySelector('#cancelDeleteButton');

        confirmButton.removeEventListener('click', handleConfirmClick);
        cancelButton.removeEventListener('click', handleCancelClick);

        confirmButton.addEventListener('click', handleConfirmClick);
        cancelButton.addEventListener('click', handleCancelClick);

        window.requestAnimationFrame(() => {
            dialog.show(); 
        });
    });
}


function handleConfirmClick(event) {
    event.preventDefault();

    if (currentTaskIdToDelete !== null) {
        const taskId = currentTaskIdToDelete; 

        setTimeout(() => {
            deleteTask(taskId); 
            console.log(`Task ${taskId} deleted successfully (direct listener).`);
        }, 50); 
    }
    currentTaskIdToDelete = null;
}

function handleCancelClick(event) {
    event.preventDefault(); 
    currentTaskIdToDelete = null;
    console.log(`Deletion cancelled (direct listener).`);
}

// ===================================
// F. WA DIALOG (Modal) für Task-Bearbeitung
// ===================================

function showEditDialog(task) {
    const dialogId = 'editDialog';
    let dialog = document.getElementById(dialogId);
    
    if (!dialog) {
        dialog = document.createElement('wa-dialog');
        dialog.id = dialogId;
        dialog.setAttribute('label', 'Aufgabe bearbeiten');
        dialog.style.setProperty('--width', '600px');
        document.body.appendChild(dialog);
    }
    
    // Format tags as comma-separated string
    const tagsString = Array.isArray(task.tags) ? task.tags.join(', ') : '';
    
    dialog.innerHTML = `
        <form id="editTaskForm">
            <wa-input type="text" id="editTaskTitle" placeholder="Titel" size="large" required value="${escapeHtml(task.title)}" style="margin-bottom: 1rem;">
                <i class="fas fa-keyboard" slot="start"></i>
            </wa-input>
            
            <wa-textarea id="editTaskDescription" placeholder="Beschreibung (optional)" size="small" rows="3" style="margin-bottom: 1rem;"></wa-textarea>
            
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
                <wa-input type="date" id="editTaskDueDate" placeholder="Fälligkeitsdatum" size="medium" value="${task.due_date || ''}" style="flex: 1; min-width: 200px;">
                    <i class="fas fa-calendar" slot="start"></i>
                </wa-input>
                
                <wa-select id="editTaskPriority" placeholder="Priorität" size="medium" value="${task.priority || 'medium'}" style="flex: 1; min-width: 150px;">
                    <wa-option value="low" ${task.priority === 'low' ? 'selected' : ''}>Niedrig</wa-option>
                    <wa-option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Mittel</wa-option>
                    <wa-option value="high" ${task.priority === 'high' ? 'selected' : ''}>Hoch</wa-option>
                </wa-select>
            </div>
            
            <wa-input type="text" id="editTaskTags" placeholder="Tags (z.B. Arbeit, Privat)" size="medium" value="${escapeHtml(tagsString)}" style="margin-bottom: 1rem;">
                <i class="fas fa-tags" slot="end"></i>
            </wa-input>
        </form>
        
        <wa-button slot="footer" id="cancelEditButton" data-dialog="close">Abbrechen</wa-button>
        <wa-button slot="footer" variant="primary" id="confirmEditButton" appearance="filled" data-dialog="close">
            <i class="fas fa-save" slot="start"></i> Speichern
        </wa-button>
    `;

    customElements.whenDefined('wa-dialog').then(() => {
        const confirmButton = dialog.querySelector('#confirmEditButton');
        const cancelButton = dialog.querySelector('#cancelEditButton');
        const form = dialog.querySelector('#editTaskForm');
        const descriptionTextarea = dialog.querySelector('#editTaskDescription');
        
        // Set textarea value after component is defined
        if (descriptionTextarea && task.description) {
            descriptionTextarea.value = task.description;
        }

        // Handle save
        const handleSave = async (event) => {
            event.preventDefault();
            
            const title = dialog.querySelector('#editTaskTitle').value.trim();
            const description = dialog.querySelector('#editTaskDescription').value.trim();
            const dueDate = dialog.querySelector('#editTaskDueDate').value;
            const priority = dialog.querySelector('#editTaskPriority').value;
            const tagsRaw = dialog.querySelector('#editTaskTags').value;
            const tags = tagsRaw.split(',').map(t => t.trim()).filter(t => t.length > 0);
            
            if (!title) {
                showValidationError('Bitte geben Sie einen Titel ein.');
                return;
            }
            
            if (title.length > 255) {
                showValidationError('Der Titel darf maximal 255 Zeichen lang sein.');
                return;
            }
            
            confirmButton.setAttribute('loading', '');
            
            await editTask(task.id, title, description, dueDate, priority, tags);
            
            confirmButton.removeAttribute('loading');
            dialog.hide();
        };
        
        // Handle cancel
        const handleCancel = (event) => {
            event.preventDefault();
            dialog.hide();
        };

        confirmButton.removeEventListener('click', handleSave);
        confirmButton.addEventListener('click', handleSave);
        
        cancelButton.removeEventListener('click', handleCancel);
        cancelButton.addEventListener('click', handleCancel);
        
        form.removeEventListener('submit', handleSave);
        form.addEventListener('submit', handleSave);

        window.requestAnimationFrame(() => {
            dialog.show();
        });
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// DOM-Elemente abrufen
const htmlElement = document.documentElement; // Das <html> Tag
const themeToggleButton = document.getElementById('themeToggleButton');
const themeToggleButtonAuth = document.getElementById('themeToggleButtonAuth');
const themeIconMain = document.getElementById('themeIconMain');
const themeIconAuth = document.getElementById('themeIconAuth');

// Funktion zum Setzen des Themas
function setTheme(isDark) {
    if (isDark) {
        // Auf Dark Mode umschalten
        htmlElement.classList.remove('wa-light');
        htmlElement.classList.add('wa-dark');
        
        // Button-Text/Icon anpassen (zeigt an, dass man auf Hell umschalten kann)
        if (themeIconMain) {
            themeIconMain.setAttribute('name', 'moon');
            themeIconMain.setAttribute('label', 'Moon');
        }
        if (themeIconAuth) {
            themeIconAuth.setAttribute('name', 'moon');
            themeIconAuth.setAttribute('label', 'Moon');
        }
        // Zustand speichern
        localStorage.setItem('taskManagerTheme', 'dark');
    } else {
        // Auf Light Mode umschalten
        htmlElement.classList.remove('wa-dark');
        htmlElement.classList.add('wa-light');
        
        // Button-Text/Icon anpassen (zeigt an, dass man auf Dunkel umschalten kann)
        if (themeIconMain) {
            themeIconMain.setAttribute('name', 'sun');
            themeIconMain.setAttribute('label', 'Sun');
        }
        if (themeIconAuth) {
            themeIconAuth.setAttribute('name', 'sun');
            themeIconAuth.setAttribute('label', 'Sun');
        }
        
        // Zustand speichern
        localStorage.setItem('taskManagerTheme', 'light');
    }
}

// Beim Laden der Seite das gespeicherte Thema anwenden (oder Standard: light)
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('taskManagerTheme');
    if (savedTheme === 'dark') {
        setTheme(true); // Dunkel-Modus laden
    } else {
        setTheme(false); // Sicherstellen, dass Light-Modus aktiv ist
    }
});

// Event Listener für den Theme-Toggle-Button
themeToggleButton.addEventListener('click', () => {
    // Prüfen, ob aktuell wa-dark gesetzt ist
    const isCurrentlyDark = htmlElement.classList.contains('wa-dark');
    
    // Thema umschalten
    setTheme(!isCurrentlyDark);
});

// Event Listener für den Theme-Toggle-Button
themeToggleButtonAuth.addEventListener('click', () => {
    // Prüfen, ob aktuell wa-dark gesetzt ist
    const isCurrentlyDark = htmlElement.classList.contains('wa-dark');
    
    // Thema umschalten
    setTheme(!isCurrentlyDark);
});