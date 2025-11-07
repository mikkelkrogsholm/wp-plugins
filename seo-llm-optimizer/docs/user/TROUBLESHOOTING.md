# Troubleshooting Guide

Common issues and their solutions for the SEO & LLM Optimizer plugin.

**Plugin Version**: 1.0.0
**Last Updated**: 2025-11-07

---

## Table of Contents

1. [Frontend Button Issues](#frontend-button-issues)
2. [Modal Issues](#modal-issues)
3. [Copy Functionality Issues](#copy-functionality-issues)
4. [REST API Issues](#rest-api-issues)
5. [Performance Issues](#performance-issues)
6. [Cache Issues](#cache-issues)
7. [Content Processing Issues](#content-processing-issues)
8. [WordPress Compatibility](#wordpress-compatibility)
9. [Server Configuration](#server-configuration)
10. [Getting Additional Help](#getting-additional-help)

---

## Frontend Button Issues

### Button Not Appearing

**Symptoms**: The "Copy for AI" button doesn't show up on posts or pages.

**Possible Causes & Solutions**:

#### 1. Button Disabled in Settings

**Check**:
- Go to **WordPress Admin > Settings > LLM Optimizer**
- Look at **Feature Settings** tab
- Is "Enable Frontend Button" checked?

**Fix**:
```
✓ Check "Enable Frontend Button"
Click "Save Changes"
Refresh your post
```

#### 2. Post Type Not Enabled

**Check**:
- Same settings page, **Feature Settings** tab
- Look at "Enabled Post Types"
- Is your post type (post, page, custom) checked?

**Fix**:
```
✓ Check the post types you want to enable
Click "Save Changes"
Refresh your post
```

#### 3. Wrong Page Type

**The button only appears on**:
- Single posts (`is_singular('post')`)
- Single pages (`is_singular('page')`)
- Single custom post types (if enabled)

**The button does NOT appear on**:
- Home page
- Archive pages
- Category/tag pages
- Search results
- 404 pages

**Test**:
```
1. Go directly to a single post URL
2. Not to your blog homepage
3. Not to a category page
```

#### 4. Visibility Settings

**Check**:
- Settings > LLM Optimizer > Feature Settings
- Look at "Button Visibility" setting
- Options: "All Users", "Logged In Only", "Administrators Only"

**Fix based on setting**:
- **"Logged In Only"**: You must be logged in to WordPress
- **"Administrators Only"**: You must be an administrator
- **"All Users"**: Should be visible to everyone (check other causes)

#### 5. JavaScript Not Loading

**Check browser console** (Press F12):
```
Look for errors like:
- "seoLlmData is not defined"
- "404" errors for .js files
- JavaScript syntax errors
```

**Fix**:
1. Clear browser cache
2. Clear WordPress cache (if using caching plugin)
3. Check file permissions on `assets/js/frontend.js` (should be 644)
4. Deactivate other plugins temporarily to check for conflicts

#### 6. Theme Conflict

**Test**:
1. Temporarily switch to a default WordPress theme (Twenty Twenty-Four)
2. View a post
3. Does the button appear now?

**If yes**:
- Your theme is conflicting
- Check theme's `footer.php` has `<?php wp_footer(); ?>`
- Contact theme developer

**If no**:
- Continue troubleshooting other causes

#### 7. CSS Hiding Button

**Check with browser inspector** (F12 > Elements):
1. Search for `slo-copy-button`
2. Is it in the HTML but hidden?
3. Look at CSS styles

**Possible CSS issues**:
```css
/* These might hide it */
display: none !important;
visibility: hidden;
opacity: 0;
z-index: -1;
```

**Fix**:
Add to your theme's CSS:
```css
#slo-copy-button {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 9999 !important;
}
```

### Button Appears But Doesn't Work

**Check**:
1. Click the button
2. Open browser console (F12)
3. Look for JavaScript errors

**Common errors and fixes**:

```javascript
// Error: "seoLlmData is not defined"
// Fix: JavaScript not properly loaded
// Solution: Clear cache, check enqueue

// Error: "Failed to fetch"
// Fix: AJAX URL wrong
// Solution: Check admin-ajax.php is accessible

// Error: "Nonce verification failed"
// Fix: Security token expired
// Solution: Refresh page
```

---

## Modal Issues

### Modal Not Opening

**Symptoms**: Click button but modal doesn't appear.

**Debugging Steps**:

1. **Check Console for Errors**:
```
Press F12 > Console tab
Click button
Look for errors
```

2. **Common Issues**:

**JavaScript Error**:
```
Solution:
- Clear cache
- Check for plugin conflicts
- Update to latest version
```

**Modal Template Not Loading**:
```
Check: wp-content/plugins/seo-llm-optimizer/templates/frontend/modal.php exists
Permissions: Should be 644
```

**CSS Conflict**:
```
Modal might be rendering but hidden by CSS
Inspect element for .slo-modal
Check display, visibility, opacity properties
```

### Modal Opens But Is Blank

**Possible Causes**:

1. **Template Error**:
```
Check error logs: wp-content/debug.log
Look for PHP errors
```

2. **JavaScript Not Initializing**:
```javascript
// Test in console:
if (typeof SLOModal === 'undefined') {
    console.log('Modal JS not loaded');
}
```

3. **Content Not Loading**:
```
- Check network tab (F12)
- Look for AJAX request to admin-ajax.php
- Check response for errors
```

### Modal Styling Issues

**Symptoms**: Modal appears but looks broken.

**Fixes**:

1. **CSS Not Loading**:
```bash
# Check file exists and is readable:
ls -la wp-content/plugins/seo-llm-optimizer/assets/css/frontend.css

# Check in browser:
View Page Source
Search for "frontend.css"
Click the link to verify it loads
```

2. **Theme CSS Conflict**:
```css
/* Add to theme CSS to fix */
.slo-modal {
    all: initial;
    /* Then redefine plugin styles */
}
```

3. **Mobile Display Issues**:
```css
/* Ensure modal is responsive */
@media (max-width: 768px) {
    .slo-modal-content {
        width: 95% !important;
        margin: 20px auto !important;
    }
}
```

---

## Copy Functionality Issues

### "Copy" Button Doesn't Work

**Symptoms**: Click "Copy Markdown" but nothing happens.

**Common Causes**:

#### 1. Browser Doesn't Support Clipboard API

**Check**:
```javascript
// Test in console:
if (!navigator.clipboard) {
    console.log('Clipboard API not supported');
}
```

**Fix**:
- Use a modern browser:
  - Chrome 90+
  - Firefox 88+
  - Safari 14+
  - Edge 90+

#### 2. HTTPS Required

**The Clipboard API requires HTTPS** (or localhost).

**Test**:
```
Check your URL:
https://your-site.com ✓ Works
http://your-site.com ✗ Won't work
http://localhost ✓ Works (exception)
```

**Fix**:
- Install SSL certificate
- Use Let's Encrypt (free)
- Or use manual copy (see workaround below)

#### 3. Browser Permissions

Some browsers require permission to access clipboard.

**Fix**:
1. Check browser address bar for permission requests
2. Click and allow clipboard access
3. Try copying again

#### 4. JavaScript Error

**Check console**:
```
Press F12 > Console
Click Copy button
Look for errors
```

**Common error**:
```javascript
// "Failed to copy: undefined"
// Usually means content not generated yet
// Solution: Click "Generate" button first in Format Options/RAG Chunks tabs
```

### Workaround: Manual Copy

If copy button doesn't work:

1. Open modal
2. Click in the preview area
3. **Select all**: Ctrl+A (Windows) or Cmd+A (Mac)
4. **Copy**: Ctrl+C (Windows) or Cmd+C (Mac)
5. Paste into your tool

### Copy Success But Paste Shows Nothing

**Possible causes**:

1. **Clipboard cleared**: Something else copied after
2. **Different format**: Try pasting in plain text editor first
3. **Character encoding**: Some special characters might not paste

**Test**:
```
1. Copy from plugin
2. Immediately paste in Notepad (Windows) or TextEdit (Mac)
3. Does content appear?
   - Yes: Issue with destination application
   - No: Issue with copy process
```

---

## REST API Issues

### API Returns 404

**Symptoms**: API endpoint returns "Not Found".

**Debugging**:

1. **Test WordPress REST API** First:
```bash
curl https://your-site.com/wp-json/

# Should return JSON with routes
# If this fails, WordPress REST API is disabled/broken
```

2. **Check Plugin API Specifically**:
```bash
curl https://your-site.com/wp-json/slo/v1/health

# Should return:
# {"status":"ok","version":"1.0.0",...}
```

**Fixes**:

#### Permalinks Not Flushed

```
WordPress Admin > Settings > Permalinks
Click "Save Changes" (even without changing anything)
Try API again
```

#### REST API Disabled Globally

Some hosts or plugins disable REST API.

**Check**: `.htaccess` file for:
```apache
# Remove/comment these lines:
<Files "xmlrpc.php">
Order Allow,Deny
Deny from all
</Files>
```

**Or check**: `wp-config.php` for:
```php
// Remove this line:
add_filter('rest_authentication_errors', '__return_true');
```

#### Plugin API Disabled in Settings

```
Settings > LLM Optimizer > Feature Settings
✓ Check "Enable REST API"
Save Changes
```

### API Returns 403 Forbidden

**Symptoms**: API says "rest_forbidden" or "rest_disabled".

**Causes & Fixes**:

#### 1. API Disabled in Settings

```
Settings > LLM Optimizer > Feature Settings
✓ Enable "Enable REST API"
Save Changes
```

#### 2. Authentication Failed

```bash
# Test with authentication:
curl -u "username:password" https://site.com/wp-json/slo/v1/posts/1/markdown

# Common issues:
# - Wrong username/password
# - Using main password instead of Application Password
# - User doesn't have required capabilities
```

**Fix - Create Application Password**:
```
1. WordPress Admin > Users > Profile
2. Scroll to "Application Passwords"
3. Enter name: "API Access"
4. Click "Add New Application Password"
5. Copy the generated password
6. Use this password (not your normal password)
```

#### 3. User Lacks Permissions

```php
// User needs 'read' capability minimum
// For cache endpoints, needs 'manage_options'
```

**Test different endpoints**:
```bash
# Public endpoint (should work without auth):
curl https://site.com/wp-json/slo/v1/health

# Read endpoint (needs auth):
curl -u "user:pass" https://site.com/wp-json/slo/v1/posts/1/markdown

# Admin endpoint (needs admin):
curl -u "admin:pass" https://site.com/wp-json/slo/v1/cache/stats
```

### API Returns 429 Rate Limit Exceeded

**Symptoms**: "Rate limit exceeded. Try again later."

**Cause**: Too many requests in the time window.

**Immediate Fix**:
```
Wait 1 hour for limit to reset
```

**Long-term Fixes**:

1. **Increase Rate Limit**:
```
Settings > LLM Optimizer > Advanced
Change "API Rate Limit" to higher number
Save Changes
```

2. **Clear Rate Limit Data**:
```
Settings > LLM Optimizer > Advanced
Click "Clear Cache" button
(This clears rate limit counters)
```

3. **Optimize Your Code**:
```python
# Bad: Individual requests
for post_id in post_ids:
    get_markdown(post_id)  # 100 requests for 100 posts

# Good: Batch request
batch_get_markdown(post_ids)  # 1 request for 100 posts
```

### API Returns Empty or Incorrect Data

**Debugging**:

1. **Check Post Exists and Is Published**:
```bash
# Get post via WordPress API first:
curl https://site.com/wp-json/wp/v2/posts/123

# If this fails, post doesn't exist or isn't published
```

2. **Check Response Format**:
```bash
# Add -v for verbose output:
curl -v -u "user:pass" https://site.com/wp-json/slo/v1/posts/123/markdown

# Check:
# - HTTP status code (should be 200)
# - Content-Type header (should be application/json)
# - Response body
```

3. **Test with Simple Post**:
```
Create a new post with just:
Title: "Test Post"
Content: "This is a test."

Try API with this post
Does it work?
```

---

## Performance Issues

### Slow Response Times

**Symptoms**: Requests take 10+ seconds.

**Common Causes**:

#### 1. Cache Disabled

**Check**:
```
Settings > LLM Optimizer > Advanced
Is "Enable Caching" checked?
```

**Performance impact**:
```
Without cache: 100-500ms per request
With cache: 1-10ms per request
Speedup: 50-100x faster
```

#### 2. Very Large Posts

**Check post word count**:
```
Posts > 10,000 words may be slow
Posts > 50,000 words may timeout
```

**Fixes**:
```
1. Increase PHP max_execution_time:
   php.ini: max_execution_time = 300

2. Increase PHP memory_limit:
   php.ini: memory_limit = 256M

3. Split very large posts into series

4. Use chunking with smaller chunk sizes
```

#### 3. Server Resources

**Check**:
```bash
# Check PHP memory usage:
# Add to wp-config.php:
define('WP_MEMORY_LIMIT', '256M');

# Check error logs:
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

#### 4. Too Many Plugins

**Test**:
```
1. Deactivate all other plugins
2. Test performance
3. Reactivate plugins one by one
4. Identify conflict
```

### Timeouts

**Symptoms**: Request times out, returns 504 or 500 error.

**Fixes**:

1. **Increase PHP Timeout**:
```php
// In .htaccess:
php_value max_execution_time 300

// Or wp-config.php:
set_time_limit(300);
```

2. **Increase Server Timeout**:
```nginx
# Nginx:
fastcgi_read_timeout 300;
proxy_read_timeout 300;
```

```apache
# Apache:
TimeOut 300
```

3. **Use Batch Processing**:
```
Instead of processing 100 posts at once,
process 10 at a time with delays
```

### High Memory Usage

**Symptoms**: "Out of memory" errors.

**Check current limit**:
```php
<?php
echo 'Memory limit: ' . ini_get('memory_limit');
echo 'Peak usage: ' . memory_get_peak_usage(true) / 1024 / 1024 . ' MB';
?>
```

**Increase memory limit**:
```php
// wp-config.php:
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

---

## Cache Issues

### Content Not Updating

**Symptoms**: Changes to post don't appear in exports.

**Cause**: Old cached version being served.

**Immediate Fix**:
```
1. Edit the post
2. Click "Update" (even without changes)
3. This clears cache for that post
4. Try export again
```

**Manual Cache Clear**:
```
Settings > LLM Optimizer > Advanced
Click "Clear Cache" button
```

**Disable Caching** (for testing):
```
Settings > LLM Optimizer > Advanced
☐ Uncheck "Enable Caching"
Save Changes
Test
Re-enable caching after testing
```

### Cache Not Working

**Symptoms**: Slow performance even with caching enabled.

**Check**:

1. **Is caching enabled?**
```
Settings > LLM Optimizer > Advanced
✓ "Enable Caching" should be checked
```

2. **Test cache effectiveness**:
```php
// First request (no cache):
$start = microtime(true);
$result = process_content($post_id);
$time1 = microtime(true) - $start;

// Second request (should be cached):
$start = microtime(true);
$result = process_content($post_id);
$time2 = microtime(true) - $start;

echo "First: {$time1}s\n";
echo "Second: {$time2}s\n";
echo "Speedup: " . ($time1 / $time2) . "x\n";

// Should see 50-100x speedup
```

3. **Check transients**:
```sql
# In database:
SELECT * FROM wp_options WHERE option_name LIKE '%slo_%';

# Should see transient entries
```

**Fix if not working**:
```
1. Object caching conflict (Redis, Memcached)
   - Check object-cache.php
   - Try disabling object cache temporarily

2. Database issues
   - Check wp_options table
   - Optimize database

3. Permissions
   - Check WordPress can write to database
```

---

## Content Processing Issues

### Markdown Looks Wrong

**Symptoms**: Converted markdown doesn't look right.

**Common Issues**:

#### 1. Headers Not Converting

**Check source HTML**:
```html
<!-- This converts to markdown: -->
<h1>Header</h1>  → # Header

<!-- This might not: -->
<div class="h1">Header</div>  → Plain text
```

**Fix**: Use proper HTML heading tags in your posts.

#### 2. Lists Not Converting

```html
<!-- Proper list (converts): -->
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
</ul>

<!-- Pseudo-list (doesn't convert): -->
<p>• Item 1</p>
<p>• Item 2</p>
```

#### 3. Images Missing

**Check**:
```
Settings > LLM Optimizer > Export Options
or
Modal > Format Options tab
✓ "Include Images" should be checked
```

**Image format in markdown**:
```markdown
![Alt text](https://site.com/image.jpg)
```

#### 4. Links Not Converting

**Check**:
```
Format Options > "Preserve Links" should be enabled
```

**Link format in markdown**:
```markdown
[Link text](https://example.com)
```

### Shortcodes Not Processing

**Expected behavior**:
- Shortcodes ARE processed
- Their output is included in markdown

**If not working**:

1. **Shortcode plugin not active**:
```
Shortcode might be from deactivated plugin
Activate the plugin providing the shortcode
```

2. **Shortcode errors**:
```
Check error logs for shortcode errors
Fix shortcode issues
```

3. **Shortcode returns empty**:
```
This is normal for some shortcodes
They might only work in specific contexts
```

### Gutenberg Blocks Missing

**Symptoms**: Gutenberg blocks don't appear in output.

**Common causes**:

1. **Block doesn't render HTML**:
```
Some blocks (like spacers) don't produce content
This is normal
```

2. **Custom block not registered**:
```
Custom block plugin might be deactivated
Activate required plugins
```

3. **Block contains only shortcode**:
```
Block wraps a shortcode
Shortcode should still process
Check shortcode issues above
```

### Metadata Missing or Incorrect

**Check post has metadata**:
```
- Title
- Date
- Author
- Categories/tags (if set)
```

**If metadata is empty**:
```
This is expected if:
- Post has no categories/tags
- Custom fields not set
```

**To add custom metadata**:
```php
// Use filter:
add_filter('slo_frontmatter_data', function($data, $post_id) {
    $data['custom_field'] = get_post_meta($post_id, 'my_field', true);
    return $data;
}, 10, 2);
```

---

## WordPress Compatibility

### Multisite Issues

**General notes**:
- Each site has independent settings
- Network-wide activation is supported
- Cache is per-site

**Common issues**:

1. **Button not showing on subsites**:
```
Go to each subsite's dashboard
Check Settings > LLM Optimizer
Configure individually
```

2. **API not working on subsites**:
```
Check subsite's permalink structure
Flush permalinks on each subsite
```

### Theme Compatibility

**Testing theme compatibility**:
```
1. Switch to Twenty Twenty-Four theme
2. Test all features
3. If working:
   - Theme conflict exists
   - Report to theme developer
4. If not working:
   - Not a theme issue
   - Continue troubleshooting
```

**Common theme issues**:

1. **Missing `wp_footer()` hook**:
```php
// Theme's footer.php must have:
<?php wp_footer(); ?>

// Add if missing
```

2. **CSS conflicts**:
```css
/* Theme might hide button */
/* Add to theme CSS: */
#slo-copy-button {
    display: block !important;
}
```

3. **JavaScript conflicts**:
```javascript
// Theme might not use jQuery properly
// Check console for "$" is not defined
// Usually theme issue
```

### Plugin Conflicts

**Testing for conflicts**:
```
1. Deactivate ALL other plugins
2. Test SEO & LLM Optimizer
3. Does it work now?
   - Yes: Plugin conflict exists
   - No: Not a plugin conflict

4. Reactivate plugins one by one
5. Test after each activation
6. Identify conflicting plugin
7. Report conflict
```

**Known compatible plugins**:
- Yoast SEO ✓
- Rank Math ✓
- WooCommerce ✓
- Jetpack ✓
- Contact Form 7 ✓
- Elementor ✓ (with limitations)

**Potential conflicts**:
- Security plugins that disable REST API
- Caching plugins (configure to exclude plugin)
- JavaScript minification (exclude plugin JS)

---

## Server Configuration

### PHP Version Issues

**Minimum: PHP 7.4**
**Recommended: PHP 8.0+**

**Check your PHP version**:
```php
<?php
echo PHP_VERSION;
?>
```

**Or in WordPress**:
```
Tools > Site Health > Info > Server
```

**If < 7.4**:
```
Contact your hosting provider
Request PHP upgrade
Or switch to modern hosting
```

### Memory Limit Too Low

**Symptoms**: "Out of memory" errors.

**Check current limit**:
```
Tools > Site Health > Info > Server > PHP memory limit
```

**Recommended**: 256MB minimum

**Increase limit**:
```php
// wp-config.php:
define('WP_MEMORY_LIMIT', '256M');
```

### Missing PHP Extensions

**Required extension**: None beyond WordPress requirements

**Used if available**:
- mbstring (for multi-byte strings)
- iconv (for character encoding)

**Check installed extensions**:
```php
<?php
print_r(get_loaded_extensions());
?>
```

### File Permission Issues

**Correct permissions**:
```bash
# Directories:
find . -type d -exec chmod 755 {} \;

# Files:
find . -type f -exec chmod 644 {} \;

# Special files:
chmod 600 wp-config.php
```

**Check permissions**:
```bash
ls -la wp-content/plugins/seo-llm-optimizer/
```

**Should see**:
```
drwxr-xr-x  folders (755)
-rw-r--r--  files (644)
```

---

## Getting Additional Help

### Before Asking for Help

Please have this information ready:

**Environment**:
- WordPress version
- Plugin version
- PHP version
- Server type (Apache/Nginx)
- Hosting provider

**Problem details**:
- Clear description of the issue
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable
- Error messages (exact text)

**What you've tried**:
- List troubleshooting steps already taken
- Results of each step

### Where to Get Help

1. **Documentation**:
   - [USER_GUIDE.md](USER_GUIDE.md) - Complete user guide
   - [README.md](README.md) - Overview and quick start
   - [REST_API_DOCUMENTATION.md](REST_API_DOCUMENTATION.md) - API reference

2. **GitHub Issues**:
   - Search existing issues first
   - Create new issue if needed
   - Use issue templates
   - Provide all requested information

3. **Community Support**:
   - GitHub Discussions
   - WordPress.org support forums (coming soon)

### Reporting Bugs

**Good bug report includes**:

```markdown
**Description**
Clear, concise description

**Steps to Reproduce**
1. Go to...
2. Click on...
3. See error

**Expected Behavior**
What should happen

**Actual Behavior**
What actually happens

**Screenshots**
[Attach screenshots]

**Environment**
- WordPress: 6.4.2
- Plugin: 1.0.0
- PHP: 8.1
- Theme: Twenty Twenty-Four
- Browser: Chrome 120

**Console Errors**
```
Paste any JavaScript console errors
```

**PHP Errors**
```
Paste any PHP errors from debug.log
```

**Additional Context**
Any other relevant information
```

### Enable Debug Mode

**To get detailed error messages**:

```php
// Add to wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

**Check logs**:
```
wp-content/debug.log
```

**Important**: Disable debug mode on production sites after troubleshooting!

---

## Quick Diagnostic Checklist

Run through this checklist for quick diagnosis:

**Basic Checks**:
- [ ] Plugin activated?
- [ ] WordPress 6.4+?
- [ ] PHP 7.4+?
- [ ] Settings saved?
- [ ] Cache cleared?
- [ ] Permalinks flushed?

**Button Issues**:
- [ ] Enabled in settings?
- [ ] On single post/page?
- [ ] Post type enabled?
- [ ] JavaScript loading?
- [ ] Console errors?

**API Issues**:
- [ ] API enabled in settings?
- [ ] WordPress REST API working?
- [ ] Authentication correct?
- [ ] Rate limit not exceeded?
- [ ] Permalinks working?

**Performance Issues**:
- [ ] Caching enabled?
- [ ] PHP memory sufficient?
- [ ] Server resources OK?
- [ ] No timeouts?
- [ ] Other plugins deactivated?

**Content Issues**:
- [ ] Post published?
- [ ] Content not empty?
- [ ] Proper HTML structure?
- [ ] Shortcodes working?
- [ ] No PHP errors?

---

**Still Having Issues?**

Open an issue on GitHub with:
- Completed checklist above
- Environment details
- Exact error messages
- Steps you've tried

We're here to help!

---

**Last Updated**: 2025-11-07
**Plugin Version**: 1.0.0
**Maintainer**: Mikkel Krogsholm
