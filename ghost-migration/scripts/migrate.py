#!/usr/bin/env python3
"""
Ghost to WordPress Migration Script
Migrates content from a Ghost blog to WordPress using RSS feed and HTML scraping.
"""

import argparse
import json
import logging
import os
import re
import sys
import time
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from urllib.parse import urljoin, urlparse

import feedparser
import requests
from bs4 import BeautifulSoup
from requests.auth import HTTPBasicAuth
from tqdm import tqdm


class GhostToWordPressMigration:
    """Handles migration from Ghost to WordPress."""

    def __init__(self, config_file: str = "../config.json"):
        """Initialize migration with configuration."""
        self.config = self._load_config(config_file)
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Ghost-to-WordPress Migration Bot)'
        })

        # Set up logging
        self._setup_logging()

        # Statistics
        self.stats = {
            'posts_processed': 0,
            'posts_created': 0,
            'posts_failed': 0,
            'images_downloaded': 0,
            'images_uploaded': 0,
            'images_failed': 0,
            'start_time': datetime.now(),
            'errors': []
        }

        # WordPress auth
        self.wp_auth = HTTPBasicAuth(
            self.config['wordpress']['username'],
            self.config['wordpress']['password']
        )

    def _load_config(self, config_file: str) -> Dict:
        """Load configuration from JSON file."""
        config_path = Path(__file__).parent / config_file
        with open(config_path, 'r', encoding='utf-8') as f:
            return json.load(f)

    def _setup_logging(self):
        """Set up logging to file and console."""
        logs_dir = Path(self.config['output']['logs_dir'])
        logs_dir.mkdir(exist_ok=True, parents=True)

        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        log_file = logs_dir / f'migration_{timestamp}.log'

        # Create logger
        self.logger = logging.getLogger('ghost_migration')
        self.logger.setLevel(logging.DEBUG)

        # File handler (detailed)
        file_handler = logging.FileHandler(log_file, encoding='utf-8')
        file_handler.setLevel(logging.DEBUG)
        file_formatter = logging.Formatter(
            '%(asctime)s - %(levelname)s - %(message)s'
        )
        file_handler.setFormatter(file_formatter)

        # Console handler (summary)
        console_handler = logging.StreamHandler()
        console_handler.setLevel(logging.INFO)
        console_formatter = logging.Formatter('%(levelname)s: %(message)s')
        console_handler.setFormatter(console_formatter)

        self.logger.addHandler(file_handler)
        self.logger.addHandler(console_handler)

    def fetch_rss_feed(self) -> feedparser.FeedParserDict:
        """Fetch and parse Ghost RSS feed."""
        self.logger.info(f"Fetching RSS feed from {self.config['ghost']['rss_feed']}")

        try:
            feed = feedparser.parse(self.config['ghost']['rss_feed'])

            if feed.bozo:
                self.logger.warning(f"RSS feed has issues: {feed.bozo_exception}")

            self.logger.info(f"Found {len(feed.entries)} posts in RSS feed")
            return feed

        except Exception as e:
            self.logger.error(f"Failed to fetch RSS feed: {e}")
            raise

    def scrape_featured_image(self, post_url: str) -> Optional[str]:
        """Scrape featured image URL from Ghost post page."""
        try:
            response = self.session.get(post_url, timeout=10)
            response.raise_for_status()

            soup = BeautifulSoup(response.content, 'lxml')

            # Try multiple selectors for featured image
            selectors = [
                'article img.kg-image',  # Ghost card image
                'article .post-full-image img',
                'article figure img',
                'article img[src*="/content/images/"]',
                'meta[property="og:image"]',  # Open Graph fallback
            ]

            for selector in selectors:
                if selector.startswith('meta'):
                    element = soup.select_one(selector)
                    if element and element.get('content'):
                        return element['content']
                else:
                    element = soup.select_one(selector)
                    if element and element.get('src'):
                        # Get highest quality image (remove size parameter)
                        img_url = element['src']
                        img_url = re.sub(r'/size/w\d+/', '/size/w2000/', img_url)
                        return img_url

            self.logger.debug(f"No featured image found for {post_url}")
            return None

        except Exception as e:
            self.logger.warning(f"Failed to scrape featured image from {post_url}: {e}")
            return None

    def download_image(self, image_url: str, post_slug: str) -> Optional[str]:
        """Download image from Ghost CDN to local storage."""
        if not self.config['migration']['download_images']:
            return None

        try:
            # Create images directory
            images_dir = Path(self.config['output']['images_dir'])
            images_dir.mkdir(exist_ok=True, parents=True)

            # Generate filename
            parsed_url = urlparse(image_url)
            original_filename = os.path.basename(parsed_url.path)
            filename = f"{post_slug}_{original_filename}"
            filepath = images_dir / filename

            # Download image
            self.logger.debug(f"Downloading image: {image_url}")
            response = self.session.get(image_url, timeout=30)
            response.raise_for_status()

            # Save to disk
            with open(filepath, 'wb') as f:
                f.write(response.content)

            self.stats['images_downloaded'] += 1
            self.logger.debug(f"Saved image to {filepath}")

            return str(filepath)

        except Exception as e:
            self.logger.error(f"Failed to download image {image_url}: {e}")
            self.stats['images_failed'] += 1
            return None

    def upload_image_to_wordpress(self, image_path: str, title: str) -> Optional[int]:
        """Upload image to WordPress media library."""
        try:
            url = f"{self.config['wordpress']['api_base']}/media"

            # Prepare file upload
            filename = os.path.basename(image_path)
            with open(image_path, 'rb') as f:
                files = {
                    'file': (filename, f, self._get_mime_type(filename))
                }

                headers = {
                    'Content-Disposition': f'attachment; filename="{filename}"'
                }

                response = self.session.post(
                    url,
                    auth=self.wp_auth,
                    files=files,
                    headers=headers,
                    data={'title': title},
                    timeout=60
                )

            response.raise_for_status()
            media_data = response.json()

            media_id = media_data.get('id')
            self.logger.debug(f"Uploaded image to WordPress, media ID: {media_id}")
            self.stats['images_uploaded'] += 1

            return media_id

        except Exception as e:
            self.logger.error(f"Failed to upload image {image_path}: {e}")
            self.stats['images_failed'] += 1
            return None

    def _get_mime_type(self, filename: str) -> str:
        """Get MIME type from filename extension."""
        ext = os.path.splitext(filename)[1].lower()
        mime_types = {
            '.jpg': 'image/jpeg',
            '.jpeg': 'image/jpeg',
            '.png': 'image/png',
            '.gif': 'image/gif',
            '.webp': 'image/webp',
            '.svg': 'image/svg+xml',
        }
        return mime_types.get(ext, 'application/octet-stream')

    def create_wordpress_post(
        self,
        title: str,
        content: str,
        date: str,
        excerpt: str,
        slug: str,
        featured_media_id: Optional[int] = None
    ) -> Optional[int]:
        """Create a post in WordPress via REST API."""
        try:
            url = f"{self.config['wordpress']['api_base']}/posts"

            post_data = {
                'title': title,
                'content': content,
                'excerpt': excerpt,
                'slug': slug,
                'date': date,
                'status': 'draft' if self.config['migration']['import_as_draft'] else 'publish',
            }

            if featured_media_id:
                post_data['featured_media'] = featured_media_id

            response = self.session.post(
                url,
                auth=self.wp_auth,
                json=post_data,
                timeout=30
            )

            response.raise_for_status()
            post_response = response.json()

            post_id = post_response.get('id')
            self.logger.info(f"Created WordPress post: {title} (ID: {post_id})")
            self.stats['posts_created'] += 1

            return post_id

        except Exception as e:
            self.logger.error(f"Failed to create WordPress post '{title}': {e}")
            if hasattr(e, 'response') and hasattr(e.response, 'text'):
                self.logger.debug(f"Response: {e.response.text}")
            self.stats['posts_failed'] += 1
            return None

    def migrate_post(self, entry: feedparser.FeedParserDict) -> bool:
        """Migrate a single post from Ghost to WordPress."""
        self.stats['posts_processed'] += 1

        title = entry.title
        self.logger.info(f"Processing post {self.stats['posts_processed']}: {title}")

        # Extract data from RSS entry
        content = entry.get('content', [{}])[0].get('value', '')
        excerpt = entry.get('summary', '')
        date = entry.get('published', '')
        link = entry.get('link', '')
        slug = link.rstrip('/').split('/')[-1] if link else ''

        # Get featured image
        featured_media_id = None
        featured_image_url = self.scrape_featured_image(link)

        if featured_image_url:
            # Download image
            image_path = self.download_image(featured_image_url, slug)

            if image_path:
                # Upload to WordPress
                featured_media_id = self.upload_image_to_wordpress(image_path, title)

        # Create WordPress post
        post_id = self.create_wordpress_post(
            title=title,
            content=content,
            date=date,
            excerpt=excerpt,
            slug=slug,
            featured_media_id=featured_media_id
        )

        # Delay between requests
        delay = self.config['migration']['delay_between_requests']
        if delay > 0:
            time.sleep(delay)

        return post_id is not None

    def migrate_all(self):
        """Migrate all posts from Ghost to WordPress."""
        self.logger.info("Starting Ghost to WordPress migration")
        self.logger.info(f"Source: {self.config['ghost']['url']}")
        self.logger.info(f"Target: {self.config['wordpress']['url']}")

        # Fetch RSS feed
        feed = self.fetch_rss_feed()

        if not feed.entries:
            self.logger.error("No posts found in RSS feed")
            return

        # Migrate each post
        self.logger.info(f"Migrating {len(feed.entries)} posts...")

        for entry in tqdm(feed.entries, desc="Migrating posts"):
            try:
                self.migrate_post(entry)
            except Exception as e:
                self.logger.error(f"Unexpected error migrating post: {e}")
                self.stats['errors'].append({
                    'post': entry.get('title', 'Unknown'),
                    'error': str(e)
                })

        # Generate report
        self.generate_report()

    def generate_report(self):
        """Generate migration report."""
        self.stats['end_time'] = datetime.now()
        duration = self.stats['end_time'] - self.stats['start_time']

        self.logger.info("\n" + "=" * 60)
        self.logger.info("MIGRATION COMPLETE")
        self.logger.info("=" * 60)
        self.logger.info(f"Duration: {duration}")
        self.logger.info(f"Posts processed: {self.stats['posts_processed']}")
        self.logger.info(f"Posts created: {self.stats['posts_created']}")
        self.logger.info(f"Posts failed: {self.stats['posts_failed']}")
        self.logger.info(f"Images downloaded: {self.stats['images_downloaded']}")
        self.logger.info(f"Images uploaded: {self.stats['images_uploaded']}")
        self.logger.info(f"Images failed: {self.stats['images_failed']}")

        if self.stats['errors']:
            self.logger.warning(f"\n{len(self.stats['errors'])} errors occurred:")
            for error in self.stats['errors']:
                self.logger.warning(f"  - {error['post']}: {error['error']}")

        # Save report to JSON
        output_dir = Path(self.config['output']['report_file']).parent
        output_dir.mkdir(exist_ok=True, parents=True)

        report_data = {
            'migration_date': self.stats['start_time'].isoformat(),
            'duration_seconds': duration.total_seconds(),
            'statistics': {
                'posts_processed': self.stats['posts_processed'],
                'posts_created': self.stats['posts_created'],
                'posts_failed': self.stats['posts_failed'],
                'images_downloaded': self.stats['images_downloaded'],
                'images_uploaded': self.stats['images_uploaded'],
                'images_failed': self.stats['images_failed'],
            },
            'errors': self.stats['errors']
        }

        with open(self.config['output']['report_file'], 'w', encoding='utf-8') as f:
            json.dump(report_data, f, indent=2, ensure_ascii=False)

        self.logger.info(f"\nReport saved to: {self.config['output']['report_file']}")


def main():
    """Main entry point."""
    parser = argparse.ArgumentParser(
        description='Migrate Ghost blog to WordPress'
    )
    parser.add_argument(
        '--config',
        default='../config.json',
        help='Path to configuration file (default: ../config.json)'
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='Perform a dry run without creating posts'
    )

    args = parser.parse_args()

    # Run migration
    migrator = GhostToWordPressMigration(config_file=args.config)

    if args.dry_run:
        migrator.logger.info("DRY RUN MODE - No posts will be created")
        migrator.config['migration']['import_as_draft'] = True

    try:
        migrator.migrate_all()
    except KeyboardInterrupt:
        migrator.logger.info("\nMigration interrupted by user")
        migrator.generate_report()
        sys.exit(1)
    except Exception as e:
        migrator.logger.error(f"Fatal error: {e}")
        migrator.generate_report()
        sys.exit(1)


if __name__ == '__main__':
    main()
