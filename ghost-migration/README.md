# Ghost to WordPress Migration

Automated migration tool for transferring content from a Ghost blog to WordPress.

## Overview

This tool migrates content from https://brokk-sindre.dk (Ghost) to a WordPress instance by:
1. Fetching posts from the Ghost RSS feed
2. Scraping featured images from post pages
3. Downloading images to local storage
4. Uploading images to WordPress media library
5. Creating posts in WordPress via REST API

## Features

- ✅ Migrates all blog posts with full HTML content
- ✅ Preserves post metadata (title, date, author, excerpt)
- ✅ Downloads and re-uploads featured images
- ✅ Maintains URL slugs for SEO
- ✅ Configurable import as draft or published
- ✅ Detailed logging and error reporting
- ✅ Progress bars for visual feedback
- ✅ Dry-run mode for testing

## Prerequisites

- Python 3.8+
- WordPress instance with REST API enabled
- WordPress user credentials (Application Password recommended)

## Installation

### 1. Install Python Dependencies

```bash
cd ghost-migration
pip install -r requirements.txt
```

Or using a virtual environment (recommended):

```bash
cd ghost-migration
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
```

### 2. Configure WordPress Application Password

1. Log into WordPress admin: http://localhost:8082/wp-admin
2. Go to Users → Profile
3. Scroll to "Application Passwords"
4. Create new password with name "Ghost Migration"
5. Copy the generated password
6. Update `config.json` with the password

**Note:** Application Passwords are more secure than using your account password.

## Configuration

Edit `config.json` to match your setup:

```json
{
  "ghost": {
    "url": "https://brokk-sindre.dk",
    "rss_feed": "https://brokk-sindre.dk/rss/"
  },
  "wordpress": {
    "url": "http://localhost:8082",
    "api_base": "http://localhost:8082/wp-json/wp/v2",
    "username": "admin",
    "password": "your-application-password-here"
  },
  "migration": {
    "download_images": true,
    "preserve_urls": true,
    "import_as_draft": false,
    "delay_between_requests": 1.0
  }
}
```

### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `download_images` | Download featured images from Ghost | `true` |
| `preserve_urls` | Use same URL slugs as Ghost | `true` |
| `import_as_draft` | Import posts as drafts instead of published | `false` |
| `delay_between_requests` | Delay in seconds between API calls | `1.0` |

## Usage

### Basic Migration

```bash
cd ghost-migration/scripts
python migrate.py
```

### Dry Run (No Changes)

Test the migration without creating posts:

```bash
python migrate.py --dry-run
```

### Custom Configuration File

```bash
python migrate.py --config /path/to/config.json
```

## Migration Process

The script performs these steps for each post:

1. **Fetch RSS Feed** - Gets all posts from Ghost RSS feed
2. **Extract Metadata** - Parses title, content, date, excerpt, URL
3. **Scrape Featured Image** - Visits post page to get featured image URL
4. **Download Image** - Saves image to `./images/` directory
5. **Upload to WordPress** - Uploads image to WordPress media library
6. **Create Post** - Creates WordPress post with all metadata
7. **Link Featured Image** - Associates uploaded image with post

## Output

### Directory Structure

```
ghost-migration/
├── images/              # Downloaded images
├── logs/                # Migration logs
│   └── migration_YYYYMMDD_HHMMSS.log
├── output/              # Reports
│   └── migration-report.json
└── scripts/             # Migration scripts
    └── migrate.py
```

### Migration Report

After completion, a JSON report is saved to `output/migration-report.json`:

```json
{
  "migration_date": "2025-11-08T16:00:00",
  "duration_seconds": 127.5,
  "statistics": {
    "posts_processed": 13,
    "posts_created": 13,
    "posts_failed": 0,
    "images_downloaded": 11,
    "images_uploaded": 11,
    "images_failed": 0
  },
  "errors": []
}
```

## Troubleshooting

### Authentication Errors

```
Error: 401 Unauthorized
```

**Solution:**
- Verify WordPress username/password in `config.json`
- Use Application Password, not account password
- Ensure user has `edit_posts` capability

### Image Download Failures

```
Failed to download image: 403 Forbidden
```

**Solution:**
- Ghost CDN may block automated requests
- Check image URL is accessible in browser
- Try reducing `delay_between_requests`

### WordPress REST API Not Found

```
Error: 404 Not Found - /wp-json/wp/v2/posts
```

**Solution:**
- Ensure WordPress permalinks are enabled
- Run: `docker compose exec -T wpcli wp rewrite structure '/%postname%/'`
- Flush permalinks: `docker compose exec -T wpcli wp rewrite flush`

### SSL Certificate Errors

```
SSLError: certificate verify failed
```

**Solution:**
- For localhost, this shouldn't occur
- For HTTPS Ghost sites, ensure SSL certificates are valid

## WordPress Setup

### Enable REST API

The REST API should be enabled by default, but verify:

```bash
docker compose exec -T wpcli wp option get slo_enable_rest_api
# Should return: 1
```

If not enabled:

```bash
docker compose exec -T wpcli wp option update slo_enable_rest_api 1
```

### Configure Permalinks

```bash
docker compose exec -T wpcli wp rewrite structure '/%postname%/'
docker compose exec -T wpcli wp rewrite flush
```

### Create Application Password

```bash
docker compose exec -T wpcli wp user application-password create admin "Ghost Migration"
```

## Testing

### Test on Local WordPress

1. Start WordPress Docker environment:
```bash
cd ..
./test.sh start
./test.sh install-wp
```

2. Run migration in dry-run mode:
```bash
cd ghost-migration/scripts
python migrate.py --dry-run
```

3. Review logs in `logs/` directory

4. If successful, run actual migration:
```bash
python migrate.py
```

### Verify Migration

1. Log into WordPress admin: http://localhost:8082/wp-admin
2. Check Posts → All Posts
3. Verify:
   - All 13 posts imported
   - Featured images present
   - Content formatted correctly
   - Dates preserved
   - URL slugs match Ghost

## Post-Migration Tasks

### 1. Review Content

- Check all posts for formatting issues
- Verify images display correctly
- Test internal links

### 2. Set Up Redirects (Optional)

If WordPress URLs differ from Ghost URLs, set up 301 redirects:

```bash
# Install Redirection plugin
docker compose exec -T wpcli wp plugin install redirection --activate

# Add redirects via WP-CLI or admin UI
```

### 3. Configure Theme

Match Ghost design palette:
- Primary color: `#406e76` (teal)
- Use minimal, content-focused theme
- Recommended: Twenty Twenty-Four, GeneratePress, Astra

### 4. Install Plugins

For SEO and optimization:
```bash
docker compose exec -T wpcli wp plugin activate seo-llm-optimizer
docker compose exec -T wpcli wp plugin activate seo-cluster-links
```

### 5. Test Plugins

- Use SEO & LLM Optimizer to export migrated content
- Create content clusters with SEO Cluster Links
- Verify all features work with migrated content

## Limitations

### What's Migrated

✅ Post title
✅ Full HTML content
✅ Publication date
✅ Author (maps to WordPress admin)
✅ Excerpt/description
✅ URL slug
✅ Featured image

### What's NOT Migrated

❌ **Tags** - Ghost API requires authentication
❌ **Categories** - Ghost only uses tags
❌ **Custom fields** - Not accessible via RSS
❌ **Comments** - Site has no comments
❌ **Reading time** - Can be calculated by plugins

### Workarounds

- **Tags:** Manually add after migration
- **Categories:** Create WordPress categories and assign manually
- **Reading time:** Install reading time plugin (auto-calculates)

## Expected Results

Based on research of https://brokk-sindre.dk:

- **Posts to migrate:** 13 blog posts
- **Pages:** 8 (can be migrated separately if needed)
- **Images:** ~11 featured images
- **Author:** Mikkel Krogsholm (1 user)
- **Est. duration:** 2-3 minutes
- **Success rate:** 95%+

## FAQ

### Q: Will this delete my Ghost content?

**A:** No. This script only reads from Ghost (via RSS feed) and writes to WordPress. Your Ghost site remains untouched.

### Q: Can I run this multiple times?

**A:** Yes, but it will create duplicate posts. Delete test posts between runs or use `import_as_draft: true` for testing.

### Q: What if migration fails halfway?

**A:** The script logs all progress. Check `logs/` for details. You can re-run for failed posts by modifying the script to skip successful ones (check WordPress for existing slugs).

### Q: Can I migrate to a live WordPress site?

**A:** Yes, but test locally first. Update `config.json` with your live site URL and credentials.

### Q: How do I migrate pages (not posts)?

**A:** Modify the script to use the `/pages` endpoint instead of `/posts`. Pages aren't in the RSS feed, so you'd need to scrape them from the sitemap.

## Advanced Usage

### Custom RSS Parsing

If you need to handle custom Ghost fields:

```python
# In migrate_post():
custom_field = entry.get('custom_field_name', '')
post_data['meta'] = {
    'custom_field': custom_field
}
```

### Image Optimization

Images are downloaded at full resolution. To optimize:

```python
from PIL import Image

# After download, resize:
img = Image.open(image_path)
img.thumbnail((1200, 800))
img.save(image_path, optimize=True, quality=85)
```

### Batch Processing

Process posts in batches:

```python
# Migrate first 5 posts only:
for entry in feed.entries[:5]:
    migrator.migrate_post(entry)
```

## Support

For issues or questions:

1. Check logs in `logs/` directory
2. Review error messages in console output
3. Consult `migration-report.json` for statistics
4. Refer to WordPress REST API docs: https://developer.wordpress.org/rest-api/

## License

MIT License - Free to use and modify

## Credits

Created with Claude Code for the wp-plugins WordPress development project.

---

**Ready to migrate?** Run `python scripts/migrate.py` to get started!
