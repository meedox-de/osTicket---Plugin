<?php

require_once(INCLUDE_DIR.'class.plugin.php');
require_once('config.php');

/**
 * Highlighter Plugin
 */
class HighlighterPlugin extends Plugin {
    var $config_class = 'HighlighterConfig';

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
    
    
    /**
     * Get the plain javascript code
     *
     * @return string
     */
    private function getPlainJavaScript():string
    {
        $config = $this->getConfig();
        $color = $config->get('highlight_color') ?? 'red';
        
        return <<<EOD
            (function() {
                // Prevent the script from being executed multiple times
                if (window.dateHighlightLoaded) return;
                window.dateHighlightLoaded = true;
                              
                // Show the debug element
                function showDebugInfo() {
                    var debugDiv = document.createElement('div');
                    debugDiv.id = 'date-highlight-panel';
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
                    debugDiv.innerHTML = '<strong>Datum Highlight aktiv</strong><div id="date-highlight-log"></div>';
                    document.body.appendChild(debugDiv);
                    
                    // Close button
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
                
                // Helper function to add messages to the debug panel
                function addDebugMessage(message) {
                    var logDiv = document.getElementById('date-highlight-log');
                    if (!logDiv) return;
                    
                    var entry = document.createElement('div');
                    entry.textContent = message;
                    entry.style.borderBottom = '1px dotted #ccc';
                    entry.style.paddingBottom = '2px';
                    entry.style.marginBottom = '2px';
                    logDiv.appendChild(entry);
                }
                
                // Function to highlight dates
                function highlightDatesInTickets() {
                    
                    // Date regex for different formats (DD.MM.YYYY, DD/MM/YYYY, etc.)
                    var dateRegex = /\b\d{1,2}[./-]\d{1,2}[./-]\d{2,4}\b/;
                    
                    // Add CSS rule
                    var style = document.createElement('style');
                    style.textContent = '.date-highlight { color: {$color} !important; font-weight: bold !important; }';
                    document.head.appendChild(style);
                    
                    // Try different approaches for the ticket list
                    
                    // 1. Direct search for elements with dates
                    var allElements = document.querySelectorAll('a, td, span, div');
                    
                    var found = 0;
                    
                    for (var i = 0; i < allElements.length; i++) {
                        var el = allElements[i];
                        
                        // Check visible elements with text
                        if (el.textContent && 
                            el.textContent.trim().length > 0 && 
                            el.offsetParent !== null) {  // Only visible elements
                            
                            if (dateRegex.test(el.textContent)) {
                                // Mark the element
                                el.classList.add('date-highlight');
                                el.style.setProperty('color', '{$color}', 'important');
                                el.style.setProperty('font-weight', 'bold', 'important');
                                
                                // Debug info
                                found++;
                                if (found <= 10) { // Maximal 10 elements
                                    addDebugMessage(el.tagName + ": " + el.textContent.trim().substring(0, 50));
                                }
                            }
                        }
                    }
                    
                    // 2. Try: Focus on the ticket table
                    var ticketLinks = document.querySelectorAll('table.list a, table#tickets a, td.subject a');
                    
                    var tableFound = 0;
                    
                    for (var j = 0; j < ticketLinks.length; j++) {
                        var link = ticketLinks[j];
                        
                        if (dateRegex.test(link.textContent)) {
                            // Mark the link
                            link.classList.add('date-highlight');
                            link.style.setProperty('color', '{$color}', 'important');
                            link.style.setProperty('font-weight', 'bold', 'important');
                            
                            tableFound++;
                        }
                    }
                    
                    // Report statistics
                    var total = found + tableFound;
                    addDebugMessage("Highlighted: " + total + " elements");
                    
                    return total;
                }
                
                // Main code
                function init() {
                    // Show the debug panel
                    var debugPanel = showDebugInfo();
                    
                    // Highlight tickets
                    setTimeout(function() {
                        var count = highlightDatesInTickets();
                        
                        // Show the result
                        if (count === 0) {
                            addDebugMessage("PROBLEM: No elements found!");
                            
                            // Analyze the HTML structure and output it
                            var tables = document.querySelectorAll('table');
                            addDebugMessage("Tables found: " + tables.length);
                            
                            for (var i = 0; i < Math.min(tables.length, 3); i++) {
                                var table = tables[i];
                                addDebugMessage("Tabelle #" + i + ": " + 
                                            (table.id ? "ID="+table.id : "") + 
                                            (table.className ? " Class="+table.className : ""));
                            }
                        }
                    }, 500);
                    
                    // Check every 5 seconds for dynamic content
                    setInterval(highlightDatesInTickets, 5000);
                }
                
                // Wait until the page is loaded
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