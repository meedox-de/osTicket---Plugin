<?php

require_once INCLUDE_DIR . 'class.plugin.php';
require_once INCLUDE_DIR . 'class.forms.php';

/**
 * Konfigurationsklasse für das DatumBetreffHervorhebung Plugin
 */
class DatumBetreffHervorhebungConfig extends PluginConfig {
    /**
     * Konfigurationsoptionen für das Plugin
     */
    function getOptions() {
        return array(
            'aktiviert' => new BooleanField(array(
                'label' => 'Plugin aktiviert',
                'default' => true,
                'configuration' => array(
                    'desc' => 'Aktiviert oder deaktiviert das Plugin'
                )
            )),
        );
    }

    /**
     * Prüfung vor dem Speichern der Konfiguration
     */
    function pre_save(&$config, &$errors) {
        return true;
    }
} 