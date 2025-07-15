=== Court Automation Hub ===
Contributors: klageclick
Tags: legal, automation, gdpr, spam, court, csv, import, financial
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.2.4
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

= 1.2.4 =
* HOTFIX: Fixed case creation to support both manual and email-based case creation
* Added: Support for email-based case creation from spam emails
* Enhanced: Automatic debtor extraction from email sender information
* Fixed: Validation now adapts to different case creation types
* Enhanced: Better debug information showing form type detection
* Added: Email details automatically added to case notes for email-based cases
* Fixed: Cases can now be created from email evidence without manual debtor entry
* Status: Complete case creation workflow for both manual and email-based cases

= 1.2.3 =
* HOTFIX: Added missing handle_bulk_actions() method for case list operations
* HOTFIX: Enhanced case creation validation with detailed error messages
* Added: Bulk delete functionality with audit trail
* Added: Bulk status change functionality
* Added: Bulk priority change functionality
* Enhanced: Better validation error reporting with debug information
* Fixed: Case list deletion buttons now work properly
* Fixed: Case creation validation shows specific field errors
* Status: Complete case management workflow fully functional

= 1.2.2 =
* HOTFIX: Added missing create_new_case() method for case creation
* HOTFIX: Added missing update_case() method for case updates
* Fixed: Case creation form now works properly
* Fixed: Error "Call to undefined method CAH_Admin_Dashboard::create_new_case()"
* Enhanced: Complete case creation workflow with debtor and financial records
* Enhanced: Automatic redirect to case view after successful creation
* Status: All case management operations now functional

= 1.2.1 =
* FIXED: Missing case editing methods (render_edit_case_form, render_view_case, handle_delete_case)
* Added: Complete case editing interface with all 57 fields
* Added: Professional case viewing with detailed information display
* Added: Secure case deletion with audit trail
* Added: Automatic financial calculations in editing
* Enhanced: Full case management workflow (create → view → edit → delete)
* Fixed: Error "Call to undefined method CAH_Admin_Dashboard::render_edit_case_form()"
* Status: Production ready with complete case management

= 1.2.0 =
* MAJOR: 57-Field Master Data Structure implementation
* Added: Dual Template System (Forderungen.com 17 fields + Comprehensive 57 fields)
* Added: Automatic field extension (17→57 fields with intelligent defaults)
* Enhanced: Complete database schema with 14 tables
* Added: Comprehensive debtor management with legal information
* Added: Extended financial tracking with multiple cost categories
* Added: Document management, communications, deadlines, case history
* Added: Enhanced CSV import/export with field mapping
* Enhanced: Professional project documentation and testing
* Status: Enterprise-level master data integration complete

= 1.1.6 =
* Enhanced: CSV import with exact Forderungen.com field mapping
* Added: Template download system
* Fixed: Headers already sent error during CSV downloads
* Enhanced: Import processing with error handling
* Added: Bulk case creation capabilities

= 1.1.3 =
* Added: Complete case management functionality
* Added: Advanced case listing with filtering
* Added: Status workflow implementation
* Added: Financial calculator system
* Added: Audit trail logging

= 1.0.6 =
* FIXED: Case editing page error (undefined method)
* Added: Working case edit page with case information display
* Enhanced: Better navigation between case list → details → edit
* Added: Case data preview in edit mode
* Status: Edit functionality placeholder (full editing in v1.0.7)

= 1.0.0 =
* Erste Veröffentlichung
* DSGVO-Spam-Modul
* Admin-Dashboard
* N8N-Integration
* REST-API
* Audit-Logging