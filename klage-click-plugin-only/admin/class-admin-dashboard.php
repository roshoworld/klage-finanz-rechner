<?php
/**
 * Admin Dashboard class
 * Manages the backend admin interface for case management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_cah_get_cases', array($this, 'ajax_get_cases'));
        add_action('wp_ajax_cah_add_case', array($this, 'ajax_add_case'));
        add_action('wp_ajax_cah_update_case', array($this, 'ajax_update_case'));
        add_action('wp_ajax_cah_delete_case', array($this, 'ajax_delete_case'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Klage.Click Hub', 'court-automation-hub'),
            __('Klage.Click Hub', 'court-automation-hub'),
            'manage_klage_click_cases',
            'klage-click-hub',
            array($this, 'admin_page_dashboard'),
            'dashicons-hammer',
            30
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Dashboard', 'court-automation-hub'),
            __('Dashboard', 'court-automation-hub'),
            'manage_klage_click_cases',
            'klage-click-hub',
            array($this, 'admin_page_dashboard')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Fälle', 'court-automation-hub'),
            __('Fälle', 'court-automation-hub'),
            'manage_klage_click_cases',
            'klage-click-cases',
            array($this, 'admin_page_cases')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Schuldner', 'court-automation-hub'),
            __('Schuldner', 'court-automation-hub'),
            'manage_klage_click_debtors',
            'klage-click-debtors',
            array($this, 'admin_page_debtors')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Mandanten', 'court-automation-hub'),
            __('Mandanten', 'court-automation-hub'),
            'manage_klage_click_cases',
            'klage-click-clients',
            array($this, 'admin_page_clients')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Gerichte', 'court-automation-hub'),
            __('Gerichte', 'court-automation-hub'),
            'manage_klage_click_cases',
            'klage-click-courts',
            array($this, 'admin_page_courts')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Einstellungen', 'court-automation-hub'),
            __('Einstellungen', 'court-automation-hub'),
            'manage_klage_click_settings',
            'klage-click-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    public function admin_init() {
        register_setting('klage_click_settings', 'klage_click_n8n_url');
        register_setting('klage_click_settings', 'klage_click_n8n_key');
        register_setting('klage_click_settings', 'klage_click_egvp_url');
        register_setting('klage_click_settings', 'klage_click_egvp_key');
        register_setting('klage_click_settings', 'klage_click_debug_mode');
    }
    
    public function admin_page_dashboard() {
        global $wpdb;
        
        // Get statistics
        $stats = $this->get_dashboard_stats();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Dashboard', 'court-automation-hub'); ?></h1>
            
            <div class="dashboard-widgets-wrap">
                <div class="metabox-holder">
                    <div class="postbox-container" style="width: 100%;">
                        
                        <!-- Statistics Cards -->
                        <div class="dashboard-stats" style="display: flex; gap: 20px; margin-bottom: 30px;">
                            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                                <h3 style="margin: 0 0 10px 0; color: #0073aa;"><?php echo esc_html($stats['total_cases']); ?></h3>
                                <p style="margin: 0; color: #666;">Gesamt Fälle</p>
                            </div>
                            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                                <h3 style="margin: 0 0 10px 0; color: #d63638;"><?php echo esc_html($stats['pending_cases']); ?></h3>
                                <p style="margin: 0; color: #666;">Ausstehende Fälle</p>
                            </div>
                            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                                <h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo esc_html($stats['completed_cases']); ?></h3>
                                <p style="margin: 0; color: #666;">Abgeschlossene Fälle</p>
                            </div>
                            <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                                <h3 style="margin: 0 0 10px 0; color: #0073aa;">€<?php echo esc_html(number_format($stats['total_amount'], 2)); ?></h3>
                                <p style="margin: 0; color: #666;">Gesamtbetrag</p>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="postbox">
                            <h2 class="hndle">Schnellaktionen</h2>
                            <div class="inside">
                                <div class="quick-actions" style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                                        <span class="dashicons dashicons-plus-alt" style="margin-right: 5px;"></span>
                                        Neuen Fall hinzufügen
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-debtors&action=add'); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-groups" style="margin-right: 5px;"></span>
                                        Schuldner hinzufügen
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-admin-settings" style="margin-right: 5px;"></span>
                                        Einstellungen
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Cases -->
                        <div class="postbox">
                            <h2 class="hndle">Neueste Fälle</h2>
                            <div class="inside">
                                <?php $this->display_recent_cases(); ?>
                            </div>
                        </div>
                        
                        <!-- System Status -->
                        <div class="postbox">
                            <h2 class="hndle">System Status</h2>
                            <div class="inside">
                                <?php $this->display_system_status(); ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function admin_page_cases() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'add':
                $this->render_add_case_form();
                break;
            case 'edit':
                $this->render_edit_case_form();
                break;
            case 'view':
                $this->render_view_case();
                break;
            default:
                $this->render_cases_list();
                break;
        }
    }
    
    private function render_cases_list() {
        global $wpdb;
        
        // Get cases with related data
        $cases = $wpdb->get_results("
            SELECT 
                c.id,
                c.case_id,
                c.case_creation_date,
                c.case_status,
                c.case_priority,
                c.total_amount,
                d.debtors_name,
                cl.users_first_name,
                cl.users_last_name,
                ct.court_name
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_debtors d ON c.debtor_id = d.id
            LEFT JOIN {$wpdb->prefix}klage_clients cl ON c.client_id = cl.id
            LEFT JOIN {$wpdb->prefix}klage_courts ct ON c.court_id = ct.id
            ORDER BY c.case_creation_date DESC
        ");
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html__('Fälle', 'court-automation-hub'); ?></h1>
            <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="page-title-action">
                <?php echo esc_html__('Neuen Fall hinzufügen', 'court-automation-hub'); ?>
            </a>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1">Bulk-Aktionen</option>
                        <option value="delete">Löschen</option>
                        <option value="complete">Als abgeschlossen markieren</option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="Anwenden">
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                        <th class="manage-column">Fall-ID</th>
                        <th class="manage-column">Erstellungsdatum</th>
                        <th class="manage-column">Status</th>
                        <th class="manage-column">Priorität</th>
                        <th class="manage-column">Schuldner</th>
                        <th class="manage-column">Mandant</th>
                        <th class="manage-column">Betrag</th>
                        <th class="manage-column">Gericht</th>
                        <th class="manage-column">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px;">
                                <p><?php echo esc_html__('Keine Fälle gefunden. Erstellen Sie Ihren ersten Fall!', 'court-automation-hub'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                                    <?php echo esc_html__('Ersten Fall erstellen', 'court-automation-hub'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" name="case[]" value="<?php echo esc_attr($case->id); ?>">
                                </th>
                                <td>
                                    <strong>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->id); ?>">
                                            <?php echo esc_html($case->case_id); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo esc_html(date_i18n('d.m.Y', strtotime($case->case_creation_date))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                        <?php echo esc_html($this->get_status_label($case->case_status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge priority-<?php echo esc_attr($case->case_priority); ?>">
                                        <?php echo esc_html($this->get_priority_label($case->case_priority)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($case->debtors_name); ?></td>
                                <td><?php echo esc_html($case->users_first_name . ' ' . $case->users_last_name); ?></td>
                                <td>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></td>
                                <td><?php echo esc_html($case->court_name); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->id); ?>" class="button button-small">
                                        <?php echo esc_html__('Ansehen', 'court-automation-hub'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" class="button button-small">
                                        <?php echo esc_html__('Bearbeiten', 'court-automation-hub'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-draft { background: #f0f0f1; color: #50575e; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #0073aa; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .priority-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .priority-low { background: #e2f3e2; color: #2e7d32; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-high { background: #ffe0cc; color: #d84315; }
        .priority-urgent { background: #f8d7da; color: #721c24; }
        </style>
        <?php
    }
    
    private function render_add_case_form() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Neuen Fall hinzufügen', 'court-automation-hub'); ?></h1>
            
            <form method="post" id="add-case-form">
                <?php wp_nonce_field('add_case_nonce', 'add_case_nonce'); ?>
                
                <div class="form-container" style="max-width: 800px;">
                    <div class="postbox">
                        <h2 class="hndle">Fall-Details</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="case_id">Fall-ID</label>
                                    </th>
                                    <td>
                                        <input type="text" id="case_id" name="case_id" class="regular-text" 
                                               value="<?php echo esc_attr($this->generate_case_id()); ?>" required>
                                        <p class="description">Eindeutige Fall-ID (wird automatisch generiert)</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="case_priority">Priorität</label>
                                    </th>
                                    <td>
                                        <select id="case_priority" name="case_priority" class="regular-text">
                                            <option value="low">Niedrig</option>
                                            <option value="medium" selected>Mittel</option>
                                            <option value="high">Hoch</option>
                                            <option value="urgent">Dringend</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="case_deadline_response">Antwortfrist</label>
                                    </th>
                                    <td>
                                        <input type="date" id="case_deadline_response" name="case_deadline_response" class="regular-text">
                                        <p class="description">Datum bis wann eine Antwort erwartet wird</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="case_deadline_payment">Zahlungsfrist</label>
                                    </th>
                                    <td>
                                        <input type="date" id="case_deadline_payment" name="case_deadline_payment" class="regular-text">
                                        <p class="description">Datum bis wann die Zahlung erwartet wird</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">SPAM E-Mail Evidenz</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="email_received_date">Empfangsdatum</label>
                                    </th>
                                    <td>
                                        <input type="date" id="email_received_date" name="email_received_date" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="email_received_time">Empfangszeit</label>
                                    </th>
                                    <td>
                                        <input type="time" id="email_received_time" name="email_received_time" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="email_sender">Absender E-Mail</label>
                                    </th>
                                    <td>
                                        <input type="email" id="email_sender" name="email_sender" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="email_user">Empfänger E-Mail</label>
                                    </th>
                                    <td>
                                        <input type="email" id="email_user" name="email_user" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="email_subject">Betreff</label>
                                    </th>
                                    <td>
                                        <input type="text" id="email_subject" name="email_subject" class="regular-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="email_content">E-Mail Inhalt</label>
                                    </th>
                                    <td>
                                        <textarea id="email_content" name="email_content" class="large-text" rows="10"></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="submit" class="button button-primary" value="Fall erstellen">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">Abbrechen</a>
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
    
    private function get_dashboard_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total cases
        $stats['total_cases'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases");
        
        // Pending cases
        $stats['pending_cases'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'pending'");
        
        // Completed cases
        $stats['completed_cases'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'completed'");
        
        // Total amount
        $stats['total_amount'] = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}klage_cases") ?: 0;
        
        return $stats;
    }
    
    private function display_recent_cases() {
        global $wpdb;
        
        $recent_cases = $wpdb->get_results("
            SELECT 
                c.case_id,
                c.case_creation_date,
                c.case_status,
                c.total_amount,
                d.debtors_name
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_debtors d ON c.debtor_id = d.id
            ORDER BY c.case_creation_date DESC
            LIMIT 10
        ");
        
        if (empty($recent_cases)) {
            echo '<p>Keine Fälle vorhanden.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Fall-ID</th><th>Datum</th><th>Status</th><th>Schuldner</th><th>Betrag</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($recent_cases as $case) {
            echo '<tr>';
            echo '<td><a href="' . admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->case_id) . '">' . esc_html($case->case_id) . '</a></td>';
            echo '<td>' . esc_html(date_i18n('d.m.Y', strtotime($case->case_creation_date))) . '</td>';
            echo '<td><span class="status-badge status-' . esc_attr($case->case_status) . '">' . esc_html($this->get_status_label($case->case_status)) . '</span></td>';
            echo '<td>' . esc_html($case->debtors_name) . '</td>';
            echo '<td>€' . esc_html(number_format($case->total_amount, 2)) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    private function display_system_status() {
        $database = new CAH_Database();
        $table_status = $database->get_table_status();
        
        echo '<div class="system-status">';
        echo '<h4>Datenbank Tabellen</h4>';
        echo '<table class="wp-list-table widefat fixed striped">';
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
        echo '</div>';
    }
    
    private function get_status_label($status) {
        $labels = array(
            'draft' => 'Entwurf',
            'pending' => 'Ausstehend',
            'processing' => 'In Bearbeitung',
            'completed' => 'Abgeschlossen',
            'cancelled' => 'Abgebrochen'
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
    
    private function get_priority_label($priority) {
        $labels = array(
            'low' => 'Niedrig',
            'medium' => 'Mittel',
            'high' => 'Hoch',
            'urgent' => 'Dringend'
        );
        
        return isset($labels[$priority]) ? $labels[$priority] : $priority;
    }
    
    private function generate_case_id() {
        return 'SPAM-' . date('Y') . '-' . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    // Placeholder methods for other admin pages
    public function admin_page_debtors() {
        echo '<div class="wrap"><h1>Schuldner-Verwaltung</h1><p>Wird in der nächsten Phase implementiert.</p></div>';
    }
    
    public function admin_page_clients() {
        echo '<div class="wrap"><h1>Mandanten-Verwaltung</h1><p>Wird in der nächsten Phase implementiert.</p></div>';
    }
    
    public function admin_page_courts() {
        echo '<div class="wrap"><h1>Gerichte-Verwaltung</h1><p>Wird in der nächsten Phase implementiert.</p></div>';
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
    
    // AJAX handlers
    public function ajax_get_cases() {
        // TODO: Implement AJAX case retrieval
        wp_die();
    }
    
    public function ajax_add_case() {
        // TODO: Implement AJAX case creation
        wp_die();
    }
    
    public function ajax_update_case() {
        // TODO: Implement AJAX case update
        wp_die();
    }
    
    public function ajax_delete_case() {
        // TODO: Implement AJAX case deletion
        wp_die();
    }
}