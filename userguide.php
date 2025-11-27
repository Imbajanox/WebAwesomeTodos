<!DOCTYPE html>
<html lang="de" class="wa-theme-awesome  wa-palette-awesome wa-brand-blue">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerhandbuch - Task Manager</title>
	<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/webawesome.css">
	<link rel="stylesheet" href="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/styles/themes/awesome.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="module" src="https://early.webawesome.com/webawesome@3.0.0-beta.6/dist/webawesome.loader.js"></script>
    <style>
        .guide-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        .guide-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .guide-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .guide-section {
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--wa-color-primary);
        }
        .subsection-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .feature-list {
            margin-left: 1rem;
        }
        .feature-item {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }
        .feature-item i {
            margin-top: 0.25rem;
            flex-shrink: 0;
            width: 1.2rem;
        }
        .tip-box {
            background: var(--wa-color-info-50);
            border: 1px solid var(--wa-color-info-200);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }
        .tip-box i {
            color: var(--wa-color-info);
            margin-right: 0.5rem;
        }
        .version-badge {
            background: var(--wa-color-success);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .new-feature {
            background: var(--wa-color-success-50);
            border-left: 4px solid var(--wa-color-success);
            padding: 1rem;
            margin: 1rem 0;
        }
        .step-list {
            counter-reset: step-counter;
            list-style: none;
            padding-left: 0;
        }
        .step-item {
            counter-increment: step-counter;
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .step-item::before {
            content: counter(step-counter);
            background: var(--wa-color-primary);
            color: white;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        .step-content {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="guide-container">
        <div class="guide-header">
            <h1><i class="fas fa-book"></i>Benutzerhandbuch</h1>
            <a href="index.php" style="text-decoration: none;">
                <wa-button variant="neutral">
                    <i class="fas fa-arrow-left" slot="start"></i> Zurück zur App
                </wa-button>
            </a>
        </div>

        <wa-callout variant="info" icon="fas fa-lightbulb">
            <strong>Neu in Version 2.2:</strong> Volltext-Suche, Drag-and-Drop, wiederkehrende Aufgaben und intelligente Filter
        </wa-callout>

        <wa-divider></wa-divider>

        <!-- 🚀 Schnellstart -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-rocket"></i>Schnellstart</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-user-plus"></i>Konto erstellen oder anmelden</h3>
                <ol class="step-list">
                    <li class="step-item">
                        <div class="step-content">
                            <strong>Neue Benutzer:</strong> Besuchen Sie die Anwendung und klicken Sie auf "Noch kein Konto? Hier registrieren"
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Geben Sie einen Benutzernamen (min. 3 Zeichen) und ein Passwort (min. 6 Zeichen) ein
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Klicken Sie auf "Registrieren" - Sie werden automatisch angemeldet
                        </div>
                    </li>
                </ol>

                <div class="tip-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tipp:</strong> Bestehende Benutzer geben einfach Benutzername und Passwort ein und klicken auf "Anmelden"
                </div>
            </wa-card>
        </div>

        <!-- 🆕 NEUE FEATURES V2.2 -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title">
                        <i class="fas fa-sparkles"></i>Neue Features in Version 2.2
                        <span class="version-badge">NEU</span>
                    </h2>
                </div>

                <!-- Volltext-Suche -->
                <div class="new-feature">
                    <h3 class="subsection-title"><i class="fas fa-search"></i>Volltext-Suche</h3>
                    <p>Suchen Sie blitzschnell über alle Ihre Aufgaben:</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Echtzeit-Suche:</strong> Ergebnisse erscheinen während der Eingabe</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Komplexe Suche:</strong> Titel, Beschreibung und Tags werden durchsucht</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>UND-Logik:</strong> Mehrere Begriffe müssen alle gefunden werden</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Responsive:</strong> Ladezustände und visuelles Feedback</span>
                        </div>
                    </div>
                    <h4>Anwendung:</h4>
                    <ol class="step-list">
                        <li class="step-item">
                            <div class="step-content">
                                Geben Sie Suchbegriffe in das Suchfeld "Aufgaben durchsuchen..." ein
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Ergebnisse werden sofort angezeigt
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Leeren Sie das Feld, um alle Aufgaben anzuzeigen
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Drag-and-Drop Neuordnung -->
                <div class="new-feature">
                    <h3 class="subsection-title"><i class="fas fa-arrows-alt"></i>Drag-and-Drop Neuordnung</h3>
                    <p>Organisieren Sie Ihre Aufgaben per Drag-and-Drop:</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Native HTML5:</strong> Verwendet moderne Browser-APIs</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Visuelles Feedback:</strong> Transparenz, Rotation und Schatten beim Ziehen</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Drop-Zonen:</strong> Zielbereiche werden hervorgehoben</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Automatisch Speichern:</strong> Positionen werden sofort gespeichert</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Mobile-Optimiert:</strong> Touch-freundliche Drag-Handles</span>
                        </div>
                    </div>
                    <h4>Anwendung:</h4>
                    <ol class="step-list">
                        <li class="step-item">
                            <div class="step-content">
                                Wählen Sie "Benutzerdefiniert" aus dem Sortier-Dropdown
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Klicken und halten Sie eine Aufgabenkarte
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Ziehen Sie sie an die gewünschte Position
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Lassen Sie los - die neue Reihenfolge wird gespeichert
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Wiederkehrende Aufgaben -->
                <div class="new-feature">
                    <h3 class="subsection-title"><i class="fas fa-sync-alt"></i>Wiederkehrende Aufgaben</h3>
                    <p>Erstellen Sie Aufgaben, die sich automatisch wiederholen:</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Flexible Muster:</strong> Täglich, wöchentlich, monatlich oder jährlich</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Anpassbare Intervalle:</strong> Alle X Tage/Wochen/Monate/Jahre</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Optionales Enddatum:</strong> Begrenzen Sie die Wiederholungen</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Automatische Generierung:</strong> Neue Aufgabeninstanzen werden erstellt</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Visuelle Markierung:</strong> 🔄 Symbol kennzeichnet wiederkehrende Aufgaben</span>
                        </div>
                    </div>
                    <h4>Anwendung:</h4>
                    <ol class="step-list">
                        <li class="step-item">
                            <div class="step-content">
                                Erstellen Sie eine Aufgabe mit Fälligkeitsdatum
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Legen Sie Wiederholungsmuster und Intervall fest
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Optional: Enddatum für die Wiederholung setzen
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Neue Instanzen werden automatisch generiert
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Intelligente Schnellfilter -->
                <div class="new-feature">
                    <h3 class="subsection-title"><i class="fas fa-bolt"></i>Intelligente Schnellfilter</h3>
                    <p>Filtern Sie Ihre Aufgaben mit einem Klick:</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Heute fällig:</strong> Nur Aufgaben, die heute fällig sind</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Diese Woche:</strong> Aufgaben der nächsten 7 Tage</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Hohe Priorität:</strong> Nur dringende Aufgaben</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Überfällig:</strong> Aufgaben mit überschrittenem Fälligkeitsdatum</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check" style="color: var(--wa-color-success);"></i>
                            <span><strong>Kombinierbar:</strong> Funktioniert mit Tag- und Prioritätsfiltern</span>
                        </div>
                    </div>
                    <h4>Anwendung:</h4>
                    <ol class="step-list">
                        <li class="step-item">
                            <div class="step-content">
                                Klicken Sie auf das "Schnellfilter"-Dropdown
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Wählen Sie einen Preset aus der Liste
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                Aufgaben werden sofort gefiltert
                            </div>
                        </li>
                        <li class="step-item">
                            <div class="step-content">
                                "Alle Aufgaben" zum Zurücksetzen der Filter
                            </div>
                        </li>
                    </ol>
                </div>
            </wa-card>
        </div>

        <!-- 📝 Grundlegende Aufgabenverwaltung -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-tasks"></i>Grundlegende Aufgabenverwaltung</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-plus-circle"></i>Aufgabe erstellen</h3>
                <ol class="step-list">
                    <li class="step-item">
                        <div class="step-content">
                            Geben Sie einen Aufgabentitel in das Eingabefeld ein
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            <strong>Optional:</strong> Beschreibung, Fälligkeitsdatum und Priorität festlegen
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            <strong>Optional:</strong> Tags hinzufügen (z.B. `Arbeit, Privat, Wichtig`)
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Klicken Sie auf "Hinzufügen" (+) oder drücken Sie Enter
                        </div>
                    </li>
                </ol>

                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-keyboard" style="color: var(--wa-color-info);"></i>
                        <span><strong>Keyboard Shortcut:</strong> Enter键 zum schnellen Erstellen</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tag" style="color: var(--wa-color-info);"></i>
                        <span><strong>Tags:</strong> Mehrere Tags mit Komma trennen</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <!-- 🎯 Erweiterte Features -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-magic"></i>Erweiterte Features</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-search"></i>Volltext-Suche</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-search" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Echtzeit-Suche:</strong> Ergebnisse erscheinen während der Eingabe</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-file-alt" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Suchbereiche:</strong> Titel, Beschreibung und Tags werden durchsucht</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-filter" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Kombination:</strong> Suche funktioniert mit allen Filtern</span>
                    </div>
                </div>

                <h3 class="subsection-title"><i class="fas fa-hand-rock"></i>Drag-and-Drop Neuordnung</h3>
                <ol class="step-list">
                    <li class="step-item">
                        <div class="step-content">
                            Klicken Sie auf Sortier-Dropdown und wählen Sie "Benutzerdefiniert"
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Klicken und halten Sie eine Aufgabenkarte
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Ziehen Sie sie an die gewünschte Position
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="step-content">
                            Lassen Sie die Maustaste los - Position wird gespeichert
                        </div>
                    </li>
                </ol>

                <h3 class="subsection-title"><i class="fas fa-sync-alt"></i>Wiederkehrende Aufgaben</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-calendar-day" style="color: var(--wa-color-success);"></i>
                        <span><strong>Unterstützte Muster:</strong> Täglich, Wöchentlich, Monatlich, Jährlich</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock" style="color: var(--wa-color-success);"></i>
                        <span><strong>Intervall-Einstellungen:</strong> Flexible Wiederholungsabstände</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-infinity" style="color: var(--wa-color-success);"></i>
                        <span><strong>Automatische Generierung:</strong> Neue Instanzen werden erstellt</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <!-- 📊 Filter und Sortierung -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-filter"></i>Filter und Sortierung</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-bolt"></i>Smart Filter (Schnellfilter)</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-calendar-day" style="color: var(--wa-color-warning);"></i>
                        <span><strong>Heute fällig:</strong> Nur Aufgaben, die heute fällig sind</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar-week" style="color: var(--wa-color-warning);"></i>
                        <span><strong>Diese Woche:</strong> Aufgaben der nächsten 7 Tage</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-exclamation-triangle" style="color: var(--wa-color-danger);"></i>
                        <span><strong>Hohe Priorität:</strong> Nur dringende Aufgaben</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-times-circle" style="color: var(--wa-color-danger);"></i>
                        <span><strong>Überfällig:</strong> Aufgaben mit überschrittenem Fälligkeitsdatum</span>
                    </div>
                </div>

                <h3 class="subsection-title"><i class="fas fa-sort"></i>Sortieroptionen</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-star" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Standard:</strong> Nach Erstellungsdatum (neueste zuerst)</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-exclamation-circle" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Nach Priorität:</strong> Hoch → Mittel → Niedrig</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Nach Fälligkeit:</strong> Früheste Termine zuerst</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-hand-pointer" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Benutzerdefiniert:</strong> Per Drag-and-Drop festgelegte Reihenfolge</span>
                    </div>
                </div>
            </wa-card>
        </div>

        <!-- 📱 Mobile Nutzung -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-mobile-alt"></i>Mobile Nutzung</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-hand-pointer"></i>Touch-Optimierung</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-plus-square" style="color: var(--wa-color-success);"></i>
                        <span><strong>Große Touch-Buttons:</strong> Einfache Bedienung auf Smartphones</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-arrows-alt" style="color: var(--wa-color-success);"></i>
                        <span><strong>Mobile Drag-and-Drop:</strong> Tippen und halten zum Verschieben</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile" style="color: var(--wa-color-success);"></i>
                        <span><strong>Responsive Design:</strong> Automatische Anpassung an Bildschirmgröße</span>
                    </div>
                </div>

                <div class="tip-box">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Mobile Tipp:</strong> Verwenden Sie die Swipe-Gesten (in zukünftigen Versionen) für schnelle Aktionen
                </div>
            </wa-card>
        </div>

        <!-- 🔐 Sicherheit -->
        <div class="guide-section">
            <wa-card>
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-shield-alt"></i>Sicherheit und Tipps</h2>
                </div>

                <h3 class="subsection-title"><i class="fas fa-key"></i>Passwortsicherheit</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle" style="color: var(--wa-color-success);"></i>
                        <span>Verwenden Sie mindestens 8 Zeichen</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle" style="color: var(--wa-color-success);"></i>
                        <span>Kombinieren Sie Groß-/Kleinbuchstaben, Zahlen und Sonderzeichen</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle" style="color: var(--wa-color-success);"></i>
                        <span>Verwenden Sie einzigartige Passwörter</span>
                    </div>
                </div>

                <h3 class="subsection-title"><i class="fas fa-tools"></i>Problembehebung</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-search" style="color: var(--wa-color-info);"></i>
                        <span><strong>Aufgaben nicht sichtbar:</strong> Filter prüfen, Suchfeld leeren, Seite neu laden</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-arrows-alt" style="color: var(--wa-color-info);"></i>
                        <span><strong>Drag-and-Drop funktioniert nicht:</strong> "Benutzerdefiniert" als Sortierung wählen, JavaScript aktivieren</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tachometer-alt" style="color: var(--wa-color-info);"></i>
                        <span><strong>Langsame Leistung:</strong> Filter verwenden, Tabs schließen, Cache leeren</span>
                    </div>
                </div>

                <div class="tip-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Technische Anforderungen:</strong> Moderner Browser (Chrome 60+, Firefox 55+, Safari 12+, Edge 79+), aktive Internetverbindung, PHP 7.4+, MySQL 5.6+
                </div>
            </wa-card>
        </div>

        <!-- 🎯 Nächste Schritte -->
        <div class="guide-section">
            <wa-card variant="primary">
                <div slot="header">
                    <h2 class="section-title"><i class="fas fa-rocket"></i>Nächste Schritte</h2>
                </div>

                <p>Nachdem Sie die Grundfunktionen beherrschen, empfehlen wir:</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-flask" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Experimentieren:</strong> Kombinieren Sie verschiedene Filter und Sortierungen</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tags" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Organisieren:</strong> Nutzen Sie Tags und Drag-and-Drop für optimale Ordnung</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-sync" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Optimieren:</strong> Verwenden Sie wiederkehrende Aufgaben für wiederkehrende Tätigkeiten</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-download" style="color: var(--wa-color-primary);"></i>
                        <span><strong>Exportieren:</strong> Nutzen Sie Kalender-Export für externe Tools</span>
                    </div>
                </div>

                <wa-divider></wa-divider>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <h3 style="color: var(--wa-color-primary); margin-bottom: 1rem;">
                        <i class="fas fa-trophy"></i> Viel Erfolg mit Ihrer Aufgabenverwaltung!
                    </h3>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="changelog.php" style="text-decoration: none;">
                            <wa-button variant="neutral">
                                <i class="fas fa-history" slot="start"></i> Changelog
                            </wa-button>
                        </a>
                        <a href="index.php" style="text-decoration: none;">
                            <wa-button variant="primary">
                                <i class="fas fa-home" slot="start"></i> Zurück zur App
                            </wa-button>
                        </a>
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