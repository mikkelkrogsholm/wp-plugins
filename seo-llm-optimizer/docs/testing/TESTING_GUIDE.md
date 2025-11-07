# SEO & LLM Optimizer - Complete Testing Guide

This guide will walk you through setting up a local WordPress environment and testing all features of the SEO & LLM Optimizer plugin.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Initial WordPress Setup](#initial-wordpress-setup)
3. [Plugin Installation & Activation](#plugin-installation--activation)
4. [Testing Checklist](#testing-checklist)
5. [Feature Testing](#feature-testing)
6. [REST API Testing](#rest-api-testing)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Prerequisites

- Docker Desktop installed and running
- Terminal/Command line access
- Web browser
- Optional: Postman or curl for API testing

### 1. Start Docker Environment

```bash
# Navigate to plugin directory
cd /Users/mikkelfreltoftkrogsholm/Projekter/wp-plugins/seo-llm-optimizer

# Start Docker containers
docker-compose up -d

# Check containers are running
docker-compose ps
```

You should see 3 containers running:
- `slo-wordpress` - WordPress site (port 8080)
- `slo-mysql` - MySQL database
- `slo-phpmyadmin` - Database admin interface (port 8081)

### 2. Access WordPress

Open your browser and navigate to:
- **WordPress Site**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (optional)

### 3. Wait for WordPress Installation

The first time you start, WordPress needs to download and install. This takes 1-2 minutes. You'll know it's ready when you see the WordPress installation screen.

---

## Initial WordPress Setup

### WordPress Installation Wizard

1. **Visit**: http://localhost:8080
2. **Select Language**: Choose your preferred language (e.g., English)
3. **Click**: "Continue"

### Site Information

Fill in the following details:

```
Site Title: SEO LLM Test Site
Username: admin
Password: admin123!@# (or use the auto-generated one)
Your Email: test@example.com
Search Engine Visibility: ✓ (checked - this is just a test site)
```

4. **Click**: "Install WordPress"
5. **Click**: "Log In"
6. **Enter credentials** and log in

---

## Plugin Installation & Activation

### Method 1: Already Mounted (Recommended)

The plugin is already mounted in the Docker container via the `docker-compose.yml` file.

1. Go to **Plugins** → **Installed Plugins**
2. Find **SEO & LLM Optimizer**
3. Click **Activate**

### Method 2: Manual Upload (If Method 1 doesn't work)

If the plugin doesn't appear:

```bash
# Copy plugin files into WordPress container
docker cp . slo-wordpress:/var/www/html/wp-content/plugins/seo-llm-optimizer/

# Set correct permissions
docker exec slo-wordpress chown -R www-data:www-data /var/www/html/wp-content/plugins/seo-llm-optimizer
```

Then refresh the Plugins page in WordPress admin.

### Verify Activation

After activation, you should see:
- **Settings** → **LLM Optimizer** menu item
- No error messages

---

## Testing Checklist

Use this checklist to verify all features work correctly:

### Admin Settings
- [ ] Navigate to Settings → LLM Optimizer
- [ ] All settings sections visible (Features, Export Options, Advanced)
- [ ] All settings can be modified and saved
- [ ] "Clear Cache" button works

### Content Creation
- [ ] Create a test post with Gutenberg blocks
- [ ] Create a test post with Classic Editor
- [ ] Create a test page
- [ ] Add various content types (headings, paragraphs, lists, images)

### Frontend Button
- [ ] "Copy for AI" button appears on posts
- [ ] Button opens modal when clicked
- [ ] Modal has 3 tabs (Quick Copy, Format Options, RAG Chunks)
- [ ] Copying to clipboard works

### Export Formats
- [ ] Markdown export with metadata
- [ ] Markdown export without metadata
- [ ] LangChain format export
- [ ] LlamaIndex format export
- [ ] Universal format export

### Chunking Strategies
- [ ] Hierarchical chunking (by headings)
- [ ] Fixed-size chunking
- [ ] Semantic chunking (by paragraphs)

### REST API
- [ ] Enable REST API in settings
- [ ] Health endpoint responds
- [ ] Authentication works
- [ ] Rate limiting triggers after 60 requests
- [ ] All 8 endpoints functional

### Security & Performance
- [ ] Rate limiting prevents abuse
- [ ] Caching improves performance
- [ ] No PHP errors in debug log
- [ ] Responsive design works on mobile

---

## Feature Testing

### Test 1: Create Sample Content

#### Create a Gutenberg Post

1. Go to **Posts** → **Add New**
2. Title: "Complete Guide to AI and WordPress"
3. Add the following blocks:

```markdown
# Introduction to AI

Artificial Intelligence is transforming web development. This guide covers the basics.

## What is AI?

AI refers to systems that can perform tasks requiring human intelligence.

### Machine Learning

Machine learning is a subset of AI that learns from data.

### Deep Learning

Deep learning uses neural networks with multiple layers.

## How WordPress Uses AI

WordPress plugins can integrate AI for:
- Content optimization
- SEO improvements
- User experience enhancement

## Conclusion

AI integration in WordPress is becoming essential for modern websites.
```

4. **Click**: "Publish"
5. **Click**: "View Post"

#### Create a Classic Editor Post

1. Go to **Plugins** → **Add New**
2. Search for "Classic Editor"
3. Install and activate
4. Go to **Posts** → **Add New**
5. Switch to Classic Editor (if not default)
6. Add HTML content:

```html
<h1>Classic Editor Test</h1>
<p>This post uses the <strong>Classic Editor</strong> with HTML formatting.</p>

<h2>Features</h2>
<ul>
<li>HTML to Markdown conversion</li>
<li>Shortcode stripping</li>
<li>Theme element removal</li>
</ul>

<p>Testing <em>various</em> HTML elements and <a href="https://wordpress.org">links</a>.</p>
```

7. **Click**: "Publish"

### Test 2: Frontend Button & Modal

#### Quick Copy Tab

1. Visit the published post
2. Look for the **"Copy for AI"** button (floating button or inline)
3. Click the button
4. Modal should open showing 3 tabs
5. **Quick Copy** tab should be active
6. Content should show:
   - YAML frontmatter (title, date, author, categories)
   - Full markdown content
7. Click **"Copy to Clipboard"**
8. Verify notification appears: "Copied to clipboard!"
9. Paste into a text editor to verify content

**Expected Output**:
```markdown
---
title: "Complete Guide to AI and WordPress"
date: "2025-11-08"
author: "admin"
categories: ["Uncategorized"]
url: "http://localhost:8080/complete-guide-to-ai-and-wordpress/"
---

# Introduction to AI

Artificial Intelligence is transforming web development...
```

#### Format Options Tab

1. Click the **"Format Options"** tab
2. Try each export format:

**Standard Markdown**:
- Toggle "Include metadata" ON/OFF
- Click "Copy Standard Markdown"
- Verify metadata is included/excluded based on toggle

**LangChain Format**:
- Click "Copy LangChain Format"
- Expected JSON structure:
```json
{
  "page_content": "# Introduction to AI\n\nArtificial Intelligence...",
  "metadata": {
    "source": "http://localhost:8080/...",
    "title": "Complete Guide to AI and WordPress",
    "date": "2025-11-08",
    "author": "admin",
    "categories": ["Uncategorized"]
  }
}
```

**LlamaIndex Format**:
- Click "Copy LlamaIndex Format"
- Expected JSON structure:
```json
{
  "text": "# Introduction to AI\n\nArtificial Intelligence...",
  "metadata": {
    "source": "http://localhost:8080/...",
    "title": "Complete Guide to AI and WordPress",
    "date": "2025-11-08"
  }
}
```

**Universal Format**:
- Click "Copy Universal Format"
- Expected JSON structure with both content and metadata

#### RAG Chunks Tab

1. Click the **"RAG Chunks"** tab
2. Select chunking strategy: **Hierarchical**
3. Click **"Generate Chunks"**
4. Loading indicator should appear
5. Chunks should display in sections:

**Expected Output** (Hierarchical):
```
Chunk 1: Introduction to AI (250 tokens)
Chunk 2: What is AI? (180 tokens)
Chunk 3: Machine Learning (120 tokens)
Chunk 4: Deep Learning (110 tokens)
Chunk 5: How WordPress Uses AI (200 tokens)
Chunk 6: Conclusion (90 tokens)
```

6. Test each chunking strategy:
   - **Hierarchical**: Splits by headings
   - **Fixed Size**: Equal-sized chunks (512 tokens default)
   - **Semantic**: Splits by paragraphs

7. Test each export format:
   - **Universal**: JSON with chunks array
   - **LangChain**: Documents array format
   - **LlamaIndex**: Nodes array format

8. Click **"Copy All Chunks"**
9. Verify all chunks copied to clipboard

### Test 3: Admin Settings

#### Navigate to Settings

1. Go to **Settings** → **LLM Optimizer**
2. Verify all sections appear:
   - Feature Settings
   - Export Options
   - Advanced Settings

#### Feature Settings

**Enable Frontend Button**:
- [ ] Uncheck → Save → Visit post → Button should disappear
- [ ] Check → Save → Visit post → Button should reappear

**Enabled Post Types**:
- [ ] Uncheck "Posts" → Save → Visit post → No button
- [ ] Check "Posts" → Save → Visit post → Button appears
- [ ] Test with Pages as well

**Button Visibility**:
- [ ] Set to "Everyone" → Button visible when logged out
- [ ] Set to "Logged-in users only" → Button hidden when logged out
- [ ] Set to "Editors and above" → Test with different user roles

#### Export Options

**Include Metadata**:
- [ ] Uncheck → Export → No YAML frontmatter
- [ ] Check → Export → YAML frontmatter included

**Chunking Strategy**:
- [ ] Set to "Hierarchical" → Generate chunks → Splits by headings
- [ ] Set to "Fixed size" → Generate chunks → Equal-sized chunks
- [ ] Set to "Semantic" → Generate chunks → Paragraph-based chunks

**Chunk Size**:
- [ ] Set to 256 tokens → Generate chunks → Smaller chunks
- [ ] Set to 1024 tokens → Generate chunks → Larger chunks

**Chunk Overlap**:
- [ ] Set to 0 → Generate chunks → No overlap
- [ ] Set to 128 → Generate chunks → 128 token overlap between chunks

#### Advanced Settings

**Enable REST API**:
- [ ] Check → Save → API endpoints become accessible
- [ ] Uncheck → Save → API returns 403 Forbidden

**Rate Limit**:
- [ ] Set to 10 → Make 11 API requests quickly → 11th request blocked
- [ ] Set to 100 → Higher rate limit

**Cache Duration**:
- [ ] Set to 60 seconds → Export content → Wait 61 seconds → Re-export
- [ ] Verify first export is cached, second export regenerates

**Clear Cache Button**:
- [ ] Click "Clear Cache" button
- [ ] Verify success message: "Cache cleared successfully"
- [ ] Check browser console for AJAX response

---

## REST API Testing

### Enable REST API

1. Go to **Settings** → **LLM Optimizer**
2. Check **"Enable REST API"**
3. Click **"Save Changes"**

### Get WordPress Credentials

You need to use WordPress Application Passwords for API authentication.

1. Go to **Users** → **Profile**
2. Scroll down to **"Application Passwords"**
3. Enter name: "API Testing"
4. Click **"Add New Application Password"**
5. **Copy the generated password** (e.g., `xxxx xxxx xxxx xxxx xxxx xxxx`)

### Test 1: Health Check (No Auth Required)

```bash
curl http://localhost:8080/wp-json/slo/v1/health
```

**Expected Response**:
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "timestamp": "2025-11-08T10:30:00+00:00"
}
```

### Test 2: Get Post Markdown (Requires Auth)

First, get a post ID:
1. Go to **Posts** → **All Posts**
2. Hover over a post title
3. Note the ID in the URL (e.g., `post=123`)

```bash
# Replace 123 with your post ID
# Replace admin and password with your credentials
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  http://localhost:8080/wp-json/slo/v1/posts/123/markdown
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "title": "Complete Guide to AI and WordPress",
    "markdown": "---\ntitle: \"Complete Guide...\n",
    "metadata": {
      "title": "Complete Guide to AI and WordPress",
      "date": "2025-11-08T10:00:00+00:00",
      "author": "admin",
      "categories": ["Uncategorized"]
    }
  }
}
```

### Test 3: Get Chunks

```bash
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  "http://localhost:8080/wp-json/slo/v1/posts/123/chunks?strategy=hierarchical&format=universal"
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "strategy": "hierarchical",
    "format": "universal",
    "chunks": [
      {
        "id": "chunk_123_0",
        "content": "# Introduction to AI\n\nArtificial Intelligence...",
        "metadata": { ... },
        "index": 0,
        "tokens": 250
      },
      ...
    ]
  }
}
```

### Test 4: Batch Operations

```bash
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 124, 125], "include_metadata": true}' \
  http://localhost:8080/wp-json/slo/v1/batch/markdown
```

### Test 5: Rate Limiting

```bash
# Make 61 requests quickly (rate limit is 60/hour by default)
for i in {1..61}; do
  echo "Request $i"
  curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
    http://localhost:8080/wp-json/slo/v1/health
  sleep 0.1
done
```

Request #61 should return:
```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit exceeded. Please try again later.",
  "data": {
    "status": 429
  }
}
```

### Test 6: Cache Stats

```bash
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  http://localhost:8080/wp-json/slo/v1/cache/stats
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "total_cached_posts": 5,
    "cache_hits": 123,
    "cache_misses": 45,
    "hit_rate": "73.2%",
    "cache_size_bytes": 245760
  }
}
```

### Test 7: Clear Cache

```bash
# Clear specific post cache
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  -X DELETE \
  http://localhost:8080/wp-json/slo/v1/cache/123

# Clear all cache
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  -X DELETE \
  http://localhost:8080/wp-json/slo/v1/cache
```

---

## Advanced Testing

### Test Gutenberg Block Parsing

Create a post with various Gutenberg blocks:

1. **Paragraph Block**: Normal text
2. **Heading Block**: H2, H3, H4
3. **List Block**: Ordered and unordered
4. **Quote Block**: Blockquote
5. **Code Block**: Code snippet
6. **Image Block**: Upload an image
7. **Table Block**: Create a table
8. **Columns Block**: Multi-column layout

Export the post and verify all blocks are correctly converted to Markdown.

### Test Classic Editor HTML Conversion

Create a post with HTML:

```html
<h1>Main Heading</h1>
<p>Paragraph with <strong>bold</strong>, <em>italic</em>, and <a href="#">links</a>.</p>

<h2>Subheading</h2>
<ul>
<li>List item 1</li>
<li>List item 2</li>
</ul>

<blockquote>This is a quote</blockquote>

<pre><code>function example() {
  return "code";
}</code></pre>
```

Verify proper Markdown conversion:
```markdown
# Main Heading

Paragraph with **bold**, *italic*, and [links](#).

## Subheading

- List item 1
- List item 2

> This is a quote

```
function example() {
  return "code";
}
```
```

### Test Theme Element Stripping

The plugin should remove WordPress theme elements like:
- Navigation menus
- Sidebars
- Footers
- Comment sections

**Test**:
1. Export a post
2. Verify output contains ONLY post content
3. No navigation, sidebar, or footer content

### Test Shortcode Handling

Create a post with shortcodes:

```
[gallery ids="1,2,3"]

Regular content here.

[caption]Image caption[/caption]

More content.
```

**Expected Behavior**:
- Shortcodes are removed
- Content inside shortcodes (like captions) is preserved
- Clean markdown output

### Test Metadata Options

Create posts with various metadata:

1. **Post with Categories**: Add 3 categories
2. **Post with Tags**: Add 5 tags
3. **Post with Custom Author**: Create another user, assign as author
4. **Post with Featured Image**: Set featured image

Export each and verify metadata is correctly included.

### Test Performance & Caching

**Test Cache Hit**:
1. Export a post (cache miss - slow)
2. Export same post immediately (cache hit - fast)
3. Check browser network tab - second request should be faster

**Test Cache Invalidation**:
1. Export a post
2. Edit the post content
3. Save the post
4. Export again
5. Verify new content is returned (cache invalidated)

---

## Troubleshooting

### Plugin Not Appearing

```bash
# Check if plugin files exist in container
docker exec slo-wordpress ls -la /var/www/html/wp-content/plugins/

# Copy plugin manually if needed
docker cp . slo-wordpress:/var/www/html/wp-content/plugins/seo-llm-optimizer/

# Fix permissions
docker exec slo-wordpress chown -R www-data:www-data /var/www/html/wp-content/plugins/seo-llm-optimizer
```

### Button Not Appearing on Frontend

1. Check Settings → LLM Optimizer → "Enable Frontend Button" is checked
2. Check "Enabled Post Types" includes "Posts"
3. Check "Button Visibility" allows your user role
4. Clear browser cache (Cmd+Shift+R / Ctrl+Shift+F5)
5. Check browser console for JavaScript errors

### Modal Not Opening

1. Open browser console (F12)
2. Look for JavaScript errors
3. Check if `seoLlmData` object exists: `console.log(seoLlmData)`
4. Verify nonce is present: `console.log(seoLlmData.nonce)`

### API Returns 403 Forbidden

1. Check Settings → LLM Optimizer → "Enable REST API" is checked
2. Verify Application Password is correct
3. Test with Basic Auth header:
```bash
echo -n "admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx" | base64
# Use output in header: Authorization: Basic <base64-string>
```

### Cache Not Clearing

1. Check browser console when clicking "Clear Cache" button
2. Verify AJAX request completes successfully
3. Check WordPress debug log:
```bash
docker exec slo-wordpress tail -f /var/www/html/wp-content/debug.log
```

### Database Connection Issues

```bash
# Check if database is running
docker-compose ps

# Restart database
docker-compose restart db

# Check database logs
docker logs slo-mysql
```

### WordPress Not Loading

```bash
# Check all containers are running
docker-compose ps

# Restart all containers
docker-compose restart

# View WordPress logs
docker logs slo-wordpress

# Access WordPress shell
docker exec -it slo-wordpress bash
```

### Memory or Performance Issues

```bash
# Check container resource usage
docker stats

# Increase memory limit in docker-compose.yml
# Add under wordpress service:
deploy:
  resources:
    limits:
      memory: 1G
```

---

## Verification Checklist

Use this final checklist to confirm everything works:

### Core Functionality
- [ ] Plugin activates without errors
- [ ] Settings page loads correctly
- [ ] All settings can be saved
- [ ] Frontend button appears on posts
- [ ] Modal opens and displays content
- [ ] Markdown conversion works
- [ ] All export formats work
- [ ] All chunking strategies work
- [ ] Copying to clipboard works

### REST API
- [ ] Health endpoint responds
- [ ] Authentication works with Application Passwords
- [ ] All 8 endpoints return correct data
- [ ] Rate limiting triggers correctly
- [ ] Batch operations work
- [ ] Cache operations work

### Security
- [ ] Nonce verification prevents CSRF
- [ ] Capability checks restrict admin access
- [ ] Rate limiting prevents abuse
- [ ] SQL injection vulnerability is fixed
- [ ] XSS protection via escaping

### Performance
- [ ] First export caches result
- [ ] Second export uses cache (faster)
- [ ] Cache clears when post is updated
- [ ] Manual cache clear works
- [ ] No performance issues with large posts

### Compatibility
- [ ] Works with Gutenberg editor
- [ ] Works with Classic Editor
- [ ] Works with various post types
- [ ] Responsive on mobile devices
- [ ] Accessible with keyboard navigation

---

## Cleanup

When you're done testing:

```bash
# Stop containers
docker-compose down

# Remove containers and volumes (deletes all data)
docker-compose down -v

# Remove images (optional)
docker rmi wordpress:latest mysql:8.0 phpmyadmin:latest
```

To restart testing later:
```bash
docker-compose up -d
```

Your WordPress installation will persist unless you use `docker-compose down -v`.

---

## Next Steps

After successful testing:

1. **Deploy to Staging**: Test on a real WordPress installation
2. **User Testing**: Get feedback from actual users
3. **Performance Optimization**: Profile and optimize bottlenecks
4. **Security Audit**: Run additional security scans
5. **Documentation**: Update based on testing findings
6. **Release**: Prepare for WordPress.org submission

---

## Support

If you encounter issues:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review `TROUBLESHOOTING.md` for detailed solutions
3. Check WordPress debug log: `wp-content/debug.log`
4. Review browser console for JavaScript errors
5. Check Docker logs: `docker logs slo-wordpress`

---

**Happy Testing!**
