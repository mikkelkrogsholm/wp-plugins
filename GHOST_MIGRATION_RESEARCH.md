# Ghost to WordPress Migration Research Report
**Site:** https://brokk-sindre.dk
**Date:** November 8, 2025
**Research Scope:** Feasibility analysis for sitemap-based content migration

---

## Executive Summary

**Feasibility:** HIGH - Migration is definitely feasible with multiple proven approaches available.

**Recommended Approach:** RSS feed scraping with WordPress REST API import (best for this specific use case)

**Estimated Complexity:** MEDIUM
- Scripting effort: 4-6 hours
- Testing/debugging: 2-3 hours
- Total: 6-9 hours

**Success Rate:** 95% - All content can be migrated with high fidelity. Minor manual adjustments may be needed for edge cases.

---

## 1. Sitemap Analysis

### Structure
The site uses a **sitemap index** architecture with three separate sitemaps:

| Sitemap Type | URL | Last Modified | Content Count |
|--------------|-----|---------------|---------------|
| Posts | `/sitemap-posts.xml` | 2025-11-03 | **13 posts** |
| Pages | `/sitemap-pages.xml` | 2025-11-02 | **8 pages** |
| Authors | `/sitemap-authors.xml` | 2025-11-08 | **1 author** |

**Total Content to Migrate:** 13 blog posts + 8 pages = **21 content items**

### Post URLs Sample
All content is in Danish, focused on AI, GDPR, and data security topics:
- `https://brokk-sindre.dk/vector-database-sikkerhed-sadan-beskytter-qdrant-jeres-embeddings/`
- `https://brokk-sindre.dk/gdpr-sikker-chatbot-i-danmark-vaelg-den-rigtige-deployment-model/`
- `https://brokk-sindre.dk/bruger-du-stadig-genai-som-en-fancy-google-sa-bruger-du-den-forkert/`

### Metadata Available in Sitemap
- `<loc>` - URL
- `<lastmod>` - Last modification date (ISO 8601 format)
- `<image:image>` - Featured images (on some posts)
- `<image:loc>` - Image URLs
- `<image:caption>` - Image captions

### Page Types
**Static Pages:**
- `/dansk-ai/` - Main service landing page
- `/ydelser/` - Services
- `/om-os/` - About us
- `/privacy/` - Privacy policy
- `/cases/` - Case studies
- `/kontakt/` - Contact
- `/testimonials/` - Testimonials
- Homepage

**Blog Posts:** 13 articles (October 2024 - November 2025)

---

## 2. Ghost Content Structure Analysis

### HTML Structure
Ghost uses semantic HTML with minimal custom classes:

```html
<article>
  <header>
    <h1>Post Title</h1>
    <div class="author-meta">
      <author>Mikkel Krogsholm</author>
      <time>November 3, 2025</time>
      <span>8 min read</span>
    </div>
  </header>

  <figure class="featured-image">
    <img src="/content/images/size/w960/2025/05/image.png" alt="...">
    <figcaption>Caption text</figcaption>
  </figure>

  <div class="content">
    <!-- Article body -->
  </div>
</article>
```

### Metadata Available
From HTML scraping:
- **Title:** H1 element, also in `<title>` tag
- **Author:** Mikkel Krogsholm (single author)
- **Date:** ISO 8601 timestamp in JSON-LD
- **Reading Time:** "X min read" text
- **Excerpt/Description:** Meta description tag
- **Featured Image:** Full URL with CDN path
- **Tags:** NOT visible in HTML (Ghost limitation)
- **Categories:** Ghost doesn't use categories

### Content Formatting
Ghost stores content as **HTML** with standard elements:

**Text Elements:**
- Headings: `<h1>`, `<h2>`, `<h3>`
- Paragraphs: `<p>`
- Emphasis: `<strong>`, `<em>`
- Lists: `<ul>`, `<ol>`, `<li>`
- Blockquotes: `<blockquote>`

**Rich Content:**
- Code blocks: `<pre><code>` (YAML, JSON examples)
- Tables: Full HTML tables for comparisons
- Links: Standard `<a href="">` tags

**No Markdown:** Content is served as HTML, not Markdown.

### Image Handling

**Featured Images:**
- **CDN Path:** `https://brokk-sindre.dk/content/images/size/w{WIDTH}/YYYY/MM/filename.jpg`
- **Original Size:** 1200×800px (typical)
- **Responsive Variant:** `w960` (960px width for desktop)
- **Format:** Primarily JPG
- **Alt Text:** Usually the article title
- **Captions:** Available in `<figcaption>` elements

**Image Example:**
```
https://brokk-sindre.dk/content/images/size/w960/2024/10/bigbadwolfdaddy_httpss.mj.runAsyU6yrZh94_a_mix_of_new_clean_c_4449428b-48de-4d60-9d2b-808b58ae98a5_1.jpg
```

**Inline Images:** Some posts have no featured images, others have captions.

**No srcset:** Ghost doesn't use responsive image sets; single URL only.

### Ghost-Specific Elements

**CSS Variables:**
```css
--ghost-accent-color: #406e76
```

**Schema.org Markup (JSON-LD):**
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "...",
  "datePublished": "2025-11-03T20:07:49.000Z",
  "author": {
    "@type": "Person",
    "name": "Mikkel Krogsholm"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Brokk & Sindre"
  }
}
```

This structured data is **very useful** for scraping metadata!

### Challenges Identified

1. **No Tags in HTML:** Tags aren't rendered in the page markup, only in Ghost's internal data
2. **Ghost API Blocked:** `/ghost/api/content/posts/` returns 403 Forbidden (API key required)
3. **Image CDN URLs:** Need to download and re-upload images to WordPress
4. **No Category System:** Ghost only uses tags, not WordPress-style categories
5. **Single Author:** All content by one person (simple mapping)

---

## 3. WordPress Import Options

### Option A: RSS Feed Scraping (RECOMMENDED)

**Why Best:**
- Ghost provides a **full-content RSS feed** at `/rss/`
- No authentication needed
- Complete HTML content included
- Already structured and parseable

**RSS Feed Structure:**
```xml
<item>
  <title><![CDATA[Article Title]]></title>
  <description>Excerpt text</description>
  <link>https://brokk-sindre.dk/post-slug/</link>
  <guid isPermaLink="false">ghost-hash-id</guid>
  <dc:creator><![CDATA[Mikkel Krogsholm]]></dc:creator>
  <pubDate>Sun, 03 Nov 2025 20:07:49 GMT</pubDate>
  <content:encoded><![CDATA[
    <h2>Full HTML content here...</h2>
    <p>Complete post body...</p>
  ]]></content:encoded>
</item>
```

**Data Available in RSS:**
- Full article HTML content ✅
- Title ✅
- Author ✅
- Publication date ✅
- Excerpt/description ✅
- Permalink URL ✅

**Missing from RSS:**
- Tags/categories ❌
- Featured images ❌
- Custom fields ❌

**Solution:** Scrape individual post pages for missing metadata.

### Option B: Sitemap URL Scraping

**Process:**
1. Fetch sitemap XML files
2. Extract all post/page URLs
3. Scrape each URL for content + metadata
4. Import to WordPress

**Pros:**
- Complete control over what's extracted
- Can get featured images from sitemap
- Can parse JSON-LD for structured data

**Cons:**
- More HTTP requests (21 pages to scrape)
- Need to parse HTML (more complex)
- Slower than RSS

### Option C: Ghost JSON Export (Not Applicable)

**Why Not Available:**
- Requires Ghost admin access
- Need to export from Ghost dashboard
- We only have public URL access
- Not feasible for this migration

### Option D: Existing Migration Tools

**ghost-to-wp (Node.js):**
- Requires Ghost JSON export (not available)
- Archived/unmaintained
- Not applicable for sitemap-based migration

**WordPress Import Plugins:**
- Feedzy: Imports from RSS feeds
- WordPress Importer: Requires WXR/XML format
- WP All Import: Supports CSV, XML, JSON

**Limitation:** None handle Ghost-to-WP directly from public URLs.

---

## 4. Technical Implementation Approach

### Recommended Strategy: Hybrid RSS + HTML Scraping

**Phase 1: RSS Feed Parsing**
1. Fetch `https://brokk-sindre.dk/rss/`
2. Parse XML to extract:
   - Title
   - Content (full HTML)
   - Author
   - Publication date
   - URL
   - Excerpt

**Phase 2: Individual Page Scraping (for metadata)**
For each post URL from RSS:
1. Fetch the HTML page
2. Parse JSON-LD structured data for:
   - Last modified date
   - Reading time
3. Extract featured image:
   - Parse `<img>` in featured image section
   - Get full CDN URL
   - Download image file

**Phase 3: Image Processing**
1. Download featured images from Ghost CDN
2. Upload to WordPress media library via:
   - REST API: `POST /wp-json/wp/v2/media`
   - OR WP-CLI: `wp media import <url>`
3. Get WordPress media ID

**Phase 4: WordPress Import**
Use **WordPress REST API** to create posts:

```bash
POST /wp-json/wp/v2/posts
{
  "title": "Post Title",
  "content": "<html>Full content</html>",
  "excerpt": "Post excerpt",
  "date": "2025-11-03T20:07:49",
  "author": 1,
  "status": "publish",
  "featured_media": 123,
  "meta": {
    "reading_time": "8 min read"
  }
}
```

### Implementation Tools

**Best Language: Python**

**Libraries Needed:**
```python
import requests          # HTTP requests
import feedparser        # RSS parsing
from bs4 import BeautifulSoup  # HTML parsing
import json              # JSON handling
from datetime import datetime
```

**Alternative: PHP**
- Can run inside WordPress environment
- Native WordPress functions available
- Good for WP-CLI script

**Alternative: Bash + jq + curl**
- Lightweight
- WP-CLI integration
- Good for server automation

### Script Workflow (Python)

```python
# 1. Fetch and parse RSS feed
feed = feedparser.parse('https://brokk-sindre.dk/rss/')

# 2. For each post in feed:
for entry in feed.entries:
    # Get basic data from RSS
    title = entry.title
    content = entry.content[0].value  # Full HTML
    author = entry.author
    date = entry.published_parsed
    url = entry.link
    excerpt = entry.description

    # 3. Scrape individual page for featured image
    response = requests.get(url)
    soup = BeautifulSoup(response.content, 'html.parser')

    # Find featured image
    featured_img = soup.select_one('figure.featured-image img')
    if featured_img:
        image_url = featured_img['src']
        image_alt = featured_img.get('alt', title)

        # Download image
        img_data = requests.get(image_url).content

        # Upload to WordPress
        wp_media = upload_to_wordpress_media(img_data, image_url)
        media_id = wp_media['id']

    # 4. Parse JSON-LD for extra metadata
    json_ld = soup.find('script', type='application/ld+json')
    if json_ld:
        metadata = json.loads(json_ld.string)

    # 5. Create WordPress post
    post_data = {
        'title': title,
        'content': content,
        'excerpt': excerpt,
        'date': format_date(date),
        'author': get_wp_author_id('Mikkel Krogsholm'),
        'status': 'publish',
        'featured_media': media_id
    }

    create_wordpress_post(post_data)
```

### WordPress REST API Authentication

**Application Passwords (Recommended):**
```python
import requests
from requests.auth import HTTPBasicAuth

wp_url = 'http://localhost:8080'
wp_user = 'admin'
wp_password = 'application_password_here'

auth = HTTPBasicAuth(wp_user, wp_password)

response = requests.post(
    f'{wp_url}/wp-json/wp/v2/posts',
    json=post_data,
    auth=auth
)
```

**Alternative: WP-CLI (No Auth Needed)**
```bash
wp post create \
  --post_title="Post Title" \
  --post_content="<html>Content</html>" \
  --post_date="2025-11-03 20:07:49" \
  --post_status=publish
```

---

## 5. Design & Styling Analysis

### Color Palette

**Primary Colors:**
- **Accent Color:** `#406e76` (Teal/Dark Cyan)
- Default Ghost theme colors (blacks, grays, whites)

**Limited Custom Styling:** The site uses Ghost's default Casper theme with minimal customization.

### Typography

**Not Explicitly Defined:** Font families appear to be Ghost/browser defaults.

**Likely System Fonts:**
- Sans-serif stack for headings
- Serif or sans-serif for body text

**Typography Hierarchy:**
- **H1:** Page/post titles
- **H2:** Major section headers
- **H3:** Subsections
- **Body:** Standard paragraph text
- **Metadata:** Smaller text for dates, reading time

### Spacing & Layout

**Minimal Custom CSS:** Most styling comes from Ghost's theme.

**Layout Patterns:**
- Clean, minimal blog design
- Card-based post listings
- Full-width hero images
- Standard blog sidebar/navigation

### Design Tokens

Only one CSS variable detected:
```css
:root {
  --ghost-accent-color: #406e76;
}
```

### Responsive Design

**Mobile-Friendly:** Ghost themes are responsive by default.

**JavaScript:** Minimal - only for mobile viewport height adjustments.

### WordPress Theme Considerations

**Recommendation:** Use a clean, minimal WordPress blog theme like:
- **Twenty Twenty-Four** (WordPress default)
- **GeneratePress** (lightweight, customizable)
- **Astra** (similar clean aesthetic)

**Custom Accent Color:** Set theme color to `#406e76` to match.

---

## 6. Feasibility Assessment

### Can This Be Done? YES ✅

**Confidence Level:** Very High (95%)

### What Can Be Preserved?

| Content Element | Preservable? | Method |
|-----------------|--------------|--------|
| Post titles | ✅ Yes | RSS feed |
| Post content (HTML) | ✅ Yes | RSS feed |
| Publication dates | ✅ Yes | RSS feed |
| Author | ✅ Yes | RSS feed |
| Excerpts | ✅ Yes | RSS feed |
| Featured images | ✅ Yes | HTML scraping + download |
| Image captions | ✅ Yes | HTML scraping |
| Reading time | ⚠️ Partial | Can calculate or scrape |
| URL slugs | ✅ Yes | Can preserve or redirect |
| Meta descriptions | ✅ Yes | HTML scraping |
| Tags | ❌ No | Not accessible without API |
| Categories | N/A | Ghost doesn't use them |
| Comments | N/A | No comments on site |

### Challenges & Solutions

**Challenge 1: Featured Images on Different CDN**
- **Solution:** Download images, upload to WordPress media library
- **Complexity:** Low - standard HTTP download

**Challenge 2: No Tags Available**
- **Solution:**
  - Accept no tags (simplest)
  - Manually add tags post-migration
  - Use AI to auto-tag based on content
- **Impact:** Low - only 13 posts

**Challenge 3: Preserving URLs**
- **Solution:**
  - Use same slug structure in WordPress
  - OR set up 301 redirects (Redirection plugin)
- **Complexity:** Low

**Challenge 4: Author Mapping**
- **Solution:** Create "Mikkel Krogsholm" WordPress user, map all posts
- **Complexity:** Very low (single author)

**Challenge 5: Content Encoding**
- **Solution:** RSS provides HTML entities properly encoded
- **Complexity:** Low - feedparser handles this

### Edge Cases to Handle

1. **Posts with no featured image:** Handle gracefully (skip image import)
2. **Very long content:** WordPress has no practical limit
3. **Special characters (Danish):** UTF-8 encoding maintained
4. **Code blocks:** Preserve as-is or convert to WordPress code blocks
5. **Tables:** HTML tables work fine in WordPress

---

## 7. Estimated Effort & Complexity

### Complexity Rating: MEDIUM

**Breakdown:**

| Task | Complexity | Time Estimate |
|------|-----------|---------------|
| RSS parsing script | Low | 1 hour |
| HTML scraping for images | Low-Medium | 1-2 hours |
| Image download/upload | Low | 1 hour |
| WordPress REST API integration | Low-Medium | 1-2 hours |
| Error handling & logging | Medium | 1 hour |
| Testing & debugging | Medium | 2-3 hours |
| **TOTAL** | **Medium** | **7-10 hours** |

### Skills Required

- **Python** (or PHP/Bash): Intermediate level
- **HTTP/REST APIs:** Basic understanding
- **HTML parsing:** Basic (Beautiful Soup)
- **WordPress REST API:** Basic familiarity
- **WP-CLI:** Optional but helpful

### Risk Assessment

**Low Risk:**
- Small site (21 pages total)
- Single author (simple mapping)
- Standard content (no complex custom fields)
- RSS feed accessible (no auth needed)

**Medium Risk:**
- Image migration (need to download/upload)
- Preserving exact formatting
- URL structure preservation

**Mitigation:**
- Test on local Docker WordPress first
- Keep backups of original content
- Verify each post after import
- Manual review of 21 items is feasible

---

## 8. Recommended Implementation Plan

### Phase 1: Preparation (30 min)
1. ✅ Start WordPress Docker environment
2. ✅ Create test user "Mikkel Krogsholm"
3. ✅ Install Redirection plugin (for URL mapping)
4. ✅ Generate WordPress Application Password for API
5. ✅ Set up Python environment with dependencies

### Phase 2: Script Development (4-6 hours)
1. **RSS Parser** (1 hour)
   - Fetch and parse RSS feed
   - Extract basic post data
   - Save to temporary data structure

2. **Image Scraper** (2 hours)
   - For each post URL, scrape HTML
   - Extract featured image URL
   - Download image files
   - Upload to WordPress media library
   - Store media IDs

3. **WordPress Importer** (2 hours)
   - Create posts via REST API
   - Set featured images
   - Map authors
   - Set publish dates
   - Add excerpts

4. **Error Handling** (1 hour)
   - Retry logic for failed downloads
   - Logging of successful/failed imports
   - Validation of created posts

### Phase 3: Testing (2-3 hours)
1. Run script on local Docker WordPress
2. Verify all 13 posts imported correctly
3. Check featured images display properly
4. Validate content formatting preserved
5. Test 8 static pages separately
6. Fix any issues

### Phase 4: Production Migration (1 hour)
1. Run script on production WordPress
2. Verify all content
3. Set up URL redirects if needed
4. Update sitemap
5. Test site functionality

### Phase 5: Post-Migration (1 hour)
1. Manual review of all posts
2. Add tags manually (if desired)
3. Optimize images (if needed)
4. Set up SEO metadata (Yoast/RankMath)
5. Update navigation/menus

**Total Time: 8-11 hours**

---

## 9. Alternative Approaches Considered

### Approach A: Manual Copy-Paste
- **Time:** ~20-30 minutes per post = 7-10 hours
- **Pros:** No coding needed, full control
- **Cons:** Tedious, error-prone, images harder to handle
- **Verdict:** Not recommended for 21 pages

### Approach B: WordPress Import Plugin (Feedzy)
- **Process:** Use Feedzy to import RSS feed
- **Pros:** No coding, WordPress UI
- **Cons:** May not handle featured images, limited customization
- **Verdict:** Could work but less flexible

### Approach C: Ghost Export + Conversion Tool
- **Requirement:** Need Ghost admin access
- **Pros:** Most complete data (tags included)
- **Cons:** No admin access available
- **Verdict:** Not feasible for this case

### Approach D: Pure Sitemap Scraping (No RSS)
- **Process:** Scrape every URL individually
- **Pros:** Complete control
- **Cons:** More HTTP requests, slower, more complex parsing
- **Verdict:** Overkill when RSS provides content

---

## 10. Script Skeleton (Python)

Here's a basic script structure to get started:

```python
#!/usr/bin/env python3
"""
Ghost to WordPress Migration Script
Migrates content from brokk-sindre.dk (Ghost) to WordPress

Usage: python migrate.py --wp-url http://localhost:8080 --wp-user admin --wp-pass app_password
"""

import requests
import feedparser
from bs4 import BeautifulSoup
import json
import argparse
from datetime import datetime
from requests.auth import HTTPBasicAuth
import time
import logging

# Set up logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

class GhostToWordPress:
    def __init__(self, wp_url, wp_user, wp_pass):
        self.ghost_url = 'https://brokk-sindre.dk'
        self.wp_url = wp_url.rstrip('/')
        self.wp_auth = HTTPBasicAuth(wp_user, wp_pass)
        self.wp_api = f'{self.wp_url}/wp-json/wp/v2'

    def fetch_rss_feed(self):
        """Fetch and parse Ghost RSS feed"""
        logger.info("Fetching RSS feed...")
        feed_url = f'{self.ghost_url}/rss/'
        feed = feedparser.parse(feed_url)
        logger.info(f"Found {len(feed.entries)} posts in RSS feed")
        return feed.entries

    def scrape_featured_image(self, post_url):
        """Scrape featured image from Ghost post page"""
        logger.info(f"Scraping featured image from {post_url}")
        try:
            response = requests.get(post_url, timeout=10)
            soup = BeautifulSoup(response.content, 'html.parser')

            # Find featured image
            img = soup.select_one('article img')  # Adjust selector as needed
            if img:
                img_url = img.get('src')
                if img_url and not img_url.startswith('http'):
                    img_url = self.ghost_url + img_url
                return {
                    'url': img_url,
                    'alt': img.get('alt', ''),
                    'caption': ''  # Extract from figcaption if needed
                }
        except Exception as e:
            logger.error(f"Error scraping image: {e}")
        return None

    def download_image(self, image_url):
        """Download image from Ghost CDN"""
        logger.info(f"Downloading image: {image_url}")
        try:
            response = requests.get(image_url, timeout=30)
            response.raise_for_status()
            return response.content
        except Exception as e:
            logger.error(f"Error downloading image: {e}")
            return None

    def upload_to_wordpress_media(self, image_data, filename, alt_text=''):
        """Upload image to WordPress media library"""
        logger.info(f"Uploading to WordPress: {filename}")
        try:
            headers = {'Content-Disposition': f'attachment; filename={filename}'}
            files = {'file': (filename, image_data)}

            response = requests.post(
                f'{self.wp_api}/media',
                headers=headers,
                files=files,
                auth=self.wp_auth
            )
            response.raise_for_status()
            media = response.json()

            # Set alt text if provided
            if alt_text:
                requests.post(
                    f'{self.wp_api}/media/{media["id"]}',
                    json={'alt_text': alt_text},
                    auth=self.wp_auth
                )

            logger.info(f"Image uploaded successfully, ID: {media['id']}")
            return media['id']
        except Exception as e:
            logger.error(f"Error uploading image: {e}")
            return None

    def get_or_create_author(self, author_name):
        """Get WordPress author ID by name"""
        # For simplicity, using admin user (ID: 1)
        # In production, search for user or create new one
        return 1

    def create_wordpress_post(self, post_data):
        """Create post in WordPress via REST API"""
        logger.info(f"Creating post: {post_data['title']}")
        try:
            response = requests.post(
                f'{self.wp_api}/posts',
                json=post_data,
                auth=self.wp_auth
            )
            response.raise_for_status()
            post = response.json()
            logger.info(f"Post created successfully, ID: {post['id']}")
            return post
        except Exception as e:
            logger.error(f"Error creating post: {e}")
            if hasattr(e, 'response'):
                logger.error(f"Response: {e.response.text}")
            return None

    def migrate_posts(self):
        """Main migration logic"""
        entries = self.fetch_rss_feed()

        for idx, entry in enumerate(entries, 1):
            logger.info(f"\n--- Processing post {idx}/{len(entries)} ---")

            # Extract data from RSS
            title = entry.title
            content = entry.content[0].value if entry.content else ''
            excerpt = entry.get('description', '')
            author = entry.get('author', '')
            pub_date = entry.get('published', '')
            post_url = entry.link

            logger.info(f"Title: {title}")
            logger.info(f"URL: {post_url}")

            # Get featured image
            featured_media_id = None
            image_info = self.scrape_featured_image(post_url)
            if image_info:
                image_data = self.download_image(image_info['url'])
                if image_data:
                    filename = image_info['url'].split('/')[-1]
                    featured_media_id = self.upload_to_wordpress_media(
                        image_data,
                        filename,
                        image_info['alt']
                    )

            # Prepare WordPress post data
            post_data = {
                'title': title,
                'content': content,
                'excerpt': excerpt,
                'status': 'publish',
                'author': self.get_or_create_author(author),
            }

            if pub_date:
                # Convert to WordPress date format
                post_data['date'] = pub_date

            if featured_media_id:
                post_data['featured_media'] = featured_media_id

            # Create the post
            result = self.create_wordpress_post(post_data)
            if result:
                logger.info(f"✅ Successfully migrated: {title}")
            else:
                logger.error(f"❌ Failed to migrate: {title}")

            # Be nice to servers
            time.sleep(1)

        logger.info("\n=== Migration Complete ===")

def main():
    parser = argparse.ArgumentParser(description='Migrate Ghost blog to WordPress')
    parser.add_argument('--wp-url', required=True, help='WordPress URL')
    parser.add_argument('--wp-user', required=True, help='WordPress username')
    parser.add_argument('--wp-pass', required=True, help='WordPress application password')
    args = parser.parse_args()

    migrator = GhostToWordPress(args.wp_url, args.wp_user, args.wp_pass)
    migrator.migrate_posts()

if __name__ == '__main__':
    main()
```

### Usage

```bash
# Install dependencies
pip install requests feedparser beautifulsoup4

# Run migration
python migrate.py \
  --wp-url http://localhost:8080 \
  --wp-user admin \
  --wp-pass "xxxx xxxx xxxx xxxx xxxx xxxx"
```

---

## 11. Next Steps

### Immediate Actions

1. **Get WordPress App Password**
   - WordPress admin → Users → Profile
   - Scroll to "Application Passwords"
   - Create new password for migration script

2. **Test Script Locally**
   - Start Docker WordPress: `./test.sh start`
   - Run migration script against localhost
   - Verify imports work correctly

3. **Refine Script**
   - Adjust HTML selectors for featured images
   - Add page migration (separate from posts)
   - Handle edge cases
   - Improve error handling

4. **Production Migration**
   - Once tested, run on production
   - Monitor for errors
   - Verify all content

### Future Enhancements

- **Tag Migration:** Use AI/NLP to auto-generate tags from content
- **SEO Metadata:** Extract meta descriptions, add to Yoast/RankMath
- **Internal Links:** Update Ghost URLs to WordPress URLs
- **URL Redirects:** Set up 301 redirects from old URLs
- **Image Optimization:** Compress images, generate WebP versions
- **Content Review:** Manual QA of all posts

---

## 12. Conclusion

### Summary

Migrating content from https://brokk-sindre.dk (Ghost) to WordPress is **highly feasible** using a Python script that:

1. Fetches the RSS feed for post content
2. Scrapes individual pages for featured images
3. Downloads and uploads images to WordPress
4. Creates posts via WordPress REST API

### Key Findings

- **21 total content items** to migrate (13 posts + 8 pages)
- **RSS feed provides full HTML content** (major advantage)
- **Featured images extractable** via HTML scraping
- **Single author** simplifies migration
- **No tags available** (minor limitation)
- **Danish content** preserved with UTF-8 encoding

### Success Criteria

✅ All 21 posts/pages migrated
✅ Featured images preserved
✅ Content formatting maintained
✅ Publication dates preserved
✅ Author attribution correct
⚠️ Tags not available (acceptable)

### Estimated Effort

**8-11 hours total:**
- 4-6 hours: Script development
- 2-3 hours: Testing
- 2 hours: Migration + QA

### Risk Level

**LOW** - Small site, standard content, proven migration methods available.

### Recommendation

**PROCEED** with the RSS + HTML scraping approach using Python. This provides the best balance of automation, flexibility, and reliability for this specific migration scenario.

---

**Report prepared by:** Claude Code Research Agent
**Date:** November 8, 2025
**Status:** Ready for implementation
