# WordPress Plugins - Testing Guide

Complete guide for testing all WordPress plugins in this project using Docker.

## Quick Start

```bash
# 1. Start WordPress environment
./test.sh start

# 2. Install WordPress
./test.sh install-wp

# 3. Activate a plugin
./test.sh activate seo-llm-optimizer

# 4. Open browser
open http://localhost:8080
```

---

## Table of Contents

1. [Setup](#setup)
2. [Available Plugins](#available-plugins)
3. [Testing Commands](#testing-commands)
4. [WordPress Management](#wordpress-management)
5. [Plugin Testing Workflow](#plugin-testing-workflow)
6. [Troubleshooting](#troubleshooting)
7. [Advanced Usage](#advanced-usage)

---

## Setup

### Prerequisites

- **Docker Desktop** installed and running
- Terminal/Command line access
- Web browser

### First Time Setup

```bash
# Start Docker environment
./test.sh start

# Install WordPress (interactive)
./test.sh install-wp
```

You'll be asked for:
- Site Title (default: "WordPress Plugins Test")
- Admin Username (default: "admin")
- Admin Password (default: "admin")
- Admin Email (default: "test@example.com")

### Access URLs

- **WordPress Site**: http://localhost:8080
- **WordPress Admin**: http://localhost:8080/wp-admin
- **phpMyAdmin**: http://localhost:8081 (username: `wordpress`, password: `wordpress`)

---

## Available Plugins

All plugins in this project are automatically mounted in the WordPress container:

```bash
# List available plugins
./test.sh plugins
```

Current plugins:
- `seo-llm-optimizer` - SEO & LLM Optimizer
- `seo-cluster-links` - SEO Cluster Links
- *(Add more as you create them)*

---

## Testing Commands

### Environment Management

```bash
# Start WordPress + MySQL + phpMyAdmin
./test.sh start

# Check status of all containers
./test.sh status

# Stop containers (preserves data)
./test.sh stop

# Reset everything (deletes all data)
./test.sh reset
```

### Plugin Management

```bash
# Activate a plugin
./test.sh activate seo-llm-optimizer

# Deactivate a plugin
./test.sh deactivate seo-llm-optimizer

# List all plugins
./test.sh plugins
```

### Debugging

```bash
# View WordPress logs
./test.sh logs

# View MySQL logs
./test.sh logs db

# View debug.log in real-time
./test.sh debug

# Open WordPress container shell
./test.sh shell

# Open MySQL shell
./test.sh shell db

# Open WP-CLI shell
./test.sh shell cli
```

### WP-CLI Commands

Run any WordPress CLI command:

```bash
# List all plugins
./test.sh wp plugin list

# Create a test post
./test.sh wp post create --post_title='Test Post' --post_status=publish

# List all users
./test.sh wp user list

# Update permalink structure
./test.sh wp rewrite structure '/%postname%/'

# Flush rewrite rules
./test.sh wp rewrite flush

# Check WordPress version
./test.sh wp core version
```

### Backup & Restore

```bash
# Create backup
./test.sh backup

# Restore from backup
./test.sh restore backups/wordpress_backup_20251108_123456.sql
```

---

## WordPress Management

### Manual WordPress Installation (Browser)

If you prefer the visual installer:

1. Start environment: `./test.sh start`
2. Visit: http://localhost:8080
3. Follow the WordPress installation wizard
4. Login credentials you choose during setup

### Using WP-CLI (Faster)

```bash
# Automated installation
./test.sh install-wp

# Or fully automated with defaults
./test.sh wp core install \
  --url="http://localhost:8080" \
  --title="Test Site" \
  --admin_user="admin" \
  --admin_password="admin" \
  --admin_email="test@example.com"
```

---

## Plugin Testing Workflow

### Testing SEO & LLM Optimizer

```bash
# 1. Start environment
./test.sh start

# 2. Install WordPress (if not already)
./test.sh install-wp

# 3. Activate plugin
./test.sh activate seo-llm-optimizer

# 4. Create test post
./test.sh wp post create \
  --post_title='AI and Machine Learning Guide' \
  --post_content='<h1>Introduction to AI</h1><p>Artificial Intelligence...</p>' \
  --post_status=publish

# 5. Visit site
open http://localhost:8080

# 6. Test features
# - Click "Copy for AI" button on post
# - Test modal with 3 tabs
# - Test export formats
# - Test chunking strategies
```

### Testing SEO Cluster Links

```bash
# 1. Activate plugin
./test.sh activate seo-cluster-links

# 2. Go to admin
open http://localhost:8080/wp-admin

# 3. Configure settings
# Settings → SEO Cluster Links

# 4. Create multiple related posts
./test.sh wp post create --post_title='Post 1' --post_content='Content with [[related-term]]' --post_status=publish
./test.sh wp post create --post_title='Post 2' --post_content='More about [[related-term]]' --post_status=publish

# 5. Verify cluster links appear
```

### Testing a New Plugin

When creating a new plugin:

```bash
# 1. Create plugin directory
mkdir my-new-plugin
cd my-new-plugin

# 2. Create main plugin file
# my-new-plugin.php with WordPress headers

# 3. Update docker-compose.yml to mount it
# Add to volumes:
#   - ./my-new-plugin:/var/www/html/wp-content/plugins/my-new-plugin

# 4. Restart Docker
./test.sh stop
./test.sh start

# 5. Activate plugin
./test.sh activate my-new-plugin

# 6. Check for errors
./test.sh debug
```

---

## Troubleshooting

### Plugin Not Appearing

```bash
# Check if mounted correctly
./test.sh shell
ls -la /var/www/html/wp-content/plugins/

# Fix permissions
./test.sh fix-permissions

# Restart containers
./test.sh stop
./test.sh start
```

### WordPress Not Loading

```bash
# Check container status
./test.sh status

# View logs for errors
./test.sh logs

# If database connection fails
./test.sh stop
./test.sh start
```

### Permission Issues

```bash
# Fix all plugin permissions
./test.sh fix-permissions

# Or manually for specific plugin
docker exec wp-plugins-wordpress chown -R www-data:www-data /var/www/html/wp-content/plugins/your-plugin
```

### Database Issues

```bash
# Access MySQL directly
./test.sh shell db
mysql -u wordpress -pwordpress wordpress

# View all tables
SHOW TABLES;

# Or use phpMyAdmin
open http://localhost:8081
```

### Debug Mode Not Showing Errors

Debug is enabled by default, but if you need to verify:

```bash
# Check WordPress config
./test.sh shell
cat /var/www/html/wp-config.php | grep WP_DEBUG

# View debug log
./test.sh debug
```

### Port Already in Use

If port 8080 or 8081 is already in use:

```bash
# Edit docker-compose.yml
# Change ports:
#   - "8082:80"  # WordPress on 8082
#   - "8083:80"  # phpMyAdmin on 8083
```

---

## Advanced Usage

### Adding a New Plugin to Docker

Edit `docker-compose.yml`:

```yaml
volumes:
  # Add your new plugin
  - ./your-plugin-name:/var/www/html/wp-content/plugins/your-plugin-name
```

Then restart:
```bash
./test.sh stop
./test.sh start
```

### Custom WordPress Configuration

The environment uses these defaults:
- WP_DEBUG: true
- WP_DEBUG_LOG: true
- WP_DEBUG_DISPLAY: false
- SCRIPT_DEBUG: true

To modify, edit `docker-compose.yml` under `WORDPRESS_CONFIG_EXTRA`.

### Using WP-CLI for Advanced Tasks

```bash
# Install a theme
./test.sh wp theme install twentytwentyfour --activate

# Install a plugin from WordPress.org
./test.sh wp plugin install classic-editor --activate

# Export database
./test.sh wp db export backup.sql

# Search and replace URLs
./test.sh wp search-replace 'http://oldurl.com' 'http://localhost:8080'

# Create multiple test posts
for i in {1..10}; do
  ./test.sh wp post create \
    --post_title="Test Post $i" \
    --post_content="Content for post $i" \
    --post_status=publish
done
```

### Multiple WordPress Environments

To run multiple separate WordPress environments:

1. Copy `docker-compose.yml` to `docker-compose-dev.yml`
2. Change container names and ports
3. Run with: `docker-compose -f docker-compose-dev.yml up -d`

### Persistent vs Fresh Testing

**Persistent** (keeps data between restarts):
```bash
./test.sh stop
./test.sh start
```

**Fresh** (new WordPress each time):
```bash
./test.sh reset
./test.sh start
./test.sh install-wp
```

### Performance Testing

```bash
# Install Query Monitor plugin
./test.sh wp plugin install query-monitor --activate

# Or install Debug Bar
./test.sh wp plugin install debug-bar --activate

# View in browser at bottom of page
```

### Database Access

**Via phpMyAdmin**: http://localhost:8081

**Via Command Line**:
```bash
# Access MySQL shell
./test.sh shell db
mysql -u wordpress -pwordpress wordpress

# Run SQL queries
SELECT * FROM wp_posts WHERE post_type='post';
SELECT * FROM wp_options WHERE option_name LIKE '%slo_%';
```

**Via WP-CLI**:
```bash
# Run SQL query
./test.sh wp db query "SELECT * FROM wp_posts LIMIT 5"

# Export database
./test.sh wp db export backup.sql

# Import database
./test.sh wp db import backup.sql
```

---

## Testing Checklist

Use this checklist for each plugin:

### Installation & Activation
- [ ] Plugin appears in Plugins list
- [ ] Activates without errors
- [ ] No PHP errors in debug log
- [ ] Admin menu items appear (if applicable)
- [ ] Settings page loads (if applicable)

### Functionality
- [ ] All features work as documented
- [ ] Frontend displays correctly
- [ ] Admin interface works
- [ ] JavaScript functions properly
- [ ] CSS styles load correctly

### Content Creation
- [ ] Works with Gutenberg editor
- [ ] Works with Classic Editor
- [ ] Handles various post types
- [ ] Metadata saves correctly
- [ ] Custom fields work (if applicable)

### Performance
- [ ] No slow page loads
- [ ] Database queries optimized
- [ ] Caching works (if applicable)
- [ ] Assets load efficiently

### Security
- [ ] Nonce verification present
- [ ] Capability checks in place
- [ ] Input sanitization works
- [ ] Output escaping correct
- [ ] No XSS vulnerabilities
- [ ] No SQL injection risks

### Compatibility
- [ ] Works with current WordPress version
- [ ] Mobile responsive
- [ ] Browser compatible
- [ ] No conflicts with other plugins
- [ ] Theme compatible

---

## Cleanup

### Keep Data (Pause Testing)

```bash
./test.sh stop
```

Containers stop but data persists. Next `./test.sh start` will resume with same data.

### Remove Everything (Fresh Start)

```bash
./test.sh reset
```

Deletes all WordPress data, database, and volumes. Next start will be fresh install.

### Remove Docker Images (Reclaim Disk Space)

```bash
docker rmi wordpress:latest mysql:8.0 phpmyadmin:latest
```

---

## Tips & Best Practices

1. **Use WP-CLI for speed** - Faster than manual browser clicks
2. **Keep debug log open** - `./test.sh debug` in separate terminal
3. **Backup before major changes** - `./test.sh backup`
4. **Test with real content** - Use provided sample posts
5. **Test different user roles** - Create editor, author, subscriber users
6. **Test on mobile** - Use Chrome DevTools device emulation
7. **Monitor performance** - Install Query Monitor plugin
8. **Check security** - Review all user inputs and outputs

---

## Common Commands Quick Reference

```bash
# Environment
./test.sh start              # Start everything
./test.sh stop               # Stop (keep data)
./test.sh reset              # Delete everything
./test.sh status             # Check status

# Plugins
./test.sh activate <slug>    # Activate plugin
./test.sh deactivate <slug>  # Deactivate plugin
./test.sh plugins            # List plugins

# Debugging
./test.sh logs               # WordPress logs
./test.sh debug              # Debug log
./test.sh shell              # Container shell

# WordPress
./test.sh install-wp         # Install WP
./test.sh wp <cmd>           # WP-CLI command

# Maintenance
./test.sh backup             # Backup database
./test.sh fix-permissions    # Fix permissions
```

---

## Plugin-Specific Testing Guides

### SEO & LLM Optimizer

See: `seo-llm-optimizer/TESTING_GUIDE.md`

**Quick Test**:
```bash
./test.sh activate seo-llm-optimizer
./test.sh wp post create --post_title='AI Guide' --post_content='<h1>Intro</h1><p>Content</p>' --post_status=publish
# Visit post, click "Copy for AI" button
```

### SEO Cluster Links

See: `seo-cluster-links/README.md`

**Quick Test**:
```bash
./test.sh activate seo-cluster-links
# Create posts with [[keywords]]
# Verify automatic linking
```

---

## Need Help?

1. **Check logs**: `./test.sh debug`
2. **Check status**: `./test.sh status`
3. **View help**: `./test.sh help`
4. **Check Docker**: `docker ps`
5. **Reset if stuck**: `./test.sh reset && ./test.sh start`

---

**Happy Testing!** 🚀

For plugin-specific testing procedures, see the TESTING_GUIDE.md in each plugin directory.
