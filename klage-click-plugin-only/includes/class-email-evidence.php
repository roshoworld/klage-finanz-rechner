<?php
/**
 * Email Evidence class
 * Handles email evidence processing and validation
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Email_Evidence {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Process email evidence and extract relevant information
     */
    public function process_email_evidence($email_data) {
        $processed_data = array();
        
        // Extract sender information
        $processed_data['sender_info'] = $this->extract_sender_info($email_data);
        
        // Analyze email headers
        $processed_data['header_analysis'] = $this->analyze_email_headers($email_data);
        
        // Check for GDPR compliance indicators
        $processed_data['gdpr_compliance'] = $this->check_gdpr_compliance($email_data);
        
        // Extract technical information
        $processed_data['technical_info'] = $this->extract_technical_info($email_data);
        
        // Calculate risk score
        $processed_data['risk_score'] = $this->calculate_risk_score($email_data);
        
        return $processed_data;
    }
    
    /**
     * Extract sender information from email
     */
    private function extract_sender_info($email_data) {
        $sender_info = array();
        
        // Extract domain from sender email
        if (!empty($email_data['emails_sender_email'])) {
            $sender_info['sender_email'] = $email_data['emails_sender_email'];
            $sender_info['sender_domain'] = $this->extract_domain($email_data['emails_sender_email']);
        }
        
        // Extract sender name from headers or content
        $sender_info['sender_name'] = $this->extract_sender_name($email_data);
        
        // Check if sender is a known spammer
        $sender_info['is_known_spammer'] = $this->check_spam_database($sender_info['sender_email']);
        
        return $sender_info;
    }
    
    /**
     * Analyze email headers for technical information
     */
    private function analyze_email_headers($email_data) {
        $header_analysis = array();
        
        if (empty($email_data['emails_header_data'])) {
            return $header_analysis;
        }
        
        $headers = $this->parse_email_headers($email_data['emails_header_data']);
        
        // Extract IP addresses
        $header_analysis['originating_ip'] = $this->extract_originating_ip($headers);
        
        // Extract mail server information
        $header_analysis['mail_servers'] = $this->extract_mail_servers($headers);
        
        // Check for authentication
        $header_analysis['spf_result'] = $this->extract_spf_result($headers);
        $header_analysis['dkim_result'] = $this->extract_dkim_result($headers);
        $header_analysis['dmarc_result'] = $this->extract_dmarc_result($headers);
        
        // Check for bulk email indicators
        $header_analysis['bulk_indicators'] = $this->check_bulk_indicators($headers);
        
        return $header_analysis;
    }
    
    /**
     * Check GDPR compliance indicators
     */
    private function check_gdpr_compliance($email_data) {
        $compliance_check = array();
        
        // Check for unsubscribe link
        $compliance_check['has_unsubscribe_link'] = $this->check_unsubscribe_link($email_data);
        
        // Check for privacy policy link
        $compliance_check['has_privacy_policy'] = $this->check_privacy_policy($email_data);
        
        // Check for consent indicators
        $compliance_check['consent_indicators'] = $this->check_consent_indicators($email_data);
        
        // Check for sender identification
        $compliance_check['sender_identification'] = $this->check_sender_identification($email_data);
        
        // Overall compliance score
        $compliance_check['compliance_score'] = $this->calculate_compliance_score($compliance_check);
        
        return $compliance_check;
    }
    
    /**
     * Extract technical information
     */
    private function extract_technical_info($email_data) {
        $technical_info = array();
        
        // Message ID
        $technical_info['message_id'] = $this->extract_message_id($email_data);
        
        // Content type
        $technical_info['content_type'] = $this->extract_content_type($email_data);
        
        // Character encoding
        $technical_info['encoding'] = $this->extract_encoding($email_data);
        
        // Email size
        $technical_info['email_size'] = $this->calculate_email_size($email_data);
        
        // Attachment information
        $technical_info['attachments'] = $this->analyze_attachments($email_data);
        
        return $technical_info;
    }
    
    /**
     * Calculate risk score for the email
     */
    private function calculate_risk_score($email_data) {
        $risk_score = 0;
        
        // Check for spam indicators
        $spam_indicators = $this->check_spam_indicators($email_data);
        $risk_score += count($spam_indicators) * 2;
        
        // Check sender reputation
        $sender_reputation = $this->check_sender_reputation($email_data['emails_sender_email']);
        $risk_score += $sender_reputation['risk_points'];
        
        // Check content quality
        $content_quality = $this->analyze_content_quality($email_data);
        $risk_score += $content_quality['risk_points'];
        
        // Check technical indicators
        $technical_indicators = $this->check_technical_indicators($email_data);
        $risk_score += $technical_indicators['risk_points'];
        
        // Normalize score to 1-10 scale
        $risk_score = min(10, max(1, $risk_score));
        
        return $risk_score;
    }
    
    /**
     * Extract domain from email address
     */
    private function extract_domain($email) {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : '';
    }
    
    /**
     * Extract sender name from email data
     */
    private function extract_sender_name($email_data) {
        // Try to extract from headers first
        if (!empty($email_data['emails_header_data'])) {
            $headers = $this->parse_email_headers($email_data['emails_header_data']);
            if (isset($headers['from'])) {
                $from_header = $headers['from'];
                if (preg_match('/^(.+)<.+>$/', $from_header, $matches)) {
                    return trim($matches[1], ' "');
                }
            }
        }
        
        // Try to extract from content
        if (!empty($email_data['emails_content'])) {
            // Look for signature patterns
            $content = $email_data['emails_content'];
            if (preg_match('/Mit freundlichen Grüßen[,\s\n]+(.+)/i', $content, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return '';
    }
    
    /**
     * Check if sender is in spam database
     */
    private function check_spam_database($email) {
        // This would integrate with external spam databases
        // For now, return false
        return false;
    }
    
    /**
     * Parse email headers into array
     */
    private function parse_email_headers($header_data) {
        $headers = array();
        
        $lines = explode("\n", $header_data);
        $current_header = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $current_header = strtolower($matches[1]);
                $headers[$current_header] = $matches[2];
            } elseif (!empty($current_header)) {
                // Continuation line
                $headers[$current_header] .= ' ' . $line;
            }
        }
        
        return $headers;
    }
    
    /**
     * Extract originating IP from headers
     */
    private function extract_originating_ip($headers) {
        $ip_addresses = array();
        
        // Check Received headers
        if (isset($headers['received'])) {
            $received_headers = is_array($headers['received']) ? $headers['received'] : array($headers['received']);
            
            foreach ($received_headers as $received) {
                if (preg_match('/\[([0-9\.]+)\]/', $received, $matches)) {
                    $ip_addresses[] = $matches[1];
                }
            }
        }
        
        // Check X-Originating-IP
        if (isset($headers['x-originating-ip'])) {
            $ip_addresses[] = $headers['x-originating-ip'];
        }
        
        return array_unique($ip_addresses);
    }
    
    /**
     * Extract mail servers from headers
     */
    private function extract_mail_servers($headers) {
        $servers = array();
        
        if (isset($headers['received'])) {
            $received_headers = is_array($headers['received']) ? $headers['received'] : array($headers['received']);
            
            foreach ($received_headers as $received) {
                if (preg_match('/from\s+([^\s]+)/', $received, $matches)) {
                    $servers[] = $matches[1];
                }
            }
        }
        
        return array_unique($servers);
    }
    
    /**
     * Check for unsubscribe link
     */
    private function check_unsubscribe_link($email_data) {
        if (empty($email_data['emails_content'])) {
            return false;
        }
        
        $content = strtolower($email_data['emails_content']);
        
        // Check for common unsubscribe patterns
        $unsubscribe_patterns = array(
            'unsubscribe',
            'abmelden',
            'abbestellen',
            'austragen',
            'kündigen'
        );
        
        foreach ($unsubscribe_patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for privacy policy link
     */
    private function check_privacy_policy($email_data) {
        if (empty($email_data['emails_content'])) {
            return false;
        }
        
        $content = strtolower($email_data['emails_content']);
        
        // Check for privacy policy patterns
        $privacy_patterns = array(
            'datenschutz',
            'privacy policy',
            'datenschutzerklärung',
            'privacy'
        );
        
        foreach ($privacy_patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check spam indicators
     */
    private function check_spam_indicators($email_data) {
        $indicators = array();
        
        if (empty($email_data['emails_content'])) {
            return $indicators;
        }
        
        $content = strtolower($email_data['emails_content']);
        
        // Common spam phrases
        $spam_phrases = array(
            'kostenlos',
            'gratis',
            'gewinnen',
            'garantiert',
            'sofort',
            'jetzt bestellen',
            'begrenzte zeit',
            'nur heute',
            'exklusiv',
            'einmalig'
        );
        
        foreach ($spam_phrases as $phrase) {
            if (strpos($content, $phrase) !== false) {
                $indicators[] = $phrase;
            }
        }
        
        return $indicators;
    }
    
    /**
     * Additional helper methods would go here...
     */
    
    /**
     * Get email evidence for a case
     */
    public function get_case_email_evidence($case_id) {
        $emails = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_emails WHERE case_id = %d",
                $case_id
            )
        );
        
        return $emails;
    }
    
    /**
     * Save processed email evidence
     */
    public function save_email_evidence($case_id, $email_data) {
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'klage_emails',
            array(
                'case_id' => $case_id,
                'emails_received_date' => $email_data['emails_received_date'],
                'emails_received_time' => $email_data['emails_received_time'],
                'emails_sender_email' => $email_data['emails_sender_email'],
                'emails_user_email' => $email_data['emails_user_email'],
                'emails_subject' => $email_data['emails_subject'],
                'emails_content' => $email_data['emails_content'],
                'emails_header_data' => $email_data['emails_header_data'],
                'emails_ip_address' => $email_data['emails_ip_address'],
                'emails_attachment_count' => $email_data['emails_attachment_count'],
                'emails_has_unsubscribe' => $email_data['emails_has_unsubscribe']
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('save_failed', 'E-Mail-Evidenz konnte nicht gespeichert werden');
        }
        
        return $this->wpdb->insert_id;
    }
}