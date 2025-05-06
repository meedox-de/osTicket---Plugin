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
                                
                                found++;
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
                    return found + tableFound;
                }
                
                // Main code
                function init() {
                    // Highlight tickets
                    setTimeout(function() {
                        var count = highlightDatesInTickets();
                        
                        // Show the result
                        if (count === 0) 
                        {
                            // Analyze the HTML structure and output it
                            var tables = document.querySelectorAll('table');
                            
                            for (var i = 0; i < Math.min(tables.length, 3); i++) {
                                var table = tables[i];
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