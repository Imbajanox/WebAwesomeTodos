<!DOCTYPE html>
<html lang="de" class="wa-theme-awesome  wa-palette-awesome wa-brand-blue">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changelog - Task Manager</title>
	<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/webawesome.css">
	<link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/themes/awesome.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="module" src="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/webawesome.loader.js"></script>
    <style>
        .changelog-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        .changelog-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .changelog-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .version-section {
            margin-bottom: 2rem;
        }
        .version-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .version-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .version-date {
            font-size: 0.9rem;
            opacity: 0.7;
        }
        .change-list {
            margin-left: 1rem;
        }
        .change-item {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .change-item i {
            margin-top: 0.25rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="changelog-container">
        <div class="changelog-header">
            <h1><i class="fas fa-clock-rotate-left"></i>Changelog</h1>
            <a href="index.php" style="text-decoration: none;">
                <wa-button variant="neutral">
                    <i class="fas fa-arrow-left" slot="start"></i> Zurück zur App
                </wa-button>
            </a>
        </div>
        <wa-callout variant="warning" icon="fas fa-exclamation-triangle">
            <strong>Achtung:</strong> Diese Seite befindet sich noch in der Entwicklung. Datenverlust kann auftreten.
        </wa-callout>
        <wa-divider></wa-divider>
        <div class="version-section">
            <wa-card>
                <div slot="header" class="version-header">
                    <wa-badge variant="primary" size="large" class="version-number">v1.4.0</wa-badge>
                    <span class="version-date">Aktuell</span>
                </div>
                <div class="change-list">
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Security Update</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Input validation</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Bessere session sicherheit</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>CSRF schutz</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Changelog-Seite hinzugefügt</span>
                    </div>
                </div>
            </wa-card>
        </div>
        <div class="version-section">
            <wa-card>
                <div slot="header" class="version-header">
                    <wa-badge variant="primary" size="large" class="version-number">v1.3.0</wa-badge>
                    <span class="version-date">Oktober 2025</span>
                </div>
                <div class="change-list">
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Changelog-Seite hinzugefügt</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-circle-check" style="color: var(--wa-color-success);"></i>
                        <span>Link zur Changelog-Seite in der Navigation</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <div class="version-section">
            <wa-card>
                <div slot="header" class="version-header">
                    <wa-badge variant="neutral" size="large" class="version-number">v1.2.0</wa-badge>
                    <span class="version-date">Oktober 2025</span>
                </div>
                <div class="change-list">
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Dark-Mode-Unterstützung mit Theme-Toggle</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Tag-Verwaltung für Aufgaben</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Filterung von Aufgaben nach Tags</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <div class="version-section">
            <wa-card>
                <div slot="header" class="version-header">
                    <wa-badge variant="neutral" size="large" class="version-number">v1.1.0</wa-badge>
                    <span class="version-date">Oktober 2025</span>
                </div>
                <div class="change-list">
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Benutzerauthentifizierung und Registrierung</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-edit" style="color: var(--wa-color-success);"></i>
                        <span>Design überarbeitet</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <div class="version-section">
            <wa-card>
                <div slot="header" class="version-header">
                    <wa-badge variant="neutral" size="large" class="version-number">v1.0.0</wa-badge>
                    <span class="version-date">Oktober 2025</span>
                </div>
                <div class="change-list">
                    <div class="change-item">
                        <i class="fas fa-rocket" style="color: var(--wa-color-info);"></i>
                        <span>Erste Veröffentlichung</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Aufgaben erstellen, bearbeiten und löschen</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>Filterung (Alle / Offen / Erledigt)</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>MySQL-Datenbankintegration</span>
                    </div>
                    <div class="change-item">
                        <i class="fas fa-plus" style="color: var(--wa-color-success);"></i>
                        <span>WebAwesome-basiertes UI-Design</span>
                    </div>
                </div>
            </wa-card>
        </div>
    </div>

    <script>
        // Theme Management (same as in index.php)
        const htmlElement = document.documentElement;
        
        function setTheme(isDark) {
            if (isDark) {
                htmlElement.classList.remove('wa-light');
                htmlElement.classList.add('wa-dark');
                localStorage.setItem('taskManagerTheme', 'dark');
            } else {
                htmlElement.classList.remove('wa-dark');
                htmlElement.classList.add('wa-light');
                localStorage.setItem('taskManagerTheme', 'light');
            }
        }
        
        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('taskManagerTheme');
            if (savedTheme === 'dark') {
                setTheme(true);
            } else {
                setTheme(false);
            }
        });
    </script>
</body>
</html>
