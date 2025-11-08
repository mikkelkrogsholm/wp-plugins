#!/usr/bin/env python3
"""
Test script to verify Ghost RSS feed and WordPress API connectivity.
"""

import json
import sys
from pathlib import Path

try:
    import requests
    import feedparser
except ImportError:
    print("ERROR: Missing dependencies. Please run:")
    print("  pip install -r requirements.txt")
    print("\nOr install individually:")
    print("  pip install requests feedparser beautifulsoup4 lxml tqdm")
    sys.exit(1)


def load_config():
    """Load configuration."""
    config_path = Path(__file__).parent / "../config.json"
    with open(config_path, 'r') as f:
        return json.load(f)


def test_ghost_rss(config):
    """Test Ghost RSS feed access."""
    print("Testing Ghost RSS feed...")
    print(f"  URL: {config['ghost']['rss_feed']}")

    try:
        feed = feedparser.parse(config['ghost']['rss_feed'])

        if feed.bozo:
            print(f"  ⚠️  WARNING: Feed has parsing issues: {feed.bozo_exception}")

        print(f"  ✅ Success! Found {len(feed.entries)} posts")

        if feed.entries:
            first_post = feed.entries[0]
            print(f"  First post: \"{first_post.title}\"")

        return True

    except Exception as e:
        print(f"  ❌ Failed: {e}")
        return False


def test_wordpress_api(config):
    """Test WordPress REST API access."""
    print("\nTesting WordPress REST API...")
    print(f"  URL: {config['wordpress']['url']}")

    try:
        # Test root endpoint
        url = f"{config['wordpress']['url']}/wp-json/"
        response = requests.get(url, timeout=10)
        response.raise_for_status()

        data = response.json()
        print(f"  ✅ REST API accessible")
        print(f"  WordPress version: {data.get('gmt_offset', 'unknown')}")

        # Check if slo namespace exists
        namespaces = data.get('namespaces', [])
        if 'slo/v1' in namespaces:
            print(f"  ✅ SEO & LLM Optimizer API detected")

        return True

    except Exception as e:
        print(f"  ❌ Failed: {e}")
        return False


def test_wordpress_auth(config):
    """Test WordPress authentication."""
    print("\nTesting WordPress authentication...")

    try:
        from requests.auth import HTTPBasicAuth

        url = f"{config['wordpress']['api_base']}/users/me"
        auth = HTTPBasicAuth(
            config['wordpress']['username'],
            config['wordpress']['password']
        )

        response = requests.get(url, auth=auth, timeout=10)
        response.raise_for_status()

        user = response.json()
        print(f"  ✅ Authentication successful")
        print(f"  Logged in as: {user.get('name', 'unknown')}")
        print(f"  User ID: {user.get('id', 'unknown')}")

        # Check capabilities
        caps = user.get('capabilities', {})
        if caps.get('edit_posts'):
            print(f"  ✅ Has edit_posts capability")
        else:
            print(f"  ⚠️  WARNING: Missing edit_posts capability")

        return True

    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 401:
            print(f"  ❌ Authentication failed (401 Unauthorized)")
            print(f"  Check username/password in config.json")
        else:
            print(f"  ❌ Failed: {e}")
        return False
    except Exception as e:
        print(f"  ❌ Failed: {e}")
        return False


def main():
    """Run all tests."""
    print("=" * 60)
    print("Ghost to WordPress Migration - Connection Test")
    print("=" * 60)

    try:
        config = load_config()
    except Exception as e:
        print(f"❌ Failed to load config.json: {e}")
        sys.exit(1)

    results = {
        'ghost_rss': test_ghost_rss(config),
        'wordpress_api': test_wordpress_api(config),
        'wordpress_auth': test_wordpress_auth(config),
    }

    print("\n" + "=" * 60)
    print("TEST RESULTS")
    print("=" * 60)

    all_passed = all(results.values())

    for test, passed in results.items():
        status = "✅ PASS" if passed else "❌ FAIL"
        print(f"{test:20s} {status}")

    print("=" * 60)

    if all_passed:
        print("✅ All tests passed! Ready to migrate.")
        print("\nTo start migration, run:")
        print("  python migrate.py")
        sys.exit(0)
    else:
        print("❌ Some tests failed. Fix issues before migrating.")
        sys.exit(1)


if __name__ == '__main__':
    main()
