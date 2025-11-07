# Installation Guide

## Prerequisites

Before activating the plugin, ensure you have:

1. WordPress 6.4 or higher
2. PHP 7.4 or higher
3. Composer installed on your system

## Installation Steps

### 1. Install Dependencies

Navigate to the plugin directory and run Composer:

```bash
cd /Users/mikkelfreltoftkrogsholm/Projekter/wp-plugins/seo-llm-optimizer
composer install
```

This will install the required `league/html-to-markdown` package.

### 2. Activate the Plugin

1. Log into your WordPress admin panel
2. Navigate to Plugins > Installed Plugins
3. Find "SEO & LLM Optimizer"
4. Click "Activate"

### 3. Configure Settings

After activation:

1. Go to Settings > LLM Optimizer
2. Configure default settings:
   - Chunk Size: Default is 1000 characters (adjust based on your needs)
   - Cache Duration: Default is 3600 seconds (1 hour)
   - Frontend Button: Enable to show the optimization button on posts

### 4. Enable Per-Post Optimization

To optimize individual posts:

1. Edit any post
2. Find the "LLM Optimization Settings" meta box in the sidebar
3. Check "Enable LLM Optimization"
4. Optionally set a custom chunk size for that post
5. Save/Update the post

## Verification

To verify the plugin is working correctly:

1. Create or edit a test post
2. Enable LLM optimization in the meta box
3. View the post on the frontend
4. You should see a "Get LLM-Optimized Version" button
5. Click the button to test the modal and content processing

## Troubleshooting

### Composer Dependencies Missing

If you see errors about missing classes (like `League\HTMLToMarkdown`), run:

```bash
composer install
```

### Frontend Button Not Showing

Check:
1. Settings > LLM Optimizer - ensure "Frontend Button" is enabled
2. The post has LLM optimization enabled in its meta box
3. You're viewing a single post (not archive or home page)

### Cache Issues

To clear the plugin cache:

1. Edit and save any post with optimization enabled
2. Or deactivate and reactivate the plugin

## Troubleshooting Common Installation Issues

### Issue: Composer Dependencies Not Installing

**Symptoms**: White screen, fatal error about missing classes

**Solutions**:

1. **Verify Composer is installed**:
```bash
composer --version
```

2. **Install dependencies** in plugin directory:
```bash
cd wp-content/plugins/seo-llm-optimizer
composer install --no-dev
```

3. **Check file permissions**:
```bash
# Should be readable (644 for files, 755 for directories)
ls -la vendor/
```

4. **Manually download dependencies**:
   - If Composer not available, download from releases page
   - Look for version with vendor folder included

### Issue: Frontend Button Not Appearing

**Symptoms**: Button doesn't show on posts

**Check**:
1. Go to Settings > LLM Optimizer
2. Verify "Enable Frontend Button" is checked
3. Check "Enabled Post Types" includes "post" and "page"
4. Save changes
5. Clear browser cache
6. Visit a single post (not homepage or archive)

**Still not working?**
- Check browser console (F12) for JavaScript errors
- Temporarily switch to Twenty Twenty-Four theme to test
- Disable other plugins to check for conflicts

### Issue: "Permission Denied" Errors

**Symptoms**: 500 errors, permission denied in error log

**Fix file permissions**:
```bash
cd wp-content/plugins/seo-llm-optimizer

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;
```

### Issue: REST API Not Working

**Symptoms**: 404 errors when accessing API endpoints

**Solutions**:

1. **Flush permalinks**:
   - Go to Settings > Permalinks
   - Click "Save Changes" (no changes needed)

2. **Check REST API is enabled**:
   - Settings > LLM Optimizer > Feature Settings
   - Ensure "Enable REST API" is checked

3. **Test WordPress REST API**:
```bash
curl https://your-site.com/wp-json/

# If this fails, WordPress REST API has issues
# Check .htaccess or server configuration
```

4. **Check server configuration**:
   - Some hosts block REST API
   - Contact hosting provider
   - May need to allow REST API access

### Issue: White Screen of Death

**Symptoms**: Blank white screen after activation

**Immediate fix**:
```bash
# Deactivate plugin via database
# In phpMyAdmin or command line:
UPDATE wp_options
SET option_value = ''
WHERE option_name = 'active_plugins';
```

**Common causes**:
1. **PHP version too old**: Need PHP 7.4+
2. **Missing dependencies**: Run `composer install`
3. **Memory limit too low**: Increase to 256MB minimum
4. **Plugin conflict**: Check error logs

**Check error logs**:
```bash
# Enable debug mode in wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

# Check logs:
tail -f wp-content/debug.log
```

### Issue: Cache Not Working

**Symptoms**: Slow performance, changes not appearing

**Solutions**:

1. **Enable caching**:
   - Settings > LLM Optimizer > Advanced
   - Check "Enable Caching"

2. **Check object cache**:
   - If using Redis/Memcached, verify it's working
   - Try disabling object cache temporarily

3. **Clear all caches**:
   - Plugin cache: Settings > LLM Optimizer > Clear Cache
   - WordPress cache: Deactivate/reactivate plugin
   - Browser cache: Hard refresh (Ctrl+Shift+R)

4. **Check transients in database**:
```sql
SELECT * FROM wp_options WHERE option_name LIKE '%slo_%';
```

### Issue: Modal Not Opening

**Symptoms**: Button works but modal doesn't appear

**Solutions**:

1. **Check JavaScript errors**:
   - Press F12 to open console
   - Look for errors when clicking button
   - Report specific errors for help

2. **CSS conflict**:
   - Modal might be hidden by theme CSS
   - Add to theme's custom CSS:
```css
.slo-modal {
    z-index: 999999 !important;
    display: flex !important;
}
```

3. **Template missing**:
   - Check file exists: `templates/frontend/modal.php`
   - Verify permissions: should be 644

### Issue: Copy Button Doesn't Work

**Symptoms**: Clicking copy does nothing

**Requirements**:
- Modern browser (Chrome 90+, Firefox 88+, Safari 14+)
- HTTPS (or localhost)
- JavaScript enabled

**Test**:
```javascript
// In browser console:
if (!navigator.clipboard) {
    console.log('Clipboard API not supported');
}
```

**Workaround**:
- Manually select text in modal
- Use Ctrl+C or Cmd+C to copy

### Issue: Very Slow Performance

**Symptoms**: Takes 10+ seconds to process content

**Solutions**:

1. **Enable caching** (if not already enabled)

2. **Increase PHP memory limit**:
```php
// In wp-config.php:
define('WP_MEMORY_LIMIT', '256M');
```

3. **Increase PHP execution time**:
```php
// In .htaccess:
php_value max_execution_time 300
```

4. **For very large posts** (50,000+ words):
   - Consider splitting content
   - Use smaller chunk sizes
   - Process in batches

### Getting More Help

If you're still experiencing issues:

1. **Check full documentation**:
   - [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Comprehensive troubleshooting guide
   - [USER_GUIDE.md](USER_GUIDE.md) - Complete user guide
   - [REST_API_DOCUMENTATION.md](REST_API_DOCUMENTATION.md) - API issues

2. **Gather information**:
   - WordPress version
   - PHP version
   - Plugin version
   - Error messages
   - Steps to reproduce

3. **Report on GitHub**:
   - Open an issue with details
   - Include environment info
   - Attach error logs if available

---

## Next Steps

### For Users

- Read the [User Guide](USER_GUIDE.md) for complete instructions
- Try the frontend button on a post
- Explore the settings page
- Join the community discussions

### For Developers

- Read the [Developer Guide](DEVELOPER_GUIDE.md) for technical documentation
- Review the [REST API Documentation](REST_API_DOCUMENTATION.md)
- Check out code examples and integration patterns
- Contribute improvements via GitHub

### Resources

- **User Guide**: [USER_GUIDE.md](USER_GUIDE.md)
- **Developer Guide**: [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
- **REST API Docs**: [REST_API_DOCUMENTATION.md](REST_API_DOCUMENTATION.md)
- **Troubleshooting**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Contributing**: [CONTRIBUTING.md](CONTRIBUTING.md)
- **Security**: [SECURITY.md](SECURITY.md)

---

**Last Updated**: 2025-11-07
**Plugin Version**: 1.0.0
