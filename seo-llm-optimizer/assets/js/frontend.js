/**
 * Frontend JavaScript
 *
 * Handles modal interactions and clipboard operations for SEO & LLM Optimizer.
 * Pure vanilla JavaScript implementation with no jQuery dependencies.
 *
 * @package SEO_LLM_Optimizer
 */

(function() {
    'use strict';

    /**
     * Modal Manager Class
     * Handles all modal operations including open/close, content loading, and clipboard
     */
    class SeoLlmModal {
        constructor() {
            this.modal = null;
            this.button = null;
            this.isOpen = false;
            this.markdown = null;
            this.currentChunks = null;
            this.init();
        }

        /**
         * Initialize modal manager
         */
        init() {
            this.button = document.getElementById('slo-copy-button');
            this.modal = document.getElementById('slo-modal');

            if (!this.button || !this.modal) {
                return;
            }

            this.attachEventListeners();
        }

        /**
         * Attach all event listeners
         */
        attachEventListeners() {
            // Open modal on button click
            this.button.addEventListener('click', () => this.open());

            // Close modal
            const closeBtn = this.modal.querySelector('.slo-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.close());
            }

            // Close on backdrop click
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.close();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });

            // Quick copy button
            const quickCopyBtn = this.modal.querySelector('#slo-quick-copy');
            if (quickCopyBtn) {
                quickCopyBtn.addEventListener('click', () => this.quickCopy());
            }

            // Setup tab navigation
            this.setupTabs();

            // Setup Format Options tab
            this.setupOptionsTab();

            // Setup Chunks tab
            this.setupChunksTab();
        }

        /**
         * Open modal and load content
         */
        open() {
            this.modal.removeAttribute('hidden');
            this.modal.classList.add('slo-modal-open');
            this.isOpen = true;
            this.button.setAttribute('aria-expanded', 'true');

            // Prevent body scroll
            document.body.style.overflow = 'hidden';

            // Focus first focusable element
            const firstFocusable = this.modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                // Use setTimeout to ensure modal is visible first
                setTimeout(() => firstFocusable.focus(), 100);
            }

            // Load content
            this.loadContent();
        }

        /**
         * Close modal
         */
        close() {
            this.modal.setAttribute('hidden', '');
            this.modal.classList.remove('slo-modal-open');
            this.isOpen = false;
            this.button.setAttribute('aria-expanded', 'false');

            // Restore body scroll
            document.body.style.overflow = '';

            // Return focus to button
            this.button.focus();
        }

        /**
         * Load content via AJAX
         */
        async loadContent() {
            const preview = this.modal.querySelector('.slo-preview-content');
            if (!preview) {
                return;
            }

            preview.innerHTML = '<div class="slo-loading">' + this.escapeHtml(seoLlmData.i18n.loading) + '</div>';

            try {
                const response = await fetch(seoLlmData.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'slo_get_markdown',
                        post_id: seoLlmData.postId,
                        nonce: seoLlmData.nonce,
                        include_metadata: '1'
                    })
                });

                const data = await response.json();

                if (data.success && data.data && data.data.markdown) {
                    this.markdown = data.data.markdown;

                    // Show preview (first 500 characters)
                    const previewText = this.markdown.substring(0, 500);
                    preview.innerHTML = '<pre>' + this.escapeHtml(previewText) + '...</pre>';
                } else {
                    const errorMsg = data.data && data.data.message ? data.data.message : 'Failed to load content';
                    preview.innerHTML = '<p class="slo-error">' + this.escapeHtml(errorMsg) + '</p>';
                }
            } catch (error) {
                console.error('Failed to load content:', error);
                preview.innerHTML = '<p class="slo-error">Network error. Please try again.</p>';
            }
        }

        /**
         * Quick copy to clipboard
         */
        async quickCopy() {
            if (!this.markdown) {
                this.showToast(seoLlmData.i18n.loading, 'info');
                return;
            }

            const success = await this.copyToClipboard(this.markdown);

            if (success) {
                this.showToast(seoLlmData.i18n.copySuccess, 'success');

                // Haptic feedback on mobile devices
                if ('vibrate' in navigator) {
                    navigator.vibrate(50);
                }
            } else {
                this.showToast(seoLlmData.i18n.copyError, 'error');
            }
        }

        /**
         * Copy text to clipboard
         * Uses modern Clipboard API with fallback
         *
         * @param {string} text Text to copy
         * @return {Promise<boolean>} Success status
         */
        async copyToClipboard(text) {
            // Try modern Clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    await navigator.clipboard.writeText(text);
                    return true;
                } catch (error) {
                    console.warn('Clipboard API failed, falling back to execCommand:', error);
                    return this.fallbackCopy(text);
                }
            }

            // Fallback for older browsers
            return this.fallbackCopy(text);
        }

        /**
         * Fallback copy method using execCommand
         *
         * @param {string} text Text to copy
         * @return {boolean} Success status
         */
        fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-999999px';
            textarea.style.top = '-999999px';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            try {
                const success = document.execCommand('copy');
                document.body.removeChild(textarea);
                return success;
            } catch (error) {
                console.error('Fallback copy failed:', error);
                document.body.removeChild(textarea);
                return false;
            }
        }

        /**
         * Show toast notification
         *
         * @param {string} message Message to display
         * @param {string} type Toast type (info, success, error)
         */
        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = 'slo-toast slo-toast-' + type;
            toast.textContent = message;
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');

            document.body.appendChild(toast);

            // Trigger animation after DOM insertion
            requestAnimationFrame(() => {
                toast.classList.add('slo-toast-show');
            });

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('slo-toast-show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }

        /**
         * Setup tab navigation
         */
        setupTabs() {
            const tabs = this.modal.querySelectorAll('[role="tab"]');
            const panels = this.modal.querySelectorAll('[role="tabpanel"]');

            if (tabs.length === 0) {
                return;
            }

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs and hide all panels
                    tabs.forEach(t => {
                        t.setAttribute('aria-selected', 'false');
                        t.classList.remove('slo-tab-active');
                    });
                    panels.forEach(p => {
                        p.setAttribute('hidden', '');
                    });

                    // Activate clicked tab
                    tab.setAttribute('aria-selected', 'true');
                    tab.classList.add('slo-tab-active');

                    // Show corresponding panel
                    const panelId = tab.getAttribute('aria-controls');
                    const panel = document.getElementById(panelId);
                    if (panel) {
                        panel.removeAttribute('hidden');
                    }
                });

                // Keyboard navigation for tabs
                tab.addEventListener('keydown', (e) => {
                    let newIndex = -1;

                    if (e.key === 'ArrowRight') {
                        newIndex = (index + 1) % tabs.length;
                        e.preventDefault();
                    } else if (e.key === 'ArrowLeft') {
                        newIndex = (index - 1 + tabs.length) % tabs.length;
                        e.preventDefault();
                    } else if (e.key === 'Home') {
                        newIndex = 0;
                        e.preventDefault();
                    } else if (e.key === 'End') {
                        newIndex = tabs.length - 1;
                        e.preventDefault();
                    }

                    if (newIndex !== -1) {
                        tabs[newIndex].click();
                        tabs[newIndex].focus();
                    }
                });
            });
        }

        /**
         * Setup Format Options tab
         */
        setupOptionsTab() {
            const previewButton = this.modal.querySelector('#slo-preview-full');
            const copyOptionsButton = this.modal.querySelector('#slo-copy-options');

            if (previewButton) {
                previewButton.addEventListener('click', () => this.showFullPreview());
            }

            if (copyOptionsButton) {
                copyOptionsButton.addEventListener('click', () => this.copyWithOptions());
            }
        }

        /**
         * Show full preview with selected options
         */
        async showFullPreview() {
            const preview = this.modal.querySelector('.slo-full-preview');
            if (!preview) return;

            preview.innerHTML = '<div class="slo-loading">' + this.escapeHtml(seoLlmData.i18n.loading) + '</div>';

            const metadata = this.modal.querySelector('#slo-toggle-metadata');
            const images = this.modal.querySelector('#slo-toggle-images');

            try {
                const response = await fetch(seoLlmData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'slo_get_markdown',
                        post_id: seoLlmData.postId,
                        nonce: seoLlmData.nonce,
                        include_metadata: metadata && metadata.checked ? '1' : '0',
                        include_images: images && images.checked ? '1' : '0'
                    })
                });

                const data = await response.json();

                if (data.success && data.data && data.data.markdown) {
                    preview.innerHTML = '<pre>' + this.escapeHtml(data.data.markdown) + '</pre>';
                } else {
                    const errorMsg = data.data && data.data.message ? data.data.message : 'Failed to load content';
                    preview.innerHTML = '<p class="slo-error">' + this.escapeHtml(errorMsg) + '</p>';
                }
            } catch (error) {
                console.error('Failed to load preview:', error);
                preview.innerHTML = '<p class="slo-error">Network error. Please try again.</p>';
            }
        }

        /**
         * Copy content with selected options
         */
        async copyWithOptions() {
            const metadata = this.modal.querySelector('#slo-toggle-metadata');
            const images = this.modal.querySelector('#slo-toggle-images');

            try {
                const response = await fetch(seoLlmData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'slo_get_markdown',
                        post_id: seoLlmData.postId,
                        nonce: seoLlmData.nonce,
                        include_metadata: metadata && metadata.checked ? '1' : '0',
                        include_images: images && images.checked ? '1' : '0'
                    })
                });

                const data = await response.json();

                if (data.success && data.data && data.data.markdown) {
                    const success = await this.copyToClipboard(data.data.markdown);

                    if (success) {
                        this.showToast(seoLlmData.i18n.copySuccess, 'success');
                    } else {
                        this.showToast(seoLlmData.i18n.copyError, 'error');
                    }
                } else {
                    const errorMsg = data.data && data.data.message ? data.data.message : 'Failed to load content';
                    this.showToast(errorMsg, 'error');
                }
            } catch (error) {
                console.error('Failed to copy content:', error);
                this.showToast('Network error. Please try again.', 'error');
            }
        }

        /**
         * Setup Chunks tab
         */
        setupChunksTab() {
            const strategySelect = this.modal.querySelector('#slo-chunk-strategy');
            const generateButton = this.modal.querySelector('#slo-generate-chunks');
            const copyJsonButton = this.modal.querySelector('#slo-copy-chunks-json');

            // Show/hide chunk size options based on strategy
            if (strategySelect) {
                strategySelect.addEventListener('change', (e) => {
                    const options = this.modal.querySelector('.slo-chunk-size-options');
                    if (options) {
                        options.style.display = e.target.value === 'fixed' ? 'block' : 'none';
                    }
                });
            }

            if (generateButton) {
                generateButton.addEventListener('click', () => this.generateChunks());
            }

            if (copyJsonButton) {
                copyJsonButton.addEventListener('click', () => this.copyChunksAsJson());
            }
        }

        /**
         * Generate chunks with selected options
         */
        async generateChunks() {
            const strategy = this.modal.querySelector('#slo-chunk-strategy');
            const chunkSize = this.modal.querySelector('#slo-chunk-size');
            const overlap = this.modal.querySelector('#slo-chunk-overlap');
            const format = this.modal.querySelector('#slo-chunk-format');
            const preview = this.modal.querySelector('.slo-chunks-preview');

            if (!preview) return;

            preview.innerHTML = '<div class="slo-loading">Generating chunks...</div>';

            try {
                const response = await fetch(seoLlmData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'slo_get_chunks',
                        post_id: seoLlmData.postId,
                        nonce: seoLlmData.nonce,
                        strategy: strategy ? strategy.value : 'hierarchical',
                        format: format ? format.value : 'universal',
                        chunk_size: chunkSize ? chunkSize.value : 512,
                        overlap: overlap ? overlap.value : 128
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.currentChunks = data.data;
                    this.renderChunks(data.data.chunks);

                    // Show copy JSON button
                    const copyBtn = this.modal.querySelector('#slo-copy-chunks-json');
                    if (copyBtn) {
                        copyBtn.style.display = 'inline-flex';
                    }
                } else {
                    const errorMsg = data.data && data.data.message ? data.data.message : 'Failed to generate chunks';
                    preview.innerHTML = '<p class="slo-error">' + this.escapeHtml(errorMsg) + '</p>';
                }
            } catch (error) {
                console.error('Failed to generate chunks:', error);
                preview.innerHTML = '<p class="slo-error">Network error. Please try again.</p>';
            }
        }

        /**
         * Render chunks in preview
         */
        renderChunks(chunks) {
            const preview = this.modal.querySelector('.slo-chunks-preview');
            if (!preview) return;

            let html = '<div class="slo-chunks-list">';
            html += '<p class="slo-chunks-count">Generated ' + chunks.length + ' chunks</p>';

            chunks.forEach((chunk, index) => {
                const tokens = chunk.metadata && chunk.metadata.token_count ? chunk.metadata.token_count : 0;
                const section = chunk.metadata && chunk.metadata.section_title ? chunk.metadata.section_title : '';

                html += '<div class="slo-chunk-item" data-index="' + index + '">';
                html += '<div class="slo-chunk-header">';
                html += '<span class="slo-chunk-number">Chunk ' + (index + 1) + '</span>';
                html += '<span class="slo-chunk-tokens">' + tokens + ' tokens</span>';
                if (section) {
                    html += '<span class="slo-chunk-section">' + this.escapeHtml(section) + '</span>';
                }
                html += '<button class="slo-copy-chunk slo-btn slo-btn-secondary" data-index="' + index + '">Copy</button>';
                html += '</div>';

                const contentPreview = chunk.content.substring(0, 200);
                html += '<pre class="slo-chunk-content">' + this.escapeHtml(contentPreview) + '...</pre>';
                html += '</div>';
            });

            html += '</div>';
            preview.innerHTML = html;

            // Attach copy handlers
            preview.querySelectorAll('.slo-copy-chunk').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(e.target.dataset.index);
                    this.copySingleChunk(index);
                });
            });
        }

        /**
         * Copy single chunk
         */
        async copySingleChunk(index) {
            if (!this.currentChunks || !this.currentChunks.chunks || !this.currentChunks.chunks[index]) {
                this.showToast('Chunk not found', 'error');
                return;
            }

            const chunk = this.currentChunks.chunks[index];
            const success = await this.copyToClipboard(chunk.content);

            if (success) {
                this.showToast('Chunk ' + (index + 1) + ' copied!', 'success');
            } else {
                this.showToast('Failed to copy chunk', 'error');
            }
        }

        /**
         * Copy all chunks as JSON
         */
        async copyChunksAsJson() {
            if (!this.currentChunks) {
                this.showToast('No chunks available', 'error');
                return;
            }

            const json = JSON.stringify(this.currentChunks, null, 2);
            const success = await this.copyToClipboard(json);

            if (success) {
                this.showToast('All chunks copied as JSON!', 'success');
            } else {
                this.showToast('Failed to copy chunks', 'error');
            }
        }

        /**
         * Escape HTML to prevent XSS
         *
         * @param {string} text Text to escape
         * @return {string} Escaped text
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    /**
     * Initialize when DOM is ready
     */
    function initializeModal() {
        // Check if required data is available
        if (typeof seoLlmData === 'undefined') {
            console.error('SEO LLM Optimizer: seoLlmData not found');
            return;
        }

        // Initialize modal manager
        new SeoLlmModal();
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeModal);
    } else {
        initializeModal();
    }

})();
