// =================================================================
// 3. FRONTEND LOGIK (AJAX/FETCH) - ANGEPASST MIT FILTER/ZÄHLER
// =================================================================

const taskListContainer = document.getElementById('taskListContainer');
const addTaskForm = document.getElementById('addTaskForm');
const taskTitleInput = document.getElementById('taskTitle');
const addButton = document.getElementById('addButton');

// NEUE ELEMENTE FÜR DIE STEUERUNG
const taskCounter = document.getElementById('taskCounter');
const filterAllButton = document.getElementById('filterAll');
const filterOpenButton = document.getElementById('filterOpen');
const filterCompletedButton = document.getElementById('filterCompleted');

// Speichert alle geladenen Aufgaben und den aktuellen Filterzustand
let allTasks = [];
let currentFilter = 'all'; // Standardfilter

// Hilfsfunktion zum Erstellen von Web Awesome Komponenten
function createTaskCard(task) {
    // 1. Haupt-Container: wa-card
    const card = document.createElement('wa-card');
    card.className = task.is_completed == 1 ? 'completed' : '';
    card.setAttribute('data-id', task.id);
    // WICHTIG: Die Attribute 'appearance' und 'orientation' sind hier nicht mehr nötig

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

    // 4. Löschen Button erstellen
    const deleteButton = document.createElement('wa-button');
    // WICHTIG: Attribute 'variant', 'size', 'appearance' entfernt, damit CSS die Kontrolle hat
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
    taskListContainer.innerHTML = `<div class="loading"><wa-spinner size="large"></wa-spinner><p>Aufgaben werden geladen...</p></div>`;
    
    try {
        const response = await fetch('api.php');
        const tasks = await response.json();
        
        // Speichere die Aufgaben global
        allTasks = tasks; 

        // Rufe die Render-Funktion auf, die nun filtert und den Zähler aktualisiert
        renderTasks(); 

    } catch (error) {
        taskListContainer.innerHTML = `<wa-callout variant="danger">Fehler beim Laden der Aufgaben.</wa-callout>`;
        console.error('Fetch error:', error);
    }
}

// ===================================
// FILTER LOGIK
// ===================================
function setActiveFilter(filter, button) {
    currentFilter = filter;
    
    // Setze alle Buttons auf neutral
    [filterAllButton, filterOpenButton, filterCompletedButton].forEach(btn => {
        if (btn) btn.setAttribute('variant', 'neutral');
        if (btn) btn.removeAttribute('active');
    });

    // Setze den aktiven Button
    if (button) {
        button.setAttribute('variant', 'primary');
        button.setAttribute('active', '');
    }

    renderTasks();
}

// Event Listener für Filter
document.addEventListener('DOMContentLoaded', () => {
    fetchTasks();
    
    if (filterAllButton) filterAllButton.addEventListener('click', () => setActiveFilter('all', filterAllButton));
    if (filterOpenButton) filterOpenButton.addEventListener('click', () => setActiveFilter('open', filterOpenButton));
    if (filterCompletedButton) filterCompletedButton.addEventListener('click', () => setActiveFilter('completed', filterCompletedButton));
});

// ===================================
// B. CREATE: Neue Aufgabe hinzufügen
// ===================================
addTaskForm.addEventListener('submit', async (e) => {
    e.preventDefault(); 
    const title = taskTitleInput.value.trim();
    if (!title) return;

    addButton.setAttribute('loading', ''); 
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: title })
        });

        if (response.ok) {
            taskTitleInput.value = '';
            // Nach dem Hinzufügen immer auf den Filter 'Alle' wechseln, falls man gerade filtert
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
    try {
        const response = await fetch('api.php?action=toggle', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        if (response.ok) {
            // Wir müssen nicht die ganze Liste neu laden, sondern nur den lokalen Cache aktualisieren
            const task = allTasks.find(t => t.id == id);
            if (task) {
                // Status im lokalen Array umschalten
                task.is_completed = task.is_completed == 1 ? 0 : 1; 
            }
            // Liste neu rendern, um Sortierung/Filterung zu aktualisieren
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
    try {
        const response = await fetch('api.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        if (response.ok) {
            // Aufgabe aus dem lokalen Cache entfernen
            allTasks = allTasks.filter(t => t.id != id); 
            // Liste neu rendern
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

// MODIFIED: We still need a global reference, but it will only be set after
// the element is fully upgraded (in showDeleteDialog).
let deleteDialog = null; 

// Global variable to store the ID of the task currently being deleted
let currentTaskIdToDelete = null; 

// NOTE: Ensure you have a 'deleteTask(id)' function defined elsewhere!
// function deleteTask(id) { /* ... your task deletion logic ... */ }

// Attach the delegated event listener to the document body or a containing element


// -----------------------------------
// Function to show the deletion dialog
// -----------------------------------

function showDeleteDialog(id, title) {
    const dialogId = 'deleteDialog';
    let dialog = document.getElementById(dialogId);
    
    // Set the current ID globally before showing
    currentTaskIdToDelete = id;
	//console.log(currentTaskIdToDelete);
    if (!dialog) {
        dialog = document.createElement('wa-dialog');
        dialog.id = dialogId;
        dialog.setAttribute('label', 'Aufgabe löschen');
        document.body.appendChild(dialog);
        
        // We will assign deleteDialog later, after the element is defined!
    }
    
    // 1. Set content with IDs and the crucial data-dialog="close" attribute
    dialog.innerHTML = `
        <p>Möchtest du die Aufgabe "<strong>${title}</strong>" wirklich löschen?</p>
        <wa-button slot="footer" id="cancelDeleteButton" data-dialog="close">Abbrechen</wa-button>
        <wa-button slot="footer" variant="danger" id="confirmDeleteButton" data-dialog="close">Wirklich löschen</wa-button>
    `;

    // 2. Show the dialog
    customElements.whenDefined('wa-dialog').then(() => {
        deleteDialog = dialog;

        // --- NEW: Attach listeners directly to the buttons ---
        const confirmButton = dialog.querySelector('#confirmDeleteButton');
        const cancelButton = dialog.querySelector('#cancelDeleteButton');

        // IMPORTANT: Use .removeEventListener first to prevent duplicate listeners
        confirmButton.removeEventListener('click', handleConfirmClick);
        cancelButton.removeEventListener('click', handleCancelClick);

        confirmButton.addEventListener('click', handleConfirmClick);
        cancelButton.addEventListener('click', handleCancelClick);
        // ----------------------------------------------------

        window.requestAnimationFrame(() => {
            dialog.show(); 
        });
    });
}

// --- NEW: Separate handler functions ---

function handleConfirmClick(event) {
    event.preventDefault(); // Prevent default if needed, though data-dialog="close" might run first

    if (currentTaskIdToDelete !== null) {
        const taskId = currentTaskIdToDelete; 
        
        // Use setTimeout to ensure the dialog closes before deletion runs
        setTimeout(() => {
            deleteTask(taskId); 
            console.log(`Task ${taskId} deleted successfully (direct listener).`);
        }, 50); 
    }
    
    // Since data-dialog="close" is on the button, the dialog should hide itself.
    currentTaskIdToDelete = null;
}

function handleCancelClick(event) {
    event.preventDefault(); 
    currentTaskIdToDelete = null;
    console.log(`Deletion cancelled (direct listener).`);
    // Dialog closes itself due to data-dialog="close"
}