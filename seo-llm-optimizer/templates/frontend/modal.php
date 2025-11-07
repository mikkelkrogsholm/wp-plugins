<?php
/**
 * Frontend Modal Template
 *
 * Accessible modal with tab navigation for content export options.
 *
 * @package SEO_LLM_Optimizer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="slo-modal" class="slo-modal" role="dialog" aria-modal="true" aria-labelledby="slo-modal-title" hidden>
    <div class="slo-modal-backdrop"></div>
    <div class="slo-modal-content">
        <div class="slo-modal-header">
            <h2 id="slo-modal-title"><?php esc_html_e('Export Content for AI', 'seo-llm-optimizer'); ?></h2>
            <button type="button" class="slo-modal-close" aria-label="<?php esc_attr_e('Close', 'seo-llm-optimizer'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="slo-modal-tabs" role="tablist" aria-label="<?php esc_attr_e('Content export options', 'seo-llm-optimizer'); ?>">
            <button role="tab"
                    aria-selected="true"
                    aria-controls="panel-quick"
                    id="tab-quick"
                    class="slo-tab slo-tab-active"
                    tabindex="0">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"/>
                    <path d="M6 3a2 2 0 00-2 2v8a2 2 0 002 2h5a2 2 0 002-2V5a2 2 0 00-2-2z"/>
                </svg>
                <?php esc_html_e('Quick Copy', 'seo-llm-optimizer'); ?>
            </button>
            <button role="tab"
                    aria-selected="false"
                    aria-controls="panel-options"
                    id="tab-options"
                    class="slo-tab"
                    tabindex="-1">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM9.05 3a2.5 2.5 0 014.9 0H16v1h-2.05a2.5 2.5 0 01-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM2.05 8a2.5 2.5 0 014.9 0H16v1H6.95a2.5 2.5 0 01-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm-2.45 1a2.5 2.5 0 014.9 0H16v1h-2.05a2.5 2.5 0 01-4.9 0H0v-1h9.05z"/>
                </svg>
                <?php esc_html_e('Options', 'seo-llm-optimizer'); ?>
            </button>
            <button role="tab"
                    aria-selected="false"
                    aria-controls="panel-chunks"
                    id="tab-chunks"
                    class="slo-tab"
                    tabindex="-1">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M0 1.75A.75.75 0 01.75 1h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 010 1.75zm0 6A.75.75 0 01.75 7h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 010 7.75zm0 6A.75.75 0 01.75 13h4.5a.75.75 0 010 1.5h-4.5a.75.75 0 01-.75-.75zM8 1.75A.75.75 0 018.75 1h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 018 1.75zm0 6A.75.75 0 018.75 7h6.5a.75.75 0 010 1.5h-6.5A.75.75 0 018 7.75zm0 6a.75.75 0 01.75-.75h6.5a.75.75 0 010 1.5h-6.5a.75.75 0 01-.75-.75z"/>
                </svg>
                <?php esc_html_e('Chunks', 'seo-llm-optimizer'); ?>
            </button>
        </div>

        <div class="slo-modal-body">
            <!-- Quick Copy Panel -->
            <div role="tabpanel" id="panel-quick" aria-labelledby="tab-quick" class="slo-panel">
                <div class="slo-panel-content">
                    <p class="slo-panel-description">
                        <?php esc_html_e('Preview the first 500 characters of your optimized markdown content.', 'seo-llm-optimizer'); ?>
                    </p>
                    <div class="slo-preview-content" aria-live="polite" aria-atomic="true">
                        <!-- Content loaded via JavaScript -->
                    </div>
                    <div class="slo-panel-actions">
                        <button type="button" id="slo-quick-copy" class="slo-btn slo-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"/>
                                <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"/>
                            </svg>
                            <?php esc_html_e('Copy Full Content to Clipboard', 'seo-llm-optimizer'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Options Panel -->
            <div role="tabpanel" id="panel-options" aria-labelledby="tab-options" class="slo-panel" hidden>
                <div class="slo-panel-content">
                    <h3 class="slo-panel-subtitle"><?php esc_html_e('Export Options', 'seo-llm-optimizer'); ?></h3>
                    <p class="slo-panel-description">
                        <?php esc_html_e('Customize how your content is exported for AI processing.', 'seo-llm-optimizer'); ?>
                    </p>

                    <div class="slo-option-group">
                        <label>
                            <input type="checkbox" id="slo-toggle-metadata" checked>
                            <?php esc_html_e('Include Metadata (YAML Frontmatter)', 'seo-llm-optimizer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Adds title, date, author, categories, and tags at the top', 'seo-llm-optimizer'); ?></p>
                    </div>

                    <div class="slo-option-group">
                        <label>
                            <input type="checkbox" id="slo-toggle-images" checked>
                            <?php esc_html_e('Include Images', 'seo-llm-optimizer'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Preserves image markdown with alt text', 'seo-llm-optimizer'); ?></p>
                    </div>

                    <div class="slo-panel-actions">
                        <button type="button" id="slo-preview-full" class="slo-btn slo-btn-secondary">
                            <?php esc_html_e('Show Full Preview', 'seo-llm-optimizer'); ?>
                        </button>
                    </div>

                    <div class="slo-full-preview"></div>

                    <div class="slo-panel-actions" style="margin-top: 16px;">
                        <button type="button" id="slo-copy-options" class="slo-btn slo-btn-primary">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"/>
                                <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"/>
                            </svg>
                            <?php esc_html_e('Copy with Selected Options', 'seo-llm-optimizer'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chunks Panel -->
            <div role="tabpanel" id="panel-chunks" aria-labelledby="tab-chunks" class="slo-panel" hidden>
                <div class="slo-panel-content">
                    <h3 class="slo-panel-subtitle"><?php esc_html_e('RAG-Ready Chunks', 'seo-llm-optimizer'); ?></h3>
                    <p class="slo-panel-description">
                        <?php esc_html_e('Content automatically split into semantic chunks for Retrieval-Augmented Generation (RAG) systems.', 'seo-llm-optimizer'); ?>
                    </p>

                    <div class="slo-chunk-controls">
                        <div class="slo-control-group">
                            <label for="slo-chunk-strategy"><?php esc_html_e('Chunking Strategy:', 'seo-llm-optimizer'); ?></label>
                            <select id="slo-chunk-strategy">
                                <option value="hierarchical"><?php esc_html_e('Hierarchical (by headers)', 'seo-llm-optimizer'); ?></option>
                                <option value="fixed"><?php esc_html_e('Fixed Size', 'seo-llm-optimizer'); ?></option>
                                <option value="semantic"><?php esc_html_e('Semantic (by paragraphs)', 'seo-llm-optimizer'); ?></option>
                            </select>
                        </div>

                        <div class="slo-chunk-size-options" style="display: none;">
                            <div class="slo-control-group">
                                <label for="slo-chunk-size"><?php esc_html_e('Chunk Size (tokens):', 'seo-llm-optimizer'); ?></label>
                                <input type="number" id="slo-chunk-size" value="512" min="128" max="2048" step="128">
                            </div>

                            <div class="slo-control-group">
                                <label for="slo-chunk-overlap"><?php esc_html_e('Overlap (tokens):', 'seo-llm-optimizer'); ?></label>
                                <input type="number" id="slo-chunk-overlap" value="128" min="0" max="512" step="64">
                            </div>
                        </div>

                        <div class="slo-control-group">
                            <label for="slo-chunk-format"><?php esc_html_e('Export Format:', 'seo-llm-optimizer'); ?></label>
                            <select id="slo-chunk-format">
                                <option value="universal"><?php esc_html_e('Universal', 'seo-llm-optimizer'); ?></option>
                                <option value="langchain"><?php esc_html_e('LangChain', 'seo-llm-optimizer'); ?></option>
                                <option value="llamaindex"><?php esc_html_e('LlamaIndex', 'seo-llm-optimizer'); ?></option>
                            </select>
                        </div>

                        <div class="slo-panel-actions">
                            <button type="button" id="slo-generate-chunks" class="slo-btn slo-btn-primary">
                                <?php esc_html_e('Generate Chunks', 'seo-llm-optimizer'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="slo-chunks-preview"></div>

                    <div class="slo-panel-actions" style="margin-top: 16px;">
                        <button type="button" id="slo-copy-chunks-json" class="slo-btn slo-btn-secondary" style="display: none;">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 010 1.5h-1.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 019.25 16h-7.5A1.75 1.75 0 010 14.25v-7.5z"/>
                                <path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0114.25 11h-7.5A1.75 1.75 0 015 9.25v-7.5zm1.75-.25a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25h-7.5z"/>
                            </svg>
                            <?php esc_html_e('Copy All as JSON', 'seo-llm-optimizer'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
