<?php
/**
 * Financial Admin Interface
 * Handles admin pages and interfaces for financial calculator
 */

class CAH_Financial_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'klage-click-hub',
            'Financial Calculator',
            '🧮 Finanz-Rechner',
            'manage_options',
            'cah-financial-calculator',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'klage-click-hub',
            'Financial Templates',
            '📋 Finanz-Vorlagen',
            'manage_options',
            'cah-financial-templates',
            array($this, 'templates_page')
        );
        
        add_submenu_page(
            'klage-click-hub',
            'Cost Items',
            '💰 Kosten Items',
            'manage_options',
            'cah-cost-items',
            array($this, 'cost_items_page')
        );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('cah-financial-admin', CAH_FINANCIAL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CAH_FINANCIAL_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-financial-admin', CAH_FINANCIAL_PLUGIN_URL . 'assets/css/admin.css', array(), CAH_FINANCIAL_PLUGIN_VERSION);
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🧮 Financial Calculator</h1>
            
            <div class="notice notice-success">
                <p><strong>Financial Calculator Plugin Active!</strong> The financial calculator has been successfully separated from the core plugin.</p>
            </div>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">💰 Case Financial Management</h2>
                    <div class="inside">
                        <p>This plugin provides advanced financial calculation capabilities for Court Automation Hub cases.</p>
                        
                        <h3>Features:</h3>
                        <ul>
                            <li>✅ Automatic financial calculations with MwSt</li>
                            <li>✅ Template-based cost management</li>
                            <li>✅ Case-specific financial tracking</li>
                            <li>✅ Integration with core plugin via hooks</li>
                        </ul>
                        
                        <h3>Quick Actions:</h3>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button button-primary">Manage Templates</a>
                            <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">View Cases</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function templates_page() {
        $template_manager = new CAH_Financial_Template_Manager();
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        
        switch ($action) {
            case 'edit':
                $this->edit_template_page($template_id);
                break;
            case 'add':
                $this->add_template_page();
                break;
            case 'delete':
                $this->delete_template($template_id);
                $this->list_templates_page();
                break;
            default:
                $this->list_templates_page();
                break;
        }
    }
    
    private function list_templates_page() {
        $template_manager = new CAH_Financial_Template_Manager();
        $templates = $template_manager->get_all_templates();
        
        ?>
        <div class="wrap">
            <h1>📋 Finanz-Vorlagen</h1>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">Verfügbare Vorlagen</h2>
                    <div class="inside">
                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Beschreibung</th>
                                    <th>Standard</th>
                                    <th>MwSt Satz</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td><?php echo esc_html($template->name); ?></td>
                                    <td><?php echo esc_html($template->description); ?></td>
                                    <td><?php echo $template->is_default ? '✅ Ja' : 'Nein'; ?></td>
                                    <td><?php echo $template->mwst_rate; ?>%</td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=edit&template_id=' . $template->id); ?>" class="button button-small">Bearbeiten</a>
                                        <?php if (!$template->is_default): ?>
                                            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=delete&template_id=' . $template->id); ?>" class="button button-small button-link-delete" onclick="return confirm('Sind Sie sicher, dass Sie diese Vorlage löschen möchten?')">Löschen</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=add'); ?>" class="button button-primary">Neue Vorlage hinzufügen</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function add_template_page() {
        ?>
        <div class="wrap">
            <h1>📋 Neue Finanz-Vorlage</h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>">
                <?php wp_nonce_field('financial_template_save'); ?>
                <input type="hidden" name="financial_template_action" value="save">
                
                <div class="metabox-holder">
                    <div class="postbox">
                        <h2 class="hndle">Vorlage Details</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Name</th>
                                    <td><input type="text" name="template_name" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Beschreibung</th>
                                    <td><textarea name="template_description" class="regular-text" rows="3"></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">Standard Vorlage</th>
                                    <td><input type="checkbox" name="is_default" value="1"> Diese Vorlage als Standard verwenden</td>
                                </tr>
                                <tr>
                                    <th scope="row">MwSt Satz (%)</th>
                                    <td><input type="number" name="mwst_rate" step="0.01" value="19.00" class="small-text" required></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Kosten Items</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Grundkosten (€)</th>
                                    <td><input type="number" name="cost_items[grundkosten]" step="0.01" value="548.11" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Gerichtskosten (€)</th>
                                    <td><input type="number" name="cost_items[gerichtskosten]" step="0.01" value="50.00" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Anwaltskosten (€)</th>
                                    <td><input type="number" name="cost_items[anwaltskosten]" step="0.01" value="200.00" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Sonstige (€)</th>
                                    <td><input type="number" name="cost_items[sonstige]" step="0.01" value="0.00" class="regular-text" required></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Vorlage speichern">
                    <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function edit_template_page($template_id) {
        $template_manager = new CAH_Financial_Template_Manager();
        $template = $template_manager->get_template($template_id);
        
        if (!$template) {
            echo '<div class="notice notice-error"><p>Vorlage nicht gefunden.</p></div>';
            $this->list_templates_page();
            return;
        }
        
        $cost_items = json_decode($template->cost_items, true);
        
        ?>
        <div class="wrap">
            <h1>📋 Finanz-Vorlage bearbeiten</h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>">
                <?php wp_nonce_field('financial_template_save'); ?>
                <input type="hidden" name="financial_template_action" value="save">
                <input type="hidden" name="template_id" value="<?php echo $template->id; ?>">
                
                <div class="metabox-holder">
                    <div class="postbox">
                        <h2 class="hndle">Vorlage Details</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Name</th>
                                    <td><input type="text" name="template_name" class="regular-text" value="<?php echo esc_attr($template->name); ?>" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Beschreibung</th>
                                    <td><textarea name="template_description" class="regular-text" rows="3"><?php echo esc_textarea($template->description); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">Standard Vorlage</th>
                                    <td><input type="checkbox" name="is_default" value="1" <?php checked($template->is_default, 1); ?>> Diese Vorlage als Standard verwenden</td>
                                </tr>
                                <tr>
                                    <th scope="row">MwSt Satz (%)</th>
                                    <td><input type="number" name="mwst_rate" step="0.01" value="<?php echo esc_attr($template->mwst_rate); ?>" class="small-text" required></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle">Kosten Items</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Grundkosten (€)</th>
                                    <td><input type="number" name="cost_items[grundkosten]" step="0.01" value="<?php echo esc_attr($cost_items['grundkosten'] ?? 548.11); ?>" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Gerichtskosten (€)</th>
                                    <td><input type="number" name="cost_items[gerichtskosten]" step="0.01" value="<?php echo esc_attr($cost_items['gerichtskosten'] ?? 50.00); ?>" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Anwaltskosten (€)</th>
                                    <td><input type="number" name="cost_items[anwaltskosten]" step="0.01" value="<?php echo esc_attr($cost_items['anwaltskosten'] ?? 200.00); ?>" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Sonstige (€)</th>
                                    <td><input type="number" name="cost_items[sonstige]" step="0.01" value="<?php echo esc_attr($cost_items['sonstige'] ?? 0.00); ?>" class="regular-text" required></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Vorlage aktualisieren">
                    <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function delete_template($template_id) {
        $template_manager = new CAH_Financial_Template_Manager();
        $template_manager->delete_template($template_id);
        echo '<div class="notice notice-success"><p>Vorlage wurde erfolgreich gelöscht.</p></div>';
    }
}