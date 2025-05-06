<?php
require_once(INCLUDE_DIR.'class.plugin.php');

class HighlighterConfig extends PluginConfig {
    
    // Configuration options for the plugin
    function getOptions() {
        return array(
            'enabled' => new BooleanField(array(
                'label' => 'Plugin aktivieren',
                'default' => true,
                'configuration' => array(
                    'desc' => 'Aktiviert oder deaktiviert dieses Plugin')
            )),
            'highlight_color' => new ChoiceField(array(
                'label' => 'Hervorhebungsfarbe',
                'choices' => array(
                    'red' => 'Rot',
                    'blue' => 'Blau',
                    'green' => 'Grün',
                    'orange' => 'Orange'
                ),
                'default' => 'red',
                'configuration' => array(
                    'desc' => 'Die Farbe, mit der Datumsangaben hervorgehoben werden')
            )),
        );
    }
}
?> 