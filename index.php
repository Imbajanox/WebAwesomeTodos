<!DOCTYPE html>
<html lang="de" class="wa-theme-awesome wa-palette-bright wa-brand-blue wa-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
	<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/webawesome.css">
	<link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/themes/awesome.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="module" src="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/webawesome.loader.js"></script>

</head>
<body>
	<div class="container">
		<h1><i class="fas fa-list-check"></i>Task Manager</h1>
		<div id="userInfo" style="display: none; margin-bottom: 1rem; text-align: right;">
			
			<wa-badge variant="success" size="small">Eingeloggt als:&nbsp;<strong id="usernameDisplay"></strong></wa-badge>
			<wa-button id="logoutButton" variant="neutral" size="small" style="margin-left: 0.5rem;">
				<i class="fas fa-right-from-bracket" slot="start"></i> Logout
			</wa-button>
			<wa-button id="themeToggleButton" variant="neutral" size="small" style="margin-right: 0.5rem;">
				<i class="fas fa-sun" slot="start" id="themeIcon"></i> Hell
			</wa-button>
		</div>
        
        <div id="authView" class="auth-view">
            <wa-card style="max-width: 400px; margin: 2rem auto;">
                <h2 slot="header" id="authTitle">Login</h2>

                <div id="authMessage" style="display: none; margin-bottom: 1rem;"></div>

                <form id="loginForm">
                    <wa-input type="text" id="loginUsername" placeholder="Benutzername" size="large" required style="margin-bottom: 1rem;">
                        <i class="fas fa-user" slot="start"></i>
                    </wa-input>
                    <wa-input type="password" id="loginPassword" placeholder="Passwort" size="large" required style="margin-bottom: 1rem;">
                        <i class="fas fa-lock" slot="start"></i>
                    </wa-input>
                    <wa-button appearance="filled" variant="primary" type="submit" size="large" id="loginButton" style="width: 100%;">
                        <i class="fas fa-sign-in-alt" slot="start"></i> Login
                    </wa-button>
                </form>

                <form id="registerForm" style="display: none;">
                    <wa-input type="text" id="registerUsername" placeholder="Benutzername" size="large" required style="margin-bottom: 1rem;">
                        <i class="fas fa-user-plus" slot="start"></i>
                    </wa-input>
                    <wa-input type="password" id="registerPassword" placeholder="Passwort (min. 6 Zeichen)" size="large" required style="margin-bottom: 1rem;">
                        <i class="fas fa-key" slot="start"></i>
                    </wa-input>
                    <wa-button appearance="filled" variant="success" type="submit" size="large" id="registerButton" style="width: 100%;">
                        <i class="fas fa-user-plus" slot="start"></i> Registrieren
                    </wa-button>
                </form>

                <p style="margin-top: 1rem; text-align: center;">
                    <a href="#" id="toggleAuthView">Noch kein Konto? Hier registrieren.</a>
                </p>
            </wa-card>
        </div>
        
        <div id="taskManagerView" style="display: none;">
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
	</div>
	<script src="tasks.js" defer></script>
</body>
</html>