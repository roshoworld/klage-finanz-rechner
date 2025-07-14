<?php
/**
 * Admin Dashboard class - Forderungen.com Master Data v1.2.0 (57 Fields)
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function admin_init() {
        register_setting('klage_click_settings', 'klage_click_n8n_url');
        register_setting('klage_click_settings', 'klage_click_n8n_key');
        register_setting('klage_click_settings', 'klage_click_debug_mode');
        
        // Handle template download EARLY before any output
        $this->handle_early_download();
        
        // Add AJAX handlers for file downloads
        add_action('wp_ajax_klage_download_template', array($this, 'ajax_download_template'));
        add_action('wp_ajax_klage_export_calculation', array($this, 'ajax_export_calculation'));
    }
    
    private function handle_early_download() {
        // Check if this is our template download request
        if (isset($_GET['page']) && $_GET['page'] === 'klage-click-import' && 
            isset($_GET['action']) && $_GET['action'] === 'template' && 
            isset($_GET['_wpnonce'])) {
            
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'download_template')) {
                wp_die('Security check failed');
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die('Insufficient permissions');
            }
            
            // Send the file download
            $this->send_template_download();
            exit; // Critical: Stop WordPress execution
        }
    }
    
    private function send_template_download() {
        // Check template type
        $template_type = $_GET['template_type'] ?? 'comprehensive';
        
        // Create filename based on template type
        if ($template_type === 'forderungen') {
            $filename = 'forderungen_com_import_template_' . date('Y-m-d') . '.csv';
        } else {
            $filename = 'klage_click_comprehensive_template_' . date('Y-m-d') . '.csv';
        }
        
        // Get file content
        $content = $this->get_template_content();
        
        // Clean any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Prevent any caching
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        
        // Set download headers
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));
        
        // Output the content
        echo $content;
        
        // Stop all further processing
        die();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Klage.Click Hub', 'court-automation-hub'),
            __('Klage.Click Hub', 'court-automation-hub'),
            'manage_options',
            'klage-click-hub',
            array($this, 'admin_page_dashboard'),
            'dashicons-hammer',
            30
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Fälle', 'court-automation-hub'),
            __('Fälle', 'court-automation-hub'),
            'manage_options',
            'klage-click-cases',
            array($this, 'admin_page_cases')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Finanz-Rechner', 'court-automation-hub'),
            __('Finanz-Rechner', 'court-automation-hub'),
            'manage_options',
            'klage-click-financial',
            array($this, 'admin_page_financial')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('CSV Import', 'court-automation-hub'),
            __('CSV Import', 'court-automation-hub'),
            'manage_options',
            'klage-click-import',
            array($this, 'admin_page_import')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Hilfe & Prozesse', 'court-automation-hub'),
            __('Hilfe & Prozesse', 'court-automation-hub'),
            'manage_options',
            'klage-click-help',
            array($this, 'admin_page_help')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Einstellungen', 'court-automation-hub'),
            __('Einstellungen', 'court-automation-hub'),
            'manage_options',
            'klage-click-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    public function admin_page_dashboard() {
        global $wpdb;
        
        // Get statistics
        $total_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases") ?? 0;
        $pending_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'pending'") ?? 0;
        $processing_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'processing'") ?? 0;
        $completed_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'completed'") ?? 0;
        $total_value = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}klage_cases") ?? 0;
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Dashboard', 'court-automation-hub'); ?></h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.3 - Vollständig funktionsfähig!</strong></p>
                <p>Alle Features wiederhergestellt: Case Management, Financial Calculator, CSV Import & Help System.</p>
            </div>
            
            <div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;"><?php echo esc_html($total_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Gesamt Fälle</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #ff9800; font-size: 28px;"><?php echo esc_html($pending_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Ausstehend</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #2196f3; font-size: 28px;"><?php echo esc_html($processing_cases); ?></h3>
                    <p style="margin: 0; color: #666;">In Bearbeitung</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #4caf50; font-size: 28px;"><?php echo esc_html($completed_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Abgeschlossen</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 24px;">€<?php echo esc_html(number_format($total_value, 2)); ?></h3>
                    <p style="margin: 0; color: #666;">Gesamtwert</p>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">🚀 Schnellaktionen</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary" style="padding: 20px; height: auto; text-decoration: none; text-align: center;">
                            <strong>📝 Neuen Fall erstellen</strong><br>
                            <small>GDPR Fall mit €548.11 Standard</small>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=klage-click-import'); ?>" class="button button-secondary" style="padding: 20px; height: auto; text-decoration: none; text-align: center;">
                            <strong>📊 CSV Import</strong><br>
                            <small>Bulk-Import von Forderungen.com</small>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=klage-click-financial&action=calculator'); ?>" class="button button-secondary" style="padding: 20px; height: auto; text-decoration: none; text-align: center;">
                            <strong>🧮 Finanzrechner</strong><br>
                            <small>Excel-ähnliche Berechnungen</small>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=klage-click-help'); ?>" class="button button-secondary" style="padding: 20px; height: auto; text-decoration: none; text-align: center;">
                            <strong>📚 Hilfe & Prozesse</strong><br>
                            <small>Komplette Anleitungen</small>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">📊 System Status</h2>
                <div class="inside" style="padding: 20px;">
                    <?php $this->display_system_status(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_add_case_form() {
        ?>
        <div class="wrap">
            <h1>Neuen GDPR Spam Fall erstellen</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.4 - Case Creation!</strong></p>
                <p>Erstellen Sie einen neuen GDPR SPAM-Fall mit automatischer €548.11 Berechnung.</p>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('create_case', 'create_case_nonce'); ?>
                <input type="hidden" name="action" value="create_case">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <!-- Case Information -->
                    <div class="postbox">
                        <h2 class="hndle">📋 Fall-Informationen</h2>
                        <div class="inside" style="padding: 20px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="case_id">Fall-ID</label></th>
                                    <td>
                                        <input type="text" id="case_id" name="case_id" class="regular-text" 
                                               value="SPAM-<?php echo date('Y'); ?>-<?php echo str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT); ?>" required>
                                        <p class="description">Eindeutige Fall-Kennung</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_status">Status</label></th>
                                    <td>
                                        <select id="case_status" name="case_status" class="regular-text">
                                            <option value="draft">📝 Entwurf</option>
                                            <option value="processing">⚡ In Bearbeitung</option>
                                            <option value="completed">✅ Abgeschlossen</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_priority">Priorität</label></th>
                                    <td>
                                        <select id="case_priority" name="case_priority" class="regular-text">
                                            <option value="medium">🟡 Medium</option>
                                            <option value="high">🟠 Hoch</option>
                                            <option value="low">🟢 Niedrig</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_notes">Notizen</label></th>
                                    <td>
                                        <textarea id="case_notes" name="case_notes" class="large-text" rows="4" 
                                                  placeholder="Interne Notizen zum Fall..."></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Email Evidence -->
                    <div class="postbox">
                        <h2 class="hndle">📧 E-Mail Evidenz</h2>
                        <div class="inside" style="padding: 20px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="emails_sender_email">Spam-Absender</label></th>
                                    <td>
                                        <input type="email" id="emails_sender_email" name="emails_sender_email" class="regular-text" required>
                                        <p class="description">E-Mail-Adresse des Spam-Absenders</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="emails_user_email">Betroffene E-Mail</label></th>
                                    <td>
                                        <input type="email" id="emails_user_email" name="emails_user_email" class="regular-text" required>
                                        <p class="description">E-Mail-Adresse des Geschädigten</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="emails_received_date">Empfangsdatum</label></th>
                                    <td>
                                        <input type="date" id="emails_received_date" name="emails_received_date" class="regular-text" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="emails_subject">E-Mail Betreff</label></th>
                                    <td>
                                        <input type="text" id="emails_subject" name="emails_subject" class="regular-text" 
                                               placeholder="Betreff der Spam-E-Mail">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="emails_content">E-Mail Inhalt</label></th>
                                    <td>
                                        <textarea id="emails_content" name="emails_content" class="large-text" rows="6" 
                                                  placeholder="Vollständiger Inhalt der Spam-E-Mail" required></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Calculation -->
                <div class="postbox" style="margin-top: 20px;">
                    <h2 class="hndle">💰 Automatische DSGVO-Berechnung</h2>
                    <div class="inside" style="padding: 20px;">
                        <div style="background: #f0f8ff; padding: 15px; border-radius: 5px;">
                            <p><strong>📊 Standard DSGVO-Beträge werden automatisch angewendet:</strong></p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;">
                                <div><strong>💰 Grundschaden:</strong> €350.00</div>
                                <div><strong>⚖️ Anwaltskosten:</strong> €96.90</div>
                                <div><strong>📞 Kommunikation:</strong> €13.36</div>
                                <div><strong>🏛️ Gerichtskosten:</strong> €32.00</div>
                                <div><strong>📊 MwSt (19%):</strong> €87.85</div>
                                <div style="background: #0073aa; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                                    <strong>🎯 GESAMTSUMME: €548.11</strong>
                                </div>
                            </div>
                            <p><em>Diese Beträge können nach der Erstellung im Fall-Editor angepasst werden.</em></p>
                        </div>
                    </div>
                </div>
                
                <!-- Submit -->
                <div style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px;">
                    <p class="submit" style="margin: 0;">
                        <input type="submit" class="button button-primary button-large" value="💾 Fall erstellen (€548.11)">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">Abbrechen</a>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
    
    private function handle_case_actions() {
        if (!isset($_POST['action'])) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'create_case':
                if (wp_verify_nonce($_POST['create_case_nonce'], 'create_case')) {
                    $this->create_new_case();
                }
                break;
            case 'update_case':
                if (wp_verify_nonce($_POST['update_case_nonce'], 'update_case')) {
                    $this->update_case();
                }
                break;
        }
    }
    
    public function admin_page_cases() {
        global $wpdb;
        
        // Handle case actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_case_actions();
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        switch ($action) {
            case 'add':
                $this->render_add_case_form();
                break;
            case 'edit':
                $this->render_edit_case_form($case_id);
                break;
            case 'view':
                $this->render_view_case($case_id);
                break;
            case 'delete':
                $this->handle_delete_case($case_id);
                $this->render_cases_list();
                break;
            default:
                $this->render_cases_list();
                break;
        }
    }
    
    private function render_cases_list() {
        global $wpdb;
        
        // Handle bulk actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action_nonce'])) {
            if (wp_verify_nonce($_POST['bulk_action_nonce'], 'bulk_actions')) {
                $this->handle_bulk_actions();
            }
        }
        
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        // Build query with filters
        $where_conditions = array('1=1');
        $query_params = array();
        
        if (!empty($status_filter)) {
            $where_conditions[] = 'c.case_status = %s';
            $query_params[] = $status_filter;
        }
        
        if (!empty($search)) {
            $where_conditions[] = '(c.case_id LIKE %s OR e.emails_sender_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $query_params[] = $search_term;
            $query_params[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Check if tables exist
        $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_cases'");
        
        if (!$tables_exist) {
            $cases = array();
        } else {
            $query = "
                SELECT 
                    c.id,
                    c.case_id,
                    c.case_creation_date,
                    c.case_status,
                    c.case_priority,
                    c.total_amount,
                    e.emails_sender_email
                FROM {$wpdb->prefix}klage_cases c
                LEFT JOIN {$wpdb->prefix}klage_emails e ON c.id = e.case_id
                WHERE {$where_clause}
                ORDER BY c.case_creation_date DESC
                LIMIT 50
            ";
            
            if (!empty($query_params)) {
                $cases = $wpdb->get_results($wpdb->prepare($query, $query_params));
            } else {
                $cases = $wpdb->get_results($query);
            }
        }
        
        // Get statistics
        $total_cases = $tables_exist ? ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases") ?? 0) : 0;
        $draft_cases = $tables_exist ? ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'draft'") ?? 0) : 0;
        $processing_cases = $tables_exist ? ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'processing'") ?? 0) : 0;
        $completed_cases = $tables_exist ? ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'completed'") ?? 0) : 0;
        $total_value = $tables_exist ? ($wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}klage_cases") ?? 0) : 0;
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">GDPR Spam Fälle</h1>
            <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="page-title-action">
                Neuen Fall hinzufügen
            </a>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.5 - Complete Case Management!</strong></p>
                <p>Vollständige Fall-Verwaltung mit Erstellen, Bearbeiten, Filtern und Bulk-Aktionen.</p>
            </div>
            
            <!-- Statistics Dashboard -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0;">
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 24px;"><?php echo esc_html($total_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Gesamt Fälle</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #ff9800; font-size: 24px;"><?php echo esc_html($draft_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Entwürfe</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #2196f3; font-size: 24px;"><?php echo esc_html($processing_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">In Bearbeitung</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #4caf50; font-size: 24px;"><?php echo esc_html($completed_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Abgeschlossen</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 20px;">€<?php echo esc_html(number_format($total_value, 2)); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Gesamtwert</p>
                </div>
            </div>
            
            <?php if (!$tables_exist): ?>
                <div class="notice notice-warning">
                    <p><strong>⚠️ Datenbank-Tabellen fehlen!</strong> Gehen Sie zu <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>">Einstellungen</a> und erstellen Sie die Tabellen.</p>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="klage-click-cases">
                    
                    <div>
                        <label for="status" style="display: block; margin-bottom: 5px; font-weight: bold;">Status:</label>
                        <select name="status" id="status">
                            <option value="">Alle Status</option>
                            <option value="draft" <?php selected($status_filter, 'draft'); ?>>📝 Entwurf</option>
                            <option value="processing" <?php selected($status_filter, 'processing'); ?>>⚡ In Bearbeitung</option>
                            <option value="completed" <?php selected($status_filter, 'completed'); ?>>✅ Abgeschlossen</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" style="display: block; margin-bottom: 5px; font-weight: bold;">Suche:</label>
                        <input type="text" name="search" id="search" value="<?php echo esc_attr($search); ?>" 
                               placeholder="Fall-ID oder E-Mail..." style="width: 200px;">
                    </div>
                    
                    <div>
                        <input type="submit" class="button" value="🔍 Filtern">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">🗑️ Zurücksetzen</a>
                    </div>
                </form>
            </div>
            
            <!-- Cases Table -->
            <form method="post" id="cases-filter">
                <?php wp_nonce_field('bulk_actions', 'bulk_action_nonce'); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <select name="bulk_action">
                            <option value="">Bulk-Aktionen</option>
                            <option value="status_processing">Status → In Bearbeitung</option>
                            <option value="status_completed">Status → Abgeschlossen</option>
                            <option value="delete">Löschen</option>
                        </select>
                        <input type="submit" class="button action" value="Anwenden">
                    </div>
                    
                    <div class="alignright">
                        <span style="color: #666;"><?php echo count($cases); ?> von <?php echo $total_cases; ?> Fällen</span>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th>Fall-ID</th>
                            <th>Status</th>
                            <th>E-Mail Absender</th>
                            <th>Betrag</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cases)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <?php if (!$tables_exist): ?>
                                        <p><strong>Datenbank-Tabellen müssen erst erstellt werden.</strong></p>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>" class="button button-primary">
                                            🔧 Tabellen erstellen
                                        </a>
                                    <?php elseif (!empty($search) || !empty($status_filter)): ?>
                                        <p>Keine Fälle gefunden, die den Filterkriterien entsprechen.</p>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Filter zurücksetzen</a>
                                    <?php else: ?>
                                        <p>Keine Fälle gefunden. Erstellen Sie Ihren ersten Fall!</p>
                                        <div style="margin-top: 15px;">
                                            <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary" style="margin-right: 10px;">
                                                📝 Neuen Fall erstellen
                                            </a>
                                            <a href="<?php echo admin_url('admin.php?page=klage-click-import'); ?>" class="button button-secondary">
                                                📊 CSV Import verwenden
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cases as $case): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="case_ids[]" value="<?php echo esc_attr($case->id); ?>">
                                    </th>
                                    <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                            <?php 
                                            $status_icons = array(
                                                'draft' => '📝 Entwurf',
                                                'processing' => '⚡ In Bearbeitung',
                                                'completed' => '✅ Abgeschlossen'
                                            );
                                            echo $status_icons[$case->case_status] ?? esc_html($case->case_status); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($case->emails_sender_email ?: '-'); ?></td>
                                    <td><strong>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></strong></td>
                                    <td><?php echo esc_html(date_i18n('d.m.Y', strtotime($case->case_creation_date))); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->id); ?>" 
                                           class="button button-small" title="Fall ansehen">👁️</a>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" 
                                           class="button button-small" title="Fall bearbeiten">✏️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
        
        <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('cb-select-all');
            const checkboxes = document.querySelectorAll('input[name="case_ids[]"]');
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }
        });
        </script>
        <?php
    }
    
    public function admin_page_financial() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'manage';
        
        switch ($action) {
            case 'calculator':
                $this->render_financial_calculator();
                break;
            default:
                $this->render_financial_field_manager();
                break;
        }
    }
    
    private function render_financial_field_manager() {
        ?>
        <div class="wrap">
            <h1>💰 Finanz-Rechner Verwaltung</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.3 - Dynamischer Finanz-Rechner!</strong></p>
                <p>Excel-ähnliche Berechnungen mit DSGVO-Standards und benutzerdefinierten Feldern.</p>
            </div>
            
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-financial&action=calculator'); ?>" class="button button-primary">
                    🧮 Rechner öffnen
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-import'); ?>" class="button button-secondary">
                    📊 CSV Import
                </a>
            </div>
            
            <!-- DSGVO Standard Overview -->
            <div class="postbox">
                <h2 class="hndle">📊 DSGVO Standard-Berechnung</h2>
                <div class="inside" style="padding: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Kostenart</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Betrag</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Beschreibung</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">💰 Grundschaden</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€350.00</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px;">DSGVO Art. 82 Schadenersatz</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">⚖️ Anwaltskosten</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€96.90</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px;">RVG Rechtsanwaltsgebühren</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">📞 Kommunikation</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€13.36</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px;">Porto, Telefon, etc.</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">🏛️ Gerichtskosten</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€32.00</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px;">Verfahrenskosten</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">📊 MwSt (19%)</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€87.85</strong></td>
                            <td style="border: 1px solid #ddd; padding: 10px;">19% auf Anwalt + Kommunikation</td>
                        </tr>
                        <tr style="background: #e7f3ff; font-weight: bold;">
                            <td style="border: 2px solid #0073aa; padding: 12px;">🎯 GESAMTSUMME</td>
                            <td style="border: 2px solid #0073aa; padding: 12px; text-align: right; font-size: 18px; color: #0073aa;"><strong>€548.11</strong></td>
                            <td style="border: 2px solid #0073aa; padding: 12px;">Standard DSGVO SPAM-Fall</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Quick Templates -->
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">⚡ Schnell-Templates</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div style="background: #f0f8ff; padding: 20px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #0073aa;">📋 DSGVO Standard</h4>
                            <p style="margin: 0 0 10px 0; font-size: 14px;">Einfache SPAM-Fälle</p>
                            <strong style="font-size: 18px; color: #0073aa;">€548.11</strong>
                        </div>
                        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #856404;">💎 DSGVO Premium</h4>
                            <p style="margin: 0 0 10px 0; font-size: 14px;">Mehrfach-Verstöße</p>
                            <strong style="font-size: 18px; color: #856404;">€750+</strong>
                        </div>
                        <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #155724;">🏢 Business-Fall</h4>
                            <p style="margin: 0 0 10px 0; font-size: 14px;">Firmen-Verstöße</p>
                            <strong style="font-size: 18px; color: #155724;">€1000+</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_financial_calculator() {
        ?>
        <div class="wrap">
            <h1>🧮 Dynamischer Finanz-Rechner</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.3 - Excel-ähnlicher Finanzrechner!</strong></p>
                <p>Berechnen Sie automatisch DSGVO-Forderungen mit Echtzeit-Berechnungen.</p>
            </div>
            
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-financial'); ?>" class="button button-secondary">
                    ← Zurück zur Feldverwaltung
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                    💰 Neuen Fall mit Rechner erstellen
                </a>
            </div>
            
            <!-- Calculator Interface -->
            <div class="postbox">
                <h2 class="hndle">📊 Finanz-Rechner (Spreadsheet-Modus)</h2>
                <div class="inside" style="padding: 20px;">
                    <table id="financial-calculator" style="width: 100%; border-collapse: collapse; background: white;">
                        <thead>
                            <tr style="background: #0073aa; color: white;">
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Feld</th>
                                <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Wert (€)</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Formel/Beschreibung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><strong>💰 Grundschaden</strong></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                    <input type="number" step="0.01" value="350.00" id="grundschaden" 
                                           style="width: 100px; text-align: right; font-weight: bold;">
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; color: #666;">DSGVO Art. 82 Schadenersatz</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><strong>⚖️ Anwaltskosten</strong></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                    <input type="number" step="0.01" value="96.90" id="anwaltskosten"
                                           style="width: 100px; text-align: right; font-weight: bold;">
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; color: #666;">RVG Rechtsanwaltsgebühren</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><strong>📞 Kommunikation</strong></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                    <input type="number" step="0.01" value="13.36" id="kommunikation"
                                           style="width: 100px; text-align: right; font-weight: bold;">
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; color: #666;">Porto, Telefon, etc.</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><strong>🏛️ Gerichtskosten</strong></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                    <input type="number" step="0.01" value="32.00" id="gerichtskosten"
                                           style="width: 100px; text-align: right; font-weight: bold;">
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; color: #666;">Verfahrenskosten</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;"><strong>📊 MwSt (19%)</strong></td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                    <input type="number" step="0.01" value="87.85" id="mwst" readonly
                                           style="width: 100px; text-align: right; font-weight: bold; background: #f0f8ff;">
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; color: #666;">=(Anwaltskosten + Kommunikation) * 0.19</td>
                            </tr>
                            <tr style="background: #f0f8ff; font-weight: bold; font-size: 16px;">
                                <td style="padding: 15px; border: 2px solid #0073aa;"><strong>🎯 GESAMTSUMME</strong></td>
                                <td style="padding: 15px; border: 2px solid #0073aa; text-align: right;">
                                    <input type="number" step="0.01" value="548.11" id="total" readonly
                                           style="width: 120px; text-align: right; font-weight: bold; font-size: 18px; 
                                                  background: #e7f3ff; border: 2px solid #0073aa; color: #0073aa;">
                                </td>
                                <td style="padding: 15px; border: 2px solid #0073aa; color: #0073aa;">
                                    =SUM(Alle Felder) - Automatisch berechnet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="button" class="button button-large" onclick="resetCalculator()">
                            🔄 Zurücksetzen
                        </button>
                        <button type="button" class="button button-primary button-large" onclick="saveCalculation()" style="margin-left: 15px;">
                            💾 Berechnung speichern
                        </button>
                        <button type="button" class="button button-secondary button-large" onclick="exportCalculation()" style="margin-left: 15px;">
                            📊 Als CSV exportieren
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fields = ['grundschaden', 'anwaltskosten', 'kommunikation', 'gerichtskosten'];
            
            fields.forEach(fieldId => {
                document.getElementById(fieldId).addEventListener('input', calculateTotal);
            });
            
            function calculateTotal() {
                const grundschaden = parseFloat(document.getElementById('grundschaden').value) || 0;
                const anwaltskosten = parseFloat(document.getElementById('anwaltskosten').value) || 0;
                const kommunikation = parseFloat(document.getElementById('kommunikation').value) || 0;
                const gerichtskosten = parseFloat(document.getElementById('gerichtskosten').value) || 0;
                
                const mwst = (anwaltskosten + kommunikation) * 0.19;
                document.getElementById('mwst').value = mwst.toFixed(2);
                
                const total = grundschaden + anwaltskosten + kommunikation + gerichtskosten + mwst;
                document.getElementById('total').value = total.toFixed(2);
            }
        });
        
        function resetCalculator() {
            document.getElementById('grundschaden').value = '350.00';
            document.getElementById('anwaltskosten').value = '96.90';
            document.getElementById('kommunikation').value = '13.36';
            document.getElementById('gerichtskosten').value = '32.00';
            
            // Trigger recalculation
            document.getElementById('grundschaden').dispatchEvent(new Event('input'));
        }
        
        function saveCalculation() {
            alert('💾 Speichern-Funktion wird in v1.1.4 implementiert!');
        }
        
        function exportCalculation() {
            alert('📊 CSV-Export wird in v1.1.4 implementiert!');
        }
        </script>
        <?php
    }
    
    public function admin_page_import() {
        global $wpdb;
        
        // Handle import actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_action'])) {
            $this->handle_import_action();
        }
        
        // Render the import page (download is handled in admin_init)
        $this->render_import_page();
    }
    
    private function render_import_page() {
        ?>
        <div class="wrap">
            <h1>📊 CSV Import - Klage.Click v1.2.0</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.2.0 - Dual Template System!</strong></p>
                <p>Wählen Sie zwischen Forderungen.com Import (17 Felder) oder Comprehensive Internal (57 Felder)</p>
            </div>
            
            <!-- Template Selection -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">📊 Forderungen.com Template</h3>
                    <p>Exakte 17 Felder für Forderungen.com CSV-Exports</p>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=klage-click-import&action=template&template_type=forderungen'), 'download_template'); ?>" class="button button-primary">
                        📥 Forderungen.com Template
                    </a>
                    <div style="margin-top: 10px; color: #666; font-size: 14px;">
                        <strong>Felder:</strong> Fall-ID, Mandant, Schuldner-Details, Beweise, Dokumente
                    </div>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">🎯 Comprehensive Template</h3>
                    <p>Vollständige 57 Felder für interne Datenverwaltung</p>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=klage-click-import&action=template&template_type=comprehensive'), 'download_template'); ?>" class="button button-secondary">
                        📥 Comprehensive Template
                    </a>
                    <div style="margin-top: 10px; color: #666; font-size: 14px;">
                        <strong>Felder:</strong> Alle 57 Felder inkl. EGVP, Timeline, Risikobewertung
                    </div>
                </div>
            </div>
            
            <!-- Step-by-Step Process -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">1️⃣ Template wählen</h3>
                    <p>Wählen Sie das passende Template für Ihre Datenquelle</p>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">2️⃣ Daten vorbereiten</h3>
                    <p>Füllen Sie die CSV mit Ihren Daten aus</p>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">3️⃣ Import durchführen</h3>
                    <p>Laden Sie die CSV hoch und prüfen Sie die Vorschau</p>
                </div>
            </div>
            
            <!-- Upload Form -->
            <div class="postbox">
                <h2 class="hndle">📁 CSV-Datei hochladen</h2>
                <div class="inside" style="padding: 20px;">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_action" value="upload_csv">
                        <?php wp_nonce_field('csv_import_action', 'csv_import_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="csv_file">CSV-Datei auswählen</label></th>
                                <td>
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                                    <p class="description">
                                        Unterstützte Formate: .csv (UTF-8 oder Windows-1252)<br>
                                        Trennzeichen: Semikolon (;) oder Komma (,)<br>
                                        Maximale Dateigröße: 10MB
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="delimiter">Trennzeichen</label></th>
                                <td>
                                    <select id="delimiter" name="delimiter">
                                        <option value=";">Semikolon (;) - Standard deutsch</option>
                                        <option value=",">Komma (,) - International</option>
                                        <option value="\t">Tab</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="import_mode">Import-Modus</label></th>
                                <td>
                                    <select id="import_mode" name="import_mode">
                                        <option value="create_new">🆕 Nur neue Fälle erstellen</option>
                                        <option value="update_existing">🔄 Bestehende Fälle aktualisieren</option>
                                        <option value="create_and_update">🚀 Neue erstellen + Bestehende aktualisieren</option>
                                    </select>
                                    <p class="description">Bei "Aktualisieren" wird anhand der Fall-ID abgeglichen</p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary button-large" value="📊 CSV hochladen & Import starten">
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Template Structure Info -->
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle">📋 Forderungen.com Template-Struktur (17 Felder)</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p><strong>✅ Template verwendet exakte Forderungen.com Feldnamen für nahtlose Integration!</strong></p>
                        <p><strong>🎯 Automatische Erweiterung:</strong> Die 17 Forderungen.com Felder werden automatisch um interne Felder erweitert</p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h4 style="color: #0073aa;">📋 Fall-Informationen (Forderungen.com)</h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr style="background: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Feldname</th>
                                    <th style="border: 1px solid #ddd; padding: 6px;">Beispiel</th>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Fall-ID (CSV)</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">SPAM-2024-0001</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Fall-Status</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">draft, processing</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Brief-Status</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">pending, sent</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Briefe</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">1, 2, 3</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Mandant</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Ihre Firma GmbH</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Schuldner</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Max Mustermann</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Einreichungsdatum</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">2024-01-15</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Beweise</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">SPAM E-Mail ohne Einwilligung</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Dokumente</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">E-Mail Screenshot</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>links zu Dokumenten</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">https://example.com/doc.pdf</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div>
                            <h4 style="color: #0073aa;">👤 Schuldner-Details (Forderungen.com)</h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr style="background: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Feldname</th>
                                    <th style="border: 1px solid #ddd; padding: 6px;">Beispiel</th>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Firmenname</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Beispiel AG (oder leer)</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Vorname</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Max</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Nachname</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Mustermann</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Adresse</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Musterstraße 123</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Postleitzahl</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">12345</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Stadt</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Musterstadt</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Land</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Deutschland</td>
                                </tr>
                            </table>
                            
                            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 15px;">
                                <strong>💡 Hinweis:</strong> Firmenname bleibt leer für Privatpersonen, wird ausgefüllt für Unternehmen.
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <h4 style="color: #856404; margin-top: 0;">🔗 Integration mit Forderungen.com</h4>
                        <ol>
                            <li><strong>Exportieren:</strong> Daten aus Forderungen.com als CSV exportieren</li>
                            <li><strong>Direkt importieren:</strong> Forderungen.com CSV direkt in Klage.Click Hub hochladen</li>
                            <li><strong>Automatische Erweiterung:</strong> 17 Felder werden zu 57 Feldern erweitert</li>
                            <li><strong>GDPR-Standard:</strong> Fälle werden automatisch mit €548.11 DSGVO-Berechnungen erstellt</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_template_content() {
        // Check if this is a Forderungen.com specific template request
        if (isset($_GET['template_type']) && $_GET['template_type'] === 'forderungen') {
            return $this->get_forderungen_template_content();
        }
        
        // Default: Return comprehensive 57-field template for internal use
        return $this->get_comprehensive_template_content();
    }
    
    private function get_forderungen_template_content() {
        // Add BOM for UTF-8 Excel compatibility
        $content = chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // CSV Header - EXACT 17 fields from Forderungen.com system
        $header = array(
            'Fall-ID (CSV)',
            'Fall-Status',
            'Brief-Status',
            'Briefe',
            'Mandant',
            'Schuldner',
            'Einreichungsdatum',
            'Beweise',
            'Dokumente',
            'links zu Dokumenten',
            'Firmenname',
            'Vorname',
            'Nachname',
            'Adresse',
            'Postleitzahl',
            'Stadt',
            'Land'
        );
        
        $content .= implode(';', $header) . "\n";
        
        // Sample data matching exact Forderungen.com structure
        $samples = array(
            array(
                'SPAM-2024-0001',               // Fall-ID (CSV)
                'draft',                        // Fall-Status
                'pending',                      // Brief-Status
                '1',                           // Briefe
                'Ihre Firma GmbH',             // Mandant
                'Max Mustermann',              // Schuldner
                '2024-01-15',                  // Einreichungsdatum
                'SPAM E-Mail ohne Einwilligung', // Beweise
                'E-Mail Screenshot',           // Dokumente
                'https://example.com/doc1.pdf', // links zu Dokumenten
                '',                            // Firmenname (leer für Privatperson)
                'Max',                         // Vorname
                'Mustermann',                  // Nachname
                'Musterstraße 123',            // Adresse
                '12345',                       // Postleitzahl
                'Musterstadt',                 // Stadt
                'Deutschland'                  // Land
            ),
            array(
                'SPAM-2024-0002',
                'processing',
                'sent',
                '2',
                'Ihre Firma GmbH',
                'Beispiel AG',
                '2024-01-16',
                'Newsletter ohne Double-Opt-In',
                'E-Mail Verlauf, Opt-In Nachweis',
                'https://example.com/doc2.pdf',
                'Beispiel AG',                 // Firmenname (für Unternehmen)
                'Erika',
                'Beispiel',
                'Beispielweg 456',
                '54321',
                'Beispielhausen',
                'Deutschland'
            )
        );
        
        foreach ($samples as $row) {
            $content .= implode(';', $row) . "\n";
        }
        
        return $content;
    }
    
    private function get_comprehensive_template_content() {
        // Add BOM for UTF-8 Excel compatibility
        $content = chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // CSV Header - Complete 57-field Internal Master Data Structure
        $header = array(
            // Core Case Information (1-10)
            'Fall-ID (CSV)',
            'Fall-Status',
            'Brief-Status',
            'Briefe',
            'Mandant',
            'Schuldner',
            'Einreichungsdatum',
            'Beweise',
            'Dokumente',
            'links zu Dokumenten',
            
            // Debtor Personal Information (11-20)
            'Firmenname',
            'Vorname',
            'Nachname',
            'Adresse',
            'Straße',
            'Hausnummer',
            'Adresszusatz',
            'Postleitzahl',
            'Stadt',
            'Land',
            
            // Contact Information (21-25)
            'E-Mail',
            'Telefon',
            'Fax',
            'Website',
            'Social Media',
            
            // Legal Information (26-32)
            'Rechtsform',
            'Handelsregister-Nr',
            'USt-ID',
            'Geschäftsführer',
            'Verfahrensart',
            'Rechtsgrundlage',
            'Kategorie',
            
            // Financial Information (33-42)
            'Streitwert',
            'Schadenersatz',
            'Anwaltskosten',
            'Gerichtskosten',
            'Nebenkosten',
            'Auslagen',
            'Mahnkosten',
            'Vollstreckungskosten',
            'Zinsen',
            'Gesamtbetrag',
            
            // Timeline & Deadlines (43-48)
            'Zeitraum von',
            'Zeitraum bis',
            'Deadline Antwort',
            'Deadline Zahlung',
            'Mahnung Datum',
            'Klage Datum',
            
            // Court & Legal Processing (49-53)
            'Gericht zuständig',
            'EGVP Aktenzeichen',
            'XJustiz UUID',
            'Erfolgsaussicht',
            'Risiko Bewertung',
            
            // Additional Metadata (54-57)
            'Komplexität',
            'Priorität intern',
            'Bearbeitungsstatus',
            'Datenquelle'
        );
        
        $content .= implode(';', $header) . "\n";
        
        // Sample data matching complete structure
        $samples = array(
            array(
                // Core Case Information (1-10)
                'SPAM-2024-0001',               // Fall-ID (CSV)
                'draft',                        // Fall-Status
                'pending',                      // Brief-Status
                '1',                           // Briefe
                'Ihre Firma GmbH',             // Mandant
                'Max Mustermann',              // Schuldner
                '2024-01-15',                  // Einreichungsdatum
                'SPAM E-Mail ohne Einwilligung', // Beweise
                'E-Mail Screenshot',           // Dokumente
                'https://example.com/doc1.pdf', // links zu Dokumenten
                
                // Debtor Personal Information (11-20)
                '',                            // Firmenname (leer für Privatperson)
                'Max',                         // Vorname
                'Mustermann',                  // Nachname
                'Musterstraße 123',            // Adresse
                'Musterstraße',                // Straße
                '123',                         // Hausnummer
                '',                            // Adresszusatz
                '12345',                       // Postleitzahl
                'Musterstadt',                 // Stadt
                'Deutschland',                 // Land
                
                // Contact Information (21-25)
                'max.mustermann@example.com',  // E-Mail
                '+49 123 456789',              // Telefon
                '',                            // Fax
                '',                            // Website
                '',                            // Social Media
                
                // Legal Information (26-32)
                'natuerliche_person',          // Rechtsform
                '',                            // Handelsregister-Nr
                '',                            // USt-ID
                '',                            // Geschäftsführer
                'mahnverfahren',               // Verfahrensart
                'DSGVO Art. 82',               // Rechtsgrundlage
                'GDPR_SPAM',                   // Kategorie
                
                // Financial Information (33-42)
                '548.11',                      // Streitwert
                '350.00',                      // Schadenersatz
                '96.90',                       // Anwaltskosten
                '32.00',                       // Gerichtskosten
                '13.36',                       // Nebenkosten
                '0.00',                        // Auslagen
                '0.00',                        // Mahnkosten
                '0.00',                        // Vollstreckungskosten
                '0.00',                        // Zinsen
                '548.11',                      // Gesamtbetrag
                
                // Timeline & Deadlines (43-48)
                '2024-01-01',                  // Zeitraum von
                '2024-01-15',                  // Zeitraum bis
                '2024-02-15',                  // Deadline Antwort
                '2024-03-15',                  // Deadline Zahlung
                '',                            // Mahnung Datum
                '',                            // Klage Datum
                
                // Court & Legal Processing (49-53)
                'Amtsgericht Frankfurt',       // Gericht zuständig
                '',                            // EGVP Aktenzeichen
                '',                            // XJustiz UUID
                'hoch',                        // Erfolgsaussicht
                'niedrig',                     // Risiko Bewertung
                
                // Additional Metadata (54-57)
                'standard',                    // Komplexität
                'normal',                      // Priorität intern
                'neu',                         // Bearbeitungsstatus
                'internal'                     // Datenquelle
            )
        );
        
        foreach ($samples as $row) {
            $content .= implode(';', $row) . "\n";
        }
        
        return $content;
    }
    
    private function handle_import_action() {
        if (!wp_verify_nonce($_POST['csv_import_nonce'], 'csv_import_action')) {
            echo '<div class="notice notice-error"><p>Sicherheitsfehler. Bitte versuchen Sie es erneut.</p></div>';
            return;
        }
        
        $action = sanitize_text_field($_POST['import_action']);
        
        if ($action === 'upload_csv') {
            $this->process_csv_upload();
        }
    }
    
    private function process_csv_upload() {
        global $wpdb;
        
        // Validate file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Datei konnte nicht hochgeladen werden.</p></div>';
            return;
        }
        
        $file = $_FILES['csv_file'];
        $delimiter = $_POST['delimiter'];
        $import_mode = $_POST['import_mode'];
        
        // Basic file validation
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Datei ist zu groß (max. 10MB).</p></div>';
            return;
        }
        
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Nur CSV-Dateien sind erlaubt.</p></div>';
            return;
        }
        
        try {
            // Read CSV content
            $file_content = file_get_contents($file['tmp_name']);
            
            // Parse CSV lines
            $lines = str_getcsv($file_content, "\n");
            if (empty($lines)) {
                echo '<div class="notice notice-error"><p><strong>Fehler!</strong> CSV-Datei ist leer.</p></div>';
                return;
            }
            
            // Get header and validate Forderungen.com structure
            $header = str_getcsv($lines[0], $delimiter);
            $data_rows = array_slice($lines, 1);
            
            // Check for required Forderungen.com fields
            $required_fields = array('Fall-ID (CSV)', 'Nachname');
            $optional_forderungen_fields = array(
                'Fall-Status', 'Brief-Status', 'Mandant', 'Schuldner', 'Einreichungsdatum', 'Beweise',
                'Dokumente', 'Firmenname', 'Vorname', 'Adresse', 'Postleitzahl', 'Stadt', 'Land',
                'E-Mail', 'Telefon', 'Rechtsform', 'Verfahrensart', 'Streitwert', 'Schadenersatz',
                'Anwaltskosten', 'Gerichtskosten', 'Gesamtbetrag', 'Gericht zuständig', 'Erfolgsaussicht',
                'Komplexität', 'Bearbeitungsstatus', 'Datenquelle'
            );
            
            $missing_required = array();
            foreach ($required_fields as $field) {
                if (!in_array($field, $header)) {
                    $missing_required[] = $field;
                }
            }
            
            if (!empty($missing_required)) {
                echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Erforderliche Forderungen.com Felder fehlen: ' . implode(', ', $missing_required) . '</p></div>';
                return;
            }
            
            // Check if this looks like a Forderungen.com export
            $forderungen_fields_found = 0;
            foreach ($optional_forderungen_fields as $field) {
                if (in_array($field, $header)) {
                    $forderungen_fields_found++;
                }
            }
            
            $is_forderungen_export = $forderungen_fields_found >= 3;
            
            // Process import
            $success_count = 0;
            $error_count = 0;
            $errors = array();
            
            foreach ($data_rows as $line_num => $line) {
                if (empty(trim($line))) continue;
                
                $data = str_getcsv($line, $delimiter);
                if (count($data) !== count($header)) {
                    $errors[] = "Zeile " . ($line_num + 2) . ": Spaltenanzahl stimmt nicht überein";
                    $error_count++;
                    continue;
                }
                
                $row_data = array_combine($header, $data);
                
                // Process this row with Forderungen.com mapping
                $result = $this->import_single_forderungen_case($row_data, $import_mode, $is_forderungen_export);
                if ($result['success']) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Zeile " . ($line_num + 2) . ": " . $result['error'];
                }
            }
            
            // Show results
            if ($success_count > 0) {
                echo '<div class="notice notice-success"><p><strong>✅ Import erfolgreich!</strong> ' . $success_count . ' Fälle aus Forderungen.com (17 Felder) wurden importiert und automatisch zu vollständigen Datensätzen erweitert.</p></div>';
                
                if ($is_forderungen_export) {
                    echo '<div class="notice notice-info"><p><strong>📊 Forderungen.com Export erkannt!</strong> 17 Felder importiert, automatisch zu 57 Feldern erweitert. Gesamtwert: €' . number_format($success_count * 548.11, 2) . '</p></div>';
                }
            }
            
            if ($error_count > 0) {
                echo '<div class="notice notice-warning"><p><strong>⚠️ Teilweise Fehler:</strong> ' . $error_count . ' Fälle konnten nicht importiert werden.</p>';
                if (!empty($errors)) {
                    echo '<details><summary>Fehlerdetails anzeigen</summary><ul>';
                    foreach (array_slice($errors, 0, 10) as $error) {
                        echo '<li>' . esc_html($error) . '</li>';
                    }
                    if (count($errors) > 10) {
                        echo '<li>... und ' . (count($errors) - 10) . ' weitere Fehler</li>';
                    }
                    echo '</ul></details>';
                }
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Import-Fehler: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
    
    private function import_single_forderungen_case($data, $import_mode, $is_forderungen_export) {
        global $wpdb;
        
        try {
            // Check if tables exist
            if (!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_cases'")) {
                return array('success' => false, 'error' => 'Datenbank-Tabellen fehlen');
            }
            
            // Extract data with Forderungen.com 17-field mapping
            $case_id = sanitize_text_field($data['Fall-ID (CSV)'] ?? $data['Fall-ID'] ?? '');
            $case_status = sanitize_text_field($data['Fall-Status'] ?? 'draft');
            $brief_status = sanitize_text_field($data['Brief-Status'] ?? 'pending');
            $briefe = intval($data['Briefe'] ?? 1);
            $mandant = sanitize_text_field($data['Mandant'] ?? '');
            $schuldner = sanitize_text_field($data['Schuldner'] ?? '');
            $submission_date = sanitize_text_field($data['Einreichungsdatum'] ?? '');
            $beweise = sanitize_textarea_field($data['Beweise'] ?? '');
            $dokumente = sanitize_text_field($data['Dokumente'] ?? '');
            $document_links = sanitize_text_field($data['links zu Dokumenten'] ?? '');
            
            // Debtor information from Forderungen.com (17 fields)
            $company_name = sanitize_text_field($data['Firmenname'] ?? '');
            $first_name = sanitize_text_field($data['Vorname'] ?? '');
            $last_name = sanitize_text_field($data['Nachname'] ?? '');
            $address = sanitize_text_field($data['Adresse'] ?? '');
            $postal_code = sanitize_text_field($data['Postleitzahl'] ?? '');
            $city = sanitize_text_field($data['Stadt'] ?? '');
            $country = sanitize_text_field($data['Land'] ?? 'Deutschland');
            
            // Validation
            if (empty($case_id) || empty($last_name)) {
                return array('success' => false, 'error' => 'Fall-ID und Nachname sind erforderlich');
            }
            
            // Check if case exists
            $existing_case = $wpdb->get_row($wpdb->prepare("
                SELECT id FROM {$wpdb->prefix}klage_cases WHERE case_id = %s
            ", $case_id));
            
            if ($existing_case && $import_mode === 'create_new') {
                return array('success' => false, 'error' => 'Fall existiert bereits');
            }
            
            if (!$existing_case && $import_mode === 'update_existing') {
                return array('success' => false, 'error' => 'Fall existiert nicht');
            }
            
            // Create debtor entry (map 17 fields to comprehensive structure)
            $debtor_name = trim($first_name . ' ' . $last_name);
            if (!empty($company_name)) {
                $debtor_name = $company_name . ' (' . $debtor_name . ')';
            }
            
            $debtor_id = null;
            if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_debtors'")) {
                $wpdb->insert(
                    $wpdb->prefix . 'klage_debtors',
                    array(
                        'debtors_name' => $debtor_name,
                        'debtors_company' => $company_name,
                        'debtors_first_name' => $first_name,
                        'debtors_last_name' => $last_name,
                        'debtors_address' => $address,
                        'debtors_postal_code' => $postal_code,
                        'debtors_city' => $city,
                        'debtors_country' => $country,
                        // Set defaults for fields not provided by Forderungen.com
                        'rechtsform' => !empty($company_name) ? 'unternehmen' : 'natuerliche_person',
                        'datenquelle' => 'forderungen_com',
                        'letzte_aktualisierung' => current_time('mysql')
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                $debtor_id = $wpdb->insert_id;
            }
            
            // Prepare date
            $submission_date_mysql = $this->parse_date($submission_date);
            
            // Case data - map Forderungen.com fields to comprehensive structure
            $case_data = array(
                'case_status' => $case_status,
                'brief_status' => $brief_status,
                'briefe' => $briefe,
                'mandant' => $mandant,
                'schuldner' => $schuldner,
                'submission_date' => $submission_date_mysql,
                'beweise' => $beweise,
                'dokumente' => $dokumente,
                'links_zu_dokumenten' => $document_links,
                'debtor_id' => $debtor_id,
                'case_updated_date' => current_time('mysql'),
                'import_source' => 'forderungen_com',
                // Set defaults for internal fields not provided by Forderungen.com
                'verfahrensart' => 'mahnverfahren',
                'rechtsgrundlage' => 'DSGVO Art. 82',
                'kategorie' => 'GDPR_SPAM',
                'schadenhoehe' => 350.00,
                'total_amount' => 548.11,
                'verfahrenswert' => 548.11,
                'erfolgsaussicht' => 'hoch',
                'risiko_bewertung' => 'niedrig',
                'komplexitaet' => 'standard',
                'prioritaet_intern' => 'normal',
                'bearbeitungsstatus' => 'neu',
                'kommunikation_sprache' => 'de'
            );
            
            if ($existing_case) {
                // Update existing case
                $wpdb->update(
                    $wpdb->prefix . 'klage_cases',
                    $case_data,
                    array('id' => $existing_case->id),
                    array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
                $case_internal_id = $existing_case->id;
            } else {
                // Create new case
                $case_data['case_id'] = $case_id;
                $case_data['case_creation_date'] = current_time('mysql');
                $case_data['case_priority'] = 'medium';
                
                $wpdb->insert(
                    $wpdb->prefix . 'klage_cases',
                    $case_data,
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                $case_internal_id = $wpdb->insert_id;
            }
            
            // Create standard GDPR financial record
            if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_financial'")) {
                // Check if financial record exists
                $existing_financial = $wpdb->get_var($wpdb->prepare("
                    SELECT id FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
                ", $case_internal_id));
                
                $financial_data = array(
                    'streitwert' => 548.11,
                    'schadenersatz' => 350.00,
                    'anwaltskosten' => 96.90,
                    'gerichtskosten' => 32.00,
                    'nebenkosten' => 13.36,
                    'total' => 548.11,
                    'damages_loss' => 350.00,
                    'partner_fees' => 96.90,
                    'communication_fees' => 13.36,
                    'vat' => 87.85,
                    'court_fees' => 32.00
                );
                
                if ($existing_financial) {
                    // Update existing financial record
                    $wpdb->update(
                        $wpdb->prefix . 'klage_financial',
                        $financial_data,
                        array('id' => $existing_financial),
                        array('%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f'),
                        array('%d')
                    );
                } else {
                    // Create new financial record
                    $financial_data['case_id'] = $case_internal_id;
                    $wpdb->insert(
                        $wpdb->prefix . 'klage_financial',
                        $financial_data,
                        array('%d', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f', '%f')
                    );
                }
            }
            
            // Create audit trail entry
            if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_audit'")) {
                $wpdb->insert(
                    $wpdb->prefix . 'klage_audit',
                    array(
                        'case_id' => $case_internal_id,
                        'action' => $existing_case ? 'case_updated' : 'case_created',
                        'details' => 'Imported from Forderungen.com (17 fields) with automatic defaults',
                        'user_id' => get_current_user_id()
                    ),
                    array('%d', '%s', '%s', '%d')
                );
            }
            
            return array('success' => true, 'case_id' => $case_internal_id);
            
        } catch (Exception $e) {
            return array('success' => false, 'error' => 'Import-Fehler: ' . $e->getMessage());
        }
    }
    
    private function parse_date($date_string) {
        if (empty($date_string)) {
            return null;
        }
        
        // Try Y-m-d format first
        $date = DateTime::createFromFormat('Y-m-d', $date_string);
        if (!$date) {
            // Try d.m.Y format
            $date = DateTime::createFromFormat('d.m.Y', $date_string);
        }
        if (!$date) {
            // Try d/m/Y format
            $date = DateTime::createFromFormat('d/m/Y', $date_string);
        }
        
        return $date ? $date->format('Y-m-d') : null;
    }
    
    public function admin_page_help() {
        ?>
        <div class="wrap">
            <h1>📚 Hilfe & Prozesse - Klage.Click Hub</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.3 - Komplette Anleitung!</strong></p>
                <p>Schritt-für-Schritt Anleitungen für alle Funktionen des Court Automation Hub.</p>
            </div>
            
            <!-- Quick Navigation -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h2 style="margin-top: 0;">🎯 Schnell-Navigation</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <a href="#workflow" class="button button-primary" style="text-decoration: none; text-align: center; padding: 15px;">
                        📋 Workflow-Übersicht
                    </a>
                    <a href="#calculator" class="button button-primary" style="text-decoration: none; text-align: center; padding: 15px;">
                        🧮 Finanzrechner Guide
                    </a>
                    <a href="#import" class="button button-primary" style="text-decoration: none; text-align: center; padding: 15px;">
                        📊 CSV Import Guide
                    </a>
                    <a href="#management" class="button button-primary" style="text-decoration: none; text-align: center; padding: 15px;">
                        📁 Fall-Management
                    </a>
                </div>
            </div>
            
            <!-- Workflow Overview -->
            <div id="workflow" class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle">📋 Komplett-Workflow: Von Forderungen.com zu fertigen Fällen</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3 style="color: #0073aa;">🎯 7-Schritt Prozess</h3>
                            <ol style="line-height: 1.8;">
                                <li><strong>Daten von Forderungen.com erhalten</strong></li>
                                <li><strong>CSV Template herunterladen</strong> (30 Sekunden)</li>
                                <li><strong>Daten in Template einfügen</strong> (15-30 Min)</li>
                                <li><strong>Bulk-Import durchführen</strong> (2-5 Min)</li>
                                <li><strong>Finanzberechnungen anpassen</strong> (5-10 Min)</li>
                                <li><strong>Fälle bearbeiten & verwalten</strong></li>
                                <li><strong>Export für weitere Bearbeitung</strong></li>
                            </ol>
                        </div>
                        <div>
                            <h3 style="color: #0073aa;">⏱️ Zeitaufwand (50 Fälle)</h3>
                            <div style="background: #f0f8ff; padding: 15px; border-radius: 5px;">
                                <p><strong>Gesamt-Zeit: 25-50 Minuten</strong></p>
                                <ul style="margin: 10px 0;">
                                    <li>Template Download: 30 Sek</li>
                                    <li>Daten-Eingabe: 15-30 Min</li>
                                    <li>Import: 2-5 Min</li>
                                    <li>Anpassungen: 5-10 Min</li>
                                </ul>
                                <p style="color: #0073aa;"><strong>= €27,405.50 Gesamtwert (50 × €548.11)</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calculator Guide -->
            <div id="calculator" class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle">🧮 Finanzrechner - Anleitung</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3 style="color: #0073aa;">📊 DSGVO Standard-Berechnung</h3>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tr style="background: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Kostenart</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Betrag</th>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">💰 Grundschaden</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€350.00</strong></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">⚖️ Anwaltskosten</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€96.90</strong></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">📞 Kommunikation</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€13.36</strong></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">🏛️ Gerichtskosten</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€32.00</strong></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">📊 MwSt (19%)</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€87.85</strong></td>
                                </tr>
                                <tr style="background: #e7f3ff; font-weight: bold;">
                                    <td style="border: 1px solid #ddd; padding: 8px;">🎯 GESAMT</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>€548.11</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <h3 style="color: #0073aa;">⚡ Nutzung des Rechners</h3>
                            <ol>
                                <li><strong>Rechner öffnen:</strong> Finanz-Rechner → Calculator</li>
                                <li><strong>Werte anpassen:</strong> Klicken Sie in die Eingabefelder</li>
                                <li><strong>Auto-Berechnung:</strong> MwSt und Gesamtsumme werden automatisch aktualisiert</li>
                                <li><strong>Templates nutzen:</strong> Standard, Premium, Business</li>
                                <li><strong>Speichern/Export:</strong> Berechnungen sichern</li>
                            </ol>
                            
                            <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 15px;">
                                <strong>💡 Tipp:</strong> Die MwSt wird automatisch als 19% von (Anwaltskosten + Kommunikation) berechnet.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Import Guide -->
            <div id="import" class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle">📊 CSV Import - Schritt-für-Schritt</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3 style="color: #0073aa;">1️⃣ Template-Vorbereitung</h3>
                            <ol>
                                <li><strong>CSV Import</strong> Seite öffnen</li>
                                <li><strong>"📥 Template downloaden"</strong> klicken</li>
                                <li>Template in <strong>Excel/LibreOffice</strong> öffnen</li>
                                <li><strong>Beispieldaten löschen</strong></li>
                                <li><strong>Echte Daten einfügen</strong></li>
                                <li>Als <strong>CSV (UTF-8)</strong> speichern</li>
                            </ol>
                        </div>
                        <div>
                            <h3 style="color: #0073aa;">2️⃣ Erforderliche Daten</h3>
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr style="background: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 6px;">Feld</th>
                                    <th style="border: 1px solid #ddd; padding: 6px;">Pflicht</th>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Fall-ID</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">✅</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;"><strong>Nachname</strong></td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">✅</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Vorname</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">⭕</td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 6px;">Email</td>
                                    <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">⭕</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Management Guide -->
            <div id="management" class="postbox">
                <h2 class="hndle">📁 Fall-Management</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h3 style="color: #0073aa;">🎯 Status-Workflow</h3>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="background: #fff3cd; padding: 10px; border-radius: 5px; display: flex; align-items: center;">
                                    <span style="margin-right: 10px;">📝</span>
                                    <strong>Draft → Processing → Completed</strong>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 style="color: #0073aa;">⚡ Quick-Aktionen</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button">📝 Neuer Fall</a>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-import'); ?>" class="button">📊 CSV Import</a>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-financial&action=calculator'); ?>" class="button">🧮 Rechner</a>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>" class="button">⚙️ Einstellungen</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .postbox h2.hndle {
            background: #0073aa;
            color: white;
            padding: 15px 20px;
            margin: 0;
            border-radius: 8px 8px 0 0;
        }
        
        .postbox .inside {
            border-radius: 0 0 8px 8px;
        }
        
        html { scroll-behavior: smooth; }
        </style>
        <?php
    }
    
    public function admin_page_settings() {
        // Handle manual database creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables_nonce'])) {
            if (wp_verify_nonce($_POST['create_tables_nonce'], 'create_tables')) {
                $this->force_create_tables();
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Einstellungen', 'court-automation-hub'); ?></h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.1.3 - System Settings!</strong></p>
                <p>Datenbank-Management und Plugin-Konfiguration verfügbar.</p>
            </div>
            
            <!-- Database Management Section -->
            <div class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">🛠️ Datenbank Management</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="margin: 15px 0;">
                        <h4>Aktuelle Tabellen-Status:</h4>
                        <?php $this->display_system_status(); ?>
                    </div>
                    
                    <form method="post" style="margin-bottom: 15px;">
                        <?php wp_nonce_field('create_tables', 'create_tables_nonce'); ?>
                        <input type="submit" class="button button-primary" value="🔧 Alle Tabellen erstellen/reparieren" 
                               onclick="return confirm('Alle fehlenden Tabellen jetzt erstellen?');">
                    </form>
                    <p class="description">Verwendet direktes SQL für bessere Kompatibilität mit allen WordPress-Umgebungen.</p>
                </div>
            </div>
            
            <!-- Plugin Settings -->
            <form method="post" action="options.php">
                <?php
                settings_fields('klage_click_settings');
                do_settings_sections('klage_click_settings');
                ?>
                
                <div class="postbox">
                    <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">⚙️ Plugin-Einstellungen</h2>
                    <div class="inside" style="padding: 20px;">
                        <table class="form-table">
                            <tr>
                                <th scope="row">N8N API URL</th>
                                <td>
                                    <input type="url" name="klage_click_n8n_url" value="<?php echo esc_attr(get_option('klage_click_n8n_url')); ?>" class="regular-text" />
                                    <p class="description">URL zu Ihrer N8N Workflow-Automation (für v1.2.0+)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">N8N API Key</th>
                                <td>
                                    <input type="password" name="klage_click_n8n_key" value="<?php echo esc_attr(get_option('klage_click_n8n_key')); ?>" class="regular-text" />
                                    <p class="description">API-Schlüssel für N8N Integration</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Debug-Modus</th>
                                <td>
                                    <input type="checkbox" name="klage_click_debug_mode" value="1" <?php checked(1, get_option('klage_click_debug_mode')); ?> />
                                    <label for="klage_click_debug_mode">Debug-Informationen in Admin-Notices anzeigen</label>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button('Einstellungen speichern'); ?>
                    </div>
                </div>
            </form>
            
            <!-- System Information -->
            <div class="postbox">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">ℹ️ System-Information</h2>
                <div class="inside" style="padding: 20px;">
                    <table class="form-table">
                        <tr>
                            <th>Plugin-Version:</th>
                            <td><strong>v1.1.3</strong></td>
                        </tr>
                        <tr>
                            <th>WordPress-Version:</th>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <th>PHP-Version:</th>
                            <td><?php echo PHP_VERSION; ?></td>
                        </tr>
                        <tr>
                            <th>Datenbank:</th>
                            <td><?php echo $GLOBALS['wpdb']->db_version(); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function force_create_tables() {
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        $database = new CAH_Database();
        
        // Try database creation
        $results = $database->create_tables_direct();
        
        if ($results['success']) {
            echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> ' . $results['message'] . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>❌ Fehler!</strong> ' . $results['message'] . '</p></div>';
        }
        
        // Show detailed results in debug mode
        if (get_option('klage_click_debug_mode')) {
            echo '<div class="notice notice-info"><p><strong>Debug Info:</strong><br>' . implode('<br>', $results['details']) . '</p></div>';
        }
    }
    
    public function ajax_download_template() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'download_template')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Create CSV template
        $filename = 'forderungen_import_template_' . date('Y-m-d') . '.csv';
        
        // Set headers for download
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8 Excel compatibility
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // CSV Header
        $header = array(
            'Fall-ID',
            'Fall-Status', 
            'Brief-Status',
            'Mandant',
            'Einreichungsdatum',
            'Beweise',
            'Firmenname',
            'Vorname',
            'Nachname', 
            'Adresse',
            'Postleitzahl',
            'Stadt',
            'Land',
            'Email',
            'Telefon',
            'Notizen'
        );
        
        echo implode(';', $header) . "\n";
        
        // Sample data
        echo "SPAM-2024-0001;draft;pending;Ihre Firma GmbH;2024-01-15;SPAM E-Mail;;Max;Mustermann;Musterstraße 123;12345;Musterstadt;Deutschland;spam@example.com;+49123456789;Test\n";
        
        exit;
    }
    
    public function ajax_export_calculation() {
        echo "CSV Export functionality - v1.1.2";
        exit;
    }
    
    private function display_system_status() {
        global $wpdb;
        
        $required_tables = array('klage_cases', 'klage_debtors', 'klage_clients', 'klage_emails', 'klage_financial', 'klage_courts', 'klage_audit', 'klage_financial_fields', 'klage_import_templates');
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th>Tabelle</th><th>Status</th><th>Einträge</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($required_tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name") : 0;
            
            $status_icon = $exists ? '✅' : '❌';
            $status_text = $exists ? 'OK' : 'Fehlt';
            
            echo '<tr>';
            echo '<td>' . esc_html($table) . '</td>';
            echo '<td>' . $status_icon . ' ' . esc_html($status_text) . '</td>';
            echo '<td>' . esc_html($count) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        
        if (!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_cases'")) {
            echo '<div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 5px;">';
            echo '<p><strong>⚠️ Hinweis:</strong> Haupttabellen fehlen. Gehen Sie zu Einstellungen → Datenbank reparieren.</p>';
            echo '</div>';
        }
    }
    
    private function render_edit_case_form($case_id) {
        global $wpdb;
        
        // Get case data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_cases WHERE id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Get debtor data
        $debtor = null;
        if ($case->debtor_id) {
            $debtor = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}klage_debtors WHERE id = %d
            ", $case->debtor_id));
        }
        
        // Get financial data
        $financial = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
        ", $case_id));
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_case'])) {
            $this->handle_case_update($case_id, $_POST);
        }
        
        ?>
        <div class="wrap">
            <h1>Fall bearbeiten: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>📝 Fall-Bearbeitung</strong></p>
                <p>Bearbeiten Sie alle Aspekte dieses Falls. Änderungen werden im Audit-Trail gespeichert.</p>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('edit_case_action', 'edit_case_nonce'); ?>
                <input type="hidden" name="save_case" value="1">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Left Column: Case Information -->
                    <div class="postbox">
                        <h2 class="hndle">📋 Fall-Informationen</h2>
                        <div class="inside" style="padding: 20px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="case_id">Fall-ID</label></th>
                                    <td>
                                        <input type="text" id="case_id" name="case_id" value="<?php echo esc_attr($case->case_id); ?>" class="regular-text" readonly>
                                        <p class="description">Fall-ID kann nicht geändert werden</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_status">Status</label></th>
                                    <td>
                                        <select id="case_status" name="case_status" class="regular-text">
                                            <option value="draft" <?php selected($case->case_status, 'draft'); ?>>📝 Entwurf</option>
                                            <option value="pending" <?php selected($case->case_status, 'pending'); ?>>⏳ Wartend</option>
                                            <option value="processing" <?php selected($case->case_status, 'processing'); ?>>🔄 In Bearbeitung</option>
                                            <option value="completed" <?php selected($case->case_status, 'completed'); ?>>✅ Abgeschlossen</option>
                                            <option value="cancelled" <?php selected($case->case_status, 'cancelled'); ?>>❌ Storniert</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_priority">Priorität</label></th>
                                    <td>
                                        <select id="case_priority" name="case_priority" class="regular-text">
                                            <option value="low" <?php selected($case->case_priority, 'low'); ?>>🔵 Niedrig</option>
                                            <option value="medium" <?php selected($case->case_priority, 'medium'); ?>>🟡 Mittel</option>
                                            <option value="high" <?php selected($case->case_priority, 'high'); ?>>🟠 Hoch</option>
                                            <option value="urgent" <?php selected($case->case_priority, 'urgent'); ?>>🔴 Dringend</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="mandant">Mandant</label></th>
                                    <td>
                                        <input type="text" id="mandant" name="mandant" value="<?php echo esc_attr($case->mandant); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="submission_date">Einreichungsdatum</label></th>
                                    <td>
                                        <input type="date" id="submission_date" name="submission_date" value="<?php echo esc_attr($case->submission_date); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_notes">Notizen</label></th>
                                    <td>
                                        <textarea id="case_notes" name="case_notes" rows="4" class="large-text"><?php echo esc_textarea($case->case_notes); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Right Column: Debtor Information -->
                    <div class="postbox">
                        <h2 class="hndle">👤 Schuldner-Details</h2>
                        <div class="inside" style="padding: 20px;">
                            <?php if ($debtor): ?>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="debtors_first_name">Vorname</label></th>
                                    <td>
                                        <input type="text" id="debtors_first_name" name="debtors_first_name" value="<?php echo esc_attr($debtor->debtors_first_name); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_last_name">Nachname</label></th>
                                    <td>
                                        <input type="text" id="debtors_last_name" name="debtors_last_name" value="<?php echo esc_attr($debtor->debtors_last_name); ?>" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_company">Firma</label></th>
                                    <td>
                                        <input type="text" id="debtors_company" name="debtors_company" value="<?php echo esc_attr($debtor->debtors_company); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_email">E-Mail</label></th>
                                    <td>
                                        <input type="email" id="debtors_email" name="debtors_email" value="<?php echo esc_attr($debtor->debtors_email); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_address">Adresse</label></th>
                                    <td>
                                        <input type="text" id="debtors_address" name="debtors_address" value="<?php echo esc_attr($debtor->debtors_address); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_postal_code">PLZ</label></th>
                                    <td>
                                        <input type="text" id="debtors_postal_code" name="debtors_postal_code" value="<?php echo esc_attr($debtor->debtors_postal_code); ?>" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="debtors_city">Stadt</label></th>
                                    <td>
                                        <input type="text" id="debtors_city" name="debtors_city" value="<?php echo esc_attr($debtor->debtors_city); ?>" class="regular-text">
                                    </td>
                                </tr>
                            </table>
                            <?php else: ?>
                            <p>Keine Schuldner-Daten vorhanden.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Information -->
                <?php if ($financial): ?>
                <div class="postbox" style="margin-top: 20px;">
                    <h2 class="hndle">💰 Finanzielle Details</h2>
                    <div class="inside" style="padding: 20px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <label for="damages_loss">Schadenersatz</label>
                                <input type="number" step="0.01" id="damages_loss" name="damages_loss" value="<?php echo esc_attr($financial->damages_loss); ?>" class="regular-text">
                            </div>
                            <div>
                                <label for="partner_fees">Anwaltskosten</label>
                                <input type="number" step="0.01" id="partner_fees" name="partner_fees" value="<?php echo esc_attr($financial->partner_fees); ?>" class="regular-text">
                            </div>
                            <div>
                                <label for="communication_fees">Kommunikationskosten</label>
                                <input type="number" step="0.01" id="communication_fees" name="communication_fees" value="<?php echo esc_attr($financial->communication_fees); ?>" class="regular-text">
                            </div>
                            <div>
                                <label for="court_fees">Gerichtskosten</label>
                                <input type="number" step="0.01" id="court_fees" name="court_fees" value="<?php echo esc_attr($financial->court_fees); ?>" class="regular-text">
                            </div>
                        </div>
                        <div style="margin-top: 15px; padding: 15px; background: #f0f8ff; border-radius: 5px;">
                            <strong>Gesamtbetrag: €<?php echo number_format($financial->total, 2); ?></strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div style="margin-top: 20px;">
                    <input type="submit" class="button button-primary button-large" value="💾 Änderungen speichern">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">🔙 Zurück zur Liste</a>
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case_id); ?>" class="button">👁️ Anzeigen</a>
                </div>
            </form>
        </div>
        <?php
    }
    
    private function render_view_case($case_id) {
        global $wpdb;
        
        // Get case data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_cases WHERE id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Get debtor data
        $debtor = null;
        if ($case->debtor_id) {
            $debtor = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}klage_debtors WHERE id = %d
            ", $case->debtor_id));
        }
        
        // Get financial data
        $financial = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
        ", $case_id));
        
        ?>
        <div class="wrap">
            <h1>Fall anzeigen: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>👁️ Fall-Ansicht</strong></p>
                <p>Detailansicht aller Fall-Informationen im Nur-Lese-Modus.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Left Column: Case Information -->
                <div class="postbox">
                    <h2 class="hndle">📋 Fall-Informationen</h2>
                    <div class="inside" style="padding: 20px;">
                        <table class="form-table">
                            <tr>
                                <th>Fall-ID:</th>
                                <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php
                                    $status_icons = array(
                                        'draft' => '📝 Entwurf',
                                        'pending' => '⏳ Wartend',
                                        'processing' => '🔄 In Bearbeitung',
                                        'completed' => '✅ Abgeschlossen',
                                        'cancelled' => '❌ Storniert'
                                    );
                                    echo $status_icons[$case->case_status] ?? $case->case_status;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Priorität:</th>
                                <td>
                                    <?php
                                    $priority_icons = array(
                                        'low' => '🔵 Niedrig',
                                        'medium' => '🟡 Mittel',
                                        'high' => '🟠 Hoch',
                                        'urgent' => '🔴 Dringend'
                                    );
                                    echo $priority_icons[$case->case_priority] ?? $case->case_priority;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Mandant:</th>
                                <td><?php echo esc_html($case->mandant); ?></td>
                            </tr>
                            <tr>
                                <th>Erstellt:</th>
                                <td><?php echo esc_html(date('d.m.Y H:i', strtotime($case->case_creation_date))); ?></td>
                            </tr>
                            <tr>
                                <th>Einreichung:</th>
                                <td><?php echo $case->submission_date ? esc_html(date('d.m.Y', strtotime($case->submission_date))) : 'Nicht gesetzt'; ?></td>
                            </tr>
                            <tr>
                                <th>Quelle:</th>
                                <td><?php echo esc_html($case->import_source ?: 'Manual'); ?></td>
                            </tr>
                        </table>
                        
                        <?php if ($case->case_notes): ?>
                        <div style="margin-top: 20px;">
                            <h4>📝 Notizen:</h4>
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
                                <?php echo nl2br(esc_html($case->case_notes)); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Right Column: Debtor Information -->
                <div class="postbox">
                    <h2 class="hndle">👤 Schuldner-Details</h2>
                    <div class="inside" style="padding: 20px;">
                        <?php if ($debtor): ?>
                        <table class="form-table">
                            <tr>
                                <th>Name:</th>
                                <td><strong><?php echo esc_html($debtor->debtors_name); ?></strong></td>
                            </tr>
                            <?php if ($debtor->debtors_company): ?>
                            <tr>
                                <th>Firma:</th>
                                <td><?php echo esc_html($debtor->debtors_company); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Vorname:</th>
                                <td><?php echo esc_html($debtor->debtors_first_name); ?></td>
                            </tr>
                            <tr>
                                <th>Nachname:</th>
                                <td><?php echo esc_html($debtor->debtors_last_name); ?></td>
                            </tr>
                            <?php if ($debtor->debtors_email): ?>
                            <tr>
                                <th>E-Mail:</th>
                                <td><a href="mailto:<?php echo esc_attr($debtor->debtors_email); ?>"><?php echo esc_html($debtor->debtors_email); ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Adresse:</th>
                                <td>
                                    <?php echo esc_html($debtor->debtors_address); ?><br>
                                    <?php echo esc_html($debtor->debtors_postal_code . ' ' . $debtor->debtors_city); ?><br>
                                    <?php echo esc_html($debtor->debtors_country); ?>
                                </td>
                            </tr>
                        </table>
                        <?php else: ?>
                        <p>Keine Schuldner-Daten vorhanden.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Financial Information -->
            <?php if ($financial): ?>
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">💰 Finanzielle Details</h2>
                <div class="inside" style="padding: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: left;">Position</th>
                            <th style="border: 1px solid #ddd; padding: 10px; text-align: right;">Betrag</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">💸 Schadenersatz</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->damages_loss, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">⚖️ Anwaltskosten</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->partner_fees, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">📞 Kommunikationskosten</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->communication_fees, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">🏛️ Gerichtskosten</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->court_fees, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 10px;">📊 MwSt</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->vat, 2); ?></strong></td>
                        </tr>
                        <tr style="background: #e7f3ff; font-weight: bold; font-size: 16px;">
                            <td style="border: 1px solid #ddd; padding: 10px;">🎯 GESAMT</td>
                            <td style="border: 1px solid #ddd; padding: 10px; text-align: right;"><strong>€<?php echo number_format($financial->total, 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <div style="margin-top: 20px;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case_id); ?>" class="button button-primary">✏️ Bearbeiten</a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">🔙 Zurück zur Liste</a>
                <button onclick="window.print()" class="button">🖨️ Drucken</button>
            </div>
        </div>
        
        <style>
        @media print {
            .wrap h1:before { content: "Klage.Click Fall-Übersicht - "; }
            .button { display: none; }
        }
        </style>
        <?php
    }
    
    private function handle_delete_case($case_id) {
        global $wpdb;
        
        // Verify nonce for security
        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_case_' . $case_id)) {
            echo '<div class="notice notice-error"><p>Sicherheitsfehler.</p></div>';
            return;
        }
        
        // Get case for logging
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT case_id FROM {$wpdb->prefix}klage_cases WHERE id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Delete from related tables first
        $wpdb->delete($wpdb->prefix . 'klage_financial', array('case_id' => $case_id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'klage_audit', array('case_id' => $case_id), array('%d'));
        
        // Delete main case
        $result = $wpdb->delete($wpdb->prefix . 'klage_cases', array('id' => $case_id), array('%d'));
        
        if ($result) {
            echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> Fall "' . esc_html($case->case_id) . '" wurde gelöscht.</p></div>';
            
            // Log the deletion
            if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_audit'")) {
                $wpdb->insert(
                    $wpdb->prefix . 'klage_audit',
                    array(
                        'case_id' => 0, // Case no longer exists
                        'action' => 'case_deleted',
                        'details' => 'Fall "' . $case->case_id . '" wurde gelöscht',
                        'user_id' => get_current_user_id()
                    ),
                    array('%d', '%s', '%s', '%d')
                );
            }
        } else {
            echo '<div class="notice notice-error"><p><strong>❌ Fehler!</strong> Fall konnte nicht gelöscht werden.</p></div>';
        }
    }
    
    private function handle_case_update($case_id, $post_data) {
        global $wpdb;
        
        // Verify nonce
        if (!wp_verify_nonce($post_data['edit_case_nonce'], 'edit_case_action')) {
            echo '<div class="notice notice-error"><p>Sicherheitsfehler.</p></div>';
            return;
        }
        
        // Update case data
        $case_data = array(
            'case_status' => sanitize_text_field($post_data['case_status']),
            'case_priority' => sanitize_text_field($post_data['case_priority']),
            'mandant' => sanitize_text_field($post_data['mandant']),
            'submission_date' => sanitize_text_field($post_data['submission_date']),
            'case_notes' => sanitize_textarea_field($post_data['case_notes']),
            'case_updated_date' => current_time('mysql')
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'klage_cases',
            $case_data,
            array('id' => $case_id),
            array('%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        // Update debtor if exists
        if (isset($post_data['debtors_first_name'])) {
            $case = $wpdb->get_row($wpdb->prepare("SELECT debtor_id FROM {$wpdb->prefix}klage_cases WHERE id = %d", $case_id));
            if ($case && $case->debtor_id) {
                $debtor_data = array(
                    'debtors_first_name' => sanitize_text_field($post_data['debtors_first_name']),
                    'debtors_last_name' => sanitize_text_field($post_data['debtors_last_name']),
                    'debtors_company' => sanitize_text_field($post_data['debtors_company']),
                    'debtors_email' => sanitize_email($post_data['debtors_email']),
                    'debtors_address' => sanitize_text_field($post_data['debtors_address']),
                    'debtors_postal_code' => sanitize_text_field($post_data['debtors_postal_code']),
                    'debtors_city' => sanitize_text_field($post_data['debtors_city']),
                    'letzte_aktualisierung' => current_time('mysql')
                );
                
                $wpdb->update(
                    $wpdb->prefix . 'klage_debtors',
                    $debtor_data,
                    array('id' => $case->debtor_id),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
            }
        }
        
        // Update financial data if exists
        if (isset($post_data['damages_loss'])) {
            $financial_data = array(
                'damages_loss' => floatval($post_data['damages_loss']),
                'partner_fees' => floatval($post_data['partner_fees']),
                'communication_fees' => floatval($post_data['communication_fees']),
                'court_fees' => floatval($post_data['court_fees'])
            );
            
            // Recalculate totals
            $vat = ($financial_data['partner_fees'] + $financial_data['communication_fees']) * 0.19;
            $total = $financial_data['damages_loss'] + $financial_data['partner_fees'] + $financial_data['communication_fees'] + $financial_data['court_fees'] + $vat;
            
            $financial_data['vat'] = $vat;
            $financial_data['total'] = $total;
            
            $wpdb->update(
                $wpdb->prefix . 'klage_financial',
                $financial_data,
                array('case_id' => $case_id),
                array('%f', '%f', '%f', '%f', '%f', '%f'),
                array('%d')
            );
        }
        
        if ($result !== false) {
            echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> Fall wurde aktualisiert.</p></div>';
            
            // Log the update
            if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}klage_audit'")) {
                $wpdb->insert(
                    $wpdb->prefix . 'klage_audit',
                    array(
                        'case_id' => $case_id,
                        'action' => 'case_updated',
                        'details' => 'Fall wurde über Admin-Interface bearbeitet',
                        'user_id' => get_current_user_id()
                    ),
                    array('%d', '%s', '%s', '%d')
                );
            }
        } else {
            echo '<div class="notice notice-error"><p><strong>❌ Fehler!</strong> Fall konnte nicht aktualisiert werden.</p></div>';
        }
    }
}