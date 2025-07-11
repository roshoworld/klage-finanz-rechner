<?php
/**
 * Legal Framework class
 * Handles legal basis and framework management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Legal_Framework {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Get legal basis for GDPR spam cases
     */
    public function get_gdpr_legal_basis($case_data) {
        $legal_basis = array();
        
        // Primary legal basis - GDPR Article 82
        $legal_basis['primary'] = array(
            'article' => 'Art. 82 DSGVO',
            'title' => 'Recht auf Schadenersatz',
            'text' => 'Jede Person, der wegen eines Verstoßes gegen diese Verordnung ein materieller oder immaterieller Schaden entstanden ist, hat Anspruch auf Schadenersatz gegen den Verantwortlichen oder gegen den Auftragsverarbeiter.',
            'relevance' => 'Grundlage für Schadenersatzanspruch bei DSGVO-Verstoß durch unerlaubte Werbe-E-Mail'
        );
        
        // Secondary legal basis - BGB
        $legal_basis['secondary'] = array(
            'article' => '§ 823 Abs. 1 BGB',
            'title' => 'Schadensersatzpflicht',
            'text' => 'Wer vorsätzlich oder fahrlässig das Leben, den Körper, die Gesundheit, die Freiheit, das Eigentum oder ein sonstiges Recht eines anderen widerrechtlich verletzt, ist dem anderen zum Ersatz des daraus entstehenden Schadens verpflichtet.',
            'relevance' => 'Verletzung des allgemeinen Persönlichkeitsrechts durch unerlaubte Werbe-E-Mail'
        );
        
        // Constitutional basis - Grundgesetz
        $legal_basis['constitutional'] = array(
            'article' => 'Art. 2 Abs. 1 und Art. 1 Abs. 1 GG',
            'title' => 'Allgemeines Persönlichkeitsrecht',
            'text' => 'Jeder hat das Recht auf die freie Entfaltung seiner Persönlichkeit, soweit er nicht die Rechte anderer verletzt...',
            'relevance' => 'Verfassungsrechtliche Grundlage des Persönlichkeitsrechts'
        );
        
        // UWG basis for commercial emails
        $legal_basis['uwg'] = array(
            'article' => '§ 7 UWG',
            'title' => 'Unzumutbare Belästigungen',
            'text' => 'Eine geschäftliche Handlung, durch die ein Marktteilnehmer in unzumutbarer Weise belästigt wird, ist unzulässig.',
            'relevance' => 'Verbot unzumutbarer Belästigung durch kommerzielle E-Mails'
        );
        
        return $legal_basis;
    }
    
    /**
     * Determine applicable legal framework based on case type
     */
    public function determine_legal_framework($case_data) {
        $framework = array();
        
        // Determine case type
        $case_type = $this->determine_case_type($case_data);
        
        switch ($case_type) {
            case 'gdpr_spam':
                $framework = $this->get_gdpr_spam_framework($case_data);
                break;
            case 'commercial_spam':
                $framework = $this->get_commercial_spam_framework($case_data);
                break;
            case 'harassment':
                $framework = $this->get_harassment_framework($case_data);
                break;
            default:
                $framework = $this->get_default_framework($case_data);
        }
        
        return $framework;
    }
    
    /**
     * Determine case type based on email content and context
     */
    private function determine_case_type($case_data) {
        // Check if it's commercial content
        if ($this->is_commercial_content($case_data)) {
            return 'commercial_spam';
        }
        
        // Check if it's harassment
        if ($this->is_harassment_content($case_data)) {
            return 'harassment';
        }
        
        // Default to GDPR spam
        return 'gdpr_spam';
    }
    
    /**
     * Check if email contains commercial content
     */
    private function is_commercial_content($case_data) {
        if (empty($case_data['emails_content'])) {
            return false;
        }
        
        $content = strtolower($case_data['emails_content']);
        
        $commercial_indicators = array(
            'kaufen', 'bestellen', 'angebot', 'preis', 'rabatt', 'verkauf',
            'shop', 'online', 'produkt', 'service', 'dienstleistung',
            'kostenlos', 'gratis', 'aktion', 'deal', 'promotion'
        );
        
        foreach ($commercial_indicators as $indicator) {
            if (strpos($content, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if email contains harassment content
     */
    private function is_harassment_content($case_data) {
        if (empty($case_data['emails_content'])) {
            return false;
        }
        
        $content = strtolower($case_data['emails_content']);
        
        $harassment_indicators = array(
            'bedrohen', 'beleidigen', 'beschimpfen', 'mobben',
            'stalken', 'verfolgen', 'terror', 'einschüchtern'
        );
        
        foreach ($harassment_indicators as $indicator) {
            if (strpos($content, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get GDPR spam legal framework
     */
    private function get_gdpr_spam_framework($case_data) {
        return array(
            'case_type' => 'gdpr_spam',
            'primary_law' => 'DSGVO',
            'legal_basis' => $this->get_gdpr_legal_basis($case_data),
            'jurisdiction' => $this->determine_jurisdiction($case_data),
            'damages' => $this->get_gdpr_damages_framework(),
            'procedure' => $this->get_civil_procedure_framework()
        );
    }
    
    /**
     * Get commercial spam legal framework
     */
    private function get_commercial_spam_framework($case_data) {
        return array(
            'case_type' => 'commercial_spam',
            'primary_law' => 'UWG',
            'legal_basis' => $this->get_uwg_legal_basis($case_data),
            'jurisdiction' => $this->determine_jurisdiction($case_data),
            'damages' => $this->get_commercial_damages_framework(),
            'procedure' => $this->get_civil_procedure_framework()
        );
    }
    
    /**
     * Get harassment legal framework
     */
    private function get_harassment_framework($case_data) {
        return array(
            'case_type' => 'harassment',
            'primary_law' => 'StGB',
            'legal_basis' => $this->get_criminal_legal_basis($case_data),
            'jurisdiction' => $this->determine_criminal_jurisdiction($case_data),
            'damages' => $this->get_harassment_damages_framework(),
            'procedure' => $this->get_criminal_procedure_framework()
        );
    }
    
    /**
     * Determine jurisdiction based on case data
     */
    private function determine_jurisdiction($case_data) {
        $jurisdiction = array();
        
        // For civil cases, jurisdiction usually follows defendant's residence
        if (!empty($case_data['debtor_postal_code'])) {
            $jurisdiction['court'] = $this->find_competent_court($case_data['debtor_postal_code']);
        }
        
        // Fallback to user's location
        if (empty($jurisdiction['court']) && !empty($case_data['user_postal_code'])) {
            $jurisdiction['court'] = $this->find_competent_court($case_data['user_postal_code']);
        }
        
        $jurisdiction['legal_basis'] = 'Defendant residence (ZPO § 12)';
        
        return $jurisdiction;
    }
    
    /**
     * Find competent court based on postal code
     */
    private function find_competent_court($postal_code) {
        $court = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_courts 
                 WHERE court_name LIKE %s 
                 ORDER BY court_name LIMIT 1",
                '%' . substr($postal_code, 0, 2) . '%'
            )
        );
        
        if (!$court) {
            // Fallback to default court
            $court = $this->wpdb->get_row(
                "SELECT * FROM {$this->wpdb->prefix}klage_courts 
                 WHERE court_name LIKE '%Frankfurt%' 
                 ORDER BY court_name LIMIT 1"
            );
        }
        
        return $court;
    }
    
    /**
     * Get GDPR damages framework
     */
    private function get_gdpr_damages_framework() {
        return array(
            'material_damage' => array(
                'basis' => 'Art. 82 Abs. 1 DSGVO',
                'description' => 'Materieller Schaden durch DSGVO-Verstoß',
                'calculation' => 'Nachweis konkreter Vermögensschäden'
            ),
            'immaterial_damage' => array(
                'basis' => 'Art. 82 Abs. 1 DSGVO',
                'description' => 'Immaterieller Schaden durch Persönlichkeitsverletzung',
                'calculation' => 'Pauschalierung nach Rechtsprechung (€350-€500)'
            ),
            'legal_fees' => array(
                'basis' => 'RVG',
                'description' => 'Anwaltskosten nach Rechtsanwaltsvergütungsgesetz',
                'calculation' => 'Streitwert-abhängige Berechnung'
            ),
            'court_fees' => array(
                'basis' => 'GKG',
                'description' => 'Gerichtskosten nach Gerichtskostengesetz',
                'calculation' => 'Streitwert-abhängige Berechnung'
            )
        );
    }
    
    /**
     * Get civil procedure framework
     */
    private function get_civil_procedure_framework() {
        return array(
            'procedure_type' => 'civil',
            'court_type' => 'Amtsgericht',
            'applicable_law' => 'ZPO (Zivilprozessordnung)',
            'phases' => array(
                'pre_litigation' => array(
                    'description' => 'Außergerichtliche Streitbeilegung',
                    'documents' => array('Abmahnung', 'Unterlassungserklärung')
                ),
                'litigation' => array(
                    'description' => 'Gerichtliches Verfahren',
                    'documents' => array('Klage', 'Ladung', 'Urteil')
                ),
                'enforcement' => array(
                    'description' => 'Vollstreckung',
                    'documents' => array('Vollstreckungsbescheid', 'Pfändung')
                )
            )
        );
    }
    
    /**
     * Save legal framework data to database
     */
    public function save_legal_data($case_id, $legal_data) {
        $insert_data = array(
            'case_id' => $case_id,
            'legal_basis_dsgvo' => $legal_data['legal_basis_dsgvo'] ?? 'Art. 82 DSGVO',
            'legal_basis_bgb' => $legal_data['legal_basis_bgb'] ?? '§ 823 Abs. 1 BGB',
            'legal_basis_gg' => $legal_data['legal_basis_gg'] ?? 'Art. 2 Abs. 1 und Art. 1 Abs. 1 GG',
            'legal_parent_type' => $legal_data['legal_parent_type'] ?? 'spam_email',
            'legal_consent_given' => $legal_data['legal_consent_given'] ?? false,
            'legal_previous_contact' => $legal_data['legal_previous_contact'] ?? false
        );
        
        // Check if legal data already exists
        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->wpdb->prefix}klage_legal WHERE case_id = %d",
                $case_id
            )
        );
        
        if ($existing) {
            // Update existing record
            $result = $this->wpdb->update(
                $this->wpdb->prefix . 'klage_legal',
                $insert_data,
                array('case_id' => $case_id),
                array('%d', '%s', '%s', '%s', '%s', '%d', '%d'),
                array('%d')
            );
        } else {
            // Insert new record
            $result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_legal',
                $insert_data,
                array('%d', '%s', '%s', '%s', '%s', '%d', '%d')
            );
        }
        
        if ($result === false) {
            return new WP_Error('save_failed', 'Rechtsdaten konnten nicht gespeichert werden');
        }
        
        return true;
    }
    
    /**
     * Get legal data for a case
     */
    public function get_case_legal_data($case_id) {
        $legal_data = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_legal WHERE case_id = %d",
                $case_id
            )
        );
        
        if (!$legal_data) {
            return new WP_Error('not_found', 'Rechtsdaten nicht gefunden');
        }
        
        return $legal_data;
    }
    
    /**
     * Generate legal document template
     */
    public function generate_legal_document($case_id, $document_type) {
        $case_manager = new CAH_Case_Manager();
        $case_data = $case_manager->get_case_details($case_id);
        
        if (is_wp_error($case_data)) {
            return $case_data;
        }
        
        switch ($document_type) {
            case 'abmahnung':
                return $this->generate_abmahnung_template($case_data);
            case 'klage':
                return $this->generate_klage_template($case_data);
            case 'mahnbescheid':
                return $this->generate_mahnbescheid_template($case_data);
            default:
                return new WP_Error('invalid_document_type', 'Ungültiger Dokumenttyp');
        }
    }
    
    /**
     * Generate Abmahnung template
     */
    private function generate_abmahnung_template($case_data) {
        $template = array(
            'title' => 'Abmahnung wegen DSGVO-Verstoß',
            'sections' => array(
                'header' => $this->generate_document_header($case_data),
                'introduction' => $this->generate_abmahnung_introduction($case_data),
                'legal_basis' => $this->generate_legal_basis_section($case_data),
                'damages' => $this->generate_damages_section($case_data),
                'demand' => $this->generate_demand_section($case_data),
                'deadline' => $this->generate_deadline_section($case_data),
                'closing' => $this->generate_document_closing($case_data)
            )
        );
        
        return $template;
    }
    
    /**
     * Additional helper methods for document generation would go here...
     */
    
    /**
     * Validate legal requirements
     */
    public function validate_legal_requirements($case_data) {
        $validation = array();
        
        // Check if all required evidence is present
        $validation['evidence_complete'] = $this->check_evidence_completeness($case_data);
        
        // Check if legal basis is established
        $validation['legal_basis_established'] = $this->check_legal_basis($case_data);
        
        // Check if damages are calculable
        $validation['damages_calculable'] = $this->check_damages_calculability($case_data);
        
        // Check if jurisdiction is clear
        $validation['jurisdiction_clear'] = $this->check_jurisdiction_clarity($case_data);
        
        // Overall validation status
        $validation['overall_status'] = $this->calculate_overall_validation_status($validation);
        
        return $validation;
    }
    
    /**
     * Check evidence completeness
     */
    private function check_evidence_completeness($case_data) {
        $required_evidence = array(
            'email_content' => !empty($case_data->emails[0]->emails_content),
            'sender_email' => !empty($case_data->emails[0]->emails_sender_email),
            'recipient_email' => !empty($case_data->emails[0]->emails_user_email),
            'received_date' => !empty($case_data->emails[0]->emails_received_date),
            'header_data' => !empty($case_data->emails[0]->emails_header_data)
        );
        
        $complete_count = count(array_filter($required_evidence));
        $total_count = count($required_evidence);
        
        return array(
            'complete' => $complete_count === $total_count,
            'completion_percentage' => round(($complete_count / $total_count) * 100, 2),
            'missing_evidence' => array_keys(array_filter($required_evidence, function($v) { return !$v; }))
        );
    }
    
    /**
     * Check legal basis establishment
     */
    private function check_legal_basis($case_data) {
        $checks = array(
            'gdpr_applicable' => $this->check_gdpr_applicability($case_data),
            'consent_absent' => $this->check_consent_absence($case_data),
            'legitimate_interest_absent' => $this->check_legitimate_interest_absence($case_data),
            'damage_occurred' => $this->check_damage_occurrence($case_data)
        );
        
        $passed_checks = count(array_filter($checks));
        $total_checks = count($checks);
        
        return array(
            'established' => $passed_checks === $total_checks,
            'strength_percentage' => round(($passed_checks / $total_checks) * 100, 2),
            'failed_checks' => array_keys(array_filter($checks, function($v) { return !$v; }))
        );
    }
    
    /**
     * Additional validation methods would go here...
     */
}