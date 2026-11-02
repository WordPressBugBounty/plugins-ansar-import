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
     * Diagnostic payload for the most recent API failure.
     *
     * @since    1.0.0
     * @access   private
     * @var      array|null    $last_api_error    Last recorded API error.
     */
    private static $last_api_error = null;

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
        if ( ! isset( $_POST['check_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['check_import_nonce'] ) ), 'ansar_demo_import_nonce' ) ) {
            wp_send_json_error( array( 'status' => 'error', 'msg' => 'Security check failed.' ) );
            return;
        }

        // Sanitize inputs
        $theme_id   = isset( $_POST['theme_id'] ) ? sanitize_key( wp_unslash( $_POST['theme_id'] ) ) : '';
        $customize  = isset( $_POST['customize'] ) ? sanitize_key( wp_unslash( $_POST['customize'] ) ) : '';
        $widget     = isset( $_POST['widget'] ) ? sanitize_key( wp_unslash( $_POST['widget'] ) ) : '';
        $content    = isset( $_POST['content'] ) ? sanitize_key( wp_unslash( $_POST['content'] ) ) : '';
        $step       = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
        $theme_name = isset( $_POST['theme_name'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_name'] ) ) : false;

        // Call importer
        $ansar_importer = new Ansar_Import();
        $ansar_importer->install_demo($theme_id, $customize, $widget, $content, $step, $theme_name);
    }

    public function get_starter_icon() {
        $svg = '<svg width="22" height="22" viewBox="0 0 22 22" fill=\'none\' xmlns="http://www.w3.org/2000/svg">'
            . '<style>.ansar-icon-bg{fill:url(#paint0_linear_1751_10)}.ansar-icon-fg{fill:#ffffff}</style>'
            . '<rect class="ansar-icon-bg" width="22" height="22" rx="11" fill=\'url(#paint0_linear_1751_10)\'/>'
            . '<path class="ansar-icon-fg" d="M18.132 13.2823C18.3581 13.3743 18.5059 13.594 18.5059 13.8381L18.506 15.4488C18.506 15.8766 18.071 16.167 17.6759 16.003L4.55032 10.5534C4.32636 10.4604 4.18039 10.2418 4.18039 9.99929L4.18039 8.49983C4.18039 8.07386 4.61187 7.78355 5.00645 7.94403L18.132 13.2823Z" fill=\'#ffffff\'/>'
            . '<g filter="url(#filter0_d_1751_10)">'
            . '<path class="ansar-icon-fg" d="M12.2326 3.56802C12.1393 3.34476 11.921 3.19941 11.679 3.19941L10.1098 3.19939C9.68133 3.19939 9.39094 3.63548 9.55616 4.03077L15.0517 17.1791C15.145 17.4024 15.3633 17.5477 15.6053 17.5477L17.1745 17.5477C17.6029 17.5477 17.8933 17.1116 17.7281 16.7163L12.2326 3.56802Z" fill=\'#ffffff\'/>'
            . '</g>'
            . '<g filter="url(#filter1_d_1751_10)">'
            . '<path class="ansar-icon-fg" d="M9.05115 3.56277C9.1464 3.34373 9.36251 3.20205 9.60136 3.20205L11.1626 3.20204C11.5948 3.20203 11.8852 3.64505 11.7129 4.04133L5.99484 17.1896C5.89959 17.4087 5.68348 17.5504 5.44463 17.5504L3.88338 17.5504C3.45125 17.5504 3.16082 17.1073 3.33315 16.7111L9.05115 3.56277Z" fill=\'#ffffff\'/>'
            . '</g>'
            . '<defs>'
            . '<filter id="filter0_d_1751_10" x="8.50928" y="2.19922" width="12.2661" height="18.3486" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">'
            . '<feFlood flood-opacity="0" result="BackgroundImageFix"/>'
            . '<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>'
            . '<feOffset dx="1" dy="1"/><feGaussianBlur stdDeviation="1"/><feComposite in2="hardAlpha" operator="out"/>'
            . '<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.3 0"/>'
            . '<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1751_10"/>'
            . '<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1751_10" result="shape"/>'
            . '</filter>'
            . '<filter id="filter1_d_1751_10" x="2.28271" y="2.20215" width="12.4805" height="18.3486" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">'
            . '<feFlood flood-opacity="0" result="BackgroundImageFix"/>'
            . '<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>'
            . '<feOffset dx="1" dy="1"/><feGaussianBlur stdDeviation="1"/><feComposite in2="hardAlpha" operator="out"/>'
            . '<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.3 0"/>'
            . '<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_1751_10"/>'
            . '<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_1751_10" result="shape"/>'
            . '</filter>'
            . '<linearGradient id="paint0_linear_1751_10" x1="22" y1="11" x2="0" y2="11" gradientUnits="userSpaceOnUse">'
            . '<stop stop-color="#0F5938"/><stop offset="1" stop-color="#3FA34D"/>'
            . '</linearGradient>'
            . '</defs>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode( $svg );
    }

    /**
     * Retrieve optional theme support configuration registered via `add_theme_support( 'ansar-starter-sites', $args )`
     * or filtered via `ansar_import_theme_support_args`.
     *
     * @param WP_Theme|null $theme Optional WP_Theme instance.
     * @return array
     */
    public static function get_theme_support_config( $theme = null ) {
        if ( null === $theme ) {
            $theme = wp_get_theme();
        }

        $support = get_theme_support( 'ansar-starter-sites' );
        $config  = array();

        if ( is_array( $support ) && isset( $support[0] ) && is_array( $support[0] ) ) {
            $config = $support[0];
        } elseif ( true === $support ) {
            $config = array( 'enabled' => true );
        }

        return (array) apply_filters( 'ansar_import_theme_support_args', $config, $theme );
    }

    /**
     * Check whether the active theme (or its parent theme) is authored by ThemeAnsar
     * or explicitly declares `add_theme_support( 'ansar-starter-sites' )`.
     *
     * @param WP_Theme|null $theme Optional WP_Theme instance.
     * @return bool
     */
    public static function is_themeansar_theme( $theme = null ) {
        if ( null === $theme ) {
            static $cached_result = null;
            if ( null !== $cached_result ) {
                return $cached_result;
            }
            $theme         = wp_get_theme();
            $author        = strtolower( trim( (string) $theme->get( 'Author' ) ) );
            $parent_author = $theme->parent() ? strtolower( trim( (string) $theme->parent()->get( 'Author' ) ) ) : '';
            $has_support   = (bool) get_theme_support( 'ansar-starter-sites' ) || ! empty( self::get_theme_support_config( $theme ) );
            $cached_result = ( $has_support || strpos( $author, 'themeansar' ) !== false || strpos( $parent_author, 'themeansar' ) !== false );
            return $cached_result;
        }

        $author        = strtolower( trim( (string) $theme->get( 'Author' ) ) );
        $parent_author = $theme->parent() ? strtolower( trim( (string) $theme->parent()->get( 'Author' ) ) ) : '';
        $has_support   = (bool) get_theme_support( 'ansar-starter-sites' ) || ! empty( self::get_theme_support_config( $theme ) );
        return ( $has_support || strpos( $author, 'themeansar' ) !== false || strpos( $parent_author, 'themeansar' ) !== false );
    }

    /**
     * Known classic ThemeAnsar themes whitelist.
     *
     * @return array
     */
    public static function get_classic_themes_whitelist() {
        return array(
            'Newsxo', 'Blogier', 'NewsPaperup', 'BlogData', 'NewsAir', 'BlogArise',
            'Blogus', 'Fameup', 'Industryup', 'Consultco', 'Agencyup', 'Newses',
            'Newsup', 'Consultup', 'Short', 'Yoga', 'Businessup', 'Transportex',
            'Shopress', 'Bizwhoop', 'Proficiency', 'Ferry', 'Financeup', 'Shopbiz',
            'Blogger Mag', 'BlogNews',
        );
    }

    /**
     * Flush cached demo and category transients once per request when ?ansar_refresh is present.
     *
     * @param string $theme_name Optional theme name (resolved automatically if empty).
     */
    public static function maybe_flush_demo_transients( $theme_name = '' ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query flag to trigger cache refresh.
        if ( ! isset( $_GET['ansar_refresh'] ) || ! empty( $GLOBALS['ansar_transients_refreshed'] ) ) {
            return;
        }
        if ( '' === $theme_name ) {
            $theme_name = wp_get_theme()->get( 'Name' );
        }

        $theme_data   = wp_get_theme();
        $parent_theme = $theme_data->parent();
        $parent_name  = $parent_theme ? $parent_theme->get( 'Name' ) : '';
        $parent_slug  = $parent_theme ? $theme_data->get_template() : '';
        $cleaned_name = trim( (string) preg_replace( '/[\s\-_]+child$/i', '', $theme_name ) );

        delete_transient( 'ansar_fallback_starter_demos' );
        delete_transient( 'ansar_fallback_demos_' . md5( $theme_name ) );
        if ( ! empty( $parent_name ) ) {
            delete_transient( 'ansar_fallback_demos_' . md5( $parent_name ) );
        }
        if ( ! empty( $parent_slug ) ) {
            delete_transient( 'ansar_fallback_demos_' . md5( $parent_slug ) );
        }
        if ( $cleaned_name !== $theme_name ) {
            delete_transient( 'ansar_fallback_demos_' . md5( $cleaned_name ) );
        }

        $all_possible_terms = array_values( array_unique( array_filter( array( $theme_name, $cleaned_name, $parent_name, $parent_slug ) ) ) );
        delete_transient( 'ansar_fallback_demos_' . md5( implode( ':', $all_possible_terms ) ) );
        delete_transient( 'ansar_fallback_plugin_demos' );
        delete_transient( 'ansar_fallback_categories' );
        self::$last_api_error                  = null;
        $GLOBALS['ansar_transients_refreshed'] = true;
    }

    /**
     * Resilient remote GET helper that automatically recovers from local cURL SSL/cacert errors
     * (e.g. cURL error 60 / missing root CA in php.ini) and applies a 30s timeout.
     *
     * @since 1.0.0
     * @param string $url  API URL.
     * @param array  $args Optional. Request arguments. Default empty array.
     * @return array|WP_Error Response array on success, WP_Error on failure.
     */
    public static function remote_get( $url, $args = array() ) {
        $defaults = array(
            'timeout'   => 30,
            'sslverify' => true,
            'headers'   => array(
                'Accept'     => 'application/json, */*',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; AnsarImport/' . ANSAR_IMPORT_VERSION . '; ' . esc_url( home_url() ),
            ),
        );
        $parsed_args = wp_parse_args( $args, $defaults );
        $response    = wp_remote_get( esc_url_raw( $url ), $parsed_args );

        // If request failed due to local SSL/CA certificate error (e.g. cURL error 60), retry with sslverify disabled.
        if ( is_wp_error( $response ) ) {
            $error_message = strtolower( (string) $response->get_error_message() );
            if (
                false !== strpos( $error_message, 'ssl' ) ||
                false !== strpos( $error_message, 'certificate' ) ||
                false !== strpos( $error_message, 'curl error 60' ) ||
                false !== strpos( $error_message, 'curl error 77' ) ||
                false !== strpos( $error_message, 'local issuer certificate' )
            ) {
                $parsed_args['sslverify'] = false;
                $response                 = wp_remote_get( esc_url_raw( $url ), $parsed_args );
            }
        }

        if ( is_wp_error( $response ) ) {
            self::$last_api_error = array(
                'url'     => $url,
                'code'    => $response->get_error_code(),
                'message' => $response->get_error_message(),
            );
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Diagnostic logging for API failure when WP_DEBUG is enabled.
                error_log( sprintf( '[Ansar Import] API Request Failed (%s): %s', esc_url_raw( $url ), $response->get_error_message() ) );
            }
        } else {
            $status_code = (int) wp_remote_retrieve_response_code( $response );
            if ( 200 !== $status_code ) {
                $raw_body     = (string) wp_remote_retrieve_body( $response );
                $body_snippet = substr( wp_strip_all_tags( $raw_body ), 0, 200 );
                self::$last_api_error = array(
                    'url'     => $url,
                    'code'    => $status_code,
                    'message' => sprintf( 'HTTP %d - %s', $status_code, ! empty( $body_snippet ) ? $body_snippet : 'Server returned non-200 status.' ),
                );
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Diagnostic logging for non-200 API response when WP_DEBUG is enabled.
                    error_log( sprintf( '[Ansar Import] API returned HTTP %d for (%s): %s', $status_code, esc_url_raw( $url ), $body_snippet ) );
                }
            }
        }

        return $response;
    }

    /**
     * Retrieve the last recorded API diagnostic error.
     *
     * @since  1.0.0
     * @return array|null
     */
    public static function get_last_api_error() {
        return self::$last_api_error;
    }

    /**
     * Centralized cache-first helper to fetch demos for 'starter', 'theme', or 'plugin' views.
     * Memoized in memory per request so calling in enqueue_scripts() and display partials incurs zero duplicate work.
     *
     * @param string $context    'starter' | 'theme' | 'plugin'
     * @param string $theme_name Optional theme name for 'theme' context.
     * @return array
     */
    public static function get_cached_demos( $context = 'starter', $theme_name = '' ) {
        static $request_cache = array();

        if ( '' === $theme_name ) {
            $theme_name = wp_get_theme()->get( 'Name' );
        }

        self::maybe_flush_demo_transients( $theme_name );

        $cache_bucket = $context . ':' . $theme_name;
        if ( isset( $request_cache[ $cache_bucket ] ) ) {
            return $request_cache[ $cache_bucket ];
        }

        if ( 'theme' === $context ) {
            $support_config = self::get_theme_support_config();
            $theme_data     = wp_get_theme();
            $parent_theme   = $theme_data->parent();
            $parent_name    = $parent_theme ? $parent_theme->get( 'Name' ) : '';
            $parent_slug    = $parent_theme ? $theme_data->get_template() : '';

            // Clean child suffix if present (e.g. "Newsup Child" or "Newsup-child" -> "Newsup")
            $cleaned_name = trim( (string) preg_replace( '/[\s\-_]+child$/i', '', $theme_name ) );

            $search_terms = array_values(
                array_unique(
                    array_filter(
                        array(
                            ! empty( $support_config['demo_search'] ) ? (string) $support_config['demo_search'] : '',
                            $theme_name,
                            ( $cleaned_name !== $theme_name ) ? $cleaned_name : '',
                            $parent_name,
                            $parent_slug,
                            ! empty( $support_config['demo_fallback_search'] ) ? (string) $support_config['demo_fallback_search'] : '',
                        )
                    )
                )
            );

            $composite_key      = 'ansar_fallback_demos_' . md5( implode( ':', $search_terms ) );
            $cached_composite   = get_transient( $composite_key );
            $ansar_import_demos = array();

            if ( is_array( $cached_composite ) && ! empty( $cached_composite ) && isset( $cached_composite[0]['id'] ) ) {
                $ansar_import_demos = $cached_composite;
            } else {
                $seen_ids = array();
                foreach ( $search_terms as $term ) {
                    $transient_key = 'ansar_fallback_demos_' . md5( $term );
                    $cached_term   = get_transient( $transient_key );
                    if ( ! is_array( $cached_term ) || empty( $cached_term ) || ! isset( $cached_term[0]['id'] ) ) {
                        $api_url  = 'https://api.themeansar.com/wp-json/wp/v2/demos/?orderby=menu_order&order=asc&search=' . urlencode( $term ) . '&per_page=30';
                        $response = self::remote_get( $api_url );
                        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                            $decoded = json_decode( wp_remote_retrieve_body( $response ), true );
                            if ( is_array( $decoded ) && ! empty( $decoded ) && isset( $decoded[0]['id'] ) ) {
                                $cached_term = $decoded;
                                set_transient( $transient_key, $cached_term, 24 * HOUR_IN_SECONDS );
                            }
                        }
                    }
                    if ( is_array( $cached_term ) && ! empty( $cached_term ) && isset( $cached_term[0]['id'] ) ) {
                        foreach ( $cached_term as $demo_item ) {
                            if ( isset( $demo_item['id'] ) && ! isset( $seen_ids[ $demo_item['id'] ] ) ) {
                                $seen_ids[ $demo_item['id'] ] = true;
                                $ansar_import_demos[]         = $demo_item;
                            }
                        }
                    }
                }
                if ( ! empty( $ansar_import_demos ) ) {
                    set_transient( $composite_key, $ansar_import_demos, 24 * HOUR_IN_SECONDS );
                }
            }

            // Ensure Block Editor and Elementor starter demos are also available when switching editors on Theme Demos view
            $has_non_classic = false;
            foreach ( $ansar_import_demos as $d ) {
                $ed = self::normalize_demo_editor( isset( $d['template_type'] ) ? $d['template_type'] : '' );
                if ( 'default-theme' !== $ed ) {
                    $has_non_classic = true;
                    break;
                }
            }
            if ( ! $has_non_classic || empty( $ansar_import_demos ) ) {
                $starter_demos = self::get_cached_demos( 'starter', $theme_name );
                $seen_ids      = array();
                foreach ( $ansar_import_demos as $d ) {
                    if ( isset( $d['id'] ) ) {
                        $seen_ids[ $d['id'] ] = true;
                    }
                }
                foreach ( $starter_demos as $sd ) {
                    if ( ! is_array( $sd ) || ! isset( $sd['id'] ) || isset( $seen_ids[ $sd['id'] ] ) ) {
                        continue;
                    }
                    $sd_editor = self::normalize_demo_editor( isset( $sd['template_type'] ) ? $sd['template_type'] : '' );
                    if ( empty( $ansar_import_demos ) || 'default-theme' !== $sd_editor ) {
                        $ansar_import_demos[] = $sd;
                        $seen_ids[ $sd['id'] ] = true;
                    }
                }
            }
        } elseif ( 'plugin' === $context ) {
            $transient_key      = 'ansar_fallback_plugin_demos';
            $api_url            = 'https://api.themeansar.com/wp-json/wp/v2/demos?categories=15&per_page=30';
            $ansar_import_demos = get_transient( $transient_key );
            if ( ! is_array( $ansar_import_demos ) || empty( $ansar_import_demos ) || ! isset( $ansar_import_demos[0]['id'] ) ) {
                $response = self::remote_get( $api_url );
                if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                    $decoded = json_decode( wp_remote_retrieve_body( $response ), true );
                    if ( is_array( $decoded ) && ! empty( $decoded ) && isset( $decoded[0]['id'] ) ) {
                        $ansar_import_demos = $decoded;
                        set_transient( $transient_key, $ansar_import_demos, 24 * HOUR_IN_SECONDS );
                    }
                }
            }
        } else {
            $transient_key      = 'ansar_fallback_starter_demos';
            $api_url            = 'https://api.themeansar.com/wp-json/wp/v2/demos/?per_page=30';
            $ansar_import_demos = get_transient( $transient_key );
            if ( ! is_array( $ansar_import_demos ) || empty( $ansar_import_demos ) || ! isset( $ansar_import_demos[0]['id'] ) ) {
                $response = self::remote_get( $api_url );
                if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                    $decoded = json_decode( wp_remote_retrieve_body( $response ), true );
                    if ( is_array( $decoded ) && ! empty( $decoded ) && isset( $decoded[0]['id'] ) ) {
                        $ansar_import_demos = $decoded;
                        set_transient( $transient_key, $ansar_import_demos, 24 * HOUR_IN_SECONDS );
                    }
                }
            }
            if ( ! empty( $ansar_import_demos ) && is_array( $ansar_import_demos ) ) {
                shuffle( $ansar_import_demos );
            }
        }

        if ( ! is_array( $ansar_import_demos ) ) {
            $ansar_import_demos = array();
        }

        $request_cache[ $cache_bucket ] = $ansar_import_demos;
        return $ansar_import_demos;
    }

    /**
     * Centralized cache-first helper to fetch demo categories.
     * Extracts categories dynamically from loaded demos to eliminate unnecessary remote API calls.
     *
     * @param string $context 'starter' | 'theme' | 'plugin'. Default 'starter'.
     * @return array
     */
    public static function get_cached_categories( $context = 'starter' ) {
        static $categories_cache = null;

        self::maybe_flush_demo_transients();

        if ( null !== $categories_cache ) {
            return $categories_cache;
        }

        // Fast & resilient: Extract categories directly from cached demos to avoid separate API request.
        $demos                = self::get_cached_demos( $context );
        $extracted_categories = array();
        if ( ! empty( $demos ) && is_array( $demos ) ) {
            foreach ( $demos as $demo ) {
                if ( ! empty( $demo['categories'] ) && is_array( $demo['categories'] ) ) {
                    foreach ( $demo['categories'] as $cat ) {
                        if ( isset( $cat['id'], $cat['name'] ) && ! isset( $extracted_categories[ $cat['id'] ] ) ) {
                            $extracted_categories[ $cat['id'] ] = array(
                                'id'   => $cat['id'],
                                'name' => $cat['name'],
                                'slug' => isset( $cat['slug'] ) ? $cat['slug'] : sanitize_title( $cat['name'] ),
                            );
                        }
                    }
                }
            }
        }

        if ( ! empty( $extracted_categories ) ) {
            $categories_cache = array_values( $extracted_categories );
            return $categories_cache;
        }

        // If demos failed or returned empty, do not call remote categories API to avoid masking the real demo error.
        if ( empty( $demos ) ) {
            $categories_cache = array();
            return $categories_cache;
        }

        $transient_key = 'ansar_fallback_categories';
        $categories    = get_transient( $transient_key );

        if ( ! is_array( $categories ) || empty( $categories ) || ! isset( $categories[0]['id'] ) ) {
            $response = self::remote_get(
                'https://api.themeansar.com/wp-json/wp/v2/categories?per_page=50'
            );
            if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                $decoded = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( is_array( $decoded ) && ! empty( $decoded ) && isset( $decoded[0]['id'] ) ) {
                    $categories = $decoded;
                    set_transient( $transient_key, $categories, 24 * HOUR_IN_SECONDS );
                }
            }
        }

        $categories_cache = ( is_array( $categories ) && ! empty( $categories ) && isset( $categories[0]['id'] ) ) ? $categories : array();
        return $categories_cache;
    }

    /**
     * Resolve Pro upgrade URL and Documentation URL for a theme and active editor.
     *
     * @param WP_Theme|null $theme_data    Optional WP_Theme instance.
     * @param string        $active_editor Active editor slug ('default-theme', 'block-editor', 'elementor').
     * @return array { pro_url: string, doc_url: string }
     */
    public static function get_theme_pro_and_doc_urls( $theme_data = null, $active_editor = 'default-theme' ) {
        if ( null === $theme_data ) {
            $theme_data = wp_get_theme();
        }

        $support_config = self::get_theme_support_config( $theme_data );

        $pro_urls = array(
            'Newsxo'      => 'https://themeansar.com/themes/newsxo-pro-magazine-blog-wordpress-theme/',
            'Blogier'     => 'https://themeansar.com/themes/blogier-pro-minimal-blog-magazine-wordpress-theme/',
            'NewsPaperup' => 'https://themeansar.com/themes/newspaperup-pro-magazine-blog-wordpress-theme/',
            'BlogData'    => 'https://themeansar.com/themes/blogdata-pro-minimal-blog-magazine-wordpress-theme/',
            'NewsAir'     => 'https://themeansar.com/themes/newsair-pro/',
            'BlogArise'   => 'https://themeansar.com/themes/blogarise-pro/',
            'Blogus'      => 'https://themeansar.com/themes/blogus-pro/',
            'Fameup'      => 'https://themeansar.com/themes/fameup-pro/',
            'Industryup'  => 'https://themeansar.com/themes/industryup-pro/',
            'Consultco'   => 'https://themeansar.com/themes/consultco-pro/',
            'Agencyup'    => 'https://themeansar.com/themes/agencyup-pro/',
            'Newses'      => 'https://themeansar.com/themes/newses-pro/',
            'Newsup'      => 'https://themeansar.com/themes/newsup-pro/',
            'Consultup'   => 'https://themeansar.com/themes/consultup-pro/',
            'Short'       => 'https://themeansar.com/themes/short-pro/',
            'Yoga'        => 'https://themeansar.com/themes/yoga-wordpress-theme/',
            'Businessup'  => 'https://themeansar.com/themes/businessup-pro/',
            'Transportex' => 'https://themeansar.com/themes/transportex-pro/',
            'Shopress'    => 'https://themeansar.com/themes/shopress-pro/',
            'Bizwhoop'    => 'https://themeansar.com/themes/bizwhoop-wordpress-theme/',
            'Proficiency' => 'https://themeansar.com/themes/proficiency-one-page-business-theme/',
            'Ferry'       => 'https://themeansar.com/themes/ferry-woocommerce-wordpress-theme/',
            'Financeup'   => 'https://themeansar.com/themes/financeup/',
            'Shopbiz'     => 'https://themeansar.com/themes/shopbiz/',
        );

        $doc_urls = array(
            'Newsxo'          => 'https://docs.themeansar.com/docs/newsxo-lite/',
            'Newsxo Pro'      => 'https://docs.themeansar.com/docs/newsxo-pro/',
            'Blogier'         => 'https://docs.themeansar.com/docs/blogus-lite/',
            'Blogier Pro'     => 'https://docs.themeansar.com/docs/blogier-pro/',
            'NewsPaperup'     => 'https://docs.themeansar.com/docs/newspaperup-lite/',
            'NewsPaperup Pro' => 'https://docs.themeansar.com/docs/newspaperup-pro/',
            'BlogData'        => 'https://docs.themeansar.com/docs/blogdata-lite/',
            'BlogData Pro'    => 'https://docs.themeansar.com/docs/blogdata-pro/',
            'NewsAir'         => 'https://docs.themeansar.com/docs/newsair-lite/',
            'NewsAir Pro'     => 'https://docs.themeansar.com/docs/newsair-pro/',
            'BlogArise'       => 'https://docs.themeansar.com/docs/blogarise-lite/',
            'BlogArise Pro'   => 'https://docs.themeansar.com/docs/blogarise-pro/',
            'Blogus'          => 'https://docs.themeansar.com/docs/blogus-lite/',
            'Blogus Pro'      => 'https://docs.themeansar.com/docs/blogus-pro/',
            'Fameup'          => 'https://docs.themeansar.com/docs/fameup-pro/',
            'Fameup Pro'      => 'https://docs.themeansar.com/docs/fameup-pro/',
            'Industryup'      => 'https://docs.themeansar.com/docs/industryup-pro/',
            'Industryup Pro'  => 'https://docs.themeansar.com/docs/industryup-pro/',
            'Consultco'       => 'https://docs.themeansar.com/docs/consultco-pro/',
            'Consultco Pro'   => 'https://docs.themeansar.com/docs/consultco-pro/',
            'Agencyup'        => 'https://docs.themeansar.com/docs/agencyup-lite/',
            'Agencyup Pro'    => 'https://docs.themeansar.com/docs/agencyup-pro/',
            'Newses'          => 'https://docs.themeansar.com/docs/newses-pro/',
            'Newses Pro'      => 'https://docs.themeansar.com/docs/newses-pro/',
            'Newsup'          => 'https://docs.themeansar.com/docs/newsup-lite/',
            'Newsup Pro'      => 'https://docs.themeansar.com/docs/newsup-pro/',
            'Consultup'       => 'https://docs.themeansar.com/docs/consultup-pro/',
            'Consultup Pro'   => 'https://docs.themeansar.com/docs/consultup-pro/',
            'Short'           => 'https://docs.themeansar.com/docs/short-pro/',
            'Short Pro'       => 'https://docs.themeansar.com/docs/short-pro/',
            'Yoga'            => 'https://docs.themeansar.com/docs/yoga-pro/',
            'Yoga Pro'        => 'https://docs.themeansar.com/docs/yoga-pro/',
            'Businessup'      => 'https://docs.themeansar.com/docs/businessup-pro/',
            'Businessup Pro'  => 'https://docs.themeansar.com/docs/businessup-pro/',
            'Transportex'     => 'https://docs.themeansar.com/docs/transportex-pro/',
            'Transportex Pro' => 'https://docs.themeansar.com/docs/transportex-pro/',
            'Shopress'        => 'https://docs.themeansar.com/docs/shopress-pro/',
            'Shopress Pro'    => 'https://docs.themeansar.com/docs/shopress-pro/',
            'Bizwhoop'        => 'https://docs.themeansar.com/',
            'Bizwhoop Pro'    => 'https://docs.themeansar.com/',
            'Proficiency'     => 'https://docs.themeansar.com/',
            'Proficiency Pro' => 'https://docs.themeansar.com/',
            'Ferry'           => 'https://docs.themeansar.com/',
            'Ferry Pro'       => 'https://docs.themeansar.com/',
            'Financeup'       => 'https://docs.themeansar.com/docs/financeup-pro/',
            'Financeup Pro'   => 'https://docs.themeansar.com/docs/financeup-pro/',
            'Shopbiz'         => 'https://docs.themeansar.com/docs/shopbiz-pro/',
            'Shopbiz Pro'     => 'https://docs.themeansar.com/docs/shopbiz-pro/',
        );

        $parent = $theme_data->parent();
        $tname  = ( $parent && 'Blogier' !== $theme_data->get( 'Name' ) )
            ? $parent->get( 'Name' )
            : $theme_data->get( 'Name' );

        if ( ! empty( $support_config['pro_url'] ) ) {
            $current_pro_url = esc_url_raw( $support_config['pro_url'] );
        } elseif ( isset( $pro_urls[ $tname ] ) ) {
            $current_pro_url = $pro_urls[ $tname ];
        } elseif ( self::is_themeansar_theme( $theme_data ) ) {
            $auto_slug       = sanitize_title( preg_replace( '/\s+pro$/i', '', $tname ) );
            $current_pro_url = 'https://themeansar.com/themes/' . $auto_slug . '-pro/';
        } else {
            $current_pro_url = 'https://themeansar.com/themes/';
        }

        if ( ! empty( $support_config['doc_url'] ) ) {
            $current_doc_url = esc_url_raw( $support_config['doc_url'] );
        } elseif ( isset( $doc_urls[ $tname ] ) ) {
            $current_doc_url = $doc_urls[ $tname ];
        } else {
            $current_doc_url = 'https://docs.themeansar.com/';
        }

        if ( 'block-editor' === $active_editor ) {
            $current_doc_url = 'https://docs.themeansar.com/docs/ansar-blocks/';
        } elseif ( 'elementor' === $active_editor ) {
            $current_doc_url = 'https://docs.themeansar.com/docs/ansar-elements/';
        }

        return (array) apply_filters(
            'ansar_import_theme_urls',
            array(
                'pro_url' => $current_pro_url,
                'doc_url' => $current_doc_url,
            ),
            $theme_data,
            $active_editor
        );
    }

    /**
     * Centralized helper to compute theme & editor switcher context for Starter Sites and Theme Demos pages.
     *
     * @param string $page_slug            Admin page slug ('ansar-starter-sites' or 'ansar-demo-import').
     * @param array  $ansar_import_all_demos_unfiltered Raw demo array for the page.
     * @return array Associative array of resolved theme/editor state variables.
     */
    public static function get_editor_context( $page_slug = 'ansar-starter-sites', $ansar_import_all_demos_unfiltered = array() ) {
        $theme_data         = wp_get_theme();
        $theme_name         = $theme_data->get( 'Name' );
        $theme_slug         = $theme_data->get_template();
        $theme_parent       = $theme_data->parent() ? $theme_data->parent()->get( 'Name' ) : '';
        $is_block_theme     = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
        $is_themeansar      = self::is_themeansar_theme( $theme_data );
        $classic_themes     = self::get_classic_themes_whitelist();
        $is_theme_demos_view = ( 'ansar-demo-import' === $page_slug );

        $is_whitelisted_classic = false;
        if ( ! $is_block_theme ) {
            if ( $is_themeansar ) {
                $is_whitelisted_classic = true;
            } else {
                foreach ( $classic_themes as $classic_name ) {
                    if (
                        0 === strcasecmp( $theme_name, $classic_name ) ||
                        false !== stripos( $theme_name, $classic_name ) ||
                        0 === strcasecmp( $theme_slug, $classic_name ) ||
                        false !== stripos( $theme_slug, $classic_name ) ||
                        ( $theme_parent && ( 0 === strcasecmp( $theme_parent, $classic_name ) || false !== stripos( $theme_parent, $classic_name ) ) )
                    ) {
                        $is_whitelisted_classic = true;
                        break;
                    }
                }
            }
        }

        if ( $is_theme_demos_view ) {
            $has_classic_demos   = false;
            $has_block_demos     = false;
            $has_elementor_demos = false;
            $classic_demos_count = 0;

            if ( is_array( $ansar_import_all_demos_unfiltered ) ) {
                foreach ( $ansar_import_all_demos_unfiltered as $ansar_import_demo ) {
                    $template_type = isset( $ansar_import_demo['template_type'] ) ? sanitize_key( $ansar_import_demo['template_type'] ) : '';
                    if ( in_array( $template_type, array( 'block-editor', 'gutenberg' ), true ) ) {
                        $has_block_demos = true;
                    } elseif ( 'elementor' === $template_type ) {
                        $has_elementor_demos = true;
                    } elseif ( '' === $template_type || 'default-theme' === $template_type || 'theme' === $template_type ) {
                        $classic_demos_count++;
                    }
                }
            }

            if ( ! $is_block_theme && ( $is_whitelisted_classic || $classic_demos_count > 0 ) ) {
                $has_classic_demos = true;
            }

            $ansar_allowed_editors = array();
            if ( $is_block_theme ) {
                $has_classic_demos       = false;
                $has_elementor_demos     = false;
                $has_block_demos         = true;
                $ansar_allowed_editors[] = 'block-editor';
            } else {
                if ( $has_classic_demos ) {
                    $ansar_allowed_editors[] = 'default-theme';
                }
                if ( $has_block_demos ) {
                    $ansar_allowed_editors[] = 'block-editor';
                }
                if ( $has_elementor_demos ) {
                    $ansar_allowed_editors[] = 'elementor';
                }
            }

            if ( empty( $ansar_allowed_editors ) ) {
                $ansar_allowed_editors[] = $is_block_theme ? 'block-editor' : ( $is_whitelisted_classic ? 'default-theme' : 'block-editor' );
            }
        } else {
            $has_classic_demos   = ( $is_whitelisted_classic && ! $is_block_theme );
            $has_block_demos     = true;
            $has_elementor_demos = ! $is_block_theme;

            $ansar_allowed_editors = array();
            if ( $is_block_theme ) {
                $ansar_allowed_editors = array( 'block-editor' );
            } else {
                if ( $has_classic_demos ) {
                    $ansar_allowed_editors[] = 'default-theme';
                }
                $ansar_allowed_editors[] = 'block-editor';
                $ansar_allowed_editors[] = 'elementor';
            }
        }

        $support_config = self::get_theme_support_config( $theme_data );
        $default_editor = ! empty( $support_config['default_editor'] ) ? sanitize_key( $support_config['default_editor'] ) : 'default-theme';

        $saved_editor = get_user_meta( get_current_user_id(), 'ansar_preferred_editor', true );
        if ( ! $saved_editor ) {
            $saved_editor = get_option( 'ansar_preferred_editor', '' );
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI parameter to switch active editor tab.
        if ( isset( $_GET['editor'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI parameter.
            $ansar_active_editor = sanitize_key( wp_unslash( $_GET['editor'] ) );
        } elseif ( ! empty( $saved_editor ) && in_array( $saved_editor, $ansar_allowed_editors, true ) ) {
            $ansar_active_editor = $saved_editor;
        } elseif ( in_array( $default_editor, $ansar_allowed_editors, true ) ) {
            $ansar_active_editor = $default_editor;
        } elseif ( in_array( 'default-theme', $ansar_allowed_editors, true ) ) {
            $ansar_active_editor = 'default-theme';
        } else {
            $ansar_active_editor = ! empty( $ansar_allowed_editors ) ? $ansar_allowed_editors[0] : 'block-editor';
        }

        if ( ! in_array( $ansar_active_editor, $ansar_allowed_editors, true ) && ! empty( $ansar_allowed_editors ) ) {
            $ansar_active_editor = in_array( 'default-theme', $ansar_allowed_editors, true ) ? 'default-theme' : $ansar_allowed_editors[0];
        }

        $ansar_refresh_url = add_query_arg( 'ansar_refresh', time(), remove_query_arg( 'ansar_refresh' ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query argument for admin menu routing.
        $base_switcher_url = isset( $_GET['page'] )
            ? remove_query_arg( array( 'ansar_refresh', 'onboarding' ) )
            : add_query_arg( 'page', $page_slug, admin_url( 'admin.php' ) );

        $ansar_editor_switcher = array();
        if ( in_array( 'default-theme', $ansar_allowed_editors, true ) ) {
            $ansar_editor_switcher['default-theme'] = array(
                'label' => __( 'Classic Theme', 'ansar-import' ),
                'icon'  => '<svg class="ansar-editor-brand-icon" viewBox="0 0 24 24" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M3 5.5C3 4.12 4.12 3 5.5 3h13C19.88 3 21 4.12 21 5.5v13c0 1.38-1.12 2.5-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 2.25h14V5.5a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v2.25Zm0 2V18.5c0 .28.22.5.5.5h4.25V9.75H5Zm6.75 0V19h6.75a.5.5 0 0 0 .5-.5V9.75h-7.25Z"/></svg>',
                'url'   => add_query_arg( 'editor', 'default-theme', $base_switcher_url ),
            );
        }

        if ( in_array( 'block-editor', $ansar_allowed_editors, true ) ) {
            $ansar_editor_switcher['block-editor'] = array(
                'label' => __( 'Block Editor', 'ansar-import' ),
                'icon'  => '<svg class="ansar-editor-brand-icon ansar-editor-brand-icon-wordpress" viewBox="0 0 24 24" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10.5" fill="currentColor"/><path fill="#fff" d="M3.58 12c0 3.33 1.94 6.2 4.75 7.55L4.31 8.54A8.36 8.36 0 0 0 3.58 12Zm14.1-.42c0-1.04-.37-1.76-.69-2.32-.42-.69-.82-1.27-.82-1.96 0-.77.58-1.48 1.41-1.48.04 0 .07 0 .11.01A8.38 8.38 0 0 0 5.04 7.41c.2.01.39.01.55.01.88 0 2.24-.11 2.24-.11.45-.03.5.64.05.69 0 0-.46.05-.96.08l3.06 9.11 1.84-5.52-1.31-3.59c-.45-.03-.88-.08-.88-.08-.45-.03-.4-.72.05-.69 0 0 1.39.11 2.21.11.88 0 2.24-.11 2.24-.11.45-.03.5.64.05.69 0 0-.46.05-.96.08l3.04 9.04.84-2.8c.36-1.16.58-2 .58-2.74ZM12.15 12.74l-2.52 7.33a8.43 8.43 0 0 0 5.16-.13.75.75 0 0 1-.06-.12l-2.58-7.08Zm7.23-4.81c.04.27.06.56.06.88 0 .87-.16 1.85-.65 3.08l-2.56 7.39A8.42 8.42 0 0 0 19.38 7.93Z"/></svg>',
                'url'   => add_query_arg( 'editor', 'block-editor', $base_switcher_url ),
            );
        }

        if ( in_array( 'elementor', $ansar_allowed_editors, true ) ) {
            $ansar_editor_switcher['elementor'] = array(
                'label' => __( 'Elementor', 'ansar-import' ),
                'icon'  => '<svg class="ansar-editor-brand-icon ansar-editor-brand-icon-elementor" viewBox="0 0 24 24" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="currentColor"/><path fill="#fff" d="M7.25 6.9h2.2v10.2h-2.2V6.9Zm4.05 0h5.45v2.1H11.3V6.9Zm0 4.05h5.45v2.1H11.3v-2.1Zm0 4.05h5.45v2.1H11.3V15Z"/></svg>',
                'url'   => add_query_arg( 'editor', 'elementor', $base_switcher_url ),
            );
        }

        $urls = self::get_theme_pro_and_doc_urls( $theme_data, $ansar_active_editor );

        return array(
            'theme_data'             => $theme_data,
            'theme_name'             => $theme_name,
            'theme_slug'             => $theme_slug,
            'theme_parent'           => $theme_parent,
            'is_block_theme'         => $is_block_theme,
            'is_themeansar_theme'    => $is_themeansar,
            'is_whitelisted_classic' => $is_whitelisted_classic,
            'has_classic_demos'      => $has_classic_demos,
            'has_block_demos'        => $has_block_demos,
            'has_elementor_demos'    => $has_elementor_demos,
            'ansar_allowed_editors'  => $ansar_allowed_editors,
            'ansar_active_editor'    => $ansar_active_editor,
            'ansar_refresh_url'      => $ansar_refresh_url,
            'ansar_editor_switcher'  => $ansar_editor_switcher,
            'current_pro_url'        => $is_theme_demos_view ? $urls['pro_url'] : 'https://themeansar.com/themes/',
            'current_doc_url'        => $urls['doc_url'],
            'starter_doc_url'        => $urls['doc_url'],
        );
    }

    /**
     * Normalize a demo's raw template_type into one of the three canonical editor slugs:
     * 'elementor', 'block-editor', or 'default-theme'.
     *
     * @param string $template_type Raw template_type string from API.
     * @return string Normalized editor slug.
     */
    public static function normalize_demo_editor( $template_type ) {
        $clean = sanitize_key( (string) $template_type );
        if ( 'elementor' === $clean ) {
            return 'elementor';
        }
        if ( in_array( $clean, array( 'block-editor', 'gutenberg' ), true ) ) {
            return 'block-editor';
        }
        return 'default-theme';
    }

    /**
     * Filter an array of demos by the active editor ('elementor', 'block-editor', or 'default-theme').
     *
     * @param array  $ansar_import_demos         Array of demo items.
     * @param string $active_editor Active editor slug.
     * @return array Filtered array of demo items.
     */
    public static function filter_demos_by_editor( $ansar_import_demos, $active_editor = 'default-theme' ) {
        if ( ! is_array( $ansar_import_demos ) ) {
            return array();
        }

        return array_values(
            array_filter(
                $ansar_import_demos,
                function ( $ansar_import_demo ) use ( $active_editor ) {
                    if ( ! is_array( $ansar_import_demo ) ) {
                        return false;
                    }

                    $template_type = isset( $ansar_import_demo['template_type'] ) ? $ansar_import_demo['template_type'] : '';
                    return self::normalize_demo_editor( $template_type ) === $active_editor;
                }
            )
        );
    }

    /**
     * Check whether the active theme supports the dedicated Theme Demos page (ansar-demo-import).
     *
     * @return bool
     */
    public static function has_theme_demos_page() {
        $support_config = self::get_theme_support_config();
        if ( isset( $support_config['context'] ) && 'starter' === $support_config['context'] ) {
            return false;
        }
        if ( isset( $support_config['context'] ) && 'theme' === $support_config['context'] ) {
            return true;
        }

        $current_theme   = wp_get_theme()->get( 'Name' );
        $excluded_themes = array( 'NewseBlog', 'Penpress', 'NewsHash' );
        return self::is_themeansar_theme() && ! in_array( $current_theme, $excluded_themes, true );
    }

    /**
     * Get the target Ansar Import demos URL for the active theme.
     * ThemeAnsar themes with a Theme Demos page resolve to `admin.php?page=ansar-demo-import`;
     * other themes resolve to `admin.php?page=ansar-starter-sites`.
     *
     * @param bool $onboarding Whether to append `&onboarding=true`.
     * @return string
     */
    public static function get_theme_demos_redirect_url( $onboarding = false ) {
        $support_config = self::get_theme_support_config();
        if ( ! empty( $support_config['starter_sites_url'] ) ) {
            $base_url = $support_config['starter_sites_url'];
            return $onboarding ? add_query_arg( 'onboarding', 'true', $base_url ) : $base_url;
        }

        $target_page = self::has_theme_demos_page() ? 'ansar-demo-import' : 'ansar-starter-sites';
        $args        = array( 'page' => $target_page );
        if ( $onboarding ) {
            $args['onboarding'] = 'true';
        }
        return add_query_arg( $args, admin_url( 'admin.php' ) );
    }

    /**
     * Detect whether the current admin request is targeting a theme's legacy Starter Sites tab/page
     * (e.g. `admin.php?page=newsup_admin_menu&tab=starter-sites`).
     * Themes declaring `add_theme_support( 'ansar-starter-sites' )` embed the modern component natively
     * and are not redirected away from their own dashboard tab.
     *
     * @return bool
     */
    public static function is_legacy_theme_starter_sites_request() {
        if ( ! self::is_themeansar_theme() ) {
            return false;
        }

        if ( function_exists( 'current_theme_supports' ) && current_theme_supports( 'ansar-starter-sites' ) ) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query parameters used to detect current admin screen tab.
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query parameters used to detect current admin screen tab.
        $tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

        if ( in_array( $page, array( 'ansar-starter-sites', 'ansar-demo-import', 'ansar-plugin-demos', 'ansar-setup-wizard' ), true ) ) {
            return false;
        }

        if ( in_array( $tab, array( 'starter-sites', 'starter_sites', 'ansar-demo-import' ), true ) ) {
            return true;
        }

        if ( '' !== $page && ( false !== strpos( $page, 'tab=starter-sites' ) || false !== strpos( $page, 'tab=starter_sites' ) ) ) {
            return true;
        }

        return false;
    }

    public function register_theme_page() {
        $starter_icon = $this->get_starter_icon();

        add_menu_page('Ansar Import', 'Ansar Import', 'manage_options', 'ansar-starter-sites', array($this, 'starter_sites_page'), $starter_icon , 20);
        
        add_submenu_page('ansar-starter-sites', 'Starter Sites', 'Starter Sites', 'manage_options', 'ansar-starter-sites');

        if ( self::has_theme_demos_page() ) {
            add_submenu_page('ansar-starter-sites', 'Theme Demos', 'Theme Demos', 'manage_options', 'ansar-demo-import', array($this, 'theme_option_page'));
        }
        if ( is_plugin_active('blognews-for-elementor/blognews-for-elementor.php') || is_plugin_active('blognews-for-elementor-pro/blognews-for-elementor.php') ) {
            add_submenu_page(
                'ansar-starter-sites',
                'BlogNews Demos',
                'BlogNews Demos',
                'manage_options',
                'ansar-plugin-demos',
                array($this, 'plugin_demos_page')
            );
        }

        add_submenu_page(
            'ansar-starter-sites',
            __( 'Setup Wizard', 'ansar-import' ),
            __( 'Setup Wizard', 'ansar-import' ),
            'manage_options',
            'ansar-setup-wizard',
            array( $this, 'setup_wizard_redirect_page' )
        );
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
            return '';
        }
    }

    function infinity_load_demos() {
        if ( ! isset( $_POST['check_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['check_import_nonce'] ) ), 'ansar_demo_import_nonce' ) ) {
            wp_send_json_error( array( 'status' => 'error', 'msg' => 'Security check failed.' ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'status' => 'error', 'msg' => 'Insufficient permissions.' ] );
        }
        $paged         = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
        $seed          = isset( $_POST['seed'] ) ? absint( $_POST['seed'] ) : wp_rand( 1, 1000 );
        $active_editor = isset( $_POST['editor'] ) ? sanitize_key( wp_unslash( $_POST['editor'] ) ) : '';
        $theme_data    = wp_get_theme();
        $theme_name    = $theme_data->get('Name');

        $cache_key     = 'ansar_infinity_demos_' . md5( $seed . '_' . $paged );
        $quaried_demos = get_transient( $cache_key );

        if ( false === $quaried_demos ) {
            $theme_data_api = self::remote_get(
                "https://api.themeansar.com/wp-json/wp/v2/demos/?orderby=rand&seed=$seed&per_page=12&page=$paged"
            );
            if ( ! is_wp_error( $theme_data_api ) && 200 === wp_remote_retrieve_response_code( $theme_data_api ) ) {
                $decoded = json_decode( wp_remote_retrieve_body( $theme_data_api ), true );
                if ( is_array( $decoded ) ) {
                    $quaried_demos = $decoded;
                    set_transient( $cache_key, $quaried_demos, HOUR_IN_SECONDS );
                }
            }
        }

        $response = '';

        if ( ! is_array( $quaried_demos ) || empty( $quaried_demos ) || ( isset( $quaried_demos['code'] ) && 'rest_post_invalid_page_number' === $quaried_demos['code'] ) ) {
            $response = 'No demos found';
        } else {
            foreach ( $quaried_demos as $ansar_import_demo ) {
                $response .= $this->plugin_get_template_content(
                    [
                        'demo'                 => $ansar_import_demo,
                        'theme_name'           => $theme_name,
                        'ansar_import_context' => 'starter',
                        'context'              => 'starter',
                        'ansar_active_editor'  => $active_editor,
                    ]
                );
            }
            $paged++;
        }

        $result = array(
            'html'  => $response,
            'paged' => $paged,
        );

        wp_send_json( $result );
    }

    /**
     * Save user preferred editor choice via AJAX
     */
    public function save_preferred_editor_ajax() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ansar_demo_import_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ansar-import' ) ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ansar-import' ) ) );
        }

        $editor = isset( $_POST['editor'] ) ? sanitize_key( wp_unslash( $_POST['editor'] ) ) : 'default-theme';
        $allowed = array( 'default-theme', 'block-editor', 'elementor' );
        if ( ! in_array( $editor, $allowed, true ) ) {
            $editor = 'default-theme';
        }

        update_option( 'ansar_preferred_editor', $editor );
        update_user_meta( get_current_user_id(), 'ansar_preferred_editor', $editor );

        wp_send_json_success( array(
            'editor'  => $editor,
            'message' => __( 'Preferred editor saved.', 'ansar-import' ),
        ) );
    }

    /**
     * Dismiss hero banner via AJAX and persist in user meta
     */
    public function dismiss_hero_ajax() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ansar_demo_import_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ansar-import' ) ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'ansar-import' ) ) );
        }

        update_user_meta( get_current_user_id(), 'ansar_dismissed_hero', true );
        wp_send_json_success( array( 'dismissed' => true ) );
    }

    /**
     * Get required plugins and their status for a demo
     */
    public function get_demo_plugins_status_ajax() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ansar_demo_import_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ansar-import' ) ) );
        }

        $ansar_import_demo_id = isset( $_POST['demo_id'] ) ? absint( $_POST['demo_id'] ) : 0;
        $ansar_import_demo_data = array();

        if ( ! empty( $_POST['demo_data'] ) ) {
            $raw = is_string( $_POST['demo_data'] )
                ? sanitize_text_field( wp_unslash( $_POST['demo_data'] ) )
                : '';
            if ( '' !== $raw ) {
                $decoded = json_decode( $raw, true );
                if ( is_array( $decoded ) ) {
                    $ansar_import_demo_data = $decoded;
                }
            } elseif ( is_array( $_POST['demo_data'] ) ) {
                $ansar_import_demo_data = map_deep( wp_unslash( $_POST['demo_data'] ), 'sanitize_text_field' );
            }
        }

        $plugin_manager = Ansar_Plugin_Manager::instance();

        if ( empty( $ansar_import_demo_data ) && $ansar_import_demo_id > 0 ) {
            $fetched = $plugin_manager->fetch_demo_data( $ansar_import_demo_id );
            if ( is_array( $fetched ) ) {
                $ansar_import_demo_data = $fetched;
            }
        }

        $required_plugins = $plugin_manager->extract_required_plugins( $ansar_import_demo_data );

        wp_send_json_success( array(
            'plugins' => array_values( $required_plugins ),
        ) );
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
     * Render the plugin demo page for plugin
     *
     * @since  1.0.0
     */
    public function plugin_demos_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ansar-plugin-sites-display.php';
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
     * Reusable Starter Sites / Theme Demos UI component for themes and plugins.
     * Hooked to `do_action( 'ansar_import_render_starter_sites', $args )`.
     * Automatically enqueues required styles, scripts, and localized data even when
     * invoked inside a theme's own custom admin dashboard screen/tab.
     *
     * @param array $args {
     *     Optional configuration arguments.
     *     @type string $context               'auto' (default), 'theme', 'starter', or 'plugin'.
     *     @type bool   $show_header           Whether to display the top Ansar Import bar (default: true).
     *     @type bool   $show_hero             Whether to display the hero banner (default: true).
     *     @type bool   $show_favorites_filter Whether to display Favorites in the plan filter (default: true).
     * }
     */
    public function render_starter_sites_component( $args = array() ) {
        $defaults = array(
            'context'               => 'auto',
            'show_header'           => true,
            'show_hero'             => true,
            'show_favorites_filter' => true,
        );
        $args = wp_parse_args( is_array( $args ) ? $args : array(), $defaults );

        $resolved_context = $args['context'];
        if ( 'auto' === $resolved_context || '' === $resolved_context ) {
            $resolved_context = self::has_theme_demos_page() ? 'theme' : 'starter';
        }

        // Ensure CSS, JS, and localized demo data are enqueued on any custom theme screen
        $this->enqueue_importer_assets( $resolved_context );

        $show_header           = (bool) $args['show_header'];
        $show_hero             = (bool) $args['show_hero'];
        $show_favorites_filter = (bool) $args['show_favorites_filter'];

        if ( 'plugin' === $resolved_context ) {
            include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-plugin-sites-display.php';
        } elseif ( 'theme' === $resolved_context && self::is_themeansar_theme() ) {
            include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-import-admin-display.php';
        } else {
            include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-starter-sites-display.php';
        }
    }

    /**
     * Enqueue Ansar Import admin styles, scripts, and localized demo data once per request.
     * Can be called from standard `admin_enqueue_scripts` or on-demand via `render_starter_sites_component()`.
     *
     * @param string $forced_context Optional context ('starter', 'theme', 'plugin').
     */
    public function enqueue_importer_assets( $forced_context = '' ) {
        static $assets_enqueued = false;
        if ( $assets_enqueued ) {
            return;
        }
        $assets_enqueued = true;

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ansar-import-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'theme' );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ansar-import-admin.js', array(), $this->version, true );

        $theme_data     = wp_get_theme();
        $theme_name     = $theme_data->get( 'Name' );
        $parent_theme   = $theme_data->parent();
        $parent_name    = $parent_theme ? $parent_theme->get( 'Name' ) : '';
        $parent_slug    = $parent_theme ? strtolower( (string) $theme_data->get_template() ) : '';
        $is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

        $preferred_editor = get_user_meta( get_current_user_id(), 'ansar_preferred_editor', true );
        if ( ! $preferred_editor ) {
            $preferred_editor = get_option( 'ansar_preferred_editor', '' );
        }
        $has_set_editor = ! empty( $preferred_editor );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query argument for script localization context.
        $current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'ansar-starter-sites';

        wp_localize_script(
            $this->plugin_name,
            'my_ajax_object',
            array(
                'ajax_url'         => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'ansar_demo_import_nonce' ),
                'theme_name'       => $theme_name,
                'theme_slug'       => strtolower( get_stylesheet() ),
                'parent_name'      => $parent_name,
                'parent_slug'      => $parent_slug,
                'is_block_theme'   => $is_block_theme,
                'preferred_editor' => $preferred_editor ? $preferred_editor : ( $is_block_theme ? 'block-editor' : 'default-theme' ),
                'has_set_editor'   => $has_set_editor,
                'current_page'     => $current_page,
                'doc_url'          => 'https://docs.themeansar.com/',
                'i18n'             => array(
                    'status_active'      => __( 'Active', 'ansar-import' ),
                    'status_installed'   => __( 'Installed (Will Activate)', 'ansar-import' ),
                    'status_installable' => __( 'Will Install from WP.org', 'ansar-import' ),
                    'status_pro'         => __( 'Pro Plugin Required', 'ansar-import' ),
                    'loading_plugins'    => __( 'Checking required plugins...', 'ansar-import' ),
                    'no_plugins_needed'  => __( 'No extra plugins required for this demo.', 'ansar-import' ),
                ),
            )
        );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query argument to refresh transients.
        if ( isset( $_GET['ansar_refresh'] ) ) {
            self::maybe_flush_demo_transients( $theme_name );
        }

        $ansar_import_demo_context = $forced_context;
        if ( '' === $ansar_import_demo_context ) {
            if ( 'ansar-demo-import' === $current_page ) {
                $ansar_import_demo_context = 'theme';
            } elseif ( 'ansar-plugin-demos' === $current_page ) {
                $ansar_import_demo_context = 'plugin';
            } else {
                $ansar_import_demo_context = 'starter';
            }
        }

        $ansar_import_all_demos      = self::get_cached_demos( $ansar_import_demo_context, $theme_name );
        $plugin_manager = Ansar_Plugin_Manager::instance();

        $slim_demos = array();
        foreach ( $ansar_import_all_demos as $ansar_import_demo ) {
            if ( ! is_array( $ansar_import_demo ) || ! isset( $ansar_import_demo['id'] ) ) {
                continue;
            }
            $slim_demos[] = array(
                'id'                  => $ansar_import_demo['id'],
                'theme_name'          => isset( $ansar_import_demo['theme_name'] ) ? $ansar_import_demo['theme_name'] : '',
                'template_type'       => isset( $ansar_import_demo['template_type'] ) ? $ansar_import_demo['template_type'] : '',
                'preview_link'        => isset( $ansar_import_demo['preview_link'] ) ? $ansar_import_demo['preview_link'] : '',
                'pro_link'            => isset( $ansar_import_demo['pro_link'] ) ? $ansar_import_demo['pro_link'] : '',
                'premiun_plugin_link' => isset( $ansar_import_demo['premiun_plugin_link'] ) ? $ansar_import_demo['premiun_plugin_link'] : '',
                'required_plugins'    => array_values( $plugin_manager->extract_required_plugins( $ansar_import_demo ) ),
            );
        }

        wp_localize_script(
            $this->plugin_name,
            'ansar_theme_object',
            $slim_demos
        );
    }

    /**
     * Check if the current screen is one of Ansar Import's native admin pages
     * or a theme screen that opted into pre-enqueuing via `ansar_import_should_enqueue_assets`.
     *
     * @return bool
     */
    private function is_importer_admin_screen() {
        $screen = get_current_screen();
        $is_native = isset( $screen->base ) && in_array(
            $screen->base,
            array(
                'toplevel_page_ansar-starter-sites',
                'ansar-import_page_ansar-demo-import',
                'ansar-import_page_ansar-plugin-demos',
            ),
            true
        );

        if ( ! $is_native && function_exists( 'current_theme_supports' ) && current_theme_supports( 'ansar-starter-sites' ) ) {
            if ( isset( $screen->base ) && false !== strpos( $screen->base, '_admin_menu' ) ) {
                $is_native = true;
            }
        }

        return (bool) apply_filters( 'ansar_import_should_enqueue_assets', $is_native, $screen );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        if ( $this->is_importer_admin_screen() ) {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ansar-import-admin.css', array(), $this->version, 'all' );
            wp_enqueue_style( 'theme' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        if ( $this->is_importer_admin_screen() ) {
            remove_all_actions( 'admin_notices' );
            $support_config = self::get_theme_support_config();
            $forced_context = ! empty( $support_config['context'] ) ? $support_config['context'] : ( self::has_theme_demos_page() ? 'theme' : '' );
            $this->enqueue_importer_assets( $forced_context );
        }
    }

    /**
     * Template library dependency plugins.
     *
     * @since    2.1.3
     * @return   array
     */
    private function template_library_plugin_configs() {
        return array(
            'free' => array(
                'name'       => 'Ansar Blocks',
                'slug'       => 'ansar-blocks',
                'directory'  => 'ansar-blocks',
                'main_files' => array(
                    'ansar-blocks/ansar-blocks.php',
                ),
                'source'     => 'wordpress',
            ),
            'pro'  => array(
                'name'       => 'Ansar Blocks Pro',
                'slug'       => 'ansar-blocks-pro',
                'directory'  => 'ansar-blocks-pro',
                'main_files' => array(
                    'ansar-blocks-pro/ansar-blocks-pro.php',
                    'ansar-blocks-pro/ansar-blocks.php',
                ),
                'source'     => 'local',
            ),
        );
    }

    /**
     * Get a template-library dependency status via Ansar_Plugin_Manager.
     *
     * @since    2.1.3
     * @param    string $type Plugin type ('free' or 'pro').
     * @return   array
     */
    private function template_library_get_plugin_status( $type ) {
        $configs = $this->template_library_plugin_configs();
        $config  = isset( $configs[ $type ] ) ? $configs[ $type ] : array();

        if ( empty( $config ) ) {
            return array(
                'installed' => false,
                'active'    => false,
                'file'      => '',
            );
        }

        $details = Ansar_Plugin_Manager::instance()->get_plugin_details(
            $config['slug'],
            isset( $config['main_files'] ) ? $config['main_files'] : array()
        );

        return array(
            'installed' => $details['installed'],
            'active'    => $details['active'],
            'file'      => $details['file'],
        );
    }

    /**
     * Prepare a dependency plugin for template import via Ansar_Plugin_Manager.
     *
     * @since    2.1.3
     */
    public function template_library_prepare_plugin_ajax() {
        if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'You do not have permission to manage plugins.', 'ansar-import' ),
                )
            );
        }

        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ansar_template_library_nonce' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Security check failed.', 'ansar-import' ),
                )
            );
        }

        $type    = isset( $_POST['plugin_type'] ) ? sanitize_key( wp_unslash( $_POST['plugin_type'] ) ) : 'free';
        $configs = $this->template_library_plugin_configs();

        if ( empty( $configs[ $type ] ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Invalid plugin type.', 'ansar-import' ),
                )
            );
        }

        $result = Ansar_Plugin_Manager::instance()->prepare_single_plugin( $configs[ $type ] );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error(
                array(
                    'message' => $result->get_error_message(),
                )
            );
        }

        wp_send_json_success( $result );
    }

    /**
     * Register the template library in the block editor.
     *
     * @since    2.1.3
     */
    public function enqueue_block_editor_assets() {
        $script_path = ANSAR_IMPORT_DIR_PATH . 'admin/js/template-library.js';
        $style_path  = ANSAR_IMPORT_DIR_PATH . 'admin/css/template-library.css';

        wp_enqueue_style( 'dashicons' );

        wp_enqueue_style(
            'ansar-import-template-library',
            ANSAR_IMPORT_DIR_URL . 'admin/css/template-library.css',
            array( 'dashicons' ),
            file_exists( $style_path ) ? filemtime( $style_path ) : $this->version
        );

        wp_enqueue_script(
            'ansar-import-template-library',
            ANSAR_IMPORT_DIR_URL . 'admin/js/template-library.js',
            array(
                'wp-blocks',
                'wp-components',
                'wp-data',
                'wp-element',
                'wp-i18n',
                'wp-plugins',
            ),
            file_exists( $script_path ) ? filemtime( $script_path ) : $this->version,
            true
        );

        wp_localize_script(
            'ansar-import-template-library',
            'taAnsarImportTemplateLibraryData',
            array(
                'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'ansar_template_library_nonce' ),
                'logoUrl'          => ANSAR_IMPORT_DIR_URL . 'admin/partials/image/ansar-icon.svg',
                'menuIcon'         => $this->get_starter_icon(),
                'sectionsEndpoint' => 'https://api.themeansar.com/wp-json/ta-be/section-templates/',
                'pagesEndpoint'    => 'https://api.themeansar.com/wp-json/ta-be/page-templates/',
                'templatesLimit'   => 1000,
                'requestPageSize'  => 100,
                'renderBatchSize'  => 24,
                'searchDelay'      => 250,
                'cacheKey'         => 'ta_ansar_import_template_library_' . ANSAR_IMPORT_VERSION . '_patterns_api_themeansar',
                'cacheTtl'         => DAY_IN_SECONDS,
                'proUrl'           => 'https://themeansar.com/',
                'helpUrl'          => 'https://themeansar.com/docs/',
                'supportUrl'       => 'https://themeansar.com/support/',
                'tutorialsUrl'     => 'https://www.youtube.com/@themeansar',
                'plugins'          => array(
                    'free' => $this->template_library_get_plugin_status( 'free' ),
                    'pro'  => $this->template_library_get_plugin_status( 'pro' ),
                ),
            )
        );
    }

    /**
     * Handles redirect on first plugin activation, setup wizard submenu click,
     * or legacy Theme > Starter Sites requests across all ThemeAnsar themes.
     * Themeansar themes/child themes redirect to Theme Demos (`ansar-demo-import`).
     * Other themes redirect to Starter Sites (`ansar-starter-sites`) with Onboarding wizard.
     */
    public function handle_activation_redirect() {
        if ( is_network_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        // 1. Handle Setup Wizard submenu click.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query argument for admin submenu redirect.
        if ( isset( $_GET['page'] ) && 'ansar-setup-wizard' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
            wp_safe_redirect( self::get_theme_demos_redirect_url( true ) );
            exit;
        }

        // 2. Handle first plugin activation redirect (overrides any theme redirect after install/activate).
        if ( get_transient( 'ansar_import_activation_redirect' ) ) {
            delete_transient( 'ansar_import_activation_redirect' );

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core WordPress query argument during multi-plugin activation.
            if ( ! isset( $_GET['activate-multi'] ) ) {
                wp_safe_redirect( self::get_theme_demos_redirect_url( true ) );
                exit;
            }
        }

        // 3. Intercept legacy Theme > Starter Sites URLs (e.g. ?page=newsup_admin_menu&tab=starter-sites).
        if ( self::is_legacy_theme_starter_sites_request() ) {
            $saved_editor = get_user_meta( get_current_user_id(), 'ansar_preferred_editor', true );
            if ( ! $saved_editor ) {
                $saved_editor = get_option( 'ansar_preferred_editor', '' );
            }
            wp_safe_redirect( self::get_theme_demos_redirect_url( empty( $saved_editor ) ) );
            exit;
        }
    }

    /**
     * Rewrite legacy Theme > Starter Sites submenu items in WP Admin sidebar
     * so they point directly to Ansar Import > Theme Demos.
     */
    public function override_theme_starter_sites_submenu() {
        if ( ! self::is_themeansar_theme() ) {
            return;
        }

        if ( function_exists( 'current_theme_supports' ) && current_theme_supports( 'ansar-starter-sites' ) ) {
            return;
        }

        global $submenu;
        if ( ! is_array( $submenu ) ) {
            return;
        }

        $saved_editor = get_user_meta( get_current_user_id(), 'ansar_preferred_editor', true );
        if ( ! $saved_editor ) {
            $saved_editor = get_option( 'ansar_preferred_editor', '' );
        }
        $target_url = self::get_theme_demos_redirect_url( empty( $saved_editor ) );

        foreach ( $submenu as $parent_slug => &$items ) {
            if ( 'ansar-starter-sites' === $parent_slug || ! is_array( $items ) ) {
                continue;
            }
            foreach ( $items as &$item ) {
                if ( isset( $item[2] ) && is_string( $item[2] ) && ( false !== strpos( $item[2], 'tab=starter-sites' ) || false !== strpos( $item[2], 'tab=starter_sites' ) ) ) {
                    $item[2] = $target_url;
                }
            }
            unset( $item );
        }
        unset( $items );
    }

    /**
     * Dequeue legacy UIkit assets that older ThemeAnsar themes attempt to load
     * from ansar-import on their own theme dashboard screens.
     */
    public function cleanup_legacy_theme_enqueues() {
        wp_dequeue_style( 'ansar-import-uikit.min-css' );
        wp_deregister_style( 'ansar-import-uikit.min-css' );
        wp_dequeue_script( 'ansar-import-uikit-js' );
        wp_deregister_script( 'ansar-import-uikit-js' );
    }

    /**
     * Output a lightweight admin footer script on ThemeAnsar themes to rewrite
     * all "Get Started", "Start with Demo Sites", and "Starter Sites" tab links
     * directly to Ansar Import > Theme Demos (`admin.php?page=ansar-demo-import`).
     */
    public function override_theme_get_started_links() {
        if ( ! self::is_themeansar_theme() ) {
            return;
        }

        if ( function_exists( 'current_theme_supports' ) && current_theme_supports( 'ansar-starter-sites' ) ) {
            return;
        }

        $saved_editor = get_user_meta( get_current_user_id(), 'ansar_preferred_editor', true );
        if ( ! $saved_editor ) {
            $saved_editor = get_option( 'ansar_preferred_editor', '' );
        }
        $target_url = self::get_theme_demos_redirect_url( empty( $saved_editor ) );
        ?>
        <script id="ansar-import-theme-links-override">
        (function() {
            var targetUrl = <?php echo wp_json_encode( esc_url_raw( $target_url ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-encoded URL for inline script. ?>;
            var selector = 'a[href*="tab=starter-sites"], a[href*="tab=starter_sites"], a[class*="-btn-get-started"], a[class*="-str-sites"], a[class*="-btn-ins"]';

            function rewriteThemeDemoLinks() {
                var links = document.querySelectorAll(selector);
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    link.setAttribute('href', targetUrl);
                    link.classList.remove('load');
                    var parentLabel = link.closest('label[for]');
                    if (parentLabel) {
                        parentLabel.removeAttribute('for');
                    }
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', rewriteThemeDemoLinks, { once: true });
            } else {
                rewriteThemeDemoLinks();
            }

            document.addEventListener('click', function(e) {
                var link = e.target && e.target.closest ? e.target.closest(selector) : null;
                if (!link) {
                    return;
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                window.location.href = targetUrl;
            }, true);
        })();
        </script>
        <?php
    }

    /**
     * Fallback callback for Setup Wizard submenu page.
     */
    public function setup_wizard_redirect_page() {
        $target_url = self::get_theme_demos_redirect_url( true );
        ?>
        <script>window.location.href = <?php echo wp_json_encode( esc_url_raw( $target_url ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-encoded URL for inline script. ?>;</script>
        <?php
    }

    /**
     * Hooked to after_switch_theme to detect when any theme or child theme is activated.
     */
    public function on_theme_switched() {
        // Clear previous editor choice so the new theme prompts onboarding
        delete_user_meta( get_current_user_id(), 'ansar_preferred_editor' );
        delete_option( 'ansar_preferred_editor' );
    }

}
