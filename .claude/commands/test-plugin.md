---
name: test-plugin
description: Test a WordPress plugin using browser automation
args:
  - name: plugin-slug
    description: Plugin slug to test (e.g., seo-llm-optimizer, seo-cluster-links)
    hint: seo-llm-optimizer
---

Delegate to the wp-plugin-tester agent to comprehensively test the {{plugin-slug}} WordPress plugin.

The agent will:
1. Verify Docker environment is running
2. Activate the plugin
3. Load plugin-specific test specifications
4. Execute browser-based tests
5. Investigate any failures
6. Provide detailed test report with screenshots

Expected plugin slug values:
- seo-llm-optimizer (SEO & LLM Optimizer plugin)
- seo-cluster-links (SEO Cluster Links plugin)
- [other plugin slugs as they're added]

Please test the {{plugin-slug}} plugin thoroughly and report results.
