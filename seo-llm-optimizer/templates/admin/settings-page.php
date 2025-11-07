<?php
/**
 * Settings Page Template
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('slo_settings'); ?>

    <div class="slo-admin-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#features" class="nav-tab nav-tab-active" data-tab="features">
                <?php esc_html_e('Features', 'seo-llm-optimizer'); ?>
            </a>
            <a href="#export" class="nav-tab" data-tab="export">
                <?php esc_html_e('Export Options', 'seo-llm-optimizer'); ?>
            </a>
            <a href="#advanced" class="nav-tab" data-tab="advanced">
                <?php esc_html_e('Advanced', 'seo-llm-optimizer'); ?>
            </a>
        </nav>

        <form method="post" action="options.php">
            <?php settings_fields('slo_settings'); ?>

            <div id="tab-features" class="slo-tab-content active">
                <h2><?php esc_html_e('Feature Settings', 'seo-llm-optimizer'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Control which features are enabled and how they behave on your site.', 'seo-llm-optimizer'); ?>
                </p>
                <table class="form-table" role="presentation">
                    <?php do_settings_fields('seo-llm-optimizer', 'slo_features'); ?>
                </table>
            </div>

            <div id="tab-export" class="slo-tab-content" style="display:none;">
                <h2><?php esc_html_e('Export Options', 'seo-llm-optimizer'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Configure how content is processed and exported for LLM consumption.', 'seo-llm-optimizer'); ?>
                </p>
                <table class="form-table" role="presentation">
                    <?php do_settings_fields('seo-llm-optimizer', 'slo_export'); ?>
                </table>
            </div>

            <div id="tab-advanced" class="slo-tab-content" style="display:none;">
                <h2><?php esc_html_e('Advanced Settings', 'seo-llm-optimizer'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Configure advanced features including REST API access and caching.', 'seo-llm-optimizer'); ?>
                </p>
                <table class="form-table" role="presentation">
                    <?php do_settings_fields('seo-llm-optimizer', 'slo_advanced'); ?>
                </table>

                <h3><?php esc_html_e('Cache Management', 'seo-llm-optimizer'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Clear all cached content to force regeneration. This will temporarily slow down content exports until the cache is rebuilt.', 'seo-llm-optimizer'); ?>
                </p>
                <p>
                    <button type="button" id="slo-clear-cache" class="button">
                        <?php esc_html_e('Clear All Caches', 'seo-llm-optimizer'); ?>
                    </button>
                    <span id="slo-cache-status" class="description" style="margin-left: 10px;"></span>
                </p>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
</div>
