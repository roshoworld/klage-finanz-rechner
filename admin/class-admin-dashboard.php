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
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;">DSGVO</h3>
                    <p style="margin: 0; color: #666;">Fall-Typ</p>
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
            case 'view':
                $this->render_view_case();
                break;
            case 'edit':
                $this->render_edit_case_form();
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
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->id); ?>" class="button button-small">Ansehen</a>
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" class="button button-small">Bearbeiten</a>
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
    
    private function render_view_case() {
        $case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$case_id) {
            echo '<div class="notice notice-error"><p>Fall-ID nicht gefunden.</p></div>';
            return;
        }
        
        global $wpdb;
        
        // Get case with all related data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, cl.users_first_name, cl.users_last_name, 
                   d.debtors_name, d.debtors_email,
                   ct.court_name
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_clients cl ON c.client_id = cl.id
            LEFT JOIN {$wpdb->prefix}klage_debtors d ON c.debtor_id = d.id
            LEFT JOIN {$wpdb->prefix}klage_courts ct ON c.court_id = ct.id
            WHERE c.id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Get email evidence
        $emails = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_emails WHERE case_id = %d
        ", $case_id));
        
        // Get financial data
        $financial = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
        ", $case_id));
        
        ?>
        <div class="wrap">
            <h1>Fall Details: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" class="button button-primary">
                    ✏️ Bearbeiten
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">
                    ← Zurück zur Übersicht
                </a>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <!-- Case Information -->
                <div class="postbox">
                    <h2 class="hndle">📋 Fall-Informationen</h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th>Fall-ID:</th>
                                <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Erstellungsdatum:</th>
                                <td><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($case->case_creation_date))); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                        <?php echo esc_html(ucfirst($case->case_status)); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Priorität:</th>
                                <td>
                                    <span class="priority-badge priority-<?php echo esc_attr($case->case_priority); ?>">
                                        <?php echo esc_html(ucfirst($case->case_priority)); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Gesamtbetrag:</th>
                                <td><strong>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></strong></td>
                            </tr>
                            <?php if ($case->court_name): ?>
                            <tr>
                                <th>Zuständiges Gericht:</th>
                                <td><?php echo esc_html($case->court_name); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <!-- Email Evidence -->
                <div class="postbox">
                    <h2 class="hndle">📧 E-Mail Evidenz</h2>
                    <div class="inside">
                        <?php if (!empty($emails)): ?>
                            <?php foreach ($emails as $email): ?>
                                <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                    <table class="form-table">
                                        <tr>
                                            <th style="width: 120px;">Von:</th>
                                            <td><strong><?php echo esc_html($email->emails_sender_email); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>An:</th>
                                            <td><?php echo esc_html($email->emails_user_email); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Datum/Zeit:</th>
                                            <td><?php echo esc_html($email->emails_received_date . ' ' . $email->emails_received_time); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Betreff:</th>
                                            <td><?php echo esc_html($email->emails_subject); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Inhalt:</th>
                                            <td>
                                                <div style="background: #f9f9f9; padding: 10px; border-radius: 3px; max-height: 200px; overflow-y: auto;">
                                                    <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;"><?php echo esc_html($email->emails_content); ?></pre>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Keine E-Mail-Evidenz gefunden.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
            <!-- Financial Breakdown -->
            <?php if ($financial): ?>
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">💰 Finanzielle Aufschlüsselung</h2>
                <div class="inside">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <strong>Grundschaden:</strong><br>
                            €<?php echo esc_html(number_format($financial->damages_loss, 2)); ?>
                        </div>
                        <div>
                            <strong>Anwaltskosten:</strong><br>
                            €<?php echo esc_html(number_format($financial->partner_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>Kommunikation:</strong><br>
                            €<?php echo esc_html(number_format($financial->communication_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>Gerichtskosten:</strong><br>
                            €<?php echo esc_html(number_format($financial->court_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>MwSt:</strong><br>
                            €<?php echo esc_html(number_format($financial->vat, 2)); ?>
                        </div>
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 5px;">
                            <strong>Gesamtsumme:</strong><br>
                            <span style="font-size: 18px; color: #0073aa;">€<?php echo esc_html(number_format($financial->total, 2)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    }
    
    private function render_edit_case_form() {
        $case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$case_id) {
            echo '<div class="notice notice-error"><p>Fall-ID nicht gefunden.</p></div>';
            return;
        }
        
        global $wpdb;
        
        // Get case data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_cases WHERE id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        ?>
        <div class="wrap">
            <h1>Fall bearbeiten: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <p><strong>⚠️ Hinweis:</strong> Die vollständige Fall-Bearbeitung wird in v1.0.6 implementiert.</p>
                <p>Aktuell können Sie den Fall ansehen und der Status wird in der nächsten Version editierbar sein.</p>
            </div>
            
            <table class="form-table">
                <tr>
                    <th>Fall-ID:</th>
                    <td><?php echo esc_html($case->case_id); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><?php echo esc_html($case->case_status); ?></td>
                </tr>
                <tr>
                    <th>Priorität:</th>
                    <td><?php echo esc_html($case->case_priority); ?></td>
                </tr>
                <tr>
                    <th>Gesamtbetrag:</th>
                    <td>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></td>
                </tr>
                <tr>
                    <th>Erstellungsdatum:</th>
                    <td><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($case->case_creation_date))); ?></td>
                </tr>
            </table>
            
            <p class="submit">
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case_id); ?>" class="button button-primary">
                    ← Zurück zu Fall Details
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">
                    Zur Übersicht
                </a>
            </p>
        </div>
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
            
            <!-- Database Fix Section -->
            <div class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">🛠️ Datenbank Reparatur</h2>
                <div class="inside" style="padding: 20px;">
                    <p><strong>Problem:</strong> Einige Datenbank-Tabellen fehlen und müssen erstellt werden.</p>
                    
                    <div style="margin: 15px 0;">
                        <h4>Aktuelle Tabellen-Status:</h4>
                        <?php $this->show_detailed_table_status(); ?>
                    </div>
                    
                    <form method="post" style="margin-bottom: 15px;">
                        <?php wp_nonce_field('create_tables', 'create_tables_nonce'); ?>
                        <input type="submit" class="button button-primary" value="🔧 Alle Tabellen erstellen (Direkt-SQL)" 
                               onclick="return confirm('Alle fehlenden Tabellen jetzt erstellen?');">
                    </form>
                    <p class="description">Verwendet direktes SQL (nicht dbDelta) für bessere Kompatibilität.</p>
                </div>
            </div>
            
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
    
    private function force_create_tables() {
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        $database = new CAH_Database();
        
        // Try advanced table creation with direct SQL
        $results = $database->create_tables_direct();
        
        if ($results['success']) {
            echo '<div class="notice notice-success"><p><strong>Erfolg!</strong> ' . $results['message'] . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> ' . $results['message'] . '</p></div>';
        }
        
        // Show detailed results
        if (get_option('klage_click_debug_mode')) {
            echo '<div class="notice notice-info"><p><strong>Debug Info:</strong><br>' . implode('<br>', $results['details']) . '</p></div>';
        }
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
    
    private function show_detailed_table_status() {
        global $wpdb;
        
        $required_tables = array('klage_cases', 'klage_debtors', 'klage_clients', 'klage_emails', 'klage_financial', 'klage_courts');
        
        echo '<table style="width: 100%; border-collapse: collapse;">';
        foreach ($required_tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name") : 0;
            
            $status_icon = $exists ? '✅' : '❌';
            $status_color = $exists ? 'green' : 'red';
            
            echo '<tr>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd;">' . $status_icon . ' <strong>' . $table . '</strong></td>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd; color: ' . $status_color . ';">' . ($exists ? 'Existiert' : 'FEHLT') . '</td>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd;">' . $count . ' Einträge</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}