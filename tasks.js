// =================================================================
// 3. FRONTEND LOGIK (AJAX/FETCH) - ANGEPASST MIT FILTER/ZÄHLER
// =================================================================

const taskListContainer = document.getElementById('taskListContainer');
const addTaskForm = document.getElementById('addTaskForm');
const taskTitleInput = document.getElementById('taskTitle');
const addButton = document.getElementById('addButton');

const taskCounter = document.getElementById('taskCounter');
const filterAllButton = document.getElementById('filterAll');
const filterOpenButton = document.getElementById('filterOpen');
const filterCompletedButton = document.getElementById('filterCompleted');

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
let isLoggedIn = false;
let currentUsername = '';

// =================================================================
// 1. ANZEIGE LOGIK (Views umschalten)
// =================================================================

/**
 * Schaltet zwischen der Login/Register-Ansicht und der Task-Manager-Ansicht um.
 * @param {boolean} loggedIn - true, wenn der Benutzer eingeloggt ist.
 * @param {string} username - Der Benutzername des eingeloggten Benutzers.
 */
function updateView(loggedIn, username = '') {
    isLoggedIn = loggedIn;
    currentUsername = username;
    
    if (loggedIn) {
        authView.style.display = 'none';
        taskManagerView.style.display = 'block';
        userInfo.style.display = 'flex';
        usernameDisplay.textContent = username;
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

    // 2. Checkbox (Toggle) erstellen
    const checkbox = document.createElement('wa-checkbox');
    // Attribute 'name', 'size', 'value' entfernt, damit CSS die Kontrolle hat
    if (task.is_completed == 1) {
        checkbox.setAttribute('checked', '');
    }
    // Event Listener für Status-Update
    checkbox.addEventListener('change', () => toggleTask(task.id));

    // 3. Titel-Span erstellen
    const titleSpan = document.createElement('span');
    titleSpan.className = 'task-title';
    titleSpan.textContent = task.title; // Füllt den Text ein

    if (Array.isArray(task.tags) && task.tags.length > 0) {
        const tagsWrap = document.createElement('div');
        tagsWrap.style.display = 'flex';
        tagsWrap.style.gap = '0.4rem';
        tagsWrap.style.marginLeft = '0';
        task.tags.forEach(tn => {
            const badge = document.createElement('wa-badge');
            badge.textContent = tn;
            badge.setAttribute('variant', 'neutral');
            badge.style.fontSize = '0.75rem';
            tagsWrap.appendChild(badge);
        });
        titleSpan.appendChild(tagsWrap);
    }

    // 4. Löschen Button erstellen
    const deleteButton = document.createElement('wa-button');
    deleteButton.innerHTML = '<i class="fas fa-trash-can"></i>'; 
    deleteButton.addEventListener('click', () => showDeleteDialog(task.id, task.title));

    // 5. Aktionen-Gruppe erstellen (enthält NUR NOCH den Löschen-Button)
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'task-actions';
    actionsDiv.appendChild(deleteButton); 

    // 6. Alles in der gewünschten Reihenfolge zur Card hinzufügen: [Checkbox] | [Text] | [Button]
    card.appendChild(checkbox);
    card.appendChild(titleSpan);
    card.appendChild(actionsDiv);
    
    return card;
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

    // 3. DOM aktualisieren
    taskListContainer.innerHTML = '';
    
    if (tasksToRender.length === 0) {
        let message = 'Keine Aufgaben gefunden.';
        if (currentFilter === 'open') message = 'Alle Aufgaben erledigt! 🎉';
        if (currentFilter === 'completed') message = 'Keine abgeschlossenen Aufgaben.';

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
});

// ===================================
// B. CREATE: Neue Aufgabe hinzufügen
// ===================================
addTaskForm.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    if (!isLoggedIn) return;

    const title = taskTitleInput.value.trim();
    if (!title) return;

    const tagsRaw = taskTagsInput ? taskTagsInput.value : '';
    const tags = tagsRaw.split(',').map(t => t.trim()).filter(t => t.length > 0);

    addButton.setAttribute('loading', ''); 
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: title, tags: tags })
        });

        if (response.ok) {
            taskTitleInput.value = '';
            await fetchTagsAndRenderBar();
            if (currentFilter !== 'all') {
                setActiveFilter('all', filterAllButton); 
            } else {
                await fetchTasks();
            }
        } else {
            alert('Fehler beim Hinzufügen der Aufgabe.');
        }
    } catch (error) {
        console.error('Add error:', error);
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
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        if (response.ok) {
            const task = allTasks.find(t => t.id == id);
            if (task) {
                task.is_completed = task.is_completed == 1 ? 0 : 1; 
            }

            renderTasks(); 
        } else {
            alert('Fehler beim Ändern des Status.');
        }
    } catch (error) {
        console.error('Toggle error:', error);
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
            headers: { 'Content-Type': 'application/json' },
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

// DOM-Elemente abrufen
const htmlElement = document.documentElement; // Das <html> Tag
const themeToggleButton = document.getElementById('themeToggleButton');
const themeIcon = document.getElementById('themeIcon');

// Funktion zum Setzen des Themas
function setTheme(isDark) {
    if (isDark) {
        // Auf Dark Mode umschalten
        htmlElement.classList.remove('wa-light');
        htmlElement.classList.add('wa-dark');
        
        // Button-Text/Icon anpassen (zeigt an, dass man auf Hell umschalten kann)
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        themeToggleButton.textContent = 'Dunkel';
        themeToggleButton.prepend(themeIcon); // Icon wieder einfügen
        
        // Zustand speichern
        localStorage.setItem('taskManagerTheme', 'dark');
    } else {
        // Auf Light Mode umschalten
        htmlElement.classList.remove('wa-dark');
        htmlElement.classList.add('wa-light');
        
        // Button-Text/Icon anpassen (zeigt an, dass man auf Dunkel umschalten kann)
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        themeToggleButton.textContent = 'Hell';
        themeToggleButton.prepend(themeIcon); // Icon wieder einfügen
        
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