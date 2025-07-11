<?php
/**
 * Admin Dashboard class - Simplified version
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
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
            __('Einstellungen', 'court-automation-hub'),
            __('Einstellungen', 'court-automation-hub'),
            'manage_options',
            'klage-click-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    public function admin_init() {
        register_setting('klage_click_settings', 'klage_click_n8n_url');
        register_setting('klage_click_settings', 'klage_click_n8n_key');
        register_setting('klage_click_settings', 'klage_click_debug_mode');
    }
    
    public function admin_page_dashboard() {
        global $wpdb;
        
        // Get statistics
        $total_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases") ?? 0;
        $pending_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'pending'") ?? 0;
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Dashboard', 'court-automation-hub'); ?></h1>
            
            <div class="dashboard-stats" style="display: flex; gap: 20px; margin: 30px 0;">
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;"><?php echo esc_html($total_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Gesamt Fälle</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #d63638; font-size: 28px;"><?php echo esc_html($pending_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Ausstehende Fälle</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;">€548.11</h3>
                    <p style="margin: 0; color: #666;">Pro Fall (DSGVO)</p>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">Schnellaktionen</h2>
                <div class="inside" style="padding: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary" style="margin-right: 10px;">
                        Neuen GDPR-Fall erstellen
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>" class="button button-secondary">
                        N8N konfigurieren
                    </a>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">System Status</h2>
                <div class="inside" style="padding: 20px;">
                    <?php $this->display_system_status(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function admin_page_cases() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_case_nonce'])) {
            if (wp_verify_nonce($_POST['create_case_nonce'], 'create_case')) {
                $this->handle_case_creation();
            }
        }
        
        switch ($action) {
            case 'add':
                $this->render_add_case_form();
                break;
            default:
                $this->render_cases_list();
                break;
        }
    }
    
    private function handle_case_creation() {
        global $wpdb;
        
        // Sanitize input data
        $case_id = sanitize_text_field($_POST['case_id']);
        $emails_sender = sanitize_email($_POST['emails_sender_email']);
        $emails_user = sanitize_email($_POST['emails_user_email']);
        $emails_subject = sanitize_text_field($_POST['emails_subject']);
        $emails_content = sanitize_textarea_field($_POST['emails_content']);
        $emails_date = sanitize_text_field($_POST['emails_received_date']);
        $emails_time = sanitize_text_field($_POST['emails_received_time']);
        
        // Create case
        $case_result = $wpdb->insert(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_id' => $case_id,
                'case_creation_date' => current_time('mysql'),
                'case_status' => 'draft',
                'case_priority' => 'high',
                'total_amount' => 548.11
            ),
            array('%s', '%s', '%s', '%s', '%f')
        );
        
        if ($case_result) {
            $case_internal_id = $wpdb->insert_id;
            
            // Create email evidence
            $wpdb->insert(
                $wpdb->prefix . 'klage_emails',
                array(
                    'case_id' => $case_internal_id,
                    'emails_received_date' => $emails_date,
                    'emails_received_time' => $emails_time,
                    'emails_sender_email' => $emails_sender,
                    'emails_user_email' => $emails_user,
                    'emails_subject' => $emails_subject,
                    'emails_content' => $emails_content
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            // Create financial record
            $wpdb->insert(
                $wpdb->prefix . 'klage_financial',
                array(
                    'case_id' => $case_internal_id,
                    'damages_loss' => 350.00,
                    'partner_fees' => 96.90,
                    'communication_fees' => 13.36,
                    'vat' => 87.85,
                    'total' => 548.11,
                    'court_fees' => 32.00
                ),
                array('%d', '%f', '%f', '%f', '%f', '%f', '%f')
            );
            
            echo '<div class="notice notice-success"><p><strong>Erfolg!</strong> Fall ' . esc_html($case_id) . ' wurde erstellt. Wert: €548.11</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Fall konnte nicht erstellt werden.</p></div>';
        }
    }
    
    private function render_add_case_form() {
        ?>
        <div class="wrap">
            <h1>Neuen GDPR Spam Fall erstellen</h1>
            <p>Standardwert pro Fall: <strong>€548.11</strong> (DSGVO Schadenersatz)</p>
            
            <form method="post">
                <?php wp_nonce_field('create_case', 'create_case_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="case_id">Fall-ID</label></th>
                        <td>
                            <input type="text" id="case_id" name="case_id" class="regular-text" 
                                   value="SPAM-<?php echo date('Y'); ?>-<?php echo str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT); ?>" required>
                            <p class="description">Eindeutige Fall-ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_sender_email">Spam-Absender E-Mail</label></th>
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
                        <th scope="row"><label for="emails_received_time">Empfangszeit</label></th>
                        <td>
                            <input type="time" id="emails_received_time" name="emails_received_time" class="regular-text" 
                                   value="<?php echo date('H:i'); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_subject">E-Mail Betreff</label></th>
                        <td>
                            <input type="text" id="emails_subject" name="emails_subject" class="regular-text">
                            <p class="description">Betreff der Spam-E-Mail</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_content">E-Mail Inhalt</label></th>
                        <td>
                            <textarea id="emails_content" name="emails_content" class="large-text" rows="10" required></textarea>
                            <p class="description">Vollständiger Inhalt der Spam-E-Mail</p>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Automatische Berechnung (DSGVO):</h3>
                    <ul>
                        <li><strong>Grundschaden:</strong> €350.00 (Art. 82 DSGVO)</li>
                        <li><strong>Anwaltskosten:</strong> €96.90 (RVG)</li>
                        <li><strong>Kommunikation:</strong> €13.36</li>
                        <li><strong>Gerichtskosten:</strong> €32.00</li>
                        <li><strong>MwSt:</strong> €87.85</li>
                        <li><strong>Gesamtsumme:</strong> €548.11</li>
                    </ul>
                </div>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Fall erstellen (€548.11)">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function render_cases_list() {
        global $wpdb;
        
        $cases = $wpdb->get_results("
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
            ORDER BY c.case_creation_date DESC
        ");
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">GDPR Spam Fälle</h1>
            <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="page-title-action">
                Neuen Fall hinzufügen
            </a>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Fall-ID</th>
                        <th>Erstellungsdatum</th>
                        <th>Status</th>
                        <th>Spam-Absender</th>
                        <th>Betrag</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <p>Keine Fälle gefunden. Erstellen Sie Ihren ersten Fall!</p>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                                    Ersten Fall erstellen (€548.11)
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                                <td><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($case->case_creation_date))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                        <?php echo esc_html(ucfirst($case->case_status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($case->emails_sender_email); ?></td>
                                <td><strong>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></strong></td>
                                <td>
                                    <a href="#" class="button button-small">Ansehen</a>
                                    <a href="#" class="button button-small">Bearbeiten</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (!empty($cases)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-radius: 5px;">
                    <h3>Zusammenfassung:</h3>
                    <p><strong>Anzahl Fälle:</strong> <?php echo count($cases); ?></p>
                    <p><strong>Gesamtwert:</strong> €<?php echo number_format(count($cases) * 548.11, 2); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function admin_page_settings() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Einstellungen', 'court-automation-hub'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('klage_click_settings');
                do_settings_sections('klage_click_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">N8N API URL</th>
                        <td>
                            <input type="url" name="klage_click_n8n_url" value="<?php echo esc_attr(get_option('klage_click_n8n_url')); ?>" class="regular-text" />
                            <p class="description">URL zu Ihrer N8N Instanz</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">N8N API Key</th>
                        <td>
                            <input type="password" name="klage_click_n8n_key" value="<?php echo esc_attr(get_option('klage_click_n8n_key')); ?>" class="regular-text" />
                            <p class="description">API-Schlüssel für N8N</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Debug-Modus</th>
                        <td>
                            <input type="checkbox" name="klage_click_debug_mode" value="1" <?php checked(1, get_option('klage_click_debug_mode')); ?> />
                            <label for="klage_click_debug_mode">Debug-Modus aktivieren</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    private function display_system_status() {
        $database = new CAH_Database();
        $table_status = $database->get_table_status();
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th>Tabelle</th><th>Status</th><th>Einträge</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($table_status as $table => $status) {
            $status_icon = $status['exists'] ? '✅' : '❌';
            $status_text = $status['exists'] ? 'OK' : 'Fehlt';
            
            echo '<tr>';
            echo '<td>' . esc_html($table) . '</td>';
            echo '<td>' . $status_icon . ' ' . esc_html($status_text) . '</td>';
            echo '<td>' . esc_html($status['count']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
}