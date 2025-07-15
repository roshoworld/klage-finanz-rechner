=== Court Automation Hub ===
Contributors: klageclick
Tags: legal, automation, gdpr, spam, court, csv, import, financial
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.2.1
License: GPLv2 or later

Klage.Click Court Automation Platform für deutsche Gerichte mit 57-Felder Master Data Integration.

== Description ==

Das Court Automation Hub Plugin ermöglicht die automatisierte Verarbeitung von DSGVO-Spam-Verstößen und anderen rechtlichen Ansprüchen über deutsche Amtsgerichte.

**Hauptfunktionen v1.2.1:**
* 57-Felder Master Data Structure - Vollständige Datenverwaltung
* Dual Template System - Forderungen.com (17 Felder) + Comprehensive (57 Felder)
* Automatische DSGVO-Schadensberechnung (€548.11 Standard)
* Bulk CSV Import/Export mit intelligenter Feldzuordnung
* Vollständige Fall-Bearbeitung mit Audit-Trail
* Deutsche Gerichtssystem-Integration (EGVP/XJustiz ready)

**Für wen ist dieses Plugin?**
* Rechtsanwälte und Kanzleien
* Inkassobüros und Forderungsmanagement
* Rechtliche Dienstleister
* DSGVO-Compliance-Experten
* Unternehmen mit Massenforderungen

**Technische Highlights:**
* 14 Datenbank-Tabellen für vollständige Datenverwaltung
* Forderungen.com CSV-Kompatibilität
* Automatische Felderweiterung (17 → 57 Felder)
* WordPress Admin Integration
* Umfassende Sicherheit und Validierung

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

= 1.0.6 =
* FIXED: Case editing page error (undefined method)
* Added: Working case edit page with case information display
* Enhanced: Better navigation between case list → details → edit
* Added: Case data preview in edit mode
* Status: Edit functionality placeholder (full editing in v1.0.7)

= 1.0.5 =
* Added: Complete case details view
* Added: Case information display (status, priority, dates)
* Added: Email evidence display with full content
* Added: Financial breakdown visualization
* Added: Working "Ansehen" buttons in case list
* Added: Navigation between case list and details
* Enhanced: Case management workflow foundation
* Ready: For case editing and status management (next version)

= 1.0.4 =
* MAJOR FIX: Added direct SQL table creation (bypasses dbDelta issues)
* Added: Detailed table status diagnostics in settings
* Enhanced: Better error reporting and debugging
* Fixed: Tables actually get created now (not just success message)
* Added: Real-time table status display
* Button: "🔧 Alle Tabellen erstellen (Direkt-SQL)"

= 1.0.3 =
* CRITICAL FIX: Removed extra closing brace causing PHP syntax error
* Fixed: Plugin activation error in class-database.php line 146
* Status: Plugin now activates successfully
* Ready: For database table creation and case processing

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