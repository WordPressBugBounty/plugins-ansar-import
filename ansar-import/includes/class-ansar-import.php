<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeansar.com/
 * @since      1.0.0
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ansar_Import
 * @subpackage Ansar_Import/includes
 * @author     Themeansar <info@themeansar.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ansar_Import {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Ansar_Import_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('ANSAR_IMPORT_VERSION')) {
            $this->version = ANSAR_IMPORT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'ansar-import';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }
    
    function is_theme_installed($theme_slug) {
        $theme = wp_get_theme($theme_slug);
        return $theme->exists();
    }
    
    function is_theme_active($theme_slug) {
        if ( empty( $theme_slug ) ) {
            return false;
        }

        $active_stylesheet = strtolower( (string) get_stylesheet() );
        $active_template   = strtolower( (string) get_template() );
        $check_slug        = strtolower( (string) $theme_slug );

        // Direct match with active theme stylesheet or parent template
        if ( $active_stylesheet === $check_slug || $active_template === $check_slug ) {
            return true;
        }

        $theme_data = wp_get_theme();
        if ( 0 === strcasecmp( $theme_data->get( 'Name' ), $theme_slug ) ) {
            return true;
        }

        // Child theme support: verify if active theme's parent matches the required theme
        if ( $theme_data->parent() && 0 === strcasecmp( $theme_data->parent()->get( 'Name' ), $theme_slug ) ) {
            return true;
        }

        return false;
    }
    
    function install_and_activate_theme($theme_slug) {
    
        // Include necessary WordPress files
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/theme.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        
        // Check if theme already exists
        $theme = wp_get_theme($theme_slug);
        if ($theme->exists()) {
            switch_theme($theme_slug);
            wp_send_json_success(['status' => 'ok', 'msg' => 'Theme already installed and activated']);
        }
        
        // Fetch theme details from WordPress.org
        $api = themes_api('theme_information', ['slug' => $theme_slug]);
        if (is_wp_error($api)) {
            wp_send_json_error('Error: Unable to fetch theme details.');
        }
        
        // Install the theme using the WordPress Theme Upgrader
        
        // Initialize the upgrader with a silent skin
        $skin = new Automatic_Upgrader_Skin();
        $skin->api = $api;

        $upgrader = new Theme_Upgrader($skin);
        $result = $upgrader->install($api->download_link);

        if (!$result) {
            wp_send_json_error('Error: Theme installation failed.');
        }
        // Check if the installed theme is a child theme
        $theme = wp_get_theme($theme_slug);
        if (isset($api->parent)) {
            $parent_slug = $api->parent['slug'];
            $parent_theme = wp_get_theme($parent_slug);
    
            // If the parent theme is not installed, install it
            if (!$parent_theme->exists()) {
                $parent_api = themes_api('theme_information', ['slug' => $parent_slug]);
                if (is_wp_error($parent_api)) {
                    wp_send_json_error("Error: Unable to fetch parent theme details for '{$parent_slug}'.");
                }
    
                $parent_result = $upgrader->install($parent_api->download_link);
                if (is_wp_error($parent_result)) {
                    wp_send_json_error("Error: Unable to install the parent theme '{$parent_slug}'.");
                }
            }
    
            // Activate the parent theme first
            switch_theme($parent_slug);
        }
    
        // Finally, activate the child theme
        switch_theme($theme_slug);
    
        wp_send_json_success([ 'status' => 'ok', 'msg' => 'Required Theme Installed/Activated Successfully' ]);
    }
    

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Ansar_Import_Loader. Orchestrates the hooks of the plugin.
     * - Ansar_Import_i18n. Defines internationalization functionality.
     * - Ansar_Import_Admin. Defines all hooks for the admin area.
     * - Ansar_Import_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ansar-import-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ansar-import-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ansar-import-admin.php';

        /**
         * Reusable engine modules.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/engine/plugin/class-ansar-plugin-manager.php';

        $this->loader = new Ansar_Import_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Ansar_Import_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Ansar_Import_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Sanitize and retrieve the demo asset URL.
     *
     * @param string $url Original asset URL.
     * @return string Sanitized asset URL.
     */
    public static function get_cdn_url( $url ) {
        return trim( (string) $url );
    }

    /**
     * Build candidate URLs for downloading demo files.
     *
     * @param string $url Original file URL.
     * @return array List of candidate URLs to try.
     */
    public static function get_demo_file_candidate_urls( $url ) {
        $url = trim( (string) $url );
        if ( '' === $url ) {
            return array();
        }

        return array( $url );
    }

    /**
     * Download a remote demo file (data.xml, widgets.wie, customizer.dat) with automatic CDN mirror failover.
     *
     * @param string $url     Remote file URL.
     * @param int    $timeout Timeout per attempt in seconds.
     * @return string|WP_Error Downloaded file body on success, WP_Error on failure.
     */
    private function download_demo_file_with_fallback( $url, $timeout = 60 ) {
        $candidates = self::get_demo_file_candidate_urls( $url );
        if ( empty( $candidates ) ) {
            return new WP_Error( 'empty_url', __( 'Demo file URL is empty.', 'ansar-import' ) );
        }

        $last_error = null;

        foreach ( $candidates as $candidate_url ) {
            $response = wp_remote_get(
                esc_url_raw( $candidate_url ),
                array(
                    'timeout'    => $timeout,
                    'decompress' => true,
                    'headers'    => array(
                        'Accept' => '*/*',
                    ),
                )
            );

            // If download failed due to local SSL/CA certificate error (e.g. cURL error 60), retry with sslverify disabled.
            if ( is_wp_error( $response ) ) {
                $error_message = strtolower( (string) $response->get_error_message() );
                if (
                    false !== strpos( $error_message, 'ssl' ) ||
                    false !== strpos( $error_message, 'certificate' ) ||
                    false !== strpos( $error_message, 'curl error 60' ) ||
                    false !== strpos( $error_message, 'curl error 77' ) ||
                    false !== strpos( $error_message, 'local issuer certificate' )
                ) {
                    $response = wp_remote_get(
                        esc_url_raw( $candidate_url ),
                        array(
                            'timeout'    => $timeout,
                            'decompress' => true,
                            'sslverify'  => false,
                            'headers'    => array(
                                'Accept' => '*/*',
                            ),
                        )
                    );
                }
            }

            if ( is_wp_error( $response ) ) {
                $last_error = $response;
                continue;
            }

            $code = (int) wp_remote_retrieve_response_code( $response );
            if ( 200 !== $code ) {
                $last_error = new WP_Error(
                    'http_error',
                    // translators: %d: HTTP response status code.
                    sprintf( __( 'Demo server returned HTTP %d while downloading content files.', 'ansar-import' ), $code )
                );
                continue;
            }

            $body = wp_remote_retrieve_body( $response );
            if ( empty( $body ) ) {
                $last_error = new WP_Error(
                    'empty_body',
                    __( 'Downloaded demo content file is empty.', 'ansar-import' )
                );
                continue;
            }

            return $body;
        }

        return $last_error ? $last_error : new WP_Error( 'download_failed', __( 'Failed to download demo file.', 'ansar-import' ) );
    }

    public function install_demo($theme_id, $customize = true, $widget = true, $content = true, $step = 'init', $theme = false) {
        
        // Prevent timeout and memory exhaustion on heavy demo files
        if ( function_exists( 'set_time_limit' ) ) {
            // phpcs:disable Squiz.PHP.DiscouragedFunctions.Discouraged, WordPress.PHP.NoSilencedErrors.Discouraged -- Extended execution time needed for demo importing.
            @set_time_limit( 300 );
        }
        if ( function_exists( 'wp_raise_memory_limit' ) ) {
            wp_raise_memory_limit( 'admin' );
        }

        // Setting upload Dir safely
        $upload = wp_upload_dir();
        $upload_dir = trailingslashit( $upload['basedir'] ) . 'ansar_import_data';
        if ( ! is_dir( $upload_dir ) ) {
            wp_mkdir_p( $upload_dir );
        }
    
        if($step == 'theme_init'){
            if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
                wp_send_json_success(['status' => 'ok', 'msg' => 'Active block theme preserved.']);
                return;
            }
            if($theme !== false){
                if($this->is_theme_installed($theme) == false || $this->is_theme_active($theme) == false){
                    $this->install_and_activate_theme($theme);
                }else{
                    wp_send_json_success(['status' => 'ok', 'msg' => 'Theme is already active.']);
                }
            }
        }

        if($step == 'install_plugins'){
            if (empty($theme_id)) {
                wp_send_json_error(['status' => 'error', 'msg' => __('No theme id passed!', 'ansar-import')]);
                return;
            }
            $plugin_manager = Ansar_Plugin_Manager::instance();
            $result = $plugin_manager->install_required_plugins($theme_id);
            if (isset($result['status']) && $result['status'] === 'error') {
                wp_send_json_error($result);
            } else {
                wp_send_json_success($result);
            }
            return;
        }
        
        if($step == 'demo_data_init'){
            if (empty($theme_id)) {
                wp_send_json_error(array('status' => 'error', 'msg' => __('No valid theme ID provided for demo import.', 'ansar-import')));
                return;
            }

            if($content !== 'false' ){
                $nav_menus = wp_get_nav_menus();
                // Delete navigation menus.
                if (!empty($nav_menus)) {
                    foreach ($nav_menus as $nav_menu) {
                        wp_delete_nav_menu($nav_menu->slug);
                    }
                }
            }

            // Load Importer API
            require_once ABSPATH . 'wp-admin/includes/import.php';
            if (!class_exists('WP_Importer')) {
                $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                if (file_exists($class_wp_importer)) {
                    require_once( $class_wp_importer );
                }
            }

            // Get demo metadata from transient cache first, falling back to remote API
            $plugin_manager = Ansar_Plugin_Manager::instance();
            $theme_data_api = $plugin_manager->fetch_demo_data( $theme_id );
            if ( is_wp_error( $theme_data_api ) ) {
                // translators: %s: Error message returned by the demo server request.
                wp_send_json_error(array('status' => 'error', 'msg' => sprintf(__('Failed to connect to demo server: %s', 'ansar-import'), $theme_data_api->get_error_message())));
                return;
            }

            if ( empty($theme_data_api) || !is_array($theme_data_api) ) {
                wp_send_json_error(array('status' => 'error', 'msg' => __('Invalid response received from demo server.', 'ansar-import')));
                return;
            }

            if ( ! is_dir( $upload_dir ) ) {
                wp_mkdir_p( $upload_dir );
            }

            // 1. Download demo content (data.xml) with CDN mirror failover
            if ( $content !== 'false' ) {
                if ( empty( $theme_data_api['data_file_url'] ) ) {
                    wp_send_json_error(array('status' => 'error', 'msg' => __('Demo content file URL is missing on the server.', 'ansar-import')));
                    return;
                }

                $body = $this->download_demo_file_with_fallback( $theme_data_api['data_file_url'], 60 );
                if ( is_wp_error( $body ) ) {
                    // translators: %s: Error message returned when downloading demo content fails.
                    wp_send_json_error(array('status' => 'error', 'msg' => sprintf(__('Failed to download demo content: %s', 'ansar-import'), $body->get_error_message())));
                    return;
                }

                $saved = file_put_contents( trailingslashit( $upload_dir ) . 'data.xml', $body );
                if ( false === $saved || ! file_exists( trailingslashit( $upload_dir ) . 'data.xml' ) ) {
                    wp_send_json_error(array('status' => 'error', 'msg' => __('Could not save data.xml to uploads folder. Please check directory permissions.', 'ansar-import')));
                    return;
                }
            }

            // 2. Download widgets with CDN mirror failover
            if ( $widget !== 'false' && ! empty( $theme_data_api['widget_file_url'] ) ) {
                $body = $this->download_demo_file_with_fallback( $theme_data_api['widget_file_url'], 30 );
                if ( ! is_wp_error( $body ) && ! empty( $body ) ) {
                    file_put_contents( trailingslashit( $upload_dir ) . 'widgets.wie', $body );
                }
            }

            // 3. Download customizer settings with CDN mirror failover
            if ( $customize !== 'false' && ! empty( $theme_data_api['customizer_file_url'] ) ) {
                $body = $this->download_demo_file_with_fallback( $theme_data_api['customizer_file_url'], 30 );
                if ( ! is_wp_error( $body ) && ! empty( $body ) ) {
                    file_put_contents( trailingslashit( $upload_dir ) . 'customizer.dat', $body );
                }
            }

            // Remove existing front page if present
            $home_d = get_posts(
                array(
                    'post_type'      => 'page',
                    'post_title'     => 'Home',
                    'post_status'    => 'any',
                    'numberposts'    => 1,
                )
            );
            if (!empty($home_d)) {
                $home_id = $home_d[0]->ID;
                wp_delete_post($home_id, true);
            }

            wp_send_json_success(['status' => 'ok', 'msg' => 'Demo Data Files Downloaded and Initialized Successfully']);
            return;
        }
        
        if($step == 'demo_data_import'){
            $data_file = trailingslashit( $upload_dir ) . 'data.xml';
            $wiget_file = trailingslashit( $upload_dir ) . 'widgets.wie';
            $customizer_file = trailingslashit( $upload_dir ) . 'customizer.dat';

            // Auto-recovery: If data.xml is somehow missing, try re-downloading on-the-fly
            if ($content !== 'false' && !file_exists($data_file)) {
                if (!empty($theme_id)) {
                    $theme_data_api = Ansar_Plugin_Manager::instance()->fetch_demo_data( $theme_id );
                    if ( is_array( $theme_data_api ) && ! empty( $theme_data_api['data_file_url'] ) ) {
                        $body = $this->download_demo_file_with_fallback( $theme_data_api['data_file_url'], 60 );
                        if ( ! is_wp_error( $body ) && ! empty( $body ) ) {
                            file_put_contents( $data_file, $body );
                        }
                    }
                }
            }

            if ($content !== 'false' && !file_exists($data_file)) {
                wp_send_json_error(array('status' => 'error', 'msg' => __('Demo content file (data.xml) could not be found. Please try again.', 'ansar-import')));
                return;
            }

            // Suppress all output from WordPress Importer
            ob_start();

            try {
                if ($content !== 'false' && file_exists($data_file)) {
                    if ( ! class_exists( 'ANSAR_IMPORT_WXR_Parser' ) ) {
                        require_once ANSAR_IMPORT_DIR_PATH . 'includes/parsers.php';
                    }
                    if ( ! class_exists( 'WP_Importer' ) ) {
                        $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
                        if ( file_exists( $class_wp_importer ) ) {
                            require_once $class_wp_importer;
                        }
                    }
                    if ( ! class_exists( 'ANSAR_IMPORT_WP_Import' ) ) {
                        require_once ANSAR_IMPORT_DIR_PATH . 'includes/class-wp-import.php';
                    }
                    $import = new ANSAR_IMPORT_WP_Import();
                    $import->dispatch();
                }

                if ($widget !== 'false' && file_exists($wiget_file)) {
                    $sidebars_widgets = get_option( 'sidebars_widgets', array() );
                    if (is_array($sidebars_widgets)) {
                        foreach ($sidebars_widgets as $key => $widgets) {
                            $sidebars_widgets[$key] = array();
                        }
                        wp_set_sidebars_widgets($sidebars_widgets);
                    }
                    $this->ansar_import_wie_import_data($wiget_file);
                }

                if ($customize !== 'false' && file_exists($customizer_file)) {
                    $this->ans_import_customizer_settings($customizer_file);
                }
            } catch (Exception $e) {
                unset( $e );
            } catch (Throwable $t) {
                unset( $t );
            }

            if (ob_get_length()) {
                ob_end_clean();
            }

            wp_send_json_success([
                'status' => 'ok',
                'msg' => 'Theme Demo Data Imported Successfully'
            ]);
            return;
        }
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Ansar_Import_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_ajax_import_action', $plugin_admin, 'import_data_ajax');
        $this->loader->add_action('admin_menu', $plugin_admin, 'register_theme_page');
        $this->loader->add_action('admin_menu', $plugin_admin, 'override_theme_starter_sites_submenu', 999);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'cleanup_legacy_theme_enqueues', 10000);
        $this->loader->add_action('enqueue_block_editor_assets', $plugin_admin, 'enqueue_block_editor_assets');
        $this->loader->add_action('wp_ajax_ansar_template_library_prepare_plugin', $plugin_admin, 'template_library_prepare_plugin_ajax');
        $this->loader->add_action('wp_ajax_infinity_load_demos', $plugin_admin, 'infinity_load_demos');
        $this->loader->add_action('wp_ajax_ansar_save_preferred_editor', $plugin_admin, 'save_preferred_editor_ajax');
        $this->loader->add_action('wp_ajax_ansar_get_demo_plugins_status', $plugin_admin, 'get_demo_plugins_status_ajax');
        $this->loader->add_action('wp_ajax_ansar_dismiss_hero', $plugin_admin, 'dismiss_hero_ajax');
        $this->loader->add_action('admin_init', $plugin_admin, 'handle_activation_redirect', 1);
        $this->loader->add_action('admin_footer', $plugin_admin, 'override_theme_get_started_links');
        $this->loader->add_action('after_switch_theme', $plugin_admin, 'on_theme_switched');
        $this->loader->add_action('ansar_import_render_starter_sites', $plugin_admin, 'render_starter_sites_component', 10, 1);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Ansar_Import_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function ansar_import_wie_available_widgets() {

        global $wp_registered_widget_controls;

        $widget_controls = $wp_registered_widget_controls;

        $available_widgets = array();

        foreach ($widget_controls as $widget) {

            // No duplicates.
            if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) {
                $available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
                $available_widgets[$widget['id_base']]['name'] = $widget['name'];
            }
        }

        return apply_filters('ansar_import_wie_available_widgets', $available_widgets);
    }

    /**
     * Import widget JSON data
     *
     * @since 0.4
     * @global array $wp_registered_sidebars
     * @param object $data JSON widget data from .wie file.
     * @return array Results array
     */
    public function ansar_import_wie_import_data($file) {

        global $wp_registered_sidebars;

        $w_data = implode('', file($file));
        $data = json_decode($w_data);

        // Have valid data?
        // If no data or could not decode.
        if (empty($data) || !is_object($data)) {
            return false;
        }

        // Hook before import.
        do_action('ansar_import_wie_before_import');
        $data = apply_filters('ansar_import_wie_import_data', $data);

        // Get all available widgets site supports.
        $available_widgets = $this->ansar_import_wie_available_widgets();

        // Get all existing widget instances.
        $widget_instances = array();
        foreach ($available_widgets as $widget_data) {
            $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
        }

        // Begin results.
        $results = array();

        // Loop import data's sidebars.
        foreach ($data as $sidebar_id => $widgets) {

            // Skip inactive widgets (should not be in export file).
            if ('wp_inactive_widgets' === $sidebar_id) {
                continue;
            }

            // Check if sidebar is available on this site.
            // Otherwise add widgets to inactive, and say so.
            if (isset($wp_registered_sidebars[$sidebar_id])) {
                $sidebar_available = true;
                $use_sidebar_id = $sidebar_id;
                $sidebar_message_type = 'success';
                $sidebar_message = '';
            } else {
                $sidebar_available = false;
                $use_sidebar_id = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
                $sidebar_message_type = 'error';
                $sidebar_message = esc_html__('Widget area does not exist in theme (using Inactive)', 'ansar-import');
            }

            // Result for sidebar
            // Sidebar name if theme supports it; otherwise ID.
            $results[$sidebar_id]['name'] = !empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id;
            $results[$sidebar_id]['message_type'] = $sidebar_message_type;
            $results[$sidebar_id]['message'] = $sidebar_message;
            $results[$sidebar_id]['widgets'] = array();

            // Loop widgets.
            foreach ($widgets as $widget_instance_id => $widget) {

                $fail = false;

                // Get id_base (remove -# from end) and instance ID number.
                $id_base = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
                $instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

                // Does site support this widget?
                if (!$fail && !isset($available_widgets[$id_base])) {
                    $fail = true;
                    $widget_message_type = 'error';
                    $widget_message = esc_html__('Site does not support widget', 'ansar-import'); // Explain why widget not imported.
                }

                // Filter to modify settings object before conversion to array and import
                // Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
                // Ideally the newer ansar_import_wie_widget_settings_array below will be used instead of this.
                $widget = apply_filters('ansar_import_wie_widget_settings', $widget);

                // Convert multidimensional objects to multidimensional arrays
                // Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
                // Without this, they are imported as objects and cause fatal error on Widgets page
                // If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
                // It is probably much more likely that arrays are used than objects, however.
                $widget = json_decode(wp_json_encode($widget), true);

                // Filter to modify settings array
                // This is preferred over the older ansar_import_wie_widget_settings filter above
                // Do before identical check because changes may make it identical to end result (such as URL replacements).
                $widget = apply_filters('ansar_import_wie_widget_settings_array', $widget);

                // Does widget with identical settings already exist in same sidebar?
                if (!$fail && isset($widget_instances[$id_base])) {

                    // Get existing widgets in this sidebar.
                    $sidebars_widgets = get_option('sidebars_widgets');
                    $sidebar_widgets = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array(); // Check Inactive if that's where will go.
                    // Loop widgets with ID base.
                    $single_widget_instances = !empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
                    foreach ($single_widget_instances as $check_id => $check_widget) {

                        // Is widget in same sidebar and has identical settings?
                        if (in_array("$id_base-$check_id", $sidebar_widgets, true) && (array) $widget === $check_widget) {

                            $fail = true;
                            $widget_message_type = 'warning';

                            // Explain why widget not imported.
                            $widget_message = esc_html__('Widget already exists', 'ansar-import');

                            break;
                        }
                    }
                }

                // No failure.
                if (!$fail) {

                    // Add widget instance
                    $single_widget_instances = get_option('widget_' . $id_base); // All instances for that widget ID base, get fresh every time.
                    $single_widget_instances = !empty($single_widget_instances) ? $single_widget_instances : array(
                        '_multiwidget' => 1, // Start fresh if have to.
                    );
                    $single_widget_instances[] = $widget; // Add it.
                    // Get the key it was given.
                    end($single_widget_instances);
                    $new_instance_id_number = key($single_widget_instances);

                    // If key is 0, make it 1
                    // When 0, an issue can occur where adding a widget causes data from other widget to load,
                    // and the widget doesn't stick (reload wipes it).
                    if ('0' === strval($new_instance_id_number)) {
                        $new_instance_id_number = 1;
                        $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                        unset($single_widget_instances[0]);
                    }

                    // Move _multiwidget to end of array for uniformity.
                    if (isset($single_widget_instances['_multiwidget'])) {
                        $multiwidget = $single_widget_instances['_multiwidget'];
                        unset($single_widget_instances['_multiwidget']);
                        $single_widget_instances['_multiwidget'] = $multiwidget;
                    }

                    // Update option with new widget.
                    update_option('widget_' . $id_base, $single_widget_instances);

                    // Assign widget instance to sidebar.
                    // Which sidebars have which widgets, get fresh every time.
                    $sidebars_widgets = get_option('sidebars_widgets');

                    // Avoid rarely fatal error when the option is an empty string
                    // https://github.com/churchthemes/widget-importer-exporter/pull/11.
                    if (!$sidebars_widgets) {
                        $sidebars_widgets = array();
                    }

                    // Use ID number from new widget instance.
                    $new_instance_id = $id_base . '-' . $new_instance_id_number;

                    // Add new instance to sidebar.
                    $sidebars_widgets[$use_sidebar_id][] = $new_instance_id;

                    // Save the amended data.
                    update_option('sidebars_widgets', $sidebars_widgets);

                    // After widget import action.
                    $after_widget_import = array(
                        'sidebar' => $use_sidebar_id,
                        'sidebar_old' => $sidebar_id,
                        'widget' => $widget,
                        'widget_type' => $id_base,
                        'widget_id' => $new_instance_id,
                        'widget_id_old' => $widget_instance_id,
                        'widget_id_num' => $new_instance_id_number,
                        'widget_id_num_old' => $instance_id_number,
                    );
                    do_action('ansar_import_wie_after_widget_import', $after_widget_import);

                    // Success message.
                    if ($sidebar_available) {
                        $widget_message_type = 'success';
                        $widget_message = esc_html__('Imported', 'ansar-import');
                    } else {
                        $widget_message_type = 'warning';
                        $widget_message = esc_html__('Imported to Inactive', 'ansar-import');
                    }
                }

                // Result for widget instance
                $results[$sidebar_id]['widgets'][$widget_instance_id]['name'] = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base; // Widget name or ID if name not available (not supported by site).
                $results[$sidebar_id]['widgets'][$widget_instance_id]['title'] = !empty($widget['title']) ? $widget['title'] : esc_html__('No Title', 'ansar-import'); // Show "No Title" if widget instance is untitled.
                $results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
                $results[$sidebar_id]['widgets'][$widget_instance_id]['message'] = $widget_message;
            }
        }

        // Hook after import.
        do_action('ansar_import_wie_after_import');

        // Return results.
        return apply_filters('ansar_import_wie_import_results', $results);
    }

    function ans_import_customizer_settings($custom_file) {
        // Check to see if the settings have already been imported.
        $template = get_template();
        $imported = get_option($template . '_customizer_import', false);

        // Bail if already imported.
        if ($imported) {
            return;
        }
        remove_theme_mods();
        // Get the path to the customizer export file.
        $path = $custom_file;

        // Return if the file doesn't exist.
        if (!file_exists($path)) {
            return;
        }

        // Get the settings data.
        $data = @unserialize(file_get_contents($path));

        // Return if something is wrong with the data.
        if ('array' != gettype($data) || !isset($data['mods'])) {
            return;
        }

        // Import options.
        if (isset($data['options'])) {
            foreach ($data['options'] as $option_key => $option_value) {
                update_option($option_key, $option_value);
            }
        }

        // Import mods.
        foreach ($data['mods'] as $key => $val) {
            set_theme_mod($key, $val);
        }

        //   echo 'Fixing Menu . .<br>';

        // Get all menus
        $menus = wp_get_nav_menus();

        // Define your target location key (usually defined in the theme)
        $target_location_key = 'primary'; // change if your theme uses something else

        // Try to find the menu you want to assign
        $target_menu = null;
        foreach ($menus as $menu) {
            if (strtolower($menu->name) === 'primary' || strtolower($menu->slug) === 'primary' || strtolower($menu->slug) === 'main-menu') {
                $target_menu = $menu;
                break;
            }
        }

        // If found, assign to location
        if ($target_menu) {
            $locations = get_theme_mod('nav_menu_locations');

            if (!is_array($locations)) {
                $locations = [];
            }

            $locations[$target_location_key] = $target_menu->term_id;

            set_theme_mod('nav_menu_locations', $locations);
        }

        // echo 'Seting Home and Blog page . .<br>';
        // Get "Home" page by its path
        $home_page = get_page_by_path('home');

        if ($home_page) {
            update_option('page_on_front', $home_page->ID);
            update_option('show_on_front', 'page');
        }
        $home_page = get_page_by_path('ta-home');

        if ($home_page) {
            update_option('page_on_front', $home_page->ID);
            update_option('show_on_front', 'page');
        }

        // Get "Blog" page by its path
        $blog_page = get_page_by_path('blog');

        if ($blog_page) {
            update_option('page_for_posts', $blog_page->ID);
        }
    }

}

if ( ! function_exists( 'ansar_import_get_theme_demos_url' ) ) {
    /**
     * Reusable helper function for Theme Ansar themes and child themes.
     * Returns the direct URL to the Theme Demos page with the Onboarding Wizard open.
     *
     * @param bool $onboarding Whether to force-launch the Onboarding wizard modal (default: true).
     * @return string Admin URL to Theme Demos.
     */
    function ansar_import_get_theme_demos_url( $onboarding = true ) {
        return add_query_arg(
            array(
                'page'       => 'ansar-demo-import',
                'onboarding' => $onboarding ? 'true' : 'false',
            ),
            admin_url( 'admin.php' )
        );
    }
}

if ( ! function_exists( 'ansar_import_get_starter_sites_url' ) ) {
    /**
     * Reusable helper function for Starter Sites page.
     *
     * @param bool $onboarding Whether to force-launch the Onboarding wizard modal (default: true).
     * @return string Admin URL to starter sites.
     */
    function ansar_import_get_starter_sites_url( $onboarding = true ) {
        return add_query_arg(
            array(
                'page'       => 'ansar-starter-sites',
                'onboarding' => $onboarding ? 'true' : 'false',
            ),
            admin_url( 'admin.php' )
        );
    }
}

if ( ! function_exists( 'ansar_import_render_starter_sites' ) ) {
    /**
     * Reusable helper function to render the Ansar Starter Sites UI inside any theme dashboard or admin page.
     * Automatically enqueues required styles, scripts, and localized demo data.
     *
     * @param array $args Optional configuration arguments (e.g. 'context', 'show_header', 'show_hero').
     */
    function ansar_import_render_starter_sites( $args = array() ) {
        do_action( 'ansar_import_render_starter_sites', $args );
    }
}

