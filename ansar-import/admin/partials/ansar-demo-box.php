<?php
/**
 * Reusable Demo Card Partial
 *
 * Expected scope variables:
 * - array  $ansar_demo        Single demo item array from ThemeAnsar API.
 * - string $ansar_theme_name  Active theme name.
 * - string $ansar_import_context Optional: 'starter' (default), 'theme', or 'plugin'.
 *
 * @package Ansar_Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ansar_demo = isset( $ansar_import_demo ) && is_array( $ansar_import_demo ) ? $ansar_import_demo : ( ( isset( $ansar_demo ) && is_array( $ansar_demo ) ) ? $ansar_demo : ( ( isset( $ansar_import_demo ) && is_array( $ansar_import_demo ) ) ? $ansar_import_demo : null ) );

if ( ! $ansar_demo ) {
    return;
}

$ansar_import_context = isset( $ansar_import_context ) ? $ansar_import_context : ( isset( $ansar_context ) ? $ansar_context : ( isset( $context ) ? $context : 'starter' ) );
$ansar_theme_name     = isset( $ansar_import_theme_name ) ? (string) $ansar_import_theme_name : ( isset( $ansar_theme_name ) ? (string) $ansar_theme_name : ( isset( $theme_name ) ? (string) $theme_name : wp_get_theme()->get( 'Name' ) ) );
$ansar_demo_id        = isset( $ansar_demo['id'] ) ? absint( $ansar_demo['id'] ) : 0;
$ansar_demo_theme     = isset( $ansar_demo['theme_name'] ) ? (string) $ansar_demo['theme_name'] : '';
$ansar_template_type  = isset( $ansar_demo['template_type'] ) ? (string) $ansar_demo['template_type'] : '';
$ansar_raw_title      = isset( $ansar_demo['title']['rendered'] ) ? (string) $ansar_demo['title']['rendered'] : '';
$ansar_display_name   = ( 'plugin' === $ansar_import_context && ! empty( $ansar_demo['demo_name'] ) ) ? (string) $ansar_demo['demo_name'] : $ansar_raw_title;
$ansar_preview_link   = isset( $ansar_demo['preview_link'] ) ? (string) $ansar_demo['preview_link'] : '';
$ansar_preview_url    = isset( $ansar_demo['preview_url'] ) ? Ansar_Import::get_cdn_url( $ansar_demo['preview_url'] ) : '';
$ansar_pro_link       = isset( $ansar_demo['pro_link'] ) ? (string) $ansar_demo['pro_link'] : '';
$ansar_categories     = ( isset( $ansar_demo['categories'] ) && is_array( $ansar_demo['categories'] ) ) ? $ansar_demo['categories'] : array();

// 1. Pro vs. Free determination per context
if ( 'plugin' === $ansar_import_context ) {
    static $ansar_is_blognews_pro_active = null;
    if ( null === $ansar_is_blognews_pro_active ) {
        $ansar_is_blognews_pro_active = function_exists( 'is_plugin_active' ) && is_plugin_active( 'blognews-for-elementor-pro/blognews-for-elementor.php' );
    }
    $ansar_is_pro_locked = ( isset( $ansar_demo['theme_type'] ) && 'pro' === $ansar_demo['theme_type'] && ! $ansar_is_blognews_pro_active );
} else {
    $ansar_active_theme_obj = wp_get_theme();
    $ansar_parent_theme_obj = $ansar_active_theme_obj->parent();
    $ansar_parent_name      = $ansar_parent_theme_obj ? $ansar_parent_theme_obj->get( 'Name' ) : '';
    $ansar_theme_matches    = ( 0 === strcasecmp( $ansar_theme_name, $ansar_demo_theme ) ) || ( ! empty( $ansar_parent_name ) && 0 === strcasecmp( $ansar_parent_name, $ansar_demo_theme ) );
    $ansar_is_pro_locked    = ( stripos( $ansar_demo_theme, 'pro' ) !== false || ( isset( $ansar_demo['theme_type'] ) && 'pro' === $ansar_demo['theme_type'] ) ) && ! $ansar_theme_matches;
}

// 2. Preview behavior: Theme Demos opens #AnsardemoPreview modal; Starter & Plugin Demos open live URL in new tab
$ansar_use_modal_preview = ( 'theme' === $ansar_import_context );
static $ansar_installed_demo_id = null;
if ( $ansar_use_modal_preview && null === $ansar_installed_demo_id ) {
    $ansar_installed_demo_id = (int) get_option( 'ansar_demo_installed' );
}
$ansar_is_live_installed = $ansar_use_modal_preview && ( $ansar_installed_demo_id === $ansar_demo_id );

// 3. First matching demo attribute (used on plugin demos view)
$ansar_is_first_demo = false;
if ( 'plugin' === $ansar_import_context && $ansar_theme_name === $ansar_demo_theme && empty( $ansar_import_first_match_found ) && empty( $ansar_first_match_found ) ) {
    $ansar_is_first_demo            = true;
    $ansar_import_first_match_found = true;
    $ansar_first_match_found        = true;
}

// 4. Theme slug attribute for .btn-import (omitted in 'plugin' context so active theme is preserved)
$ansar_tname_slug = ( 'plugin' !== $ansar_import_context && '' !== $ansar_demo_theme ) ? strtolower( str_replace( ' ', '-', $ansar_demo_theme ) ) : '';

// 5. Normalized editor slug for instant client-side editor switching
$ansar_demo_editor     = Ansar_Import_Admin::normalize_demo_editor( $ansar_template_type );
$ansar_is_editor_hidden = ( 'plugin' !== $ansar_import_context && ! empty( $ansar_active_editor ) && $ansar_demo_editor !== $ansar_active_editor );
?>
<div class="ansar-inner-box" data-editor="<?php echo esc_attr( $ansar_demo_editor ); ?>" data-color="<?php
    foreach ( $ansar_categories as $ansar_in_cat ) {
        if ( isset( $ansar_in_cat['id'] ) ) {
            echo 'cat_' . esc_attr( $ansar_in_cat['id'] ) . ' ';
        }
    }
?>"<?php if ( $ansar_is_first_demo ) : ?> data-first-demo<?php endif; ?><?php if ( $ansar_is_editor_hidden ) : ?> style="display: none;"<?php endif; ?>>
    <!-- product -->
    <div class="uk-card theme" style="width: 100%;" tabindex="0">
        <div class="theme-screenshot">
            <button type="button" class="ansar-card-favorite-btn" data-id="<?php echo esc_attr( $ansar_demo_id ); ?>" aria-label="<?php esc_attr_e( 'Add to favorites', 'ansar-import' ); ?>" title="<?php esc_attr_e( 'Add to favorites', 'ansar-import' ); ?>">
                <svg class="ansar-favorite-heart" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </button>
            <?php if ( $ansar_is_pro_locked ) : ?>
                <span class="ribbon pro">
                    <svg class="ansar-ribbon-svg" viewBox="0 0 24 24" width="11" height="11" fill="currentColor" aria-hidden="true"><path d="M2.5 9l3.5 11h12l3.5-11-5.5 4.5-4-7-4 7L2.5 9z"/></svg>
                    <?php esc_html_e( 'Pro', 'ansar-import' ); ?>
                </span>
            <?php else : ?>
                <span class="ribbon">
                    <svg class="ansar-ribbon-svg" viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <?php esc_html_e( 'Free', 'ansar-import' ); ?>
                </span>
            <?php endif; ?>
            <?php if ( 'elementor' === $ansar_template_type ) : ?>
                <span class="ani-demo-editor-badge ani-badge-elementor">
                    <svg class="ansar-badge-icon" viewBox="0 0 24 24" width="11" height="11" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="currentColor"/><path fill="#fff" d="M7.25 6.9h2.2v10.2h-2.2V6.9Zm4.05 0h5.45v2.1H11.3V6.9Zm0 4.05h5.45v2.1H11.3v-2.1Zm0 4.05h5.45v2.1H11.3V15Z"/></svg>
                    <span>Elementor</span>
                </span>
            <?php elseif ( 'block-editor' === $ansar_template_type ) : ?>
                <span class="ani-demo-editor-badge ani-badge-block-editor">
                    <svg class="ansar-badge-icon" viewBox="0 0 24 24" width="11" height="11" fill="currentColor" aria-hidden="true"><path fill="currentColor" d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-5h6v5zm0-7H6V6h6v4zm6 7h-4V6h4v11z"/></svg>
                    <span>Block Editor</span>
                </span>
            <?php else : ?>
                <span class="ani-demo-editor-badge ani-badge-classic">
                    <svg class="ansar-badge-icon" viewBox="0 0 24 24" width="11" height="11" fill="currentColor" aria-hidden="true"><path fill="currentColor" d="M3 5.5C3 4.12 4.12 3 5.5 3h13C19.88 3 21 4.12 21 5.5v13c0 1.38-1.12 2.5-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 2.25h14V5.5a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v2.25Zm0 2V18.5c0 .28.22.5.5.5h4.25V9.75H5Zm6.75 0V19h6.75a.5.5 0 0 0 .5-.5V9.75h-7.25Z"/></svg>
                    <span>Classic</span>
                </span>
            <?php endif; ?>
            <img src="<?php echo esc_url( $ansar_preview_url ); ?>" loading="lazy" decoding="async" alt="<?php echo esc_attr( $ansar_raw_title ); ?>">

            <?php if ( $ansar_use_modal_preview ) : ?>
                <span class="more-details btn-preview" data-id="<?php echo esc_attr( $ansar_demo_id ); ?>" data-live="<?php echo esc_attr( $ansar_is_live_installed ? '1' : '' ); ?>" data-toggle="modal" data-target="#AnsardemoPreview"><svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><?php esc_html_e( 'Preview', 'ansar-import' ); ?></span>
            <?php else : ?>
                <a href="<?php echo esc_url( $ansar_preview_link ); ?>" target="_blank" tabindex="-1">
                    <span class="more-details" data-id="<?php echo esc_attr( $ansar_demo_id ); ?>"><svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><?php esc_html_e( 'Preview', 'ansar-import' ); ?></span>
                </a>
            <?php endif; ?>
        </div>

        <div class="theme-author"><?php esc_html_e( 'By Themeansar', 'ansar-import' ); ?> </div>
        <div class="theme-id-container">
            <div class="theme-names-about">
                <h2 class="theme-name" title="<?php echo esc_attr( $ansar_raw_title ); ?>"><?php echo esc_html( $ansar_display_name ); ?></h2>
                <?php if ( ! empty( $ansar_categories ) ) : ?>
                    <ul class="theme-cate">
                        <?php foreach ( $ansar_categories as $ansar_in_cat ) : ?>
                            <?php if ( isset( $ansar_in_cat['name'] ) ) : ?>
                                <li class="theme-cate-item"><?php echo esc_html( $ansar_in_cat['name'] ); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="theme-actions">
                <?php if ( $ansar_is_pro_locked ) : ?>
                    <a class="button ani-btn-pro" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $ansar_pro_link ); ?>">
                        <svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg><?php esc_html_e( 'Buy Now', 'ansar-import' ); ?>
                    </a>
                <?php else : ?>
                    <button type="button" class="button button-primary activate btn-import btn-import-<?php echo esc_attr( $ansar_demo_id ); ?>" data-id="<?php echo esc_attr( $ansar_demo_id ); ?>"<?php if ( '' !== $ansar_template_type ) : ?> data-template-type="<?php echo esc_attr( $ansar_template_type ); ?>"<?php endif; ?><?php if ( '' !== $ansar_tname_slug ) : ?> tname="<?php echo esc_attr( $ansar_tname_slug ); ?>"<?php endif; ?>>
                        <svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg><?php esc_html_e( 'Import', 'ansar-import' ); ?>
                    </button>
                <?php endif; ?>
                <?php if ( $ansar_use_modal_preview ) : ?>
                    <a class="button load-customize hide-if-no-customize btn-preview" data-id="<?php echo esc_attr( $ansar_demo_id ); ?>" data-toggle="modal" data-target="#AnsardemoPreview" href="#"><svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><?php esc_html_e( 'Preview', 'ansar-import' ); ?></a>
                <?php else : ?>
                    <a class="button load-customize hide-if-no-customize" href="<?php echo esc_url( $ansar_preview_link ); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="ansar-btn-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg><?php esc_html_e( 'Preview', 'ansar-import' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- /product -->
</div>