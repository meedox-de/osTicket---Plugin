# Datum Betreff Hervorhebung Plugin für osTicket 1.18

Dieses Plugin für osTicket 1.18 färbt den Betreff in der Agenten-Ticketübersicht rot, wenn ein Datum im Betreff enthalten ist.

## Funktionen

- Erkennt automatisch Datumsformate im Betreff (DD.MM.YYYY, YYYY-MM-DD, DD/MM/YYYY, etc.)
- Hebt Tickets mit Datum im Betreff durch rote Schriftfarbe hervor
- Macht solche Tickets für Agenten sofort erkennbar

## Installation

1. Laden Sie die Plugin-Dateien herunter
2. Entpacken Sie die Dateien in das Verzeichnis `/include/plugins/datum_betreff_hervorhebung/` Ihrer osTicket-Installation
3. Melden Sie sich im Admin-Panel an
4. Gehen Sie zu "Verwalten" > "Plugins"
5. Klicken Sie auf "Neues Plugin hinzufügen"
6. Wählen Sie "Datum Betreff Hervorhebung" aus der Liste aus
7. Klicken Sie auf "Installieren"
8. Aktivieren Sie das Plugin

## Unterstützte Datumsformate

Das Plugin erkennt folgende Datumsformate:
- DD.MM.YYYY (z.B. 31.12.2023)
- DD-MM-YYYY (z.B. 31-12-2023)
- DD/MM/YYYY (z.B. 31/12/2023)
- YYYY-MM-DD (z.B. 2023-12-31)
- YYYY/MM/DD (z.B. 2023/12/31)
- Und Variationen mit ein- oder zweistelligen Tag/Monat und zwei- oder vierstelligen Jahreszahlen

## Anforderungen

- osTicket 1.18
- PHP 8.0 oder höher
- jQuery (wird standardmäßig mit osTicket mitgeliefert)

## Lizenz

Dieses Plugin ist unter der MIT-Lizenz veröffentlicht. 