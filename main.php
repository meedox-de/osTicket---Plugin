<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class DatumHervorhebungPlugin extends Plugin {
    var $config_class = 'DatumHervorhebungConfig';

    // Diese Debug-Funktion ist optional und kann für Fehlerbehebung verwendet werden
    private function debug_log($message) {
        $logfile = __DIR__ . '/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND);
    }

    // init() wird von osTicket automatisch aufgerufen
    function init() {
        // Elternmethode aufrufen
        parent::init();

        // Debug-Meldung
        $this->debug_log('DatumHervorhebung Plugin init() wurde aufgerufen');
        
        // DIREKTE METHODE 1: Exportiere JavaScript-Datei
        $jsFile = dirname(dirname(dirname(__DIR__))) . '/js/datumhervorhebung.js';
        $this->debug_log("Exportiere JavaScript-Datei nach: $jsFile");
        
        // JavaScript-Code direkt mit allen nötigen Funktionen
        $jsContent = $this->getPlainJavaScript();
        file_put_contents($jsFile, $jsContent);
        $this->debug_log("JavaScript-Datei wurde exportiert");

        // DIREKTE METHODE 2: Direkter Header-Eintrag in Session
        // Dies ist ein Fallback, falls osTicket keine Signale verwendet
        $_SESSION['datum_plugin_loaded'] = true;
        $_SESSION['datum_plugin_time'] = time();
        $this->debug_log("Session-Marker für Plugin wurde gesetzt");
        
        // DIREKTE METHODE 3: Füge das JavaScript direkt in die Agenten-Header-Datei ein
        // Diese Methode modifiziert temporär eine osTicket-Datei, wird aber beim nächsten Update überschrieben
        $headerFile = INCLUDE_DIR . 'staff/header.inc.php';
        if (file_exists($headerFile) && is_writable($headerFile)) {
            $this->debug_log("Versuche, JS-Einbindung in $headerFile hinzuzufügen");
            
            // Lese den Inhalt der Header-Datei
            $content = file_get_contents($headerFile);
            
            // Unsere JavaScript-Einbindung
            $scriptTag = '<script src="'.ROOT_PATH.'js/datumhervorhebung.js"></script>';
            
            // Prüfe, ob das Script bereits enthalten ist
            if (strpos($content, 'datumhervorhebung.js') === false) {
                // Suche nach dem schließenden </head> Tag
                $newContent = str_replace('</head>', "$scriptTag\n</head>", $content);
                
                // Nur schreiben, wenn sich der Inhalt geändert hat
                if ($content !== $newContent) {
                    // Backup erstellen
                    $backupFile = $headerFile . '.bak';
                    if (!file_exists($backupFile)) {
                        file_put_contents($backupFile, $content);
                        $this->debug_log("Backup der Header-Datei erstellt: $backupFile");
                    }
                    
                    // Neue Datei mit Script-Tag schreiben
                    file_put_contents($headerFile, $newContent);
                    $this->debug_log("Script-Tag wurde zur Header-Datei hinzugefügt");
                } else {
                    $this->debug_log("Keine Änderung in der Header-Datei notwendig");
                }
            } else {
                $this->debug_log("Script ist bereits in der Header-Datei enthalten");
            }
        } else {
            $this->debug_log("Header-Datei $headerFile existiert nicht oder ist nicht beschreibbar");
        }
        
        // METHODE 4: Erstelle/Aktualisiere eine Datei, die JavaScript direkt enthält
        // Diese Methode schreibt das vollständige JavaScript in eine Datei im Stamm-Verzeichnis
        $directJsFile = dirname(dirname(dirname(__DIR__))) . '/datumhervorhebung-direkt.js.php';
        $this->debug_log("Erstelle direkte JavaScript-PHP-Datei: $directJsFile");
        
        // Erstelle eine PHP-Datei, die das JavaScript ausgibt
        $directJsContent = '<?php 
// Datum Hervorhebung Plugin - Direkte JavaScript-Datei
header("Content-Type: application/javascript");
?>' . "\n" . $jsContent;
        
        file_put_contents($directJsFile, $directJsContent);
        $this->debug_log("Direkte JavaScript-PHP-Datei wurde erstellt");
        
        // METHODE 5: Registriere das Plugin für einen bestimmten Hook
        // osTicket nutzt verschiedene Signal-Klassen für verschiedene Bereiche
        if (class_exists('Format', false)) {
            $this->debug_log("Format-Klasse gefunden, versuche Format::add_callback zu verwenden");
            
            // Annahme: Format::add_callback könnte existieren (in neueren osTicket-Versionen)
            if (method_exists('Format', 'add_callback')) {
                Format::add_callback('html_footer', function() {
                    $this->debug_log("html_footer Callback wurde aufgerufen");
                    echo '<script src="'.ROOT_PATH.'js/datumhervorhebung.js"></script>';
                });
                $this->debug_log("Callback für html_footer registriert");
            }
        }
        
        // Zusätzlicher Debug-Info
        $this->debug_log('init() wurde abgeschlossen. Script wurde auf mehreren Wegen eingebunden.');
    }
    
    // JavaScript-Code ohne PHP-Tags als separate Methode
    private function getPlainJavaScript() {
        // Konfiguration abrufen
        $config = $this->getConfig();
        $color = $config->get('highlight_color') ?: 'red';
        
        // JavaScript-Code ohne PHP-Tags
        return <<<EOD
// Datum Hervorhebung Plugin
(function() {
    // Verhindern, dass das Script mehrfach ausgeführt wird
    if (window.datumHighlightLoaded) return;
    window.datumHighlightLoaded = true;
    
    console.log("===== Datum Hervorhebung Plugin geladen =====");
    
    // Debugging-Funktion
    function log(message) {
        console.log("[DatumHervorhebung] " + message);
    }
    
    // Debug-Element anzeigen
    function showDebugInfo() {
        var debugDiv = document.createElement('div');
        debugDiv.id = 'datum-debug-panel';
        debugDiv.style.position = 'fixed';
        debugDiv.style.bottom = '10px';
        debugDiv.style.right = '10px';
        debugDiv.style.backgroundColor = 'yellow';
        debugDiv.style.border = '2px solid red';
        debugDiv.style.padding = '10px';
        debugDiv.style.zIndex = '9999';
        debugDiv.style.fontFamily = 'monospace';
        debugDiv.style.fontSize = '12px';
        debugDiv.style.color = 'black';
        debugDiv.innerHTML = '<strong>Datum Hervorhebung aktiv</strong><div id="datum-debug-log"></div>';
        document.body.appendChild(debugDiv);
        
        // Schließen-Button
        var closeButton = document.createElement('button');
        closeButton.innerHTML = 'X';
        closeButton.style.position = 'absolute';
        closeButton.style.top = '2px';
        closeButton.style.right = '2px';
        closeButton.style.backgroundColor = 'red';
        closeButton.style.color = 'white';
        closeButton.style.border = 'none';
        closeButton.style.borderRadius = '50%';
        closeButton.style.width = '20px';
        closeButton.style.height = '20px';
        closeButton.style.fontSize = '12px';
        closeButton.style.cursor = 'pointer';
        closeButton.onclick = function() {
            debugDiv.style.display = 'none';
        };
        debugDiv.appendChild(closeButton);
        
        log("Debug-Panel wurde angezeigt");
        return debugDiv;
    }
    
    // Helfer-Funktion zum Hinzufügen von Nachrichten zum Debug-Panel
    function addDebugMessage(message) {
        var logDiv = document.getElementById('datum-debug-log');
        if (!logDiv) return;
        
        var entry = document.createElement('div');
        entry.textContent = message;
        entry.style.borderBottom = '1px dotted #ccc';
        entry.style.paddingBottom = '2px';
        entry.style.marginBottom = '2px';
        logDiv.appendChild(entry);
    }
    
    // Funktion zum Hervorheben von Datumsangaben
    function highlightDatesInTickets() {
        log("Suche nach Datumsangaben in Tickets...");
        
        // Datumsregex für verschiedene Formate (DD.MM.YYYY, DD/MM/YYYY, etc.)
        var dateRegex = /\b\d{1,2}[./-]\d{1,2}[./-]\d{2,4}\b/;
        
        // CSS-Regel hinzufügen
        var style = document.createElement('style');
        style.textContent = '.datum-highlight { color: {$color} !important; font-weight: bold !important; }';
        document.head.appendChild(style);
        
        // Verschiedene Ansätze für die Ticketliste versuchen
        
        // 1. Direkte Suche nach Elementen mit Datumsangaben
        var allElements = document.querySelectorAll('a, td, span, div');
        log("Prüfe " + allElements.length + " Elemente auf Datumsangaben");
        
        var found = 0;
        
        for (var i = 0; i < allElements.length; i++) {
            var el = allElements[i];
            
            // Nur sichtbare Elemente mit Text prüfen
            if (el.textContent && 
                el.textContent.trim().length > 0 && 
                el.offsetParent !== null) {  // Nur sichtbare Elemente
                
                if (dateRegex.test(el.textContent)) {
                    // Element markieren
                    el.classList.add('datum-highlight');
                    el.style.setProperty('color', '{$color}', 'important');
                    el.style.setProperty('font-weight', 'bold', 'important');
                    
                    // Debug-Info
                    found++;
                    if (found <= 10) { // Maximal 10 Elemente anzeigen
                        log("Datum gefunden in: " + el.textContent.trim());
                        addDebugMessage(el.tagName + ": " + el.textContent.trim().substring(0, 50));
                    }
                }
            }
        }
        
        // 2. Versuch: Speziell auf Tickettabelle abzielen
        var ticketLinks = document.querySelectorAll('table.list a, table#tickets a, td.subject a');
        log("Prüfe " + ticketLinks.length + " Ticket-Links");
        
        var tableFound = 0;
        
        for (var j = 0; j < ticketLinks.length; j++) {
            var link = ticketLinks[j];
            
            if (dateRegex.test(link.textContent)) {
                // Link markieren
                link.classList.add('datum-highlight');
                link.style.setProperty('color', '{$color}', 'important');
                link.style.setProperty('font-weight', 'bold', 'important');
                
                tableFound++;
                log("Datum in Ticket-Link gefunden: " + link.textContent.trim());
            }
        }
        
        // Statistik melden
        var total = found + tableFound;
        log("Insgesamt " + total + " Elemente mit Datumsangaben hervorgehoben");
        addDebugMessage("Hervorgehoben: " + total + " Elemente");
        
        return total;
    }
    
    // Hauptcode
    function init() {
        // Debug-Panel anzeigen
        var debugPanel = showDebugInfo();
        
        // Tickets hervorheben
        setTimeout(function() {
            var count = highlightDatesInTickets();
            
            // Ergebnis anzeigen
            if (count === 0) {
                addDebugMessage("PROBLEM: Keine Elemente gefunden!");
                
                // HTML-Struktur analysieren und ausgeben
                var tables = document.querySelectorAll('table');
                addDebugMessage("Tabellen gefunden: " + tables.length);
                
                for (var i = 0; i < Math.min(tables.length, 3); i++) {
                    var table = tables[i];
                    addDebugMessage("Tabelle #" + i + ": " + 
                                  (table.id ? "ID="+table.id : "") + 
                                  (table.className ? " Class="+table.className : ""));
                }
            }
        }, 500);
        
        // Alle 5 Sekunden erneut prüfen für dynamische Inhalte
        setInterval(highlightDatesInTickets, 5000);
    }
    
    // Warten, bis die Seite geladen ist
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
EOD;
    }
    
    // JavaScript-Code als Methode, damit er an verschiedenen Stellen verwendet werden kann
    private function getJavaScript() {
        // JavaScript-Code (integrierte Version)
        return '<script type="text/javascript">' . $this->getPlainJavaScript() . '</script>';
    }

    // enable() wird aufgerufen, wenn das Plugin aktiviert wird
    function enable() {
        $this->debug_log('DatumHervorhebung Plugin wurde aktiviert');
        
        // Bei der Aktivierung die JavaScript-Datei erstellen oder aktualisieren
        $jsFile = dirname(dirname(dirname(__DIR__))) . '/js/datumhervorhebung.js';
        file_put_contents($jsFile, $this->getPlainJavaScript());
        $this->debug_log("JavaScript-Datei bei Aktivierung erstellt/aktualisiert: $jsFile");
        
        return parent::enable();
    }
    
    // disable() wird aufgerufen, wenn das Plugin deaktiviert wird
    function disable() {
        $this->debug_log('DatumHervorhebung Plugin wird deaktiviert');
        
        // Header-Datei wiederherstellen
        $headerFile = INCLUDE_DIR . 'staff/header.inc.php';
        $backupFile = $headerFile . '.bak';
        if (file_exists($backupFile)) {
            // Backup wiederherstellen
            copy($backupFile, $headerFile);
            $this->debug_log("Header-Datei aus Backup wiederhergestellt");
            
            // Backup löschen
            unlink($backupFile);
        }
        
        return parent::disable();
    }
}
?> 