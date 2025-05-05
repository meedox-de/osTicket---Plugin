<?php

require_once INCLUDE_DIR . 'class.plugin.php';
require_once INCLUDE_DIR . 'class.signal.php';
require_once INCLUDE_DIR . 'class.app.php';
require_once('config.php');

/**
 * Plugin zur Hervorhebung von Datum in Ticket-Betreffs
 */
class DatumBetreffHervorhebung extends Plugin {
    var $config_class = 'DatumBetreffHervorhebungConfig';

    /**
     * Initialisierung des Plugins
     */
    function bootstrap() {
        // Signal-Handler registrieren, um Ticket-Betreff zu modifizieren
        Signal::connect('backend.dispatch', array($this, 'injectStyles'));
    }

    /**
     * CSS und JavaScript für die Betreff-Hervorhebung einfügen
     */
    function injectStyles($dispatcher) {
        // CSS-Styles nur im Agenten-Panel hinzufügen
        if (strcasecmp($dispatcher->getController()->getName(), 'TicketsController') === 0) {
            $css = '
            <style type="text/css">
                .ticket-subject.has-date {
                    color: red !important;
                    font-weight: bold !important;
                }
            </style>';

            // JavaScript für die Erkennung von Daten im Betreff und Anwendung der CSS-Klasse
            $js = '
            <script type="text/javascript">
                $(document).ready(function() {
                    // Datum-Regex (erkennt gängige Datumsformate wie DD.MM.YYYY, YYYY-MM-DD, DD/MM/YYYY)
                    var dateRegex = /(\d{1,2}[.-/]\d{1,2}[.-/]\d{2,4})|(\d{4}[.-/]\d{1,2}[.-/]\d{1,2})/;
                    
                    // Alle Ticket-Betreffs überprüfen
                    $("a.ticket-subject").each(function() {
                        var subject = $(this).text();
                        if (dateRegex.test(subject)) {
                            $(this).addClass("has-date");
                        }
                    });
                });
            </script>';

            // Styles und JavaScript in die Seite einfügen
            echo $css;
            $dispatcher->appendToResponse($js, 'html');
        }
    }
} 