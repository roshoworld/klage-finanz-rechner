=== Court Automation Hub ===
Contributors: klageclick
Tags: legal, automation, gdpr, spam, court
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later

Klage.Click Court Automation Platform für deutsche Gerichte mit KI-gestützter Verarbeitung.

== Description ==

Das Court Automation Hub Plugin ermöglicht die automatisierte Verarbeitung von DSGVO-Spam-Verstößen und anderen rechtlichen Ansprüchen über deutsche Amtsgerichte.

**Hauptfunktionen:**
* DSGVO-Spam-Fallverwaltung
* Automatische Schadensberechnung
* N8N-Integration für KI-Verarbeitung
* Vollständige Audit-Protokollierung
* Deutsche Gerichtssystem-Integration

**Für wen ist dieses Plugin?**
* Rechtsanwälte
* Inkassobüros
* Rechtliche Dienstleister
* DSGVO-Compliance-Experten

== Installation ==

1. Plugin-ZIP-Datei hochladen über WordPress Admin → Plugins → Neues Plugin hinzufügen
2. Plugin aktivieren
3. Zu "Klage.Click Hub" im Admin-Menü navigieren
4. N8N-API-Einstellungen konfigurieren
5. Ersten Fall erstellen

== Frequently Asked Questions ==

= Welche Daten werden gespeichert? =
Das Plugin speichert Fall-, Schuldner-, E-Mail-Evidenz- und Finanzdaten gemäß DSGVO-Bestimmungen.

= Ist N8N erforderlich? =
Ja, für die vollständige Automatisierung wird eine N8N-Instanz benötigt.

= Welche Gerichte werden unterstützt? =
Alle deutschen Amtsgerichte mit EGVP-Unterstützung.

== Changelog ==

= 1.0.2 =
* URGENT FIX: Added manual database table creation button in settings
* Fixed: Database table creation during plugin updates
* Added: "🔧 Alle Tabellen erstellen" button in Einstellungen
* Fixed: klage_cases table creation issue
* Enhanced: Better error handling for database operations
* Ready: For immediate case processing after table fix

= 1.0.1 =
* Fixed: Database table creation (klage_cases table was missing)
* Fixed: Case creation form now works properly
* Added: Complete case entry workflow with €548.11 calculations
* Added: All 6 required database tables
* Added: Working "Neuen Fall hinzufügen" functionality
* Fixed: Plugin activation and database setup

= 1.0.0 =
* Erste Veröffentlichung
* DSGVO-Spam-Modul
* Admin-Dashboard
* N8N-Integration
* REST-API
* Audit-Logging