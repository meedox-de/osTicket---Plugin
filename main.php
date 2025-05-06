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
(function() {
    if (window.dateHighlightLoaded) return;
    window.dateHighlightLoaded = true;

    const HIGHLIGHT_COLOR = '{$color}';
    const DATE_REGEX = /(\d{1,2}[.\/]\d{1,2}[.\/]\d{2,4}|\d{4}-\d{1,2}-\d{1,2})/g;

    const style = document.createElement('style');
    style.textContent = '.date-highlight { color: ' + HIGHLIGHT_COLOR + ' !important; font-weight: bold !important; }' +
                        '.date-highlight span { color: inherit; font-weight: inherit; }';
    document.head.appendChild(style);

    const isTicketPage = () => {
        const path = window.location.pathname;
        return path.endsWith('/scp/tickets.php')
            || path.endsWith('/scp/index.php')
            || !!document.querySelector('table.list, #tickets');
    };

    const highlightDatesInTickets = () => {
        if (!isTicketPage()) return;

        const links = Array.from(document.querySelectorAll('a[href*="/scp/tickets.php?id="]'));
        let count = 0;

        links.forEach(link => {
            if (!link.offsetParent) return;
            const text = link.textContent.trim();
            const match = DATE_REGEX.exec(text);
            DATE_REGEX.lastIndex = 0;

            if (match) {
                link.classList.add('date-highlight');
                const [full, datePart] = match;
                link.innerHTML = text.replace(
                    datePart,
                    '<span>' + datePart + '</span>'
                );
                count++;
            }
        });

        return count;
    };

    const init = () => {
        highlightDatesInTickets();

        const observer = new MutationObserver(mutations => {
            if (mutations.some(m =>
                Array.from(m.addedNodes).some(node =>
                    node.nodeType === 1 &&
                    (node.matches && node.matches('tr, table.list, a[href*="/scp/tickets.php?id="]'))
                )
            )) {
                highlightDatesInTickets();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
EOD;

    }
}
?> 