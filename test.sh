#!/bin/bash

# WordPress Plugins - Testing Helper Script
# Quick commands to manage the Docker testing environment for ALL plugins

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Command: start
start() {
    print_header "Starting WordPress Test Environment"

    # Check if docker-compose.yml exists
    if [ ! -f "docker-compose.yml" ]; then
        print_error "docker-compose.yml not found!"
        print_info "Make sure you're in the project root directory"
        exit 1
    fi

    docker-compose up -d

    print_success "Containers started!"
    print_info "WordPress: http://localhost:8080"
    print_info "phpMyAdmin: http://localhost:8081"
    print_info ""
    print_info "Waiting 10 seconds for services to initialize..."
    sleep 10

    print_success "Environment ready!"
    print_info ""
    print_info "Next steps:"
    print_info "1. Visit http://localhost:8080"
    print_info "2. Complete WordPress installation wizard"
    print_info "3. Go to Plugins → Installed Plugins"
    print_info "4. Activate the plugin(s) you want to test"
}

# Command: stop
stop() {
    print_header "Stopping WordPress Test Environment"
    docker-compose down
    print_success "Containers stopped (data preserved)"
    print_info "Run './test.sh start' to restart with existing data"
}

# Command: reset
reset() {
    print_header "Resetting WordPress Test Environment"
    print_error "WARNING: This will delete all WordPress data!"
    print_error "- All posts, pages, and settings will be lost"
    print_error "- You'll need to reinstall WordPress"
    echo ""
    read -p "Are you sure? Type 'yes' to confirm: " confirm

    if [ "$confirm" = "yes" ]; then
        docker-compose down -v
        print_success "Environment reset complete"
        print_info "Run './test.sh start' to begin fresh"
    else
        print_info "Reset cancelled"
    fi
}

# Command: logs
logs() {
    local service="${2:-wordpress}"
    print_header "Viewing Logs: $service"
    print_info "Press Ctrl+C to exit"
    docker-compose logs -f "$service"
}

# Command: status
status() {
    print_header "Container Status"
    docker-compose ps
    echo ""

    # Check if WordPress is responding
    if curl -s http://localhost:8080 > /dev/null 2>&1; then
        print_success "WordPress is responding at http://localhost:8080"
    else
        print_error "WordPress is not responding"
        print_info "Run './test.sh start' to start the environment"
    fi

    # Check if phpMyAdmin is responding
    if curl -s http://localhost:8081 > /dev/null 2>&1; then
        print_success "phpMyAdmin is responding at http://localhost:8081"
    else
        print_error "phpMyAdmin is not responding"
    fi

    echo ""
    print_info "Mounted Plugins:"
    docker exec wp-plugins-wordpress ls -1 /var/www/html/wp-content/plugins/ 2>/dev/null | grep -v "^index" | while read plugin; do
        echo "  - $plugin"
    done
}

# Command: shell
shell() {
    local container="${2:-wordpress}"
    print_header "Opening Container Shell: $container"
    print_info "Type 'exit' to return"

    case "$container" in
        wordpress|wp)
            docker exec -it wp-plugins-wordpress bash
            ;;
        db|mysql)
            docker exec -it wp-plugins-mysql bash
            ;;
        cli|wpcli)
            docker exec -it wp-plugins-wpcli bash
            ;;
        *)
            print_error "Unknown container: $container"
            print_info "Available: wordpress, db, cli"
            exit 1
            ;;
    esac
}

# Command: wp (WP-CLI commands)
wp() {
    shift  # Remove 'wp' from arguments
    print_header "Running WP-CLI Command"
    print_info "Command: wp $*"
    docker exec -it wp-plugins-wpcli wp "$@" --allow-root
}

# Command: debug
debug() {
    print_header "WordPress Debug Log"
    print_info "Tailing debug.log (Press Ctrl+C to exit)"
    docker exec wp-plugins-wordpress tail -f /var/www/html/wp-content/debug.log 2>/dev/null || {
        print_error "Debug log not found or empty"
        print_info "Debug logging is enabled by default in this environment"
        print_info "Logs will appear here once WordPress generates them"
    }
}

# Command: plugins
plugins() {
    print_header "Installed Plugins"

    print_info "Checking WordPress container..."
    docker exec wp-plugins-wordpress ls -la /var/www/html/wp-content/plugins/ 2>/dev/null || {
        print_error "Could not access plugins directory"
        print_info "Make sure WordPress is running: ./test.sh start"
        exit 1
    }

    echo ""
    print_info "Available plugins from this project:"
    ls -d */ 2>/dev/null | grep -v "^seo-llm/$" | while read dir; do
        plugin_name="${dir%/}"
        if [ -f "$plugin_name/composer.json" ] || [ -f "$plugin_name/*.php" ] 2>/dev/null; then
            echo "  ✓ $plugin_name"
        fi
    done
}

# Command: activate
activate() {
    local plugin_slug="$2"

    if [ -z "$plugin_slug" ]; then
        print_error "Please specify a plugin slug"
        print_info "Usage: ./test.sh activate <plugin-slug>"
        print_info "Example: ./test.sh activate seo-llm-optimizer"
        exit 1
    fi

    print_header "Activating Plugin: $plugin_slug"
    docker exec -it wp-plugins-wpcli wp plugin activate "$plugin_slug" --allow-root

    if [ $? -eq 0 ]; then
        print_success "Plugin activated!"
    else
        print_error "Failed to activate plugin"
        print_info "Make sure WordPress is installed and the plugin exists"
    fi
}

# Command: deactivate
deactivate() {
    local plugin_slug="$2"

    if [ -z "$plugin_slug" ]; then
        print_error "Please specify a plugin slug"
        print_info "Usage: ./test.sh deactivate <plugin-slug>"
        exit 1
    fi

    print_header "Deactivating Plugin: $plugin_slug"
    docker exec -it wp-plugins-wpcli wp plugin deactivate "$plugin_slug" --allow-root
}

# Command: install-wp
install_wp() {
    print_header "Installing WordPress via WP-CLI"

    read -p "Site Title [WordPress Plugins Test]: " site_title
    site_title=${site_title:-"WordPress Plugins Test"}

    read -p "Admin Username [admin]: " admin_user
    admin_user=${admin_user:-"admin"}

    read -p "Admin Password [admin]: " admin_pass
    admin_pass=${admin_pass:-"admin"}

    read -p "Admin Email [test@example.com]: " admin_email
    admin_email=${admin_email:-"test@example.com"}

    print_info "Installing WordPress..."
    docker exec -it wp-plugins-wpcli wp core install \
        --url="http://localhost:8080" \
        --title="$site_title" \
        --admin_user="$admin_user" \
        --admin_password="$admin_pass" \
        --admin_email="$admin_email" \
        --allow-root

    if [ $? -eq 0 ]; then
        print_success "WordPress installed!"
        print_info ""
        print_info "Login details:"
        print_info "URL: http://localhost:8080/wp-admin"
        print_info "Username: $admin_user"
        print_info "Password: $admin_pass"
    else
        print_error "WordPress installation failed"
    fi
}

# Command: fix-permissions
fix_permissions() {
    print_header "Fixing Plugin Permissions"

    # Fix all plugin directories
    for plugin_dir in */; do
        plugin_name="${plugin_dir%/}"
        if [ "$plugin_name" != "seo-llm" ]; then
            print_info "Fixing: $plugin_name"
            docker exec wp-plugins-wordpress chown -R www-data:www-data "/var/www/html/wp-content/plugins/$plugin_name" 2>/dev/null || true
        fi
    done

    print_success "Permissions fixed for all plugins"
}

# Command: backup
backup() {
    print_header "Creating Backup"

    local backup_dir="backups"
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_name="wordpress_backup_${timestamp}"

    mkdir -p "$backup_dir"

    print_info "Backing up database..."
    docker exec wp-plugins-mysql mysqldump -u wordpress -pwordpress wordpress > "$backup_dir/${backup_name}.sql"

    print_info "Backing up uploads..."
    docker cp wp-plugins-wordpress:/var/www/html/wp-content/uploads "$backup_dir/${backup_name}_uploads" 2>/dev/null || true

    print_success "Backup created: $backup_dir/$backup_name"
    print_info "Database: $backup_dir/${backup_name}.sql"
    print_info "Uploads: $backup_dir/${backup_name}_uploads"
}

# Command: restore
restore() {
    local backup_file="$2"

    if [ -z "$backup_file" ]; then
        print_error "Please specify a backup SQL file"
        print_info "Usage: ./test.sh restore <backup.sql>"
        exit 1
    fi

    if [ ! -f "$backup_file" ]; then
        print_error "Backup file not found: $backup_file"
        exit 1
    fi

    print_header "Restoring Database"
    print_info "Importing: $backup_file"

    docker exec -i wp-plugins-mysql mysql -u wordpress -pwordpress wordpress < "$backup_file"

    if [ $? -eq 0 ]; then
        print_success "Database restored!"
    else
        print_error "Restore failed"
    fi
}

# Command: help
help() {
    print_header "WordPress Plugins - Testing Helper"

    echo "Usage: ./test.sh [command] [options]"
    echo ""
    echo "Environment Management:"
    echo "  start              Start Docker containers"
    echo "  stop               Stop containers (keep data)"
    echo "  reset              Stop and remove all data"
    echo "  status             Check container status"
    echo ""
    echo "WordPress Management:"
    echo "  install-wp         Install WordPress via WP-CLI"
    echo "  wp <command>       Run WP-CLI command"
    echo "  plugins            List available plugins"
    echo "  activate <slug>    Activate a plugin"
    echo "  deactivate <slug>  Deactivate a plugin"
    echo ""
    echo "Debugging & Logs:"
    echo "  logs [service]     View container logs (default: wordpress)"
    echo "  debug              Tail WordPress debug log"
    echo "  shell [container]  Open container shell (wordpress|db|cli)"
    echo ""
    echo "Maintenance:"
    echo "  fix-permissions    Fix plugin file permissions"
    echo "  backup             Create database backup"
    echo "  restore <file>     Restore from backup"
    echo ""
    echo "Quick Start:"
    echo "  1. ./test.sh start"
    echo "  2. ./test.sh install-wp"
    echo "  3. ./test.sh activate seo-llm-optimizer"
    echo "  4. Visit http://localhost:8080"
    echo ""
    echo "Access URLs:"
    echo "  WordPress:  http://localhost:8080"
    echo "  Admin:      http://localhost:8080/wp-admin"
    echo "  phpMyAdmin: http://localhost:8081"
    echo ""
    echo "Examples:"
    echo "  ./test.sh wp plugin list"
    echo "  ./test.sh wp post create --post_title='Test Post' --post_status=publish"
    echo "  ./test.sh shell wordpress"
    echo "  ./test.sh logs db"
    echo ""
}

# Main script
case "${1:-help}" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    reset)
        reset
        ;;
    logs)
        logs "$@"
        ;;
    status)
        status
        ;;
    shell)
        shell "$@"
        ;;
    wp)
        wp "$@"
        ;;
    debug)
        debug
        ;;
    plugins)
        plugins
        ;;
    activate)
        activate "$@"
        ;;
    deactivate)
        deactivate "$@"
        ;;
    install-wp)
        install_wp
        ;;
    fix-permissions)
        fix_permissions
        ;;
    backup)
        backup
        ;;
    restore)
        restore "$@"
        ;;
    help|--help|-h)
        help
        ;;
    *)
        print_error "Unknown command: $1"
        help
        exit 1
        ;;
esac
