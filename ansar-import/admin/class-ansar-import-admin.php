<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeansar.com/
 * @since      1.0.0
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/admin
 * @author     Themeansar <info@themeansar.com>
 */
class Ansar_Import_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function import_data_ajax() {
        // Verify nonce
        if ( !isset($_POST['check_import_nonce']) || !wp_verify_nonce(wp_unslash($_POST['check_import_nonce']), 'ansar_demo_import_nonce') ) {
            wp_send_json_error(['status' => 'error', 'msg' => 'Security check failed.']);
            return;
        }

        // Sanitize inputs
        $theme_id   = isset($_POST['theme_id']) ? sanitize_key(($_POST['theme_id'])) : '';
        $customize  = isset($_POST['customize']) ? sanitize_key($_POST['customize']) : '';
        $widget     = isset($_POST['widget']) ? sanitize_key($_POST['widget']) : '';
        $content    = isset($_POST['content']) ? sanitize_key($_POST['content']) : '';
        $step       = isset($_POST['step']) ? sanitize_key($_POST['step']) : '';
        $theme_name = isset($_POST['theme_name']) ? sanitize_text_field(wp_unslash($_POST['theme_name'])) : false;

        // Call importer
        $ansar_importer = new Ansar_Import();
        $ansar_importer->install_demo($theme_id, $customize, $widget, $content, $step, $theme_name);
    }

    public function register_theme_page() {
        $starter_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjQ0IiBoZWlnaHQ9IjQ4Ij4KPHBhdGggZD0iTTAgMCBDMi45MTcwOTEzNyAxLjE1Njk0MzI4IDUuNTU2OTMwMjQgMi42NDcyMjE1NCA4LjI1IDQuMjUgQzguOTU2NDA2MjUgNC42NDU3NDIxOSA5LjY2MjgxMjUgNS4wNDE0ODQzNyAxMC4zOTA2MjUgNS40NDkyMTg3NSBDMTQuMDY3NDY3OTIgNy41NzMxNTI2MyAxNi43NjAyOTkxMiA5LjI4MjUyMzc4IDE5IDEzIEMxOS42MTE1NTIzMiAxNi42MjM4NjMxOCAxOS40NjM2OTc1MiAyMC4yNjc0NjUzMiAxOS40Mzc1IDIzLjkzNzUgQzE5LjQ2NzQ3MDcgMjUuNDIxNTMzMiAxOS40Njc0NzA3IDI1LjQyMTUzMzIgMTkuNDk4MDQ2ODggMjYuOTM1NTQ2ODggQzE5LjQ5NTE4NzEzIDMxLjE4Nzk5MzQxIDE5LjQ2NDg4MTcgMzQuMTUxODc0MzcgMTcuMzk4NDM3NSAzNy45MjE4NzUgQzE0LjUxMTMyMjE4IDQwLjQyMzQxNDY1IDExLjUwMzMyNzA1IDQyLjQ0MzU3OTMgOC4xODc1IDQ0LjMxMjUgQzcuNTk2NDY0ODQgNDQuNjcxNTAzOTEgNy4wMDU0Mjk2OSA0NS4wMzA1MDc4MSA2LjM5NjQ4NDM4IDQ1LjQwMDM5MDYyIEMyLjA2ODAyNzczIDQ3Ljg3ODUxNDY2IC0xLjA2NDI1ODU3IDQ4LjY4NjU3MDgyIC02IDQ4IEMtOC45MTcwOTEzNyA0Ni44NDMwNTY3MiAtMTEuNTU2OTMwMjQgNDUuMzUyNzc4NDYgLTE0LjI1IDQzLjc1IEMtMTQuOTU2NDA2MjUgNDMuMzU0MjU3ODEgLTE1LjY2MjgxMjUgNDIuOTU4NTE1NjIgLTE2LjM5MDYyNSA0Mi41NTA3ODEyNSBDLTIwLjA2NzQ2NzkyIDQwLjQyNjg0NzM3IC0yMi43NjAyOTkxMiAzOC43MTc0NzYyMiAtMjUgMzUgQy0yNS42MTE1NTIzMiAzMS4zNzYxMzY4MiAtMjUuNDYzNjk3NTIgMjcuNzMyNTM0NjggLTI1LjQzNzUgMjQuMDYyNSBDLTI1LjQ1NzQ4MDQ3IDIzLjA3MzE0NDUzIC0yNS40Nzc0NjA5NCAyMi4wODM3ODkwNiAtMjUuNDk4MDQ2ODggMjEuMDY0NDUzMTIgQy0yNS40OTUxODcxMyAxNi44MTIwMDY1OSAtMjUuNDY0ODgxNyAxMy44NDgxMjU2MyAtMjMuMzk4NDM3NSAxMC4wNzgxMjUgQy0yMC41MTEzMjIxOCA3LjU3NjU4NTM1IC0xNy41MDMzMjcwNSA1LjU1NjQyMDcgLTE0LjE4NzUgMy42ODc1IEMtMTMuNTk2NDY0ODQgMy4zMjg0OTYwOSAtMTMuMDA1NDI5NjkgMi45Njk0OTIxOSAtMTIuMzk2NDg0MzggMi41OTk2MDkzOCBDLTguMDY4MDI3NzMgMC4xMjE0ODUzNCAtNC45MzU3NDE0MyAtMC42ODY1NzA4MiAwIDAgWiBNLTUgMTAgQy02LjMyIDEyLjY0IC03LjY0IDE1LjI4IC05IDE4IEMtMTAuNjUgMTggLTEyLjMgMTggLTE0IDE4IEMtMTQgMTkuMzIgLTE0IDIwLjY0IC0xNCAyMiBDLTEzLjAxIDIyIC0xMi4wMiAyMiAtMTEgMjIgQy0xMS4yODg3NSAyMi42OTg2NzE4OCAtMTEuNTc3NSAyMy4zOTczNDM3NSAtMTEuODc1IDI0LjExNzE4NzUgQy0xMi4yNDYyNSAyNS4wMjcyNjU2MiAtMTIuNjE3NSAyNS45MzczNDM3NSAtMTMgMjYuODc1IEMtMTMuNTU2ODc1IDI4LjIzMjM4MjgxIC0xMy41NTY4NzUgMjguMjMyMzgyODEgLTE0LjEyNSAyOS42MTcxODc1IEMtMTUuMTE0OTAyMjYgMzEuODg5ODM4NTggLTE1LjExNDkwMjI2IDMxLjg4OTgzODU4IC0xNSAzNCBDLTEzLjY4IDM0IC0xMi4zNiAzNCAtMTEgMzQgQy0xMC4wMSAzMS4wMyAtOS4wMiAyOC4wNiAtOCAyNSBDLTYuMjIyMjU2ODMgMjUuNTQwMjY0OSAtNC40NTI5MDU3MyAyNi4xMDgxOTA0NyAtMi42ODc1IDI2LjY4NzUgQy0xLjcwMTM2NzE5IDI3LjAwMDc0MjE5IC0wLjcxNTIzNDM4IDI3LjMxMzk4NDM4IDAuMzAwNzgxMjUgMjcuNjM2NzE4NzUgQzMuNTI5MjAyNSAyOS4yNjcyODE3MyA0LjI1NjgwMDQ2IDMwLjkxNzQ1MDY3IDYgMzQgQzYuOTkgMzQgNy45OCAzNCA5IDM0IEM5LjY2IDMzLjM0IDEwLjMyIDMyLjY4IDExIDMyIEMxMC42Nzk1ODYwMyAyOS4zOTcxOTQzMiAxMC42Nzk1ODYwMyAyOS4zOTcxOTQzMiAxMCAyNyBDOS4zODEyNSAyNi45NTg3NSA4Ljc2MjUgMjYuOTE3NSA4LjEyNSAyNi44NzUgQzcuNDIzNzUgMjYuNTg2MjUgNi43MjI1IDI2LjI5NzUgNiAyNiBDNS4yNDMyMDU2NSAyNC4wMzIzMzQ2OSA0LjYwMjUzMDEgMjIuMDIwMjQ3OTkgNCAyMCBDMi40MTUzMjUwNiAxNi42MjQzOTA4NCAwLjcxNDUzNDE4IDEzLjMxMTI3NTkzIC0xIDEwIEMtMi4zMiAxMCAtMy42NCAxMCAtNSAxMCBaICIgZmlsbD0iI0E2QTlBQyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUsMCkiLz4KPHBhdGggZD0iTTAgMCBDMC42NiAwIDEuMzIgMCAyIDAgQzIuOTkgMi42NCAzLjk4IDUuMjggNSA4IEMwLjI1IDcuMjUgMC4yNSA3LjI1IC0yIDUgQy0xLjM0IDMuMzUgLTAuNjggMS43IDAgMCBaICIgZmlsbD0iI0E2QThBQiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjEsMTUpIi8+Cjwvc3ZnPgo=';

        
        add_menu_page('Ansar Import', 'Ansar Import', 'manage_options', 'ansar-starter-sites', array($this, 'starter_sites_page'), $starter_icon , 20);
        
        add_submenu_page('ansar-starter-sites', 'Starter Sites', 'Starter Sites', 'manage_options', 'ansar-starter-sites');
        
        if(wp_get_theme()->get('Author') == 'themeansar' || wp_get_theme()->get('Author') == 'Themeansar'){
            add_submenu_page('ansar-starter-sites', 'Theme Demos', 'Theme Demos', 'manage_options', 'ansar-demo-import', array($this, 'theme_option_page'));
        }
    }
    
    function plugin_get_template_content( $data = []) {
        // Generate the file path
        $template = ANSAR_IMPORT_DIR_PATH . "admin/partials/ansar-demo-box.php";
    
        if (file_exists($template)) {
            // Start output buffering
            ob_start();
    
            // Extract data array into variables for use in the template
            if (!empty($data)) {
                extract($data);
            }
    
            // Include the template file
            include $template;
    
            // Get the buffered content
            return ob_get_clean();
        } else {
            // Return a placeholder or empty string if the file doesn't exist
            error_log("Template not found: {$template}");
            return '';
        }
    }

    function infinity_load_demos() {
        if ( isset($_POST) ) {
            $paged = $_POST['paged'];
            $seed = $_POST['seed'];
        }
        $theme_data = wp_get_theme();
        $theme_name = $theme_data->get('Name');

        $theme_data_api = wp_remote_get(esc_url_raw("https://api.themeansar.com/wp-json/wp/v2/demos/?orderby=rand&seed=$seed&per_page=12&page=$paged"), [ 'timeout' => 15 ]);
        $theme_data_api_body = wp_remote_retrieve_body($theme_data_api);
        $quaried_demos = json_decode($theme_data_api_body, TRUE);
        
        $response = '';

        if (isset($quaried_demos["code"]) && $quaried_demos["code"] == 'rest_post_invalid_page_number') {
            $response = 'No demos found';
        } else {
            foreach ($quaried_demos as $demo) {
                // Pass $demo as data to the template
                $response .= $this->plugin_get_template_content(['demo' => $demo, 'theme_name' => $theme_name]);
            }
            $paged++;
        } 
    
        $result = [
            'html' => $response,
            'paged' => $paged,
        ];
    
        echo json_encode($result);
        exit;
    }

    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function theme_option_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ansar-import-admin-display.php';
    }

    /**
     * Render the starter sites page for plugin
     *
     * @since  1.0.0
     */
    public function starter_sites_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ansar-starter-sites-display.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
		$screen = get_current_screen();

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ansar_Import_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ansar_Import_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ( (isset( $screen->base ) && $screen->base == 'toplevel_page_ansar-starter-sites' ) || isset( $screen->base ) && $screen->base == 'ansar-import_page_ansar-demo-import' ) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ansar-import-admin.css', array(), $this->version, 'all');
            wp_enqueue_style('uikit', plugin_dir_url(__FILE__) . 'css/uikit.min.css', array(), $this->version, 'all');
            wp_enqueue_style('theme');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
		$screen = get_current_screen();

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ansar_Import_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ansar_Import_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        //$themes = wp_prepare_themes_for_js( array( wp_get_theme() ) );
        if ( (isset( $screen->base ) && $screen->base == 'toplevel_page_ansar-starter-sites' ) || isset( $screen->base ) && $screen->base == 'ansar-import_page_ansar-demo-import' ) {
            remove_all_actions( 'admin_notices' );
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ansar-import-admin.js', array('jquery'), $this->version, false);
            wp_enqueue_script('uikit-js', plugin_dir_url(__FILE__) . 'js/uikit.min.js', array('jquery'), $this->version, false);
            $theme_data = wp_get_theme();
            $theme_name = $theme_data->get('Name');
            $theme_slug = $theme_data->get('TextDomain');
            
            wp_localize_script(
                $this->plugin_name,
                'my_ajax_object',
                    array( 
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('ansar_demo_import_nonce'),
                        'theme_name' => $theme_name
                    )
            );
        }

        if( isset($_GET['page']) && $_GET['page'] === "ansar-demo-import" ) {
            $theme_data_api = wp_remote_get(esc_url_raw("https://api.themeansar.com/wp-json/wp/v2/demos/?search=%27" . urlencode($theme_name) . "%27&per_page=50"), [ 'timeout' => 15 ]);
            $theme_data_api_body = wp_remote_retrieve_body($theme_data_api);
            $all_demos = json_decode($theme_data_api_body, TRUE);
    
            wp_localize_script(
                $this->plugin_name, 
                'ansar_theme_object', 
                $all_demos
            );
        }
    }

}
