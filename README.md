# Datum Hervorhebung Plugin für osTicket

Dieses Plugin hebt Datumsangaben in Ticketbetreffen in der Agenten-Ansicht rot hervor.

## Funktionen

- Hebt Ticketbetreffzeilen, die ein Datum enthalten (z.B. 12.05.2025, 5/12/2023) in der gewählten Farbe hervor
- Funktioniert sowohl in der Ticketliste als auch in der Detailansicht
- Anpassbare Hervorhebungsfarbe (Rot, Blau, Grün, Orange)

## Installation

1. Laden Sie das Plugin-Verzeichnis in den `/include/plugins/` Ordner Ihrer osTicket-Installation hoch
2. Melden Sie sich im Admin-Panel an
3. Gehen Sie zu: Admin-Panel → Verwaltung → Plugins
4. Klicken Sie auf "Neues Plugin hinzufügen"
5. Wählen Sie "Dateisystem durchsuchen" und navigieren Sie zu `/include/plugins/plugin`
6. Installieren und aktivieren Sie das Plugin

## Konfiguration

Nach der Installation können Sie die Hervorhebungsfarbe im Plugin-Konfigurationsmenü anpassen:

1. Gehen Sie zu: Admin-Panel → Verwaltung → Plugins
2. Klicken Sie auf das Einstellungssymbol neben "Datum Hervorhebung"
3. Wählen Sie die gewünschte Farbe aus dem Dropdown-Menü
4. Speichern Sie die Einstellungen

## Hinweise zur Fehlersuche

Das Plugin erstellt Debugging-Informationen in der Datei `debug.log` im Plugin-Verzeichnis. 
Zusätzlich werden Informationen in der Browser-Konsole (F12) ausgegeben.

Ein gelbes Informationsfeld erscheint kurz nach dem Laden der Seite, wenn das Plugin aktiv ist.

## Anforderungen

- osTicket 1.10 oder höher
- Modernes Browser mit JavaScript-Unterstützung 