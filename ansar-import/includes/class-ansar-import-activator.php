<?php

/**
 * Fired during plugin activation
 *
 * @link       https://themeansar.com/
 * @since      1.0.0
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ansar_Import
 * @subpackage Ansar_Import/includes
 * @author     Themeansar <info@themeansar.com>
 */
class Ansar_Import_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Set transient for seamless onboarding redirect on activation (including theme AJAX install/activate)
        set_transient( 'ansar_import_activation_redirect', true, 120 );
        delete_user_meta( get_current_user_id(), 'ansar_preferred_editor' );
        delete_option( 'ansar_preferred_editor' );
    }

}
