<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://themeansar.com/
 * @since      1.0.0
 *
 * @package    Ansar_Import
 * @subpackage Ansar_Import/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ansar_import_theme_data = wp_get_theme();
$ansar_import_theme_name = $ansar_import_theme_data->get( 'Name' );

$ansar_import_all_demos_unfiltered = Ansar_Import_Admin::get_cached_demos( 'theme', $ansar_import_theme_name );
extract( Ansar_Import_Admin::get_editor_context( 'ansar-demo-import', $ansar_import_all_demos_unfiltered ) );

$ansar_import_is_themeansar_theme = isset( $is_themeansar_theme ) ? $is_themeansar_theme : Ansar_Import_Admin::is_themeansar_theme( $ansar_import_theme_data );

if ( ! $ansar_import_is_themeansar_theme ) {
    echo '<div class="notice notice-error"><p>' . wp_kses_post( __( 'Ansar Import - This plugin requires an official <a href="https://themeansar.com/" target="_blank">Theme Ansar</a> theme to be activated.', 'ansar-import' ) ) . '</p></div>';
    return;
}
$ansar_import_is_embedded_view = ( isset( $ansar_import_show_header ) && false === $ansar_import_show_header ) || ( isset( $show_header ) && false === $show_header );
?>

<div class="<?php echo esc_attr( $ansar_import_is_embedded_view ? 'ansar-content-wrapper ansar-embedded-wrapper' : 'wrap ansar-content-wrapper' ); ?>">
    <?php
    include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-admin-header-hero.php';

    if ( empty( $ansar_import_all_demos_unfiltered ) ) { ?>
        <div class="ansar-empty-editor-state" style="margin-top: 50px;">
            <h2><?php esc_html_e( 'Unable to connect to the demo library', 'ansar-import' ); ?></h2>
            <p><?php esc_html_e( 'We could not fetch the demos from the server. Please check your internet connection, or try refreshing the page.', 'ansar-import' ); ?></p>
            <a href="<?php echo esc_url( $ansar_refresh_url ); ?>" class="button button-primary" style="margin-top: 20px;"><?php esc_html_e( 'Try Again', 'ansar-import' ); ?></a>
            <?php
            $ansar_import_api_err = Ansar_Import_Admin::get_last_api_error();
            if ( current_user_can( 'manage_options' ) && ! empty( $ansar_import_api_err ) ) : ?>
                <div style="margin: 25px auto 0; max-width: 650px; text-align: left; background: #fff; border-left: 4px solid #dc3232; padding: 15px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px;">
                    <h4 style="margin: 0 0 8px; color: #dc3232;"><?php esc_html_e( 'Server Diagnostic Details (Admins Only):', 'ansar-import' ); ?></h4>
                    <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Error Message:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( $ansar_import_api_err['message'] ); ?></code></p>
                    <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Requested URL:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( $ansar_import_api_err['url'] ); ?></code></p>
                    <?php if ( ! empty( $ansar_import_api_err['code'] ) ) : ?>
                        <p style="margin: 4px 0;"><strong><?php esc_html_e( 'Status Code:', 'ansar-import' ); ?></strong> <code><?php echo esc_html( (string) $ansar_import_api_err['code'] ); ?></code></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        </div> <!-- close wrap -->
    <?php return; }

    $ansar_import_all_categories = Ansar_Import_Admin::get_cached_categories( 'theme' );
    $ansar_import_active_editor  = isset( $ansar_import_active_editor ) ? $ansar_import_active_editor : ( isset( $ansar_active_editor ) ? $ansar_active_editor : 'default-theme' );
    $ansar_import_all_demos      = Ansar_Import_Admin::filter_demos_by_editor( $ansar_import_all_demos_unfiltered, $ansar_import_active_editor );
    $ansar_import_no_demos       = ( count( $ansar_import_all_demos ) === 0 );
    $ansar_import_present_cat    = array();
    $ansar_import_all_theme_cat  = array();

    foreach ( $ansar_import_all_demos_unfiltered as $ansar_import_demo ) {
        if ( ! empty( $ansar_import_demo['categories'] ) && is_array( $ansar_import_demo['categories'] ) ) {
            foreach ( $ansar_import_demo['categories'] as $ansar_import_in_cat ) {
                if ( isset( $ansar_import_in_cat['id'] ) ) {
                    $ansar_import_all_theme_cat[] = $ansar_import_in_cat['id'];
                }
            }
        }
    }
    foreach ( $ansar_import_all_demos as $ansar_import_demo ) {
        if ( ! empty( $ansar_import_demo['categories'] ) && is_array( $ansar_import_demo['categories'] ) ) {
            foreach ( $ansar_import_demo['categories'] as $ansar_import_in_cat ) {
                if ( isset( $ansar_import_in_cat['id'] ) ) {
                    $ansar_import_present_cat[] = $ansar_import_in_cat['id'];
                }
            }
        }
    }

    $ansar_import_all_theme_cat = array_values( array_unique( $ansar_import_all_theme_cat ) );
    $ansar_import_present_cat   = array_values( array_unique( $ansar_import_present_cat ) );
    ?>

    <hr class="wp-header-end">
    <div class="theme-browser rendered demo-ansar-container">
        <div class="themes wp-clearfix">
            <!-- Filter Controls -->
            <div uk-filter="target: .js-filter">

                <ul class="uk-subnav uk-subnav-pill"<?php if ( $ansar_import_no_demos ) : ?> style="display: none;"<?php endif; ?>>
                    <li class="uk-active" uk-filter-control><a href="#">All</a></li>
                    <?php
                    foreach ( $ansar_import_all_categories as $ansar_import_category ) {
                        if ( in_array( $ansar_import_category['id'], $ansar_import_all_theme_cat, false ) ) {
                            $ansar_import_cat_is_hidden = ! in_array( $ansar_import_category['id'], $ansar_import_present_cat, false );
                            ?>
                            <li uk-filter-control="[data-color*='cat_<?php echo esc_attr( $ansar_import_category['id'] ); ?>']"<?php if ( $ansar_import_cat_is_hidden ) : ?> style="display: none;"<?php endif; ?>><a href="#"><?php echo esc_html( $ansar_import_category['name'] ); ?></a></li>
                            <?php
                        }
                    }
                    ?>
                </ul>

                <div class="js-filter grid-wrap">
                    <div class="ansar-empty-editor-state"<?php if ( ! $ansar_import_no_demos ) : ?> style="display: none;"<?php endif; ?>>
                        <span class="ansar-empty-editor-state-icon" aria-hidden="true"><?php
                            $ansar_import_current_switcher = isset( $ansar_import_editor_switcher ) ? $ansar_import_editor_switcher : ( isset( $ansar_editor_switcher ) ? $ansar_editor_switcher : array() );
                            echo isset( $ansar_import_current_switcher[ $ansar_import_active_editor ]['icon'] ) ? $ansar_import_current_switcher[ $ansar_import_active_editor ]['icon'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static trusted inline SVG icon.
                        ?></span>
                        <h2><?php esc_html_e( 'No demos available yet', 'ansar-import' ); ?></h2>
                        <p><?php esc_html_e( 'Try another editor type from the switcher above.', 'ansar-import' ); ?></p>
                    </div>
                    <?php
                    $ansar_import_context = 'theme';
                    foreach ( $ansar_import_all_demos_unfiltered as $ansar_import_demo ) {
                        include ANSAR_IMPORT_DIR_PATH . 'admin/partials/ansar-demo-box.php';
                    }
                    ?>
                </div>

            </div>



            <!-- /product -->


        </div>
    </div>
</div>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->




<!-- Modal -->
<div id="AnsardemoPreview" tabindex="-1" class="uk-modal-full" uk-modal>
    <!-- main include -->   
    <div class="theme-install-overlay wp-full-overlay expanded iframe-ready" style="display: block;">
        <div class="wp-full-overlay-sidebar">
            
            <div class="wp-full-overlay-header">
                <button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e('Close', 'ansar-import'); ?></span></button>
                <a class="button activate preview-live-btn uk-hidden" target="_new"  href="<?php echo esc_url(home_url()); ?>"> <?php esc_html_e('Live Preview','ansar-import'); ?></a>
                <button type="button" class="button button-primary import-priview activate btn-import" href="#" data-id="0"><svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg><?php esc_html_e('Import', 'ansar-import'); ?></button>
                <a class="button activate preview-buy uk-hidden" target="_new" href="#" ><svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 4px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg><?php esc_html_e('Unlock Pro', 'ansar-import'); ?></a>
            </div>

            <div class="wp-full-overlay-sidebar-content">
                <div class="install-theme-info">
                    <h3 class="theme-name"> <?php echo esc_html( $ansar_import_theme_data->get( 'Name' ) ); ?> </h3>
                    <span class="theme-by"><?php esc_html_e( 'By', 'ansar-import' ); ?> <?php echo esc_html( $ansar_import_theme_data->get( 'Author' ) ); ?> </span>
                    <img class="theme-screenshot" src="" alt="">
                    <div class="theme-details">
                        <div class="theme-version"><?php echo esc_html( $ansar_import_theme_data->get( 'Version' ) ); ?></div>
                        <div class="theme-description"><?php echo esc_html( $ansar_import_theme_data->get( 'Description' ) ); ?></div>
                    </div>
                </div>
            </div>
            <div class="wp-full-overlay-footer">

                <button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="Collapse Sidebar">
                    <span class="collapse-sidebar-arrow"></span>
                    <span class="collapse-sidebar-label"><?php esc_html_e('Collapse', 'ansar-import'); ?></span>
                </button>
                <div class="devices-wrapper">
                    <div class="devices">
                        <button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
                            <span class="screen-reader-text"><?php esc_html_e('Enter desktop preview mode', 'ansar-import'); ?><?php esc_html_e('Collapse', 'ansar-import'); ?></span>
                        </button>
                        <button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
                            <span class="screen-reader-text"><?php esc_html_e('Enter tablet preview mode', 'ansar-import'); ?></span>
                        </button>
                        <button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
                            <span class="screen-reader-text"><?php esc_html_e('Enter mobile preview mode', 'ansar-import'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="wp-full-overlay-main">
            <iframe id="theme_preview" title="Preview"></iframe> 
        </div>
    </div>
    <!-- main include -->   

</div>
<!-- Modal preview  End -->

<?php
require plugin_dir_path( __FILE__ ) . 'ansar-import-modals.php';
?>