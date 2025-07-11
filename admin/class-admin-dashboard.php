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
        ?>
        <div class="wrap">
            <h1>GDPR Spam Fälle</h1>
            <p>Hier können Sie Ihre GDPR-Verstöße verwalten. Jeder Fall bringt €548.11.</p>
            <a href="#" class="button button-primary">Neuen Fall erstellen</a>
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