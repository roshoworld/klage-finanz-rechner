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
                            <a href="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>" class="button button-primary">Manage Cost Items</a>
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
    
    // Cost Items Management
    public function cost_items_page() {
        $db_manager = new CAH_Financial_DB_Manager();
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        
        // Handle form submissions
        if (isset($_POST['cost_item_action'])) {
            $this->handle_cost_item_actions();
        }
        
        switch ($action) {
            case 'edit':
                $this->edit_cost_item_page($item_id);
                break;
            case 'add':
                $this->add_cost_item_page();
                break;
            case 'delete':
                $this->delete_cost_item($item_id);
                $this->list_cost_items_page();
                break;
            default:
                $this->list_cost_items_page();
                break;
        }
    }
    
    private function list_cost_items_page() {
        $db_manager = new CAH_Financial_DB_Manager();
        $cost_items = $db_manager->get_all_cost_items(false); // Show both active and inactive
        
        ?>
        <div class="wrap">
            <h1>💰 Kosten Items</h1>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">Alle Kosten Items</h2>
                    <div class="inside">
                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Beschreibung</th>
                                    <th>Standard Betrag</th>
                                    <th>Kategorie</th>
                                    <th>Status</th>
                                    <th>Reihenfolge</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cost_items as $item): ?>
                                <tr<?php echo !$item->is_active ? ' style="opacity: 0.6;"' : ''; ?>>
                                    <td><?php echo esc_html($item->name); ?></td>
                                    <td><?php echo esc_html($item->description); ?></td>
                                    <td>€<?php echo number_format($item->default_amount, 2, ',', '.'); ?></td>
                                    <td><?php echo esc_html(ucfirst($item->category)); ?></td>
                                    <td><?php echo $item->is_active ? '✅ Aktiv' : '❌ Inaktiv'; ?></td>
                                    <td><?php echo $item->sort_order; ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=edit&item_id=' . $item->id); ?>" class="button button-small">Bearbeiten</a>
                                        <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=delete&item_id=' . $item->id); ?>" class="button button-small button-link-delete" onclick="return confirm('Sind Sie sicher, dass Sie dieses Kosten Item löschen möchten?')">Löschen</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=add'); ?>" class="button button-primary">Neues Kosten Item hinzufügen</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function add_cost_item_page() {
        ?>
        <div class="wrap">
            <h1>💰 Neues Kosten Item</h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>">
                <?php wp_nonce_field('cost_item_save'); ?>
                <input type="hidden" name="cost_item_action" value="save">
                
                <div class="metabox-holder">
                    <div class="postbox">
                        <h2 class="hndle">Kosten Item Details</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Name *</th>
                                    <td><input type="text" name="name" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Beschreibung</th>
                                    <td><textarea name="description" class="regular-text" rows="3"></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">Standard Betrag (€) *</th>
                                    <td><input type="number" name="default_amount" step="0.01" min="0" class="regular-text" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Kategorie *</th>
                                    <td>
                                        <select name="category" class="regular-text" required>
                                            <option value="grundkosten">Grundkosten</option>
                                            <option value="gerichtskosten">Gerichtskosten</option>
                                            <option value="anwaltskosten">Anwaltskosten</option>
                                            <option value="sonstige">Sonstige</option>
                                            <option value="general">Allgemein</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Reihenfolge</th>
                                    <td><input type="number" name="sort_order" min="0" value="0" class="small-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Status</th>
                                    <td><input type="checkbox" name="is_active" value="1" checked> Aktiv</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Kosten Item speichern">
                    <a href="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function edit_cost_item_page($item_id) {
        $db_manager = new CAH_Financial_DB_Manager();
        $item = $db_manager->get_cost_item($item_id);
        
        if (!$item) {
            echo '<div class="notice notice-error"><p>Kosten Item nicht gefunden.</p></div>';
            $this->list_cost_items_page();
            return;
        }
        
        ?>
        <div class="wrap">
            <h1>💰 Kosten Item bearbeiten</h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>">
                <?php wp_nonce_field('cost_item_save'); ?>
                <input type="hidden" name="cost_item_action" value="save">
                <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                
                <div class="metabox-holder">
                    <div class="postbox">
                        <h2 class="hndle">Kosten Item Details</h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Name *</th>
                                    <td><input type="text" name="name" class="regular-text" value="<?php echo esc_attr($item->name); ?>" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Beschreibung</th>
                                    <td><textarea name="description" class="regular-text" rows="3"><?php echo esc_textarea($item->description); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">Standard Betrag (€) *</th>
                                    <td><input type="number" name="default_amount" step="0.01" min="0" class="regular-text" value="<?php echo esc_attr($item->default_amount); ?>" required></td>
                                </tr>
                                <tr>
                                    <th scope="row">Kategorie *</th>
                                    <td>
                                        <select name="category" class="regular-text" required>
                                            <option value="grundkosten" <?php selected($item->category, 'grundkosten'); ?>>Grundkosten</option>
                                            <option value="gerichtskosten" <?php selected($item->category, 'gerichtskosten'); ?>>Gerichtskosten</option>
                                            <option value="anwaltskosten" <?php selected($item->category, 'anwaltskosten'); ?>>Anwaltskosten</option>
                                            <option value="sonstige" <?php selected($item->category, 'sonstige'); ?>>Sonstige</option>
                                            <option value="general" <?php selected($item->category, 'general'); ?>>Allgemein</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Reihenfolge</th>
                                    <td><input type="number" name="sort_order" min="0" value="<?php echo esc_attr($item->sort_order); ?>" class="small-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row">Status</th>
                                    <td><input type="checkbox" name="is_active" value="1" <?php checked($item->is_active, 1); ?>> Aktiv</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Kosten Item aktualisieren">
                    <a href="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function delete_cost_item($item_id) {
        $db_manager = new CAH_Financial_DB_Manager();
        $db_manager->delete_cost_item($item_id);
        echo '<div class="notice notice-success"><p>Kosten Item wurde erfolgreich gelöscht.</p></div>';
    }
    
    private function handle_cost_item_actions() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'cost_item_save')) {
            return;
        }
        
        $action = $_POST['cost_item_action'];
        
        if ($action === 'save') {
            $data = array(
                'name' => sanitize_text_field($_POST['name']),
                'description' => sanitize_textarea_field($_POST['description']),
                'default_amount' => floatval($_POST['default_amount']),
                'category' => sanitize_text_field($_POST['category']),
                'sort_order' => intval($_POST['sort_order']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            );
            
            if (isset($_POST['item_id'])) {
                $data['id'] = intval($_POST['item_id']);
            }
            
            $db_manager = new CAH_Financial_DB_Manager();
            $db_manager->save_cost_item($data);
            
            echo '<div class="notice notice-success"><p>Kosten Item wurde erfolgreich gespeichert.</p></div>';
        }
    }
}