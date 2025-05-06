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
    function init() :void
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
                function highlightDatesInTickets() 
                {
                    // Check if we are on a ticket list page (either tickets.php or index.php)
                    var isTicketsPath = window.location.pathname.includes('/scp/tickets.php');
                    var isIndexPath = window.location.pathname.includes('/scp/index.php');
                    
                    // Also check if there's a ticket list table in the DOM, regardless of path
                    var hasTicketTable = (document.querySelector('table.list') !== null || document.getElementById('tickets') !== null);
                    
                    // Only proceed if we're on a relevant page
                    if (!(isTicketsPath || isIndexPath || hasTicketTable)) {
                        return 0;
                    }
                    
                    // Extended regex for different date formats
                    // - DD.MM.YYYY or DD.MM.YY (German format)
                    // - DD/MM/YYYY or DD/MM/YY (UK format)
                    // - MM/DD/YYYY or MM/DD/YY (US format)
                    // - YYYY-MM-DD (ISO format)
                    var dateRegex = /(\d{1,2}[.]\d{1,2}[.]\d{2,4}|\d{1,2}\/\d{1,2}\/\d{2,4}|\d{4}-\d{1,2}-\d{1,2})/g;
                    
                    // Add CSS rule - using a stronger selector and !important
                    var style = document.createElement('style');
                    style.textContent = 'a.date-highlight, a.date-highlight:link, a.date-highlight:visited, a.date-highlight:hover { color: {$color} !important; font-weight: bold !important; }';
                    document.head.appendChild(style);
                    
                    // APPROACH 1: Try to find ALL ticket subject links in any way possible
                    var allLinks = document.getElementsByTagName('a');
                    
                    var found = 0;
                    
                    // Loop through all links on the page
                    for (var i = 0; i < allLinks.length; i++) {
                        var link = allLinks[i];
                        
                        // Skip invisible elements
                        if (!link.offsetParent) continue;
                        
                        // Skip if no content
                        if (!link.textContent || link.textContent.trim() === '') continue;
                        
                        // Get the text
                        var text = link.textContent.trim();
                        
                        // Check if it looks like a ticket link 
                        // - Either by having a '/scp/tickets.php?id=' in the href
                        // - Or by being inside a table with class 'list'
                        var isTicketLink = (link.href && link.href.indexOf('/scp/tickets.php?id=') !== -1);
                        var isInTicketTable = false;
                        
                        // Check if parent elements include a table
                        var parent = link.parentNode;
                        while (parent && !isInTicketTable) {
                            if (parent.tagName === 'TABLE' && parent.classList && parent.classList.contains('list')) {
                                isInTicketTable = true;
                            }
                            parent = parent.parentNode;
                        }
                        
                        // If this link is not related to tickets, skip it
                        if (!isTicketLink && !isInTicketTable) continue;
                        
                        // Now check for dates in the content
                        var match = text.match(dateRegex);
                        if (match) {
                            
                            // Apply highlighting styles
                            link.classList.add('date-highlight');
                            link.style.color = '{$color}';
                            link.style.fontWeight = 'bold';
                            
                            // To ensure it works, also create an inline span for the date
                            if (text.indexOf(match[0]) > -1) {
                                // Only do this transformation if we can safely find the date in the text
                                var beforeDate = text.substring(0, text.indexOf(match[0]));
                                var afterDate = text.substring(text.indexOf(match[0]) + match[0].length);
                                
                                // Replace link content with HTML that highlights just the date portion
                                link.innerHTML = beforeDate + 
                                                '<span style="color:{$color}; font-weight:bold;">' + 
                                                match[0] + 
                                                '</span>' + 
                                                afterDate;
                            }
                            
                            found++;
                        }
                    }
                    
                    return found;
                }
                
                // Main code
                function init() {
                    // Highlight tickets on initial page load
                    setTimeout(function() {
                        var count = highlightDatesInTickets();
                        
                        // Log information (only for debugging)
                        var isTicketPage = window.location.pathname.includes('/scp/tickets.php') || 
                                          window.location.pathname.includes('/scp/index.php') ||
                                          document.querySelector('table.list') !== null;
                                          
                        if (count === 0 && isTicketPage) {}
                    }, 500);
                    
                    // Set up a MutationObserver to watch for DOM changes (replacing deprecated DOMNodeInserted)
                    var ticketListObserver = new MutationObserver(function(mutations) {
                        // Check if any mutations are relevant to ticket list
                        var shouldRehighlight = false;
                        
                        mutations.forEach(function(mutation) {
                            // Check if nodes were added
                            if (mutation.addedNodes && mutation.addedNodes.length) {
                                for (var i = 0; i < mutation.addedNodes.length; i++) {
                                    var node = mutation.addedNodes[i];
                                    
                                    // Check if added node is relevant to the ticket list
                                    if (node.nodeType === 1 && ( // Element node
                                        node.tagName === 'TR' || 
                                        node.tagName === 'TABLE' || 
                                        (node.classList && node.classList.contains('list')) ||
                                        node.querySelector('a[href*="/scp/tickets.php?id="]') // Contains ticket links
                                    )) {
                                        shouldRehighlight = true;
                                        break;
                                    }
                                }
                            }
                        });
                        
                        if (shouldRehighlight) {
                            highlightDatesInTickets();
                        }
                    });
                    
                    // Start observing the document with the configured parameters
                    ticketListObserver.observe(document.body, {
                        childList: true, // Watch for changes in direct children
                        subtree: true    // Watch the entire subtree
                    });
                    
                    // Also do regular checks for any other dynamic content
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