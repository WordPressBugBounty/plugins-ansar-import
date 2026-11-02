<?php
/**
 * Shared Import Modals for Ansar Import Plugin
 * 
 * Provides Confirmation (with Safe Guarantee), Progress, Launchpad, and In-Modal Error recovery.
 *
 * @package Ansar_Import
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ansar_import_modal_doc_url = !empty($current_doc_url) ? $current_doc_url : 'https://docs.themeansar.com/';
$ansar_import_front_page_id = get_option('page_on_front');
$ansar_import_edit_front_page_url = $ansar_import_front_page_id ? admin_url('post.php?post=' . absint($ansar_import_front_page_id) . '&action=edit') : admin_url('edit.php?post_type=page');

if ( ! function_exists( 'ansar_render_wizard_steps' ) ) {
    /**
     * Render the 4-Step Wizard Breadcrumb bar.
     *
     * @param int    $current_step Active step number (1..4).
     * @param string $extra_style  Optional inline style for the bar container.
     */
    function ansar_render_wizard_steps( $current_step = 1, $extra_style = '' ) {
        $steps = array(
            1 => __( '1. Choose Editor', 'ansar-import' ),
            2 => __( '2. Select Demo', 'ansar-import' ),
            3 => __( '3. Install & Import', 'ansar-import' ),
            4 => __( '4. Ready!', 'ansar-import' ),
        );
        ?>
        <!-- Modern 4-Step Flow Breadcrumb -->
        <div class="ansar-wizard-steps-bar"<?php if ( $extra_style ) : ?> style="<?php echo esc_attr( $extra_style ); ?>"<?php endif; ?>>
            <?php foreach ( $steps as $step_num => $step_label ) :
                $item_classes = array( 'ansar-step-item' );
                $badge_text   = (string) $step_num;

                if ( 4 === $current_step && 4 === $step_num ) {
                    $item_classes[] = 'step-completed';
                    $item_classes[] = 'step-ready';
                    $badge_text     = '🎉';
                } elseif ( $step_num < $current_step ) {
                    $item_classes[] = 'step-completed';
                    $badge_text     = '✓';
                } elseif ( $step_num === $current_step ) {
                    $item_classes[] = 'step-active';
                }
                ?>
                <div class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
                    <span class="ansar-step-number"><?php echo esc_html( $badge_text ); ?></span>
                    <span class="ansar-step-label"><?php echo esc_html( $step_label ); ?></span>
                </div>
                <?php if ( $step_num < 4 ) :
                    $connector_class = 'ansar-step-connector';
                    if ( 4 === $current_step || $step_num < $current_step - 1 ) {
                        $connector_class .= ' connector-completed';
                    } elseif ( $step_num === $current_step - 1 ) {
                        $connector_class .= ' connector-active';
                    }
                    ?>
                    <div class="<?php echo esc_attr( $connector_class ); ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
?>

<div id="ImportConfirm" class="ansar-modal" style="display: none;">
    <!-- Step 2: Select Demo & Customization Options -->
    <div class="ansar-modal-dialog ansar-import-options" id="ansar-import-options">
        <button class="ansar-modal-close-default" type="button" id="closeConfirm" aria-label="<?php esc_attr_e('Close', 'ansar-import'); ?>">&times;</button>
        
        <?php ansar_render_wizard_steps( 2 ); ?>

        <div class="ansar-modal-header">
            <h2 class="ansar-modal-title"><?php esc_html_e('Step 2: Demo Setup & Confirmation', 'ansar-import'); ?></h2>
        </div>

        <div class="ansar-modal-body">
            <div class="ansar-safe-guarantee-card">
                <div class="ansar-safe-guarantee-header">
                    <span class="ansar-shield-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </span>
                    <div>
                        <h4 class="ansar-safe-title"><?php esc_html_e('Safe Import Guarantee', 'ansar-import'); ?></h4>
                        <p class="ansar-safe-subtitle"><?php esc_html_e('Existing posts & media will not be deleted. Required free plugins will be configured automatically.', 'ansar-import'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Dynamic Required Plugins Box (Compact Chips) -->
            <div class="ansar-modal-plugins-wrapper" id="ansar-modal-plugins-wrapper">
                <div class="ansar-options-selector-title">
                    <span><?php esc_html_e('Required Plugins for This Demo:', 'ansar-import'); ?></span>
                </div>
                <div class="ansar-modal-plugins-card">
                    <div class="ansar-modal-plugins-list" id="ansar-modal-plugins-list">
                        <span class="ansar-plugin-loading-placeholder">
                            <span class="dashicons dashicons-update spinning"></span>
                            <span><?php esc_html_e('Checking plugin requirements...', 'ansar-import'); ?></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="ansar-options-selector-title">
                <span><?php esc_html_e('Customize What to Import:', 'ansar-import'); ?></span>
            </div>
            <ul class="import-option-list">
                <li class="active">
                    <input class="ansar-checkbox" type="checkbox" id="import-customizer" name="import-customizer" checked="checked">
                    <label for="import-customizer"><?php esc_html_e('Customizer Settings', 'ansar-import'); ?></label>
                </li>
                <li class="active">
                    <input class="ansar-checkbox" type="checkbox" id="import-widgets" name="import-widgets" checked="checked">
                    <label for="import-widgets"><?php esc_html_e('Widgets & Sidebars', 'ansar-import'); ?></label>
                </li>
                <li class="active">
                    <input class="ansar-checkbox" type="checkbox" id="import-content" name="import-content" checked="checked">
                    <label for="import-content"><?php esc_html_e('Sample Content & Pages', 'ansar-import'); ?></label>
                </li>
            </ul>
        </div>

        <div class="ansar-modal-footer">
            <form method="post" class="import">
                <input type="hidden" name="theme_id" id="theme_id" value="0">
                <?php wp_nonce_field('ansar_demo_import_nonce'); ?>
                <button type="button" class="ansar-button ansar-button-default" id="cancelModal"><?php esc_html_e('Cancel', 'ansar-import'); ?></button>
                <button type="button" class="ansar-button ansar-button-primary" id="import_data">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: text-bottom;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <?php esc_html_e('Confirm & Start Import', 'ansar-import'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Step 3: Progress & Plugin Installation -->
    <div class="ansar-modal-dialog ansar-importing" id="ansar-importing" style="display: none;">
        <div class="ansar-intall-importer">
            <div class="inner">
                <?php ansar_render_wizard_steps( 3 ); ?>
                <div class="heading">
                    <h4 class="title"><?php esc_html_e('Step 3: Setting Up Plugins & Demo Data', 'ansar-import'); ?></h4>
                    <p class="ansar-importing-subtext"><?php esc_html_e('Please stay on this page while we download content and configure your site.', 'ansar-import'); ?></p>
                </div>  
                <div class="ansar-import-menu" id="ansar_import_menu">
                    <div class="progress-tooltip">
                        <span class="progress-tooltip-info" style="left: 0%;">0%</span>
                        <progress class="progress" value="0" max="100">0%</progress>
                    </div>
                    <ul class="ansar-import-tabs" id="ansar_import_tabs">
                        <li class="tab_disabled dashicons" id="theme_step">
                            <a href="#"><?php esc_html_e('Required Theme Checking', 'ansar-import'); ?></a>
                        </li>
                        <li class="tab_disabled dashicons" id="plugin_step">
                            <a href="#"><?php esc_html_e('Required Plugins Setup', 'ansar-import'); ?></a>
                        </li>
                        <li class="tab_disabled dashicons" id="demo_file_step">
                            <a href="#"><?php esc_html_e('Checking Theme Data Files', 'ansar-import'); ?></a>
                        </li>
                        <li class="tab_disabled dashicons" id="demo_import_step">
                            <a href="#"><?php esc_html_e('Importing Theme Content & Styles', 'ansar-import'); ?></a>
                        </li>
                    </ul>
                    <div class="ansar-rotating-tips-wrap">
                        <div class="ansar-tip-pill">
                            <span class="ansar-tip-pill-icon">💡</span>
                            <span class="ansar-tip-pill-text"><?php esc_html_e('Quick Tip', 'ansar-import'); ?></span>
                        </div>
                        <div class="ansar-tip-rotator" id="ansar-rotating-tip">
                            <span><?php esc_html_e('You can customize colors, fonts, and headers anytime under Appearance → Customize.', 'ansar-import'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Ready! Post-Import Guided Launchpad -->
    <div class="ansar-modal-dialog ansar-import-complete" id="ansar-import-complete" style="display: none;">
        <div class="ansar-launchpad-container">
            <button class="ansar-modal-close-default" type="button" id="importDoneClose" aria-label="<?php esc_attr_e('Close', 'ansar-import'); ?>">&times;</button>
            
            <?php ansar_render_wizard_steps( 4, 'margin-bottom: 24px;' ); ?>

            <div class="ansar-launchpad-header">
                <div class="ansar-celebrate-badge">🚀</div>
                <h3 class="ansar-launchpad-title"><?php esc_html_e('Step 4: Your Website is Ready!', 'ansar-import'); ?></h3>
                <p class="ansar-launchpad-subtitle"><?php esc_html_e('Your site is now ready with demo layouts and styles. Here is what to do next:', 'ansar-import'); ?></p>
            </div>

            <div class="ansar-launchpad-grid">
                <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="ansar-launchpad-card ansar-card-primary">
                    <div class="ansar-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="ansar-card-text">
                        <span class="ansar-card-title"><?php esc_html_e('View Live Website', 'ansar-import'); ?></span>
                        <span class="ansar-card-desc"><?php esc_html_e('Preview your newly imported homepage in a new tab', 'ansar-import'); ?></span>
                    </div>
                    <span class="ansar-card-arrow" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </span>
                </a>

                <a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="ansar-launchpad-card">
                    <div class="ansar-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                    </div>
                    <div class="ansar-card-text">
                        <span class="ansar-card-title"><?php esc_html_e('Customize Colors & Logo', 'ansar-import'); ?></span>
                        <span class="ansar-card-desc"><?php esc_html_e('Upload your logo, adjust colors, and header styles', 'ansar-import'); ?></span>
                    </div>
                    <span class="ansar-card-arrow" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </a>

                <a href="<?php echo esc_url($ansar_import_edit_front_page_url); ?>" class="ansar-launchpad-card" id="ansar-edit-homepage-link">
                    <div class="ansar-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <div class="ansar-card-text">
                        <span class="ansar-card-title"><?php esc_html_e('Edit Homepage Content', 'ansar-import'); ?></span>
                        <span class="ansar-card-desc"><?php esc_html_e('Modify headlines, section blocks, and text', 'ansar-import'); ?></span>
                    </div>
                    <span class="ansar-card-arrow" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </a>

                <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="ansar-launchpad-card">
                    <div class="ansar-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </div>
                    <div class="ansar-card-text">
                        <span class="ansar-card-title"><?php esc_html_e('Manage Navigation Menus', 'ansar-import'); ?></span>
                        <span class="ansar-card-desc"><?php esc_html_e('Set up navigation links and mobile menu items', 'ansar-import'); ?></span>
                    </div>
                    <span class="ansar-card-arrow" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </a>
            </div>

            <div class="ansar-launchpad-footer">
                <a href="<?php echo esc_url($ansar_import_modal_doc_url); ?>" target="_blank" class="ansar-doc-link" id="ansar-modal-doc-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <?php esc_html_e('Documentation', 'ansar-import'); ?>
                </a>
                <div class="ansar-launchpad-actions">
                    <a href="<?php echo esc_url(admin_url()); ?>" class="ansar-button ansar-button-default"><?php esc_html_e('Back to Dashboard', 'ansar-import'); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="ansar-button ansar-button-primary"><?php esc_html_e('Visit Site', 'ansar-import'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: In-Modal Error Recovery (P1 Kill Native alert() ) -->
    <div class="ansar-modal-dialog ansar-import-error" id="ansar-import-error" style="display: none;">
        <div class="ansar-error-container">
            <button class="ansar-modal-close-default" type="button" id="importErrorClose" aria-label="<?php esc_attr_e('Close', 'ansar-import'); ?>">&times;</button>
            
            <div class="ansar-error-icon-wrap">
                <svg class="ansar-error-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>

            <h3 class="ansar-error-title"><?php esc_html_e('Import Interrupted', 'ansar-import'); ?></h3>
            <p class="ansar-error-desc" id="ansar-error-desc"><?php esc_html_e('The server took longer than expected to process the demo files or a temporary network interruption occurred.', 'ansar-import'); ?></p>
            
            <div class="ansar-error-remedies">
                <span class="ansar-remedies-title"><?php esc_html_e('Recommended Next Steps:', 'ansar-import'); ?></span>
                <ul>
                    <li><?php esc_html_e('Click "Try Again" below to resume the import process.', 'ansar-import'); ?></li>
                    <li><?php esc_html_e('Ensure your PHP max_execution_time is at least 60s.', 'ansar-import'); ?></li>
                </ul>
            </div>

            <div class="ansar-error-actions">
                <button type="button" class="ansar-button ansar-button-default" id="ansar-error-close-btn"><?php esc_html_e('Cancel', 'ansar-import'); ?></button>
                <a href="<?php echo esc_url($ansar_import_modal_doc_url); ?>" target="_blank" class="ansar-button ansar-button-secondary" id="ansar-error-doc-link"><?php esc_html_e('Help Docs', 'ansar-import'); ?></a>
                <button type="button" class="ansar-button ansar-button-primary" id="ansar-error-retry-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; vertical-align: middle;">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    <?php esc_html_e('Try Again', 'ansar-import'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Step 1 Onboarding: Editor / Page Builder Selection Modal -->
<div id="AnsarEditorOnboarding" class="ansar-modal ansar-onboarding-modal" style="display: none;">
    <div class="ansar-modal-dialog ansar-onboarding-dialog">
        <button class="ansar-modal-close-default" type="button" id="closeOnboardingModal" aria-label="<?php esc_attr_e('Close', 'ansar-import'); ?>">&times;</button>
        
        <?php ansar_render_wizard_steps( 1, 'margin-bottom: 24px;' ); ?>

        <div class="ansar-onboarding-header">
            <div class="ansar-onboarding-badge">
                <span class="ansar-badge-sparkle">✨</span>
                <span><?php esc_html_e('Step 1 of 4: Select Builder', 'ansar-import'); ?></span>
            </div>
            <h2 class="ansar-onboarding-title"><?php esc_html_e('Choose Your Preferred Page Builder', 'ansar-import'); ?></h2>
            <p class="ansar-onboarding-subtitle"><?php esc_html_e('Select your favorite editor to explore tailor-made starter templates. You can switch builders at any time.', 'ansar-import'); ?></p>
        </div>

        <div class="ansar-onboarding-cards-grid">
            <?php if ( ! empty( $has_block_demos ) || empty( $ansar_allowed_editors ) ) : ?>
            <!-- Block Editor Card -->
            <div class="ansar-builder-card" data-editor="block-editor">
                <div class="ansar-builder-card-badge-top"><?php esc_html_e('Fast & Native', 'ansar-import'); ?></div>
                <div class="ansar-builder-icon-wrap ansar-icon-wordpress">
                    <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10.5" fill="#0073aa"/>
                        <path fill="#fff" d="M3.58 12c0 3.33 1.94 6.2 4.75 7.55L4.31 8.54A8.36 8.36 0 0 0 3.58 12Zm14.1-.42c0-1.04-.37-1.76-.69-2.32-.42-.69-.82-1.27-.82-1.96 0-.77.58-1.48 1.41-1.48.04 0 .07 0 .11.01A8.38 8.38 0 0 0 5.04 7.41c.2.01.39.01.55.01.88 0 2.24-.11 2.24-.11.45-.03.5.64.05.69 0 0-.46.05-.96.08l3.06 9.11 1.84-5.52-1.31-3.59c-.45-.03-.88-.08-.88-.08-.45-.03-.4-.72.05-.69 0 0 1.39.11 2.21.11.88 0 2.24-.11 2.24-.11.45-.03.5.64.05.69 0 0-.46.05-.96.08l3.04 9.04.84-2.8c.36-1.16.58-2 .58-2.74ZM12.15 12.74l-2.52 7.33a8.43 8.43 0 0 0 5.16-.13.75.75 0 0 1-.06-.12l-2.58-7.08Zm7.23-4.81c.04.27.06.56.06.88 0 .87-.16 1.85-.65 3.08l-2.56 7.39A8.42 8.42 0 0 0 19.38 7.93Z"/>
                    </svg>
                </div>
                <h3 class="ansar-builder-card-title"><?php esc_html_e('Block Editor (Gutenberg)', 'ansar-import'); ?></h3>
                <p class="ansar-builder-card-desc"><?php esc_html_e('Clean, ultra-lightweight native blocks. Zero bloated dependencies and blazing-fast performance.', 'ansar-import'); ?></p>
                <div class="ansar-builder-card-action">
                    <button type="button" class="ansar-button ansar-button-primary ansar-btn-choose-builder" data-editor="block-editor">
                        <?php esc_html_e('Select Block Editor', 'ansar-import'); ?> &rarr;
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $has_elementor_demos ) ) : ?>
            <!-- Elementor Card -->
            <div class="ansar-builder-card" data-editor="elementor">
                <div class="ansar-builder-card-badge-top"><?php esc_html_e('Drag & Drop', 'ansar-import'); ?></div>
                <div class="ansar-builder-icon-wrap ansar-icon-elementor">
                    <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10.5" fill="#92003b"/>
                        <path fill="#fff" d="M7.25 6.9h2.2v10.2h-2.2V6.9Zm4.05 0h5.45v2.1H11.3V6.9Zm0 4.05h5.45v2.1H11.3v-2.1Zm0 4.05h5.45v2.1H11.3V15Z"/>
                    </svg>
                </div>
                <h3 class="ansar-builder-card-title"><?php esc_html_e('Elementor', 'ansar-import'); ?></h3>
                <p class="ansar-builder-card-desc"><?php esc_html_e('Visual live drag-and-drop page builder with flexible styling, responsive controls, and animations.', 'ansar-import'); ?></p>
                <div class="ansar-builder-card-action">
                    <button type="button" class="ansar-button ansar-button-primary ansar-btn-choose-builder" data-editor="elementor">
                        <?php esc_html_e('Select Elementor', 'ansar-import'); ?> &rarr;
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php
            $ansar_import_is_block_theme_now = function_exists('wp_is_block_theme') && wp_is_block_theme();
            if ( ! empty( $has_classic_demos ) ) :
            ?>
            <!-- Classic Theme Card (hidden automatically if 0 classic demos) -->
            <div class="ansar-builder-card <?php echo esc_attr( $ansar_import_is_block_theme_now ? 'ansar-builder-disabled' : '' ); ?>" data-editor="default-theme">
                <div class="ansar-builder-card-badge-top">
                    <?php
                    if ( $ansar_import_is_block_theme_now ) {
                        esc_html_e( 'Classic Only', 'ansar-import' );
                    } else {
                        esc_html_e( 'Theme Customizer', 'ansar-import' );
                    }
                    ?>
                </div>
                <div class="ansar-builder-icon-wrap ansar-icon-classic">
                    <svg viewBox="0 0 24 24" width="40" height="40" aria-hidden="true" role="img" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10.5" fill="#475569"/>
                        <path fill="#fff" d="M3 5.5C3 4.12 4.12 3 5.5 3h13C19.88 3 21 4.12 21 5.5v13c0 1.38-1.12 2.5-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 2.25h14V5.5a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v2.25Zm0 2V18.5c0 .28.22.5.5.5h4.25V9.75H5Zm6.75 0V19h6.75a.5.5 0 0 0 .5-.5V9.75h-7.25Z"/>
                    </svg>
                </div>
                <h3 class="ansar-builder-card-title"><?php esc_html_e( 'Classic Theme', 'ansar-import' ); ?></h3>
                <p class="ansar-builder-card-desc">
                    <?php
                    if ( $ansar_import_is_block_theme_now ) {
                        esc_html_e( 'Active theme is a Full-Site Block Theme. Classic editor is disabled.', 'ansar-import' );
                    } else {
                        esc_html_e( 'Traditional theme layout driven by standard WordPress Customizer options and widgets.', 'ansar-import' );
                    }
                    ?>
                </p>
                <div class="ansar-builder-card-action">
                    <button type="button" class="ansar-button ansar-button-primary ansar-btn-choose-builder" data-editor="default-theme" <?php disabled( $ansar_import_is_block_theme_now ); ?>>
                        <?php if ( $ansar_import_is_block_theme_now ) : ?>
                            <?php esc_html_e( 'Not Available', 'ansar-import' ); ?>
                        <?php else : ?>
                            <?php esc_html_e( 'Select Classic', 'ansar-import' ); ?> &rarr;
                        <?php endif; ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="ansar-onboarding-footer">
            <label class="ansar-onboarding-remember">
                <input type="checkbox" id="ansar-remember-editor-choice" checked="checked">
                <span><?php esc_html_e('Remember my preference (you can change anytime from the top switcher)', 'ansar-import'); ?></span>
            </label>
        </div>
    </div>
</div>
