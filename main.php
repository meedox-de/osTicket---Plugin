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
        var_dump("test");
        // Elternmethode aufrufen
        parent::init();

        // Nur für Fehlersuche - kann auskommentiert werden in der Produktion
        $this->debug_log('DatumHervorhebung Plugin init() wurde aufgerufen');

        // Einfügen des JavaScript-Codes in die Admin-/Agenten-Seitenansicht
        Signal::connect('scp.footer', function() {
            $this->debug_log('Signal scp.footer wurde ausgelöst');
            
            // Konfiguration abrufen
            $config = $this->getConfig();
            $color = $config->get('highlight_color') ?: 'red';
            
            // Script-Tag öffnen
            ?>
            <script type="text/javascript">
            (function() {
                // Debug-Helper - zeigt ein kleines Element, um zu bestätigen, dass das Plugin geladen wurde
                var showDebug = true;
                if (showDebug) {
                    var debug = document.createElement('div');
                    debug.style.position = 'fixed';
                    debug.style.bottom = '10px';
                    debug.style.right = '10px';
                    debug.style.padding = '5px';
                    debug.style.background = 'yellow';
                    debug.style.border = '1px solid black';
                    debug.style.zIndex = '9999';
                    debug.innerHTML = 'Datum Hervorhebung aktiv';
                    document.body.appendChild(debug);

                    // Nach 5 Sekunden wieder ausblenden
                    setTimeout(function() {
                        debug.style.display = 'none';
                    }, 5000);
                }

                // Funktion zum Highlighten von Texten mit Datumsangaben
                function highlightDateElements() {
                    console.log("Datum Hervorhebung Plugin aktiv");
                    
                    // Regulärer Ausdruck für Datumsformate (DD.MM.YYYY, DD/MM/YYYY, etc.)
                    var dateRegex = /\b\d{1,2}[./-]\d{1,2}[./-]\d{2,4}\b/;
                    
                    // Selektoren für Tickettitel im Agenten-Interface
                    // Wir versuchen mehrere mögliche Selektoren um verschiedene Ansichten abzudecken
                    var selectors = [
                        // Ticket-Liste
                        'td.subject a',      // In der Ticketliste (Hauptselektor)
                        'table#tickets a',   // Allgemeiner Selektor für Tickets-Tabelle
                        'a.preview',         // Ticket-Vorschaulinks
                        'a.truncate',        // Abgeschnittene Texte
                        
                        // Detailansicht
                        'h2.subject',        // Betreff in der Ticket-Detailansicht
                        '.ticket-header h2'  // Alternativer Betreff in der Ticket-Detailansicht
                    ];
                    
                    // Zähler für gefundene Elemente
                    var totalFound = 0;
                    var totalHighlighted = 0;
                    
                    // Jeden Selektor durchlaufen
                    selectors.forEach(function(selector) {
                        var elements = document.querySelectorAll(selector);
                        
                        if (elements.length > 0) {
                            console.log("Gefunden: " + elements.length + " Elemente mit '" + selector + "'");
                            totalFound += elements.length;
                            
                            // Alle gefundenen Elemente durchlaufen
                            for (var i = 0; i < elements.length; i++) {
                                var element = elements[i];
                                
                                // Wenn der Text ein Datum enthält
                                if (dateRegex.test(element.textContent)) {
                                    element.style.color = '<?php echo $color; ?>';
                                    element.style.fontWeight = 'bold';
                                    totalHighlighted++;
                                    console.log("Datum gefunden und markiert: " + element.textContent);
                                }
                            }
                        }
                    });
                    
                    // Debug-Info ausgeben
                    if (showDebug) {
                        console.log("Insgesamt geprüft: " + totalFound + " Elemente");
                        console.log("Hervorgehoben: " + totalHighlighted + " Elemente");
                        
                        if (totalFound === 0) {
                            // Falls wir keine bekannten Elemente gefunden haben, die HTML-Struktur ausgeben
                            console.log("Keine bekannten Elemente gefunden. HTML-Struktur für Debug-Zwecke:");
                            var ticketTables = document.querySelectorAll('table');
                            ticketTables.forEach(function(table, index) {
                                console.log("Tabelle #" + index + " ID: " + table.id + ", Klasse: " + table.className);
                            });
                        }
                    }
                }
                
                // Hauptcode ausführen - mit Verzögerung, um sicherzustellen, dass die Seite geladen ist
                setTimeout(highlightDateElements, 500);
                
                // Für dynamisch geladene Inhalte: Funktion regelmäßig ausführen
                setInterval(highlightDateElements, 3000);
            })();
            </script>
            <?php
        });
    }

    // enable() wird aufgerufen, wenn das Plugin aktiviert wird
    function enable() {
        $this->debug_log('DatumHervorhebung Plugin wurde aktiviert');
        return parent::enable();
    }
}
?> 