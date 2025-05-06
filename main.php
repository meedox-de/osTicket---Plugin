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
            (function() 
            {
                // Prevent the script from being executed multiple times
                if (window.dateHighlightLoaded) return;
                window.dateHighlightLoaded = true;
                           
                // Function to highlight dates
                function highlightDatesInTickets() {
                    
                    // Check if we are on the ticket list page
                    if (!window.location.pathname.includes('/scp/tickets.php')) {
                        return 0;
                    }
                    
                    
                    // Extended regex for different date formats
                    // - DD.MM.YYYY or DD.MM.YY (German format)
                    // - DD/MM/YYYY or DD/MM/YY (UK format)
                    // - MM/DD/YYYY or MM/DD/YY (US format)
                    // - YYYY-MM-DD (ISO format)
                    var dateRegex = /(\d{1,2}[.]\d{1,2}[.]\d{2,4}|\d{1,2}\/\d{1,2}\/\d{2,4}|\d{4}-\d{1,2}-\d{1,2})/g;
                    
                    // Add CSS rule
                    var style = document.createElement('style');
                    style.textContent = '.date-highlight { color: {$color} !important; font-weight: bold !important; }';
                    document.head.appendChild(style);
                    
                    // More comprehensive search in the ticket table
                    // 1. Subject lines in the ticket table
                    // 2. All links within the ticket table
                    var subjectCells = document.querySelectorAll('table.list td.subject a, #tickets td.subject a, a[href*="/scp/tickets.php?id="]');
                    
                    var found = 0;
                    
                    for (var i = 0; i < subjectCells.length; i++) {
                        var el = subjectCells[i];
                        
                        if (el.textContent && 
                            el.textContent.trim().length > 0 && 
                            el.offsetParent !== null) {  // Only visible elements
                            
                            var text = el.textContent.trim();s
                            
                            // Search for date formats in the text
                            var match = text.match(dateRegex);
                            if (match) {
                                // Mark the element
                                el.classList.add('date-highlight');
                                el.style.setProperty('color', '{$color}', 'important');
                                el.style.setProperty('font-weight', 'bold', 'important');
                                
                                found++;
                            }
                        }
                    }
                    
                    return found;
                }
                
                // Main code
                function init() {
                    // Highlight tickets
                    setTimeout(function() {
                        var count = highlightDatesInTickets();
                        
                        // Log information (only for debugging)
                        if (count === 0 && window.location.pathname.includes('/scp/tickets.php')) {
                            console.log("Highlighter-Plugin: No date values found in subject lines");
                        }
                    }, 500);
                    
                    // Regular check for dynamically loaded content
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