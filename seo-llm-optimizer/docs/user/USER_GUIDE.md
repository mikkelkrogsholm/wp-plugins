# SEO & LLM Optimizer - User Guide

Complete guide for using the SEO & LLM Optimizer WordPress plugin.

**Version**: 1.0.0
**Last Updated**: 2025-11-07

---

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Using the Copy Button](#using-the-copy-button)
4. [Settings](#settings)
5. [Use Cases](#use-cases)
6. [Troubleshooting](#troubleshooting)
7. [FAQ](#faq)

---

## Introduction

### What is This Plugin?

The SEO & LLM Optimizer plugin transforms your WordPress content into formats optimized for Large Language Models (LLMs) and Retrieval Augmented Generation (RAG) systems. It converts your posts and pages into clean markdown with proper metadata, making it easy to:

- Copy content into AI tools like ChatGPT or Claude
- Export content for training AI systems
- Integrate WordPress content with RAG systems
- Archive content in future-proof formats
- Analyze and improve your content with AI assistance

### Why Use It?

**Traditional Problem**: WordPress content includes lots of HTML, shortcodes, and theme elements that confuse AI systems and make content hard to analyze.

**This Plugin's Solution**: Clean, semantic content in markdown format with proper metadata, ready for AI processing.

### Who Is It For?

- **Content Creators**: Get AI assistance with writing, editing, and SEO
- **Bloggers**: Easily share posts with AI tools for improvement suggestions
- **Developers**: Build AI features using your WordPress content
- **Marketers**: Analyze content strategy with AI tools
- **Anyone**: Who wants to use AI to work with their WordPress content

---

## Getting Started

### Installation

1. **Install the plugin**:
   - Download from GitHub or WordPress Plugin Directory
   - Upload to `/wp-content/plugins/`
   - Or install via WordPress Admin > Plugins > Add New

2. **Install dependencies** (if installing from GitHub):
   ```bash
   cd wp-content/plugins/seo-llm-optimizer
   composer install --no-dev
   ```

3. **Activate**:
   - Go to WordPress Admin > Plugins
   - Find "SEO & LLM Optimizer"
   - Click "Activate"

For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md).

### Initial Setup

After activation, configure the plugin:

1. Go to **WordPress Admin > Settings > LLM Optimizer**

2. **Feature Settings** (first tab):
   - ✓ Enable Frontend Button (recommended)
   - ✓ Enable for "post" and "page" post types
   - Choose button visibility (all users or logged in only)

3. **Export Options** (second tab):
   - Set default chunk size: **512 tokens** (good for most use cases)
   - Set chunk overlap: **128 tokens** (good for context)
   - Choose chunking strategy: **hierarchical** (recommended)
   - ✓ Include metadata in exports

4. **Advanced Settings** (third tab):
   - Cache duration: **3600 seconds** (1 hour is good default)
   - Rate limit: **60 requests/hour** (adjust based on needs)
   - ✓ Enable caching

5. Click **Save Changes**

### Settings Overview

The plugin adds:

- **Settings page**: WordPress Admin > Settings > LLM Optimizer
- **Frontend button**: Floating button on posts/pages (when enabled)
- **REST API**: Available at `/wp-json/slo/v1/` (when enabled)

---

## Using the Copy Button

The frontend "Copy for AI" button is the easiest way to export your content.

### Finding the Button

1. **Navigate to any published post or page** on your site
2. **Look for the button** in the bottom right corner:
   - Blue circular button with a robot icon
   - Says "Copy for AI"
   - Floats above other content

3. **Button visibility**:
   - Only appears on singular posts/pages (not archives)
   - Only on post types enabled in settings
   - Respects visibility settings (all users vs logged in)

### Opening the Modal

**Click the "Copy for AI" button** to open the export modal.

The modal has three tabs:

#### Tab 1: Quick Copy

The fastest way to copy content.

**Features**:
- **Preview**: See your content in markdown format
- **Metadata included**: YAML frontmatter with title, date, author, categories, tags
- **One-click copy**: Click "Copy Markdown" button
- **Instant feedback**: "Copied!" confirmation

**How to Use**:
1. Click "Copy for AI" button
2. Modal opens on Quick Copy tab
3. Review the preview (optional)
4. Click "Copy Markdown" button
5. Paste into your AI tool (ChatGPT, Claude, etc.)

**Example Output**:
```markdown
---
title: "How to Make Perfect Coffee"
date: "2025-01-15 10:00:00"
author: "John Doe"
url: "https://example.com/perfect-coffee/"
categories:
  - "Food & Drink"
tags:
  - "Coffee"
  - "Tutorial"
---

# How to Make Perfect Coffee

Making perfect coffee requires attention to three key factors...
```

#### Tab 2: Format Options

Customize your export before copying.

**Options**:

1. **Include Metadata**:
   - ✓ On: Includes YAML frontmatter
   - ☐ Off: Just the content in markdown

2. **Include Images**:
   - ✓ On: Preserves image markdown (`![alt](url)`)
   - ☐ Off: Removes all images

3. **Preserve Links**:
   - ✓ On: Converts links to markdown (`[text](url)`)
   - ☐ Off: Just the link text

4. **Generate Button**: Click to create custom export

**How to Use**:
1. Open modal and click "Format Options" tab
2. Toggle options as needed
3. Click "Generate Markdown"
4. Preview appears below
5. Click "Copy Markdown" to copy
6. Paste into your AI tool

**Use Cases**:
- **No metadata**: For cleaner AI input
- **No images**: For text-only analysis
- **No links**: For content that will be used elsewhere

#### Tab 3: RAG Chunks

Advanced option for RAG systems and vector databases.

**What Are Chunks?**
Chunks are smaller pieces of your content, split intelligently for AI processing. Instead of one large document, you get multiple focused segments.

**Chunking Strategies**:

1. **Hierarchical** (recommended for most content):
   - Splits by markdown headers (H1, H2, H3, etc.)
   - Preserves document structure
   - Best for: Structured articles with clear sections

2. **Fixed Size**:
   - Fixed-size chunks with overlap
   - Consistent chunk sizes
   - Best for: Long-form content, consistent processing

3. **Semantic**:
   - Splits by paragraphs
   - Keeps related content together
   - Best for: Essays, stories, flowing text

**Export Formats**:

1. **Universal**:
   - Standard JSON format
   - Works with any system
   - Includes all metadata
   - **Best for**: Custom implementations

2. **LangChain**:
   - Python LangChain library format
   - `page_content` + `metadata` structure
   - **Best for**: LangChain users

3. **LlamaIndex**:
   - LlamaIndex document format
   - `text` field with `id_`
   - **Best for**: LlamaIndex users

**How to Use**:
1. Open modal and click "RAG Chunks" tab
2. Select chunking strategy
3. Select export format
4. Click "Generate Chunks"
5. View chunks in the preview area
6. Copy individual chunks or all at once
7. Click "Copy All Chunks" for complete export

**Chunk Information**:
Each chunk shows:
- Chunk number (e.g., "Chunk 1 of 5")
- Token count
- Section title (for hierarchical)
- Content preview
- Individual copy button

**Use Cases**:
- Building RAG systems
- Vector database population
- Training data preparation
- Semantic search indexing

### Keyboard Shortcuts

- **Escape**: Close modal
- **Tab**: Navigate between elements
- **Enter**: Activate buttons
- **Arrow keys**: Navigate tabs

### Mobile Usage

The modal is fully responsive:
- Full screen on small devices
- Touch-friendly buttons
- Swipe to switch tabs (on some devices)
- All features available

---

## Settings

Access settings at **WordPress Admin > Settings > LLM Optimizer**.

### Feature Settings Tab

Control which features are active and where they appear.

#### Enable Frontend Button

**What it does**: Shows/hides the "Copy for AI" button on your site.

**Options**:
- ✓ Enabled: Button appears on posts/pages
- ☐ Disabled: Button hidden from frontend

**When to disable**:
- During site maintenance
- If you only use the REST API
- For specific user testing

#### Enabled Post Types

**What it does**: Choose which content types support LLM optimization.

**Default**: Post, Page

**Options**: Any registered post type (posts, pages, custom post types)

**How to use**:
- Check boxes for post types you want to enable
- Uncheck to disable for specific types
- Custom post types appear automatically

**Examples**:
- Enable for "Products" (WooCommerce)
- Enable for "Portfolio" (custom post type)
- Disable for "Media" (usually not needed)

#### Button Visibility

**What it does**: Control who can see the frontend button.

**Options**:
- **All Users**: Everyone sees the button (default)
- **Logged In Only**: Only logged-in users see it
- **Administrators Only**: Only admins see it

**Use cases**:
- **All Users**: Public blogs, open access
- **Logged In**: Member sites, private content
- **Administrators**: Testing, private sites

#### Enable REST API

**What it does**: Turns the REST API on/off.

**Options**:
- ✓ Enabled: API accessible at `/wp-json/slo/v1/`
- ☐ Disabled: API returns 403 Forbidden

**When to enable**:
- For programmatic access
- For external integrations
- For batch processing
- For automation

**When to disable**:
- If you only use the frontend button
- For security on private sites
- During maintenance

### Export Options Tab

Configure how content is processed and exported.

#### Default Chunk Size

**What it does**: Sets target size for content chunks.

**Range**: 128 - 2048 tokens

**Default**: 512 tokens

**Recommendations**:
- **256 tokens**: Short, focused chunks
- **512 tokens**: Good balance (recommended)
- **1024 tokens**: Longer context
- **2048 tokens**: Maximum context

**Notes**:
- 1 token ≈ 4 characters for English
- Actual chunks may be slightly larger/smaller
- Headers and sentences are never split

#### Chunk Overlap

**What it does**: Sets how much chunks overlap for context.

**Range**: 0 - 512 tokens

**Default**: 128 tokens

**Recommendations**:
- **0 tokens**: No overlap (most efficient)
- **128 tokens**: Good context preservation (recommended)
- **256 tokens**: Maximum context
- **50% of chunk size**: Rule of thumb

**Use cases**:
- **High overlap**: RAG systems, Q&A
- **Low overlap**: Training data, embeddings
- **No overlap**: Maximum efficiency

#### Default Chunking Strategy

**What it does**: Sets the default chunking method.

**Options**:
1. **Hierarchical**: Split by headers (recommended)
2. **Fixed**: Fixed size with overlap
3. **Semantic**: Paragraph-based

**When to use each**:
- **Hierarchical**: Articles, tutorials, documentation
- **Fixed**: Long-form content, novels
- **Semantic**: Essays, stories, blog posts

#### Include Metadata by Default

**What it does**: Adds YAML frontmatter to exports.

**Options**:
- ✓ On: Includes title, date, author, categories, tags
- ☐ Off: Just content

**Metadata includes**:
- Post title
- Publication date
- Author name
- Post URL
- Categories
- Tags
- Excerpt (if set)

**When to include**:
- For context in AI systems
- For RAG metadata
- For archiving
- For multi-post exports

**When to exclude**:
- For clean content analysis
- For simple AI prompts
- For training data

### Advanced Tab

Fine-tune performance and security settings.

#### Cache Duration

**What it does**: How long processed content is cached.

**Range**: 300 - 86400 seconds (5 minutes to 24 hours)

**Default**: 3600 seconds (1 hour)

**Recommendations**:
- **Short (300-900s)**: Frequently updated content
- **Medium (3600s)**: Normal blogs (recommended)
- **Long (21600-86400s)**: Static content

**Benefits of caching**:
- Faster response times
- Reduced server load
- Better user experience

**Cache clearing**:
- Automatic on post update
- Manual via "Clear Cache" button
- Via REST API

#### API Rate Limit

**What it does**: Maximum REST API requests per hour per user/IP.

**Range**: 1 - 1000 requests/hour

**Default**: 60 requests/hour

**Recommendations**:
- **Low (10-30)**: Public sites, high security
- **Medium (60)**: Normal use (recommended)
- **High (200-1000)**: Internal use, trusted users

**Rate limit tracking**:
- Per IP address (anonymous users)
- Per user ID (logged-in users)
- Resets every hour
- Returns HTTP 429 when exceeded

#### Enable Caching

**What it does**: Turns caching system on/off globally.

**Options**:
- ✓ Enabled: Caching active (recommended)
- ☐ Disabled: No caching (debug mode)

**When to disable**:
- Testing/development
- Troubleshooting issues
- Ensuring fresh data

**Impact of disabling**:
- Slower response times
- Higher server load
- Immediate updates
- Good for debugging

#### Clear Cache Button

**What it does**: Manually clears all plugin caches.

**When to use**:
- After bulk content updates
- When troubleshooting
- After changing settings
- When chunks seem outdated

**What it clears**:
- All markdown caches
- All chunk caches
- Rate limit data (optional)

---

## Use Cases

### 1. Getting AI Writing Help

**Scenario**: You want ChatGPT to improve your blog post.

**Steps**:
1. Open your published post
2. Click "Copy for AI" button
3. Click "Copy Markdown" in Quick Copy tab
4. Open ChatGPT
5. Paste and add prompt: "Please review this article and suggest improvements for clarity and SEO"
6. Review suggestions
7. Update your post

**Tips**:
- Include metadata for context
- Ask specific questions
- Try multiple AI tools for different perspectives

### 2. Content SEO Analysis

**Scenario**: Analyze your content for SEO improvements.

**Steps**:
1. Copy your post markdown (with metadata)
2. Paste into Claude or ChatGPT
3. Use prompt: "Analyze this content for SEO. Check keyword usage, readability, structure, and suggest improvements."
4. Implement suggestions
5. Re-export and verify improvements

**AI Prompts to Try**:
- "Identify the main keyword and check density"
- "Suggest better header structure for SEO"
- "Rate readability and suggest improvements"
- "Suggest internal linking opportunities"

### 3. Translation Preparation

**Scenario**: Prepare content for translation.

**Steps**:
1. Open Format Options tab
2. Disable "Include Images" (translators don't need them)
3. Enable "Include Metadata" (for context)
4. Copy markdown
5. Send to translation service or AI translator
6. Content is clean and ready for translation

### 4. Content Repurposing

**Scenario**: Turn blog post into social media content.

**Steps**:
1. Copy post in Quick Copy tab
2. Paste into AI tool
3. Use prompt: "Convert this blog post into 5 social media posts for Twitter/LinkedIn"
4. Review and edit suggestions
5. Schedule social posts

**Other repurposing ideas**:
- Email newsletter
- YouTube script
- Infographic outline
- Presentation slides

### 5. Building a RAG System

**Scenario**: Create an AI chatbot trained on your blog.

**Steps**:
1. Use REST API to export all posts (see Developer Guide)
2. Choose "LangChain" or "LlamaIndex" format
3. Use "Hierarchical" chunking strategy
4. Set chunk size to 512 tokens
5. Import chunks into vector database
6. Build chatbot using RAG framework

**See Also**:
- [REST API Documentation](REST_API_DOCUMENTATION.md)
- [Developer Guide](DEVELOPER_GUIDE.md)

### 6. Content Auditing

**Scenario**: Audit all content for quality and consistency.

**Steps**:
1. Export multiple posts using batch API
2. Feed into AI for analysis
3. Get reports on:
   - Content quality
   - Topic coverage
   - Tone consistency
   - Keyword strategy
4. Create improvement plan

---

## Troubleshooting

### Button Not Appearing

**Possible Causes**:

1. **Button disabled in settings**
   - Go to Settings > LLM Optimizer
   - Check "Enable Frontend Button" is checked
   - Save changes

2. **Post type not enabled**
   - Go to Settings > LLM Optimizer > Feature Settings
   - Verify your post type is checked in "Enabled Post Types"
   - Save changes

3. **Not on singular post/page**
   - Button only appears on single posts/pages
   - Does not appear on archives, home, category pages

4. **Visibility settings**
   - Check "Button Visibility" setting
   - If set to "Logged In Only", log in to see button
   - If set to "Administrators Only", you need admin role

5. **JavaScript error**
   - Open browser console (F12)
   - Look for JavaScript errors
   - Report errors as bug if plugin-related

6. **Theme conflict**
   - Try switching to default WordPress theme temporarily
   - If button appears, report theme compatibility issue

### Copy Not Working

**Possible Causes**:

1. **Browser doesn't support Clipboard API**
   - Use modern browser (Chrome 90+, Firefox 88+, Safari 14+)
   - Enable JavaScript
   - Check browser permissions

2. **HTTPS required**
   - Clipboard API requires HTTPS
   - Use SSL certificate for your site
   - Or test on localhost

3. **Browser extension blocking**
   - Disable extensions temporarily
   - Check privacy extensions
   - Whitelist your site

4. **JavaScript error**
   - Open browser console (F12)
   - Look for errors when clicking copy
   - Report plugin-related errors

**Workaround**:
- Manually select text in preview
- Right-click > Copy
- Or use Ctrl+C / Cmd+C

### Modal Not Opening

**Possible Causes**:

1. **JavaScript not loaded**
   - Check browser console for errors
   - Verify plugin assets are loading
   - Check for 404 errors in Network tab

2. **Conflict with other plugins**
   - Disable other plugins temporarily
   - Enable one by one to find conflict
   - Report compatibility issue

3. **Theme conflict**
   - Try default WordPress theme
   - Report theme compatibility issue

4. **Caching plugin issue**
   - Clear site cache
   - Clear browser cache
   - Disable caching temporarily

### REST API Not Working

**Possible Causes**:

1. **API disabled in settings**
   - Go to Settings > LLM Optimizer > Feature Settings
   - Check "Enable REST API"
   - Save changes

2. **WordPress REST API disabled**
   - Some hosts disable REST API
   - Check with hosting provider
   - Try accessing `/wp-json/` on your site

3. **Authentication failed**
   - Verify username and password
   - Use Application Passwords (not main password)
   - Check user has proper capabilities

4. **Rate limit exceeded**
   - Wait for rate limit to reset (1 hour)
   - Or increase rate limit in settings
   - Check response for `429` status code

5. **Permalink issues**
   - Go to Settings > Permalinks
   - Click "Save Changes" (flush rewrite rules)
   - Try API again

**Testing API**:
```bash
# Test if API is working
curl https://your-site.com/wp-json/slo/v1/health

# Should return:
# {"status":"ok","version":"1.0.0",...}
```

### Performance Issues

**Symptoms**:
- Slow response times
- Timeouts
- High server load

**Solutions**:

1. **Enable caching**
   - Go to Settings > LLM Optimizer > Advanced
   - Check "Enable Caching"
   - Set appropriate cache duration

2. **Reduce chunk size**
   - Large chunks take longer to process
   - Try 256-512 tokens instead of 1024-2048

3. **Check hosting resources**
   - Verify PHP memory limit (256MB+ recommended)
   - Check CPU usage
   - Consider upgrading hosting

4. **Optimize content**
   - Very large posts (10,000+ words) take longer
   - Consider splitting very long content

5. **Clear cache manually**
   - Old caches can cause issues
   - Use "Clear Cache" button in settings

### Cache Issues

**Symptoms**:
- Outdated content in exports
- Changes not appearing
- Stale chunks

**Solutions**:

1. **Clear plugin cache**
   - Go to Settings > LLM Optimizer > Advanced
   - Click "Clear Cache" button
   - Reload and try again

2. **Disable caching temporarily**
   - Uncheck "Enable Caching"
   - Test if issue resolves
   - Re-enable after testing

3. **Update the post**
   - Edit post and click "Update"
   - This automatically clears cache for that post

4. **Check cache duration**
   - Very long durations may cause issues
   - Try shorter duration (1-3 hours)

For more troubleshooting, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

---

## FAQ

### General Questions

**Q: What is LLM optimization?**

A: LLM (Large Language Model) optimization means formatting your content so AI systems can understand and process it effectively. This plugin converts WordPress HTML into clean markdown with metadata.

**Q: Do I need technical knowledge to use this?**

A: No! The frontend button is designed for non-technical users. Just click and copy. Technical features (REST API) are optional.

**Q: Will this change my website's appearance?**

A: No. The plugin only exports content in different formats. It doesn't change how your site looks to visitors.

**Q: Is this plugin free?**

A: Yes! The plugin is open source under GPL v2 license.

**Q: Does it work with Gutenberg?**

A: Yes! Fully supports both Gutenberg blocks and Classic Editor.

### Content Questions

**Q: What content gets exported?**

A: Post/page title, content, categories, tags, author, and date. Does not include comments, custom fields (by default), or theme elements.

**Q: Can I export custom fields?**

A: Not in the default export, but developers can use WordPress filters to add custom data.

**Q: Does it work with shortcodes?**

A: Yes. Shortcodes are processed and their output is included in the markdown.

**Q: What about embedded content (videos, tweets)?**

A: Embeds are processed and converted to appropriate markdown format. Complex embeds may have limitations.

**Q: Can I export drafts or private posts?**

A: Only published posts are exportable by default. This is for security.

### Technical Questions

**Q: What is markdown?**

A: Markdown is a simple text format that's easy for both humans and computers to read. Headers use `#`, links use `[text](url)`, etc.

**Q: What is YAML frontmatter?**

A: Metadata at the top of markdown files, between `---` markers. Includes title, date, author, etc.

**Q: What is chunking?**

A: Breaking content into smaller pieces for AI processing. Useful for vector databases and RAG systems.

**Q: What is a RAG system?**

A: RAG (Retrieval Augmented Generation) means giving AI systems access to your content so they can answer questions about it.

**Q: What are tokens?**

A: Units that AI systems use to measure text. Roughly 1 token = 4 characters in English.

### Usage Questions

**Q: Can I use this with ChatGPT?**

A: Yes! Copy markdown and paste into ChatGPT, Claude, or any AI tool.

**Q: How many posts can I export at once?**

A: Frontend: One at a time. REST API: Up to 50 posts per batch request.

**Q: Does it work on mobile?**

A: Yes! The modal is fully responsive and works on phones and tablets.

**Q: Can multiple users use this on the same site?**

A: Yes! Each user can use the button independently. Rate limits apply per user.

**Q: Is there a limit to content length?**

A: No hard limit, but very long posts (50,000+ words) may be slow to process.

### Security & Privacy Questions

**Q: Is my content secure?**

A: Yes. Content is only exported when you request it. No data is sent to external services by this plugin.

**Q: Who can access the REST API?**

A: Only authenticated users with read permissions. Rate limiting prevents abuse.

**Q: Does this track my usage?**

A: No tracking or analytics by the plugin. Your content stays on your server.

**Q: Can I limit who sees the button?**

A: Yes! Use the "Button Visibility" setting to restrict to logged-in users or administrators.

### Compatibility Questions

**Q: What WordPress version do I need?**

A: WordPress 6.4 or higher.

**Q: What PHP version?**

A: PHP 7.4 or higher. PHP 8.0+ recommended.

**Q: Does it work with multisite?**

A: Yes! Each site in a multisite network has independent settings.

**Q: Compatible with page builders?**

A: Partial. Works best with native WordPress content. Page builders (Elementor, Divi) have varying compatibility.

**Q: Works with WooCommerce?**

A: Yes! You can export product descriptions if you enable the "product" post type.

**Q: Translation ready?**

A: Yes! The plugin is fully translatable. POT file included.

### Support Questions

**Q: Where can I get help?**

A: Check this User Guide, TROUBLESHOOTING.md, or open a GitHub issue.

**Q: How do I report a bug?**

A: Open an issue on GitHub with details about the problem and steps to reproduce.

**Q: Can I request features?**

A: Yes! Open a GitHub issue with the "enhancement" label.

**Q: Is there commercial support?**

A: Currently community-supported via GitHub. Check README for updates.

---

## Need More Help?

- **Troubleshooting Guide**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Developer Guide**: [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
- **REST API Docs**: [REST_API_DOCUMENTATION.md](REST_API_DOCUMENTATION.md)
- **GitHub Issues**: [Report problems or ask questions](https://github.com/mikkelkrogsholm/wp-plugins/issues)

---

**Last Updated**: 2025-11-07
**Plugin Version**: 1.0.0
**WordPress**: 6.4+
**PHP**: 7.4+
