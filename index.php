<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Awesome Task Manager (PHP/MySQL)</title>
	<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/webawesome.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="module" src="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/webawesome.loader.js"></script>

</head>
<body>

<div class="container">
    <h1><i class="fas fa-list-check"></i>Task Manager</h1>
    
    <form id="addTaskForm" class="add-task-form">
        <wa-input type="text" id="taskTitle" placeholder="Neue Aufgabe hinzufügen..." size="large" required>
            <i class="fas fa-keyboard" slot="start"></i>
        </wa-input>
        <wa-button appearance="filled" variant="primary" type="submit" size="large" id="addButton">
            <i class="fas fa-plus" slot="start"></i> Hinzufügen
        </wa-button>
    </form>

    <div class="task-controls">
        <wa-badge variant="info" id="taskCounter" style="--wa-badge-padding-inline: 1rem;">
            Noch <span>0</span> offene Aufgaben
        </wa-badge>

        <wa-button-group appearance="filled" size="small">
            <wa-button id="filterAll" variant="primary" active>Alle</wa-button>
            <wa-button id="filterOpen" variant="neutral">Offen</wa-button>
            <wa-button id="filterCompleted" variant="neutral">Erledigt</wa-button>
        </wa-button-group>
    </div>

    <div id="taskListContainer">
        </div>
</div>
<script src="tasks.js" defer></script>
</body>
</html>