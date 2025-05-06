<?php
require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

class DatumHervorhebungPlugin extends Plugin {
    var $config_class = 'DatumHervorhebungConfig';

    /**
     * Function to log messages in the debug.log file
     *
     * @param string $message
     * @return void
     */
    private function debug_log(string $message) {
        $logfile = __DIR__ . '/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * osTicket function
     *
     * @return void
     */
    function init() 
    {
        parent::init();
        
        // output the javascript code directly in the html head
        echo '<script type="text/javascript">'. $this->getPlainJavaScript() .'</script>';
    }
    
    
    // JavaScript-Code ohne PHP-Tags als separate Methode
    private function getPlainJavaScript() {
        $config = $this->getConfig();
        $color = $config->get('highlight_color') ?? 'red';
        
        // JavaScript-Code ohne PHP-Tags
        return <<<EOD
            // Datum Hervorhebung Plugin
            (function() {
                // Verhindern, dass das Script mehrfach ausgeführt wird
                if (window.datumHighlightLoaded) return;
                window.datumHighlightLoaded = true;
                              
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
                    
                    // Datumsregex für verschiedene Formate (DD.MM.YYYY, DD/MM/YYYY, etc.)
                    var dateRegex = /\b\d{1,2}[./-]\d{1,2}[./-]\d{2,4}\b/;
                    
                    // CSS-Regel hinzufügen
                    var style = document.createElement('style');
                    style.textContent = '.datum-highlight { color: {$color} !important; font-weight: bold !important; }';
                    document.head.appendChild(style);
                    
                    // Verschiedene Ansätze für die Ticketliste versuchen
                    
                    // 1. Direkte Suche nach Elementen mit Datumsangaben
                    var allElements = document.querySelectorAll('a, td, span, div');
                    
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
                                    addDebugMessage(el.tagName + ": " + el.textContent.trim().substring(0, 50));
                                }
                            }
                        }
                    }
                    
                    // 2. Versuch: Speziell auf Tickettabelle abzielen
                    var ticketLinks = document.querySelectorAll('table.list a, table#tickets a, td.subject a');
                    
                    var tableFound = 0;
                    
                    for (var j = 0; j < ticketLinks.length; j++) {
                        var link = ticketLinks[j];
                        
                        if (dateRegex.test(link.textContent)) {
                            // Link markieren
                            link.classList.add('datum-highlight');
                            link.style.setProperty('color', '{$color}', 'important');
                            link.style.setProperty('font-weight', 'bold', 'important');
                            
                            tableFound++;
                        }
                    }
                    
                    // Statistik melden
                    var total = found + tableFound;
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
}
?> 