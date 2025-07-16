<?php
/**
 * Database Management Admin Interface
 * Complete CRUD system for database management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Database_Admin {
    
    private $schema_manager;
    private $form_generator;
    private $import_export_manager;
    
    public function __construct() {
        $this->schema_manager = new CAH_Schema_Manager();
        $this->form_generator = new CAH_Form_Generator();
        $this->import_export_manager = new CAH_Import_Export_Manager();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_action('wp_ajax_cah_sync_schema', array($this, 'ajax_sync_schema'));
        add_action('wp_ajax_cah_export_data', array($this, 'ajax_export_data'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'klage-click-hub',
            'Database Management',
            'Database Management',
            'manage_options',
            'klage-click-database',
            array($this, 'render_database_management_page')
        );
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle schema synchronization
        if (isset($_POST['action']) && $_POST['action'] === 'sync_schema') {
            if (wp_verify_nonce($_POST['_wpnonce'], 'sync_schema')) {
                $results = $this->schema_manager->synchronize_all_tables();
                
                $success_count = 0;
                $error_count = 0;
                
                foreach ($results as $table => $result) {
                    if ($result['success']) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
                
                if ($error_count === 0) {
                    add_action('admin_notices', function() use ($success_count) {
                        echo '<div class="notice notice-success"><p>Schema synchronization completed successfully for ' . $success_count . ' tables.</p></div>';
                    });
                } else {
                    add_action('admin_notices', function() use ($error_count) {
                        echo '<div class="notice notice-error"><p>Schema synchronization failed for ' . $error_count . ' tables.</p></div>';
                    });
                }
            }
        }
        
        // Handle add column
        if (isset($_POST['action']) && $_POST['action'] === 'add_column') {
            if (wp_verify_nonce($_POST['_wpnonce'], 'add_column')) {
                $table_name = sanitize_text_field($_POST['table_name']);
                $column_name = sanitize_text_field($_POST['column_name']);
                $column_type = sanitize_text_field($_POST['column_type']);
                $column_null = sanitize_text_field($_POST['column_null']);
                $column_default = sanitize_text_field($_POST['column_default']);
                
                // Build column definition
                $column_definition = $column_type . ' ' . $column_null;
                
                if (!empty($column_default)) {
                    if (in_array($column_type, array('varchar(255)', 'varchar(100)', 'varchar(50)', 'text'))) {
                        $column_definition .= " DEFAULT '" . $column_default . "'";
                    } else {
                        $column_definition .= " DEFAULT " . $column_default;
                    }
                }
                
                $result = $this->schema_manager->add_column($table_name, $column_name, $column_definition);
                
                if ($result['success']) {
                    add_action('admin_notices', function() use ($column_name) {
                        echo '<div class="notice notice-success"><p><strong>Column "' . $column_name . '" added successfully!</strong></p>';
                        echo '<p>✅ Database table updated<br>';
                        echo '✅ Dynamic forms will automatically include this field<br>';
                        echo '✅ CSV import templates will automatically include this field<br>';
                        echo '✅ No additional steps required</p>';
                        echo '</div>';
                    });
                } else {
                    add_action('admin_notices', function() use ($result) {
                        echo '<div class="notice notice-error"><p>Error adding column: ' . $result['message'] . '</p></div>';
                    });
                }
            }
        }
        
        // Handle drop column
        if (isset($_GET['action']) && $_GET['action'] === 'drop_column' && isset($_GET['column'])) {
            $table_name = sanitize_text_field($_GET['table']);
            $column_name = sanitize_text_field($_GET['column']);
            
            $result = $this->schema_manager->drop_column($table_name, $column_name);
            
            if ($result['success']) {
                add_action('admin_notices', function() use ($column_name) {
                    echo '<div class="notice notice-success"><p>Column "' . $column_name . '" dropped successfully.</p></div>';
                });
            } else {
                add_action('admin_notices', function() use ($result) {
                    echo '<div class="notice notice-error"><p>Error dropping column: ' . $result['message'] . '</p></div>';
                });
            }
        }
        
        // Handle CSV import
        if (isset($_POST['action']) && $_POST['action'] === 'import_csv') {
            if (wp_verify_nonce($_POST['_wpnonce'], 'import_csv')) {
                $table_name = sanitize_text_field($_POST['table_name']);
                $csv_data = sanitize_textarea_field($_POST['csv_data']);
                
                $results = $this->import_export_manager->process_csv_import($table_name, $csv_data);
                
                add_action('admin_notices', function() use ($results) {
                    if ($results['success'] > 0) {
                        echo '<div class="notice notice-success"><p>Successfully imported ' . $results['success'] . ' records.</p></div>';
                    }
                    
                    if ($results['errors'] > 0) {
                        echo '<div class="notice notice-error"><p>' . $results['errors'] . ' errors occurred during import.</p></div>';
                    }
                });
            }
        }
        
        // Handle data insert/update
        if (isset($_POST['action']) && $_POST['action'] === 'save_data') {
            if (wp_verify_nonce($_POST['_wpnonce'], 'save_data')) {
                $table_name = sanitize_text_field($_POST['table_name']);
                $record_id = intval($_POST['record_id']);
                
                // Process form data
                $data = array();
                $schema = $this->schema_manager->get_complete_schema_definition()[$table_name];
                
                foreach ($schema['columns'] as $field_name => $field_def) {
                    if (isset($_POST[$field_name])) {
                        $data[$field_name] = sanitize_text_field($_POST[$field_name]);
                    }
                }
                
                // Remove system fields
                unset($data['id'], $data['created_at'], $data['updated_at']);
                
                if ($record_id > 0) {
                    // Update existing record
                    $result = $this->schema_manager->update_data($table_name, $data, array('id' => $record_id));
                } else {
                    // Insert new record
                    $result = $this->schema_manager->insert_data($table_name, $data);
                }
                
                if ($result['success']) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success"><p>Record saved successfully.</p></div>';
                    });
                } else {
                    add_action('admin_notices', function() use ($result) {
                        echo '<div class="notice notice-error"><p>Error saving record: ' . $result['message'] . '</p></div>';
                    });
                }
            }
        }
    }
    
    /**
     * Render database management page
     */
    public function render_database_management_page() {
        $tab = $_GET['tab'] ?? 'schema';
        
        echo '<div class="wrap">';
        echo '<h1>Database Management</h1>';
        
        // Tab navigation
        echo '<nav class="nav-tab-wrapper">';
        echo '<a href="?page=klage-click-database&tab=schema" class="nav-tab ' . ($tab === 'schema' ? 'nav-tab-active' : '') . '">Schema Management</a>';
        echo '<a href="?page=klage-click-database&tab=data" class="nav-tab ' . ($tab === 'data' ? 'nav-tab-active' : '') . '">Data Management</a>';
        echo '<a href="?page=klage-click-database&tab=import" class="nav-tab ' . ($tab === 'import' ? 'nav-tab-active' : '') . '">Import/Export</a>';
        echo '<a href="?page=klage-click-database&tab=forms" class="nav-tab ' . ($tab === 'forms' ? 'nav-tab-active' : '') . '">Form Generator</a>';
        echo '</nav>';
        
        echo '<div class="tab-content">';
        
        switch ($tab) {
            case 'schema':
                $this->render_schema_management_tab();
                break;
            case 'data':
                $this->render_data_management_tab();
                break;
            case 'import':
                $this->render_import_export_tab();
                break;
            case 'forms':
                $this->render_form_generator_tab();
                break;
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Render schema management tab
     */
    private function render_schema_management_tab() {
        $table_name = $_GET['table'] ?? 'klage_cases';
        $action = $_GET['action'] ?? 'status';
        
        echo '<div class="schema-management">';
        
        // Table selector
        echo '<div class="table-selector">';
        echo '<label for="schema-table-select">Select Table:</label>';
        echo '<select id="schema-table-select" onchange="window.location.href=\'?page=klage-click-database&tab=schema&table=\' + this.value">';
        
        $tables = array_keys($this->schema_manager->get_complete_schema_definition());
        foreach ($tables as $table) {
            $selected = ($table === $table_name) ? 'selected' : '';
            echo '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        // Action buttons
        echo '<div class="schema-actions">';
        echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=status" class="button ' . ($action === 'status' ? 'button-primary' : '') . '">Schema Status</a>';
        echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=structure" class="button ' . ($action === 'structure' ? 'button-primary' : '') . '">Table Structure</a>';
        echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=add_column" class="button ' . ($action === 'add_column' ? 'button-primary' : '') . '">Add Column</a>';
        echo '</div>';
        
        if ($action === 'status') {
            $this->render_schema_status();
        } elseif ($action === 'structure') {
            $this->render_table_structure($table_name);
        } elseif ($action === 'add_column') {
            $this->render_add_column_form($table_name);
        }
        
        echo '</div>';
    }
    
    /**
     * Render schema status overview
     */
    private function render_schema_status() {
        echo '<h2>Schema Status Overview</h2>';
        
        $status = $this->schema_manager->get_schema_status();
        
        echo '<div class="schema-status-grid">';
        
        foreach ($status as $table_name => $table_status) {
            $status_class = '';
            $status_icon = '';
            
            switch ($table_status['status']) {
                case 'ok':
                    $status_class = 'status-ok';
                    $status_icon = '✅';
                    break;
                case 'missing':
                    $status_class = 'status-missing';
                    $status_icon = '❌';
                    break;
                case 'out_of_sync':
                    $status_class = 'status-out-of-sync';
                    $status_icon = '⚠️';
                    break;
                default:
                    $status_class = 'status-error';
                    $status_icon = '🔴';
                    break;
            }
            
            echo '<div class="schema-status-card ' . $status_class . '">';
            echo '<h3>' . $status_icon . ' ' . $table_name . '</h3>';
            echo '<p>' . $table_status['message'] . '</p>';
            
            if (isset($table_status['details'])) {
                echo '<div class="schema-details">';
                if (isset($table_status['details']['missing_columns'])) {
                    echo '<p><strong>Missing columns:</strong> ' . implode(', ', $table_status['details']['missing_columns']) . '</p>';
                }
                if (isset($table_status['details']['extra_columns'])) {
                    echo '<p><strong>Extra columns:</strong> ' . implode(', ', $table_status['details']['extra_columns']) . '</p>';
                }
                echo '</div>';
            }
            
            echo '<div class="schema-actions">';
            echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=structure" class="button button-small">View Structure</a>';
            echo '</div>';
            
            echo '</div>';
        }
        
        echo '</div>';
        
        // Global schema sync button
        echo '<div class="global-schema-actions">';
        echo '<form method="post">';
        wp_nonce_field('sync_schema');
        echo '<input type="hidden" name="action" value="sync_schema">';
        echo '<button type="submit" class="button button-primary">Synchronize All Schemas</button>';
        echo '</form>';
        echo '</div>';
    }
    
    /**
     * Render table structure view
     */
    private function render_table_structure($table_name) {
        echo '<h2>Table Structure: ' . $table_name . '</h2>';
        
        $current_schema = $this->schema_manager->get_current_table_schema($table_name);
        $expected_schema = $this->schema_manager->get_complete_schema_definition()[$table_name] ?? null;
        
        if (!$current_schema) {
            echo '<div class="notice notice-error"><p>Table does not exist in database</p></div>';
            return;
        }
        
        echo '<div class="table-structure">';
        
        // Current columns
        echo '<h3>Current Columns</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Column Name</th>';
        echo '<th>Type</th>';
        echo '<th>Null</th>';
        echo '<th>Default</th>';
        echo '<th>Extra</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($current_schema['columns'] as $column_name => $column_info) {
            $is_expected = $expected_schema && isset($expected_schema['columns'][$column_name]);
            $row_class = $is_expected ? '' : 'extra-column';
            
            echo '<tr class="' . $row_class . '">';
            echo '<td><strong>' . $column_name . '</strong></td>';
            echo '<td>' . $column_info['Type'] . '</td>';
            echo '<td>' . $column_info['Null'] . '</td>';
            echo '<td>' . $column_info['Default'] . '</td>';
            echo '<td>' . $column_info['Extra'] . '</td>';
            echo '<td>';
            
            if (!in_array($column_name, array('id', 'created_at', 'updated_at'))) {
                echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=modify_column&column=' . $column_name . '" class="button button-small">Modify</a> ';
                echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=drop_column&column=' . $column_name . '" class="button button-small button-link-delete" onclick="return confirm(\'Are you sure you want to drop this column?\')">Drop</a>';
            } else {
                echo '<span class="description">System column</span>';
            }
            
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        // Expected vs Current comparison
        if ($expected_schema) {
            echo '<h3>Expected vs Current Schema</h3>';
            $differences = $this->schema_manager->compare_schemas($table_name);
            
            if (empty($differences)) {
                echo '<div class="notice notice-success"><p>Schema is synchronized</p></div>';
            } else {
                echo '<div class="notice notice-warning">';
                echo '<p><strong>Schema differences found:</strong></p>';
                echo '<ul>';
                
                if (isset($differences['missing_columns'])) {
                    echo '<li>Missing columns: ' . implode(', ', $differences['missing_columns']) . '</li>';
                }
                
                if (isset($differences['extra_columns'])) {
                    echo '<li>Extra columns: ' . implode(', ', $differences['extra_columns']) . '</li>';
                }
                
                echo '</ul>';
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render add column form
     */
    private function render_add_column_form($table_name) {
        echo '<h2>Add Column to ' . $table_name . '</h2>';
        
        echo '<form method="post">';
        wp_nonce_field('add_column');
        echo '<input type="hidden" name="action" value="add_column">';
        echo '<input type="hidden" name="table_name" value="' . $table_name . '">';
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="column_name">Column Name</label></th>';
        echo '<td><input type="text" id="column_name" name="column_name" class="regular-text" required></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="column_type">Column Type</label></th>';
        echo '<td>';
        echo '<select id="column_type" name="column_type" class="regular-text">';
        echo '<option value="varchar(255)">VARCHAR(255)</option>';
        echo '<option value="varchar(100)">VARCHAR(100)</option>';
        echo '<option value="varchar(50)">VARCHAR(50)</option>';
        echo '<option value="text">TEXT</option>';
        echo '<option value="int(11)">INT(11)</option>';
        echo '<option value="bigint(20)">BIGINT(20)</option>';
        echo '<option value="decimal(10,2)">DECIMAL(10,2)</option>';
        echo '<option value="date">DATE</option>';
        echo '<option value="datetime">DATETIME</option>';
        echo '<option value="tinyint(1)">TINYINT(1) - Boolean</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="column_null">Allow NULL</label></th>';
        echo '<td>';
        echo '<select id="column_null" name="column_null" class="regular-text">';
        echo '<option value="NULL">Allow NULL</option>';
        echo '<option value="NOT NULL">NOT NULL</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="column_default">Default Value</label></th>';
        echo '<td><input type="text" id="column_default" name="column_default" class="regular-text" placeholder="Leave empty for no default"></td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<div class="form-actions">';
        echo '<button type="submit" class="button button-primary">Add Column</button>';
        echo '<a href="?page=klage-click-database&tab=schema&table=' . $table_name . '&action=structure" class="button">Cancel</a>';
        echo '</div>';
        
        echo '</form>';
    }
    
    /**
     * Render data management tab
     */
    private function render_data_management_tab() {
        $table_name = $_GET['table'] ?? 'klage_cases';
        $action = $_GET['action'] ?? 'list';
        $record_id = intval($_GET['id'] ?? 0);
        
        echo '<div class="data-management">';
        echo '<h2>Data Management</h2>';
        
        // Table selector
        echo '<div class="table-selector">';
        echo '<label for="table-select">Select Table:</label>';
        echo '<select id="table-select" onchange="window.location.href=\'?page=klage-click-database&tab=data&table=\' + this.value">';
        
        $tables = array_keys($this->schema_manager->get_complete_schema_definition());
        foreach ($tables as $table) {
            $selected = ($table === $table_name) ? 'selected' : '';
            echo '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        if ($action === 'list') {
            $this->render_data_list($table_name);
        } elseif ($action === 'edit' || $action === 'new') {
            $this->render_data_form($table_name, $record_id);
        }
        
        echo '</div>';
    }
    
    /**
     * Render data list
     */
    private function render_data_list($table_name) {
        $data = $this->schema_manager->get_table_data($table_name, 50, 0);
        
        if (isset($data['error'])) {
            echo '<div class="notice notice-error"><p>' . $data['error'] . '</p></div>';
            return;
        }
        
        echo '<div class="data-list">';
        echo '<div class="data-list-header">';
        echo '<h3>Records in ' . $table_name . '</h3>';
        echo '<a href="?page=klage-click-database&tab=data&table=' . $table_name . '&action=new" class="button button-primary">Add New Record</a>';
        echo '</div>';
        
        if (empty($data['data'])) {
            echo '<p>No records found.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        
        $columns = array_keys($data['data'][0]);
        foreach ($columns as $column) {
            echo '<th>' . $column . '</th>';
        }
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($data['data'] as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo '<td>' . esc_html(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . '</td>';
            }
            echo '<td>';
            echo '<a href="?page=klage-click-database&tab=data&table=' . $table_name . '&action=edit&id=' . $row['id'] . '" class="button button-small">Edit</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        echo '<div class="data-list-footer">';
        echo '<p>Showing ' . count($data['data']) . ' of ' . $data['total'] . ' records</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Render data form
     */
    private function render_data_form($table_name, $record_id) {
        $data = array();
        
        if ($record_id > 0) {
            $result = $this->schema_manager->get_table_data($table_name, 1, $record_id - 1);
            if (!empty($result['data'])) {
                $data = $result['data'][0];
            }
        }
        
        echo '<div class="data-form">';
        echo '<h3>' . ($record_id > 0 ? 'Edit' : 'Add New') . ' Record in ' . $table_name . '</h3>';
        
        echo '<form method="post">';
        wp_nonce_field('save_data');
        echo '<input type="hidden" name="action" value="save_data">';
        echo '<input type="hidden" name="table_name" value="' . $table_name . '">';
        echo '<input type="hidden" name="record_id" value="' . $record_id . '">';
        
        echo $this->form_generator->generate_form($table_name, $data, array('id', 'created_at', 'updated_at'));
        
        echo '<div class="form-actions">';
        echo '<button type="submit" class="button button-primary">Save Record</button>';
        echo '<a href="?page=klage-click-database&tab=data&table=' . $table_name . '" class="button">Cancel</a>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        echo $this->form_generator->generate_form_validation_js($table_name);
    }
    
    /**
     * Render import/export tab
     */
    private function render_import_export_tab() {
        echo '<div class="import-export">';
        echo '<h2>Import/Export</h2>';
        
        $templates = $this->import_export_manager->get_available_templates();
        
        // Template download section
        echo '<div class="template-download">';
        echo '<h3>Download CSV Templates</h3>';
        echo '<div class="template-grid">';
        
        foreach ($templates as $table_name => $table_templates) {
            echo '<div class="template-group">';
            echo '<h4>' . $table_name . '</h4>';
            
            foreach ($table_templates as $template_key => $template_info) {
                echo '<div class="template-item">';
                echo '<h5>' . $template_info['name'] . '</h5>';
                echo '<p>' . $template_info['description'] . '</p>';
                echo '<a href="?page=klage-click-database&tab=import&action=download_template&table=' . $table_name . '&template=' . $template_key . '" class="button">Download Template</a>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
        
        // CSV import section
        echo '<div class="csv-import">';
        echo '<h3>Import CSV Data</h3>';
        
        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field('import_csv');
        echo '<input type="hidden" name="action" value="import_csv">';
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="table_name">Target Table</label></th>';
        echo '<td>';
        echo '<select id="table_name" name="table_name">';
        foreach (array_keys($this->schema_manager->get_complete_schema_definition()) as $table) {
            echo '<option value="' . $table . '">' . $table . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th><label for="csv_data">CSV Data</label></th>';
        echo '<td>';
        echo '<textarea id="csv_data" name="csv_data" rows="10" cols="80" placeholder="Paste CSV data here or upload file below"></textarea>';
        echo '<p class="description">Paste your CSV data here with semicolon (;) as separator</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<button type="submit" class="button button-primary">Import CSV</button>';
        echo '</form>';
        
        echo '</div>';
        
        // Data export section
        echo '<div class="data-export">';
        echo '<h3>Export Data</h3>';
        
        foreach (array_keys($this->schema_manager->get_complete_schema_definition()) as $table) {
            echo '<div class="export-item">';
            echo '<h4>' . $table . '</h4>';
            echo '<a href="?page=klage-click-database&tab=import&action=export&table=' . $table . '" class="button">Export CSV</a>';
            echo '</div>';
        }
        
        echo '</div>';
        
        echo '</div>';
        
        // Handle download template
        if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
            $table = sanitize_text_field($_GET['table']);
            $template = sanitize_text_field($_GET['template']);
            
            $csv_content = $this->import_export_manager->generate_csv_template($table, $template);
            
            if ($csv_content) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $table . '_' . $template . '_template.csv"');
                header('Content-Length: ' . strlen($csv_content));
                echo $csv_content;
                exit;
            }
        }
        
        // Handle data export
        if (isset($_GET['action']) && $_GET['action'] === 'export') {
            $table = sanitize_text_field($_GET['table']);
            
            $csv_content = $this->import_export_manager->export_table_data($table);
            
            if ($csv_content) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $table . '_export.csv"');
                header('Content-Length: ' . strlen($csv_content));
                echo $csv_content;
                exit;
            }
        }
        
        // Add CSS
        echo '<style>
        .table-selector {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .schema-actions {
            margin: 20px 0;
        }
        
        .schema-actions .button {
            margin-right: 10px;
        }
        
        .schema-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .schema-status-card {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        
        .schema-status-card.status-ok {
            border-left: 4px solid #28a745;
        }
        
        .schema-status-card.status-missing {
            border-left: 4px solid #dc3545;
        }
        
        .schema-status-card.status-out-of-sync {
            border-left: 4px solid #ffc107;
        }
        
        .schema-status-card.status-error {
            border-left: 4px solid #dc3545;
        }
        
        .schema-details {
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
        }
        
        .global-schema-actions {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .table-structure {
            margin: 20px 0;
        }
        
        .table-structure h3 {
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .extra-column {
            background-color: #fff3cd;
        }
        
        .form-actions {
            margin-top: 20px;
        }
        
        .form-actions .button {
            margin-right: 10px;
        }
        </style>';
    }
    
    /**
     * Render form generator tab
     */
    private function render_form_generator_tab() {
        $table_name = $_GET['table'] ?? 'klage_cases';
        
        echo '<div class="form-generator">';
        echo '<h2>Form Generator</h2>';
        
        // Table selector
        echo '<div class="table-selector">';
        echo '<label for="form-table-select">Select Table:</label>';
        echo '<select id="form-table-select" onchange="window.location.href=\'?page=klage-click-database&tab=forms&table=\' + this.value">';
        
        $tables = array_keys($this->schema_manager->get_complete_schema_definition());
        foreach ($tables as $table) {
            $selected = ($table === $table_name) ? 'selected' : '';
            echo '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
        }
        
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-preview">';
        echo '<h3>Generated Form for ' . $table_name . '</h3>';
        
        echo '<div class="form-container">';
        echo $this->form_generator->generate_form($table_name, array(), array('id', 'created_at', 'updated_at'));
        echo '</div>';
        
        echo '</div>';
        
        echo '</div>';
        
        echo $this->form_generator->generate_form_validation_js($table_name);
    }
    
    /**
     * AJAX handler for schema synchronization
     */
    public function ajax_sync_schema() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('sync_schema', 'nonce');
        
        $results = $this->schema_manager->synchronize_all_tables();
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for data export
     */
    public function ajax_export_data() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_ajax_referer('export_data', 'nonce');
        
        $table_name = sanitize_text_field($_POST['table_name']);
        $csv_content = $this->import_export_manager->export_table_data($table_name);
        
        wp_send_json_success(array('csv_content' => $csv_content));
    }
}