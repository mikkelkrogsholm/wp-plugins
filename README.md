# WordPress Plugins - Development & Testing

This repository contains WordPress plugins with a shared Docker-based testing environment.

## Plugins

- **[seo-llm-optimizer](./seo-llm-optimizer/)** - SEO & LLM Optimizer for converting WordPress content to AI-friendly formats
- **[seo-cluster-links](./seo-cluster-links/)** - Automatic internal linking for SEO clusters

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

## What's Included

### Docker Environment
- **WordPress**: Latest version on port 8080
- **MySQL 8.0**: Database server
- **phpMyAdmin**: Database management on port 8081
- **WP-CLI**: Command-line WordPress management

### Helper Script
`test.sh` - Manages the entire testing environment:
- Start/stop/reset containers
- Install WordPress automatically
- Activate/deactivate plugins
- Run WP-CLI commands
- View logs and debug
- Backup/restore database

### Access URLs
- **WordPress**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/wp-admin
- **phpMyAdmin**: http://localhost:8081

## Testing Commands

### Environment
```bash
./test.sh start              # Start all containers
./test.sh stop               # Stop (preserves data)
./test.sh reset              # Delete all data
./test.sh status             # Check status
```

### Plugins
```bash
./test.sh plugins            # List available plugins
./test.sh activate <slug>    # Activate a plugin
./test.sh deactivate <slug>  # Deactivate a plugin
```

### WordPress Management
```bash
./test.sh install-wp         # Install WordPress via CLI
./test.sh wp <command>       # Run any WP-CLI command
```

Examples:
```bash
./test.sh wp plugin list
./test.sh wp post create --post_title='Test' --post_status=publish
./test.sh wp user create editor editor@example.com --role=editor
```

### Debugging
```bash
./test.sh logs               # View WordPress logs
./test.sh debug              # Tail debug.log
./test.sh shell              # Open container shell
```

## Plugin Development Workflow

### Creating a New Plugin

1. **Create plugin directory**
   ```bash
   mkdir my-new-plugin
   cd my-new-plugin
   ```

2. **Create main plugin file**
   ```bash
   touch my-new-plugin.php
   ```

3. **Add WordPress headers**
   ```php
   <?php
   /**
    * Plugin Name: My New Plugin
    * Description: Description here
    * Version: 1.0.0
    * Author: Your Name
    */
   ```

4. **Mount in Docker**

   Edit `docker-compose.yml` and add:
   ```yaml
   - ./my-new-plugin:/var/www/html/wp-content/plugins/my-new-plugin
   ```

5. **Restart environment**
   ```bash
   ./test.sh stop
   ./test.sh start
   ```

6. **Activate and test**
   ```bash
   ./test.sh activate my-new-plugin
   ```

### Testing a Plugin

1. **Activate the plugin**
   ```bash
   ./test.sh activate plugin-slug
   ```

2. **Monitor debug log**
   ```bash
   ./test.sh debug
   ```

3. **Test functionality** in browser at http://localhost:8080

4. **Check for errors** in debug log

5. **Deactivate when done**
   ```bash
   ./test.sh deactivate plugin-slug
   ```

## Documentation

- **[TESTING.md](./TESTING.md)** - Comprehensive testing guide
- **Plugin-specific guides** - See each plugin directory for detailed documentation

## File Structure

```
wp-plugins/
├── docker-compose.yml          # Docker setup for all plugins
├── test.sh                     # Testing helper script
├── TESTING.md                  # Comprehensive testing guide
├── README.md                   # This file
├── .dockerignore              # Docker build optimization
│
├── seo-llm-optimizer/         # SEO & LLM Optimizer plugin
│   ├── seo-llm-optimizer.php  # Main plugin file
│   ├── includes/              # PHP classes
│   ├── assets/                # CSS, JS
│   ├── templates/             # PHP templates
│   ├── README.md              # Plugin documentation
│   ├── TESTING_GUIDE.md       # Plugin testing guide
│   └── ...
│
├── seo-cluster-links/         # SEO Cluster Links plugin
│   ├── seo-cluster-links.php  # Main plugin file
│   ├── includes/              # PHP classes
│   └── ...
│
└── backups/                   # Database backups (auto-created)
```

## Requirements

- **Docker Desktop** - For running WordPress locally
- **bash** - For running test.sh script
- **curl** - For status checks (usually pre-installed)

## Database Credentials

```
Host:          localhost:3306 (or 'db' from inside containers)
Database:      wordpress
Username:      wordpress
Password:      wordpress
Root Password: rootpassword
```

## Tips & Best Practices

### Development
- Keep `./test.sh debug` running in a separate terminal
- Use `./test.sh wp` commands for quick testing
- Backup before major changes: `./test.sh backup`

### Testing
- Test with real content, not just "Hello World"
- Test with different user roles (admin, editor, subscriber)
- Check mobile responsiveness
- Monitor performance with Query Monitor plugin

### Debugging
- Check debug.log first: `./test.sh debug`
- Use browser console (F12) for JavaScript errors
- Use phpMyAdmin to inspect database
- Use WP-CLI for quick queries: `./test.sh wp db query "SELECT ..."`

## Common Tasks

### Create Test Content
```bash
# Create a post
./test.sh wp post create \
  --post_title='Test Post' \
  --post_content='<h1>Title</h1><p>Content here</p>' \
  --post_status=publish

# Create multiple posts
for i in {1..10}; do
  ./test.sh wp post create \
    --post_title="Post $i" \
    --post_status=publish
done
```

### Backup & Restore
```bash
# Create backup
./test.sh backup

# Restore from backup
./test.sh restore backups/wordpress_backup_20251108_123456.sql
```

### Fix Permission Issues
```bash
./test.sh fix-permissions
```

### Reset to Fresh WordPress
```bash
./test.sh reset
./test.sh start
./test.sh install-wp
```

## Troubleshooting

### Plugin Not Appearing
```bash
# Check if mounted correctly
./test.sh shell
ls -la /var/www/html/wp-content/plugins/

# Fix permissions
./test.sh fix-permissions

# Restart
./test.sh stop && ./test.sh start
```

### WordPress Not Loading
```bash
# Check status
./test.sh status

# View logs
./test.sh logs

# Reset if needed
./test.sh reset && ./test.sh start
```

### Port Already in Use
Edit `docker-compose.yml` and change ports:
```yaml
ports:
  - "8082:80"  # Change 8080 to 8082
```

### Database Connection Issues
```bash
# Restart database
docker-compose restart db

# Check database logs
./test.sh logs db
```

## Advanced Usage

### Run Multiple WordPress Instances

Copy `docker-compose.yml` to `docker-compose-dev.yml`, change ports and container names, then:

```bash
docker-compose -f docker-compose-dev.yml up -d
```

### Access MySQL Directly
```bash
./test.sh shell db
mysql -u wordpress -pwordpress wordpress
```

### Custom WP-CLI Commands
```bash
# Install theme
./test.sh wp theme install twentytwentyfour --activate

# Install plugin from WordPress.org
./test.sh wp plugin install query-monitor --activate

# Export/import database
./test.sh wp db export backup.sql
./test.sh wp db import backup.sql
```

## Cleanup

### Stop Containers (Keep Data)
```bash
./test.sh stop
```

### Remove Everything
```bash
./test.sh reset
```

### Remove Docker Images
```bash
docker rmi wordpress:latest mysql:8.0 phpmyadmin:latest
```

## Contributing

When adding a new plugin:

1. Create plugin directory in project root
2. Update `docker-compose.yml` to mount the plugin
3. Add plugin documentation (README.md, etc.)
4. Update this README to list the new plugin
5. Test using `./test.sh`

## Resources

- **WordPress Plugin Handbook**: https://developer.wordpress.org/plugins/
- **WP-CLI Documentation**: https://wp-cli.org/
- **Docker Documentation**: https://docs.docker.com/

## Support

For testing issues:
1. Check `./test.sh status`
2. View logs: `./test.sh logs`
3. Read [TESTING.md](./TESTING.md)
4. Check plugin-specific documentation

For plugin-specific issues:
- See individual plugin README files
- Check plugin TESTING_GUIDE.md files

---

## Quick Reference

```bash
# Most Common Commands
./test.sh start                    # Start WordPress
./test.sh install-wp               # Install WordPress
./test.sh activate plugin-name     # Activate plugin
./test.sh debug                    # Watch debug log
./test.sh wp plugin list           # List all plugins
./test.sh stop                     # Stop WordPress
```

**Ready to start?** Run `./test.sh start` and follow the prompts!
