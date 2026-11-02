<?php
/**
 * Ansar Plugin Manager Engine
 *
 * Handles dependency detection, status checking, installation from WP.org,
 * and activation for required plugins defined in ThemeAnsar demos (tademos API).
 *
 * Compatible with tademos / api.themeansar.com `premiun_plugin_link` format.
 * Reusable across plugins and themes.
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/includes/engine/plugin
 * @since      2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ansar_Plugin_Manager {

    /**
     * Singleton instance.
     *
     * @var Ansar_Plugin_Manager|null
     */
    private static $instance = null;

    /**
     * Cached list of installed plugins for the current request.
     *
     * @var array|null
     */
    private $installed_plugins_cache = null;

    /**
     * Get instance.
     *
     * @return Ansar_Plugin_Manager
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Ready for standalone or hooked usage.
    }

    /**
     * Get all installed plugins from WordPress (memoized per request).
     *
     * @param bool $force_refresh Whether to bypass the in-memory cache.
     * @return array
     */
    public function get_installed_plugins( $force_refresh = false ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( $force_refresh || is_null( $this->installed_plugins_cache ) ) {
            wp_clean_plugins_cache( false );
            $this->installed_plugins_cache = get_plugins();
        }
        return $this->installed_plugins_cache;
    }

    /**
     * Locate the main file path of an installed plugin by its slug or explicit main file candidates.
     *
     * @param string $plugin_slug Directory or slug of the plugin.
     * @param array  $main_files  Optional list of explicit plugin file paths to check first.
     * @return string Plugin basename (e.g. 'elementor/elementor.php') or empty string if not found.
     */
    public function get_plugin_file_path( $plugin_slug, $main_files = array() ) {
        $plugin_slug = sanitize_key( $plugin_slug );
        $all_plugins = $this->get_installed_plugins();

        if ( ! empty( $main_files ) && is_array( $main_files ) ) {
            foreach ( $main_files as $candidate_file ) {
                if ( isset( $all_plugins[ $candidate_file ] ) ) {
                    return $candidate_file;
                }
            }
        }

        // Check common pattern slug/slug.php
        $direct_match = $plugin_slug . '/' . $plugin_slug . '.php';
        if ( isset( $all_plugins[ $direct_match ] ) ) {
            return $direct_match;
        }

        // Iterate through all plugins to match directory or root file
        foreach ( $all_plugins as $plugin_file => $plugin_data ) {
            $folder_arr = explode( '/', $plugin_file );
            $folder     = $folder_arr[0];

            if ( $folder === $plugin_slug ) {
                return $plugin_file;
            }

            // For single-file plugins without a folder
            if ( $plugin_file === $plugin_slug . '.php' ) {
                return $plugin_file;
            }
        }

        return '';
    }

    /**
     * Get the installation/activation status of a plugin.
     *
     * @param string $plugin_slug Plugin slug (e.g. 'elementor', 'post-extra').
     * @param array  $main_files  Optional list of explicit plugin file paths.
     * @return string 'active', 'inactive', or 'not-installed'
     */
    public function get_plugin_status( $plugin_slug, $main_files = array() ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $file_path = $this->get_plugin_file_path( $plugin_slug, $main_files );

        if ( empty( $file_path ) ) {
            return 'not-installed';
        }

        if ( is_plugin_active( $file_path ) ) {
            return 'active';
        }

        return 'inactive';
    }

    /**
     * Get detailed status array for a plugin (reusable by Template Library & Demo Importer).
     *
     * @param string $plugin_slug Plugin slug.
     * @param array  $main_files  Optional list of explicit plugin file paths.
     * @return array { installed: bool, active: bool, status: string, file: string }
     */
    public function get_plugin_details( $plugin_slug, $main_files = array() ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $file_path = $this->get_plugin_file_path( $plugin_slug, $main_files );
        $installed = ! empty( $file_path );
        $active    = $installed && is_plugin_active( $file_path );

        return array(
            'installed' => $installed,
            'active'    => $active,
            'status'    => $active ? 'active' : ( $installed ? 'inactive' : 'not-installed' ),
            'file'      => $file_path,
        );
    }

    /**
     * Parse required plugins from demo data.
     *
     * Matches the `premiun_plugin_link` format from tademos / api.themeansar.com:
     * [
     *   [
     *     'plugin_name'    => 'Elementor',
     *     'plugin_slug'    => 'elementor',
     *     'plugin_type'    => 'free'|'pro',
     *     'plugin_pro_url' => '...'
     *   ],
     *   ...
     * ]
     *
     * Also automatically infers default plugins from template_type (e.g. Elementor for elementor demos).
     *
     * @param array $ansar_import_demo_data Raw demo data array from API.
     * @return array Normalized list of required plugins.
     */
    public function extract_required_plugins( $ansar_import_demo_data ) {
        $required_plugins = array();

        if ( ! is_array( $ansar_import_demo_data ) ) {
            return $required_plugins;
        }

        // 1. Extract from tademos 'premiun_plugin_link' (root or under meta)
        $raw_links = null;
        if ( isset( $ansar_import_demo_data['premiun_plugin_link'] ) && is_array( $ansar_import_demo_data['premiun_plugin_link'] ) ) {
            $raw_links = $ansar_import_demo_data['premiun_plugin_link'];
        } elseif ( isset( $ansar_import_demo_data['meta']['premiun_plugin_link'] ) && is_array( $ansar_import_demo_data['meta']['premiun_plugin_link'] ) ) {
            $raw_links = $ansar_import_demo_data['meta']['premiun_plugin_link'];
        }

        if ( ! empty( $raw_links ) ) {
            foreach ( $raw_links as $item ) {
                if ( empty( $item['plugin_slug'] ) ) {
                    continue;
                }

                $slug = sanitize_key( $item['plugin_slug'] );
                $required_plugins[ $slug ] = array(
                    'plugin_name'    => ! empty( $item['plugin_name'] ) ? sanitize_text_field( $item['plugin_name'] ) : $slug,
                    'plugin_slug'    => $slug,
                    'plugin_type'    => ! empty( $item['plugin_type'] ) && 'pro' === $item['plugin_type'] ? 'pro' : 'free',
                    'plugin_pro_url' => ! empty( $item['plugin_pro_url'] ) ? esc_url_raw( $item['plugin_pro_url'] ) : '',
                    'status'         => $this->get_plugin_status( $slug ),
                );
            }
        }

        // 2. Fallback inference based on template_type if empty or missing builder
        $template_type = isset( $ansar_import_demo_data['template_type'] ) ? sanitize_key( $ansar_import_demo_data['template_type'] ) : '';
        if ( 'elementor' === $template_type && ! isset( $required_plugins['elementor'] ) ) {
            $required_plugins['elementor'] = array(
                'plugin_name'    => 'Elementor',
                'plugin_slug'    => 'elementor',
                'plugin_type'    => 'free',
                'plugin_pro_url' => '',
                'status'         => $this->get_plugin_status( 'elementor' ),
            );
        }

        /**
         * Filter extracted required plugins for a demo.
         *
         * @param array $required_plugins
         * @param array $ansar_import_demo_data
         */
        return apply_filters( 'ansar_demo_required_plugins', $required_plugins, $ansar_import_demo_data );
    }

    /**
     * Install a plugin from WordPress.org by slug.
     *
     * @param string $plugin_slug Plugin slug on WP.org.
     * @return true|WP_Error
     */
    public function install_wporg_plugin( $plugin_slug ) {
        $plugin_slug = sanitize_key( $plugin_slug );

        if ( empty( $plugin_slug ) ) {
            return new WP_Error( 'invalid_slug', __( 'Invalid plugin slug.', 'ansar-import' ) );
        }

        if ( ! current_user_can( 'install_plugins' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to install plugins.', 'ansar-import' ) );
        }

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        $api = plugins_api(
            'plugin_information',
            array(
                'slug'   => $plugin_slug,
                'fields' => array(
                    'sections'          => false,
                    'screenshots'       => false,
                    'versions'          => false,
                    'reviews'           => false,
                    'banners'           => false,
                    'icons'             => false,
                    'ratings'           => false,
                    'compatibility'     => false,
                    'short_description' => false,
                    'description'       => false,
                ),
            )
        );

        if ( is_wp_error( $api ) ) {
            return $api;
        }

        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( ! $result ) {
            return new WP_Error( 'install_failed',  /* translators: %s: Plugin slug or name */ sprintf( __( 'Failed to install "%s" plugin.', 'ansar-import' ), $plugin_slug ) );
        }

        // Refresh installed plugins cache so subsequent file lookup finds the newly installed plugin
        $this->get_installed_plugins( true );

        return true;
    }

    /**
     * Activate an installed plugin by its slug or explicit main file candidates.
     *
     * @param string $plugin_slug Plugin slug.
     * @param array  $main_files  Optional list of explicit plugin file paths.
     * @return true|WP_Error
     */
    public function activate_plugin_by_slug( $plugin_slug, $main_files = array() ) {
        $plugin_slug = sanitize_key( $plugin_slug );

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return new WP_Error( 'permission_denied', __( 'You do not have permission to activate plugins.', 'ansar-import' ) );
        }

        $file_path = $this->get_plugin_file_path( $plugin_slug, $main_files );
        if ( empty( $file_path ) ) {
            $this->get_installed_plugins( true );
            $file_path = $this->get_plugin_file_path( $plugin_slug, $main_files );
        }

        if ( empty( $file_path ) ) {
            return new WP_Error( 'plugin_not_found', 
            /* translators: %s: Plugin slug */
            sprintf( __( 'Plugin file for "%s" could not be found.', 'ansar-import' ), $plugin_slug ) );
        }

        $result = activate_plugin( $file_path );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return true;
    }

    /**
     * Ensure a single plugin is installed (if free) and activated.
     * Unified helper used by both Gutenberg Template Library and Demo Importer.
     *
     * @param array $config { slug: string, name?: string, source?: 'wordpress'|'local', main_files?: array }
     * @return array|WP_Error Structured status array on success or missing-pro, WP_Error on failure.
     */
    public function prepare_single_plugin( $config ) {
        $slug       = isset( $config['slug'] ) ? sanitize_key( $config['slug'] ) : '';
        $name       = isset( $config['name'] ) ? $config['name'] : $slug;
        $source     = isset( $config['source'] ) ? $config['source'] : 'wordpress';
        $main_files = isset( $config['main_files'] ) && is_array( $config['main_files'] ) ? $config['main_files'] : array();

        if ( empty( $slug ) ) {
            return new WP_Error( 'invalid_plugin', __( 'Invalid plugin configuration.', 'ansar-import' ) );
        }

        $details = $this->get_plugin_details( $slug, $main_files );

        if ( 'local' === $source && ! $details['installed'] ) {
            return array(
                'installed' => false,
                'active'    => false,
                'status'    => 'missing',
                'file'      => '',
                'message'   => sprintf( 
                    /* translators: %s: Plugin name */
                    __( '%s is not installed.', 'ansar-import' ), 
                    $name 
                ),
            );
        }

        if ( 'wordpress' === $source && ! $details['installed'] ) {
            $installed = $this->install_wporg_plugin( $slug );
            if ( is_wp_error( $installed ) ) {
                return $installed;
            }
            $details = $this->get_plugin_details( $slug, $main_files );
        }

        if ( ! $details['active'] ) {
            $activated = $this->activate_plugin_by_slug( $slug, $main_files );
            if ( is_wp_error( $activated ) ) {
                return $activated;
            }
            $details = $this->get_plugin_details( $slug, $main_files );
        }

        return array(
            'installed' => $details['installed'],
            'active'    => $details['active'],
            'status'    => $details['active'] ? 'active' : 'inactive',
            'file'      => $details['file'],
            'message'   => $details['active']
                ? __( 'Required plugin is active.', 'ansar-import' )
                : __( 'Required plugin is not active.', 'ansar-import' ),
        );
    }

    /**
     * Fetch demo data from local transient cache first, falling back to ThemeAnsar API by theme ID.
     *
     * @param int|string $theme_id Demo ID.
     * @return array|WP_Error
     */
    public function fetch_demo_data( $theme_id ) {
        $theme_id = absint( $theme_id );
        if ( empty( $theme_id ) ) {
            return new WP_Error( 'empty_id', __( 'No demo ID provided.', 'ansar-import' ) );
        }

        $item_transient_key = 'ansar_demo_item_' . $theme_id;
        $cached_item = get_transient( $item_transient_key );
        if ( is_array( $cached_item ) && ! empty( $cached_item['id'] ) ) {
            return $cached_item;
        }

        // Check existing bulk demo transients before making an external HTTP request.
        $theme_name = wp_get_theme()->get( 'Name' );
        $bulk_transients = array(
            'ansar_fallback_starter_demos',
            'ansar_fallback_demos_' . md5( $theme_name ),
            'ansar_fallback_plugin_demos',
        );

        foreach ( $bulk_transients as $t_key ) {
            $list = get_transient( $t_key );
            if ( is_array( $list ) ) {
                foreach ( $list as $ansar_import_demo ) {
                    if ( is_array( $ansar_import_demo ) && isset( $ansar_import_demo['id'] ) && absint( $ansar_import_demo['id'] ) === $theme_id ) {
                        set_transient( $item_transient_key, $ansar_import_demo, 12 * HOUR_IN_SECONDS );
                        return $ansar_import_demo;
                    }
                }
            }
        }

        $api_url = esc_url_raw( 'https://api.themeansar.com/wp-json/wp/v2/demos/' . $theme_id );
        $response = wp_remote_get( $api_url, array( 'timeout' => 30 ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $ansar_import_demo_data = json_decode( $body, true );

        if ( empty( $ansar_import_demo_data ) || ! is_array( $ansar_import_demo_data ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid response from demo server.', 'ansar-import' ) );
        }

        set_transient( $item_transient_key, $ansar_import_demo_data, 12 * HOUR_IN_SECONDS );

        return $ansar_import_demo_data;
    }

    /**
     * Core reusable function: Install and activate all required plugins for a demo.
     *
     * Matches the pattern used in ThemeAnsar ecosystem / tademos.
     *
     * @param int|string  $theme_id Demo ID.
     * @param array|null  $ansar_import_demo_data Optional preloaded demo data array.
     * @return array Structured response.
     */
    public function install_required_plugins( $theme_id, $ansar_import_demo_data = null ) {
        if ( empty( $ansar_import_demo_data ) ) {
            $ansar_import_demo_data = $this->fetch_demo_data( $theme_id );
            if ( is_wp_error( $ansar_import_demo_data ) ) {
                return array(
                    'status' => 'error',
                    'msg'    => $ansar_import_demo_data->get_error_message(),
                );
            }
        }

        $required_plugins = $this->extract_required_plugins( $ansar_import_demo_data );

        $installed_log = array();
        $activated_log = array();
        $failed_log    = array();
        $missing_pro   = array();

        foreach ( $required_plugins as $slug => $plugin ) {
            $type   = $plugin['plugin_type'];
            $name   = $plugin['plugin_name'];
            $status = $this->get_plugin_status( $slug );

            // Handle Pro Plugins (cannot be auto-installed from WP.org)
            if ( 'pro' === $type ) {
                if ( 'not-installed' === $status ) {
                    $missing_pro[] = $name;
                    continue;
                } elseif ( 'inactive' === $status ) {
                    $act_result = $this->activate_plugin_by_slug( $slug );
                    if ( true === $act_result ) {
                        $activated_log[] = $slug;
                    }
                }
                continue;
            }

            // Handle Free Plugins from WordPress.org
            if ( 'not-installed' === $status ) {
                $install_res = $this->install_wporg_plugin( $slug );
                if ( is_wp_error( $install_res ) ) {
                    $failed_log[] = $name . ' (' . $install_res->get_error_message() . ')';
                    continue;
                }
                $installed_log[] = $slug;
                $status = 'inactive';
            }

            if ( 'inactive' === $status ) {
                $activate_res = $this->activate_plugin_by_slug( $slug );
                if ( is_wp_error( $activate_res ) ) {
                    $failed_log[] = $name . ' (' . $activate_res->get_error_message() . ')';
                    continue;
                }
                $activated_log[] = $slug;
            }
        }

        // Configure builder-specific options if Elementor was installed/activated
        if ( in_array( 'elementor', $activated_log, true ) || 'active' === $this->get_plugin_status( 'elementor' ) ) {
            $elem_options = array(
                'elementor_experiment-container'              => 'active',
                'elementor_experiment-container_grid'         => 'active',
                'elementor_unfiltered_files_upload'           => true,
            );
            foreach ( $elem_options as $opt_key => $opt_val ) {
                update_option( $opt_key, $opt_val );
            }
        }

        if ( ! empty( $failed_log ) ) {
            return array(
                'status'    => 'error',
                'msg'       => sprintf( 
                    /* translators: %s: Comma-separated list of failed plugins */
                    __( 'Failed to setup plugins: %s', 'ansar-import' ), 
                    implode( ', ', $failed_log ) 
                ),
                'installed' => $installed_log,
                'activated' => $activated_log,
            );
        }

        $message = __( 'All required plugins installed and activated successfully.', 'ansar-import' );
        if ( ! empty( $missing_pro ) ) {
            $message .= ' ' . sprintf( 
                /* translators: %s: Comma-separated list of missing Pro plugins */
                __( 'Note: Pro plugin(s) need manual installation: %s', 'ansar-import' ), 
                implode( ', ', $missing_pro ) 
            );
        }

        return array(
            'status'      => 'ok',
            'msg'         => $message,
            'installed'   => $installed_log,
            'activated'   => $activated_log,
            'missing_pro' => $missing_pro,
        );
    }
}

/**
 * Global helper function for themes and plugins to install required demo plugins.
 *
 * @param int|string $theme_id Demo ID.
 * @param array|null $ansar_import_demo_data Optional demo data array.
 * @return array
 */
function ansar_install_required_plugins( $theme_id, $ansar_import_demo_data = null ) {
    return Ansar_Plugin_Manager::instance()->install_required_plugins( $theme_id, $ansar_import_demo_data );
}
