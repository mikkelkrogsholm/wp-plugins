# Quick Start Guide - SEO & LLM Optimizer

Get up and running in 5 minutes!

## 1. Start Docker Environment

```bash
# Navigate to the plugin directory
cd /Users/mikkelfreltoftkrogsholm/Projekter/wp-plugins/seo-llm-optimizer

# Start WordPress + MySQL
docker-compose up -d

# Wait 30 seconds for services to start
```

## 2. Install WordPress

1. Open browser: **http://localhost:8080**
2. Select language: **English**
3. Fill in details:
   - Site Title: `SEO LLM Test`
   - Username: `admin`
   - Password: `admin` (or auto-generated)
   - Email: `test@example.com`
4. Click **Install WordPress**
5. Click **Log In**

## 3. Activate Plugin

1. Go to **Plugins** → **Installed Plugins**
2. Find **SEO & LLM Optimizer**
3. Click **Activate**
4. Verify: **Settings** → **LLM Optimizer** appears

## 4. Configure Settings

1. Go to **Settings** → **LLM Optimizer**
2. Recommended settings:
   - ✅ Enable Frontend Button
   - ✅ Enable REST API
   - Enabled Post Types: **Posts** + **Pages**
   - Button Visibility: **Everyone**
   - Chunking Strategy: **Hierarchical**
   - Chunk Size: **512** tokens
   - Chunk Overlap: **128** tokens
3. Click **Save Changes**

## 5. Create Test Post

1. Go to **Posts** → **Add New**
2. Title: `AI and Machine Learning Guide`
3. Add content:

```markdown
# Introduction to AI

Artificial Intelligence is transforming technology.

## Machine Learning

ML algorithms learn from data patterns.

### Supervised Learning
- Classification
- Regression

### Unsupervised Learning
- Clustering
- Dimensionality reduction

## Conclusion

AI will continue to evolve and impact our lives.
```

4. Click **Publish**
5. Click **View Post**

## 6. Test Frontend Button

1. On the published post, look for the **"Copy for AI"** button
2. Click the button
3. Modal opens with 3 tabs:
   - **Quick Copy**: Full markdown with metadata
   - **Format Options**: LangChain, LlamaIndex, Universal formats
   - **RAG Chunks**: Generate chunks for AI/LLM
4. Click **"Copy to Clipboard"** on any tab
5. Paste into text editor to verify

## 7. Test Export Formats

### Quick Copy Tab
- Click **"Copy to Clipboard"**
- Verify YAML frontmatter + markdown content

### Format Options Tab
- Try **Standard Markdown** (toggle metadata on/off)
- Try **LangChain Format** (JSON with page_content + metadata)
- Try **LlamaIndex Format** (JSON with text + metadata)
- Try **Universal Format** (JSON with content + metadata)

### RAG Chunks Tab
- Select **Hierarchical** strategy
- Click **"Generate Chunks"**
- Verify chunks appear (split by headings)
- Try other strategies: **Fixed Size**, **Semantic**
- Try export formats: **Universal**, **LangChain**, **LlamaIndex**
- Click **"Copy All Chunks"**

## 8. Test REST API

### Get Application Password

1. Go to **Users** → **Profile**
2. Scroll to **"Application Passwords"**
3. Name: `API Test`
4. Click **"Add New Application Password"**
5. Copy password: `xxxx xxxx xxxx xxxx xxxx xxxx`

### Test Health Endpoint

```bash
curl http://localhost:8080/wp-json/slo/v1/health
```

Expected:
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "timestamp": "2025-11-08T..."
}
```

### Get Post Markdown

Replace `123` with your post ID and use your Application Password:

```bash
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  http://localhost:8080/wp-json/slo/v1/posts/123/markdown
```

### Get Chunks

```bash
curl -u admin:xxxx-xxxx-xxxx-xxxx-xxxx-xxxx \
  "http://localhost:8080/wp-json/slo/v1/posts/123/chunks?strategy=hierarchical&format=universal"
```

## 9. Verify All Features Work

- [x] Plugin activates without errors
- [x] Settings page loads and saves
- [x] Frontend button appears on posts
- [x] Modal opens and displays content
- [x] All 3 tabs work (Quick Copy, Format Options, RAG Chunks)
- [x] Copying to clipboard works
- [x] All export formats work
- [x] All chunking strategies work
- [x] REST API endpoints respond
- [x] Authentication works
- [x] Rate limiting works (try 61 requests)

## 10. What to Test

### Gutenberg Blocks
Create posts with:
- Headings (H1-H6)
- Paragraphs
- Lists (ordered/unordered)
- Code blocks
- Quotes
- Images
- Tables

### Classic Editor
Install Classic Editor plugin and test HTML to Markdown conversion.

### Different Content Types
- Short posts (< 500 words)
- Medium posts (500-2000 words)
- Long posts (> 2000 words)
- Posts with code
- Posts with lists
- Posts with tables

### Chunking Strategies

**Hierarchical** (splits by headings):
```
Chunk 1: # Introduction
Chunk 2: ## Machine Learning
Chunk 3: ### Supervised Learning
Chunk 4: ### Unsupervised Learning
Chunk 5: ## Conclusion
```

**Fixed Size** (equal-sized chunks):
```
Chunk 1: 512 tokens
Chunk 2: 512 tokens
Chunk 3: 512 tokens
...
```

**Semantic** (splits by paragraphs):
```
Chunk 1: Paragraph 1 + 2
Chunk 2: Paragraph 3 + 4
Chunk 3: Paragraph 5 + 6
...
```

### Export Formats

**Standard Markdown**:
```markdown
---
title: "Post Title"
date: "2025-11-08"
---

# Content here
```

**LangChain**:
```json
{
  "page_content": "markdown content",
  "metadata": { ... }
}
```

**LlamaIndex**:
```json
{
  "text": "markdown content",
  "metadata": { ... }
}
```

**Universal**:
```json
{
  "content": "markdown content",
  "metadata": { ... },
  "chunks": [ ... ]
}
```

## Troubleshooting

### Plugin not appearing
```bash
docker cp . slo-wordpress:/var/www/html/wp-content/plugins/seo-llm-optimizer/
docker exec slo-wordpress chown -R www-data:www-data /var/www/html/wp-content/plugins/seo-llm-optimizer
```

### Button not showing
1. Check Settings → LLM Optimizer
2. Verify "Enable Frontend Button" is checked
3. Verify "Posts" is in "Enabled Post Types"
4. Clear browser cache (Cmd+Shift+R)

### Modal not opening
1. Open browser console (F12)
2. Look for JavaScript errors
3. Check if `seoLlmData` exists: `console.log(seoLlmData)`

### API returns 403
1. Check Settings → "Enable REST API" is checked
2. Verify Application Password is correct
3. Use `-u username:password` in curl

### Docker issues
```bash
# Restart all containers
docker-compose restart

# View logs
docker logs slo-wordpress
docker logs slo-mysql

# Stop everything
docker-compose down

# Start fresh
docker-compose up -d
```

## Cleanup

When done testing:

```bash
# Stop containers (keeps data)
docker-compose down

# Remove everything including data
docker-compose down -v
```

## Next Steps

- Read **TESTING_GUIDE.md** for comprehensive testing procedures
- Read **USER_GUIDE.md** for detailed feature documentation
- Read **DEVELOPER_GUIDE.md** for technical implementation details
- Read **REST_API_DOCUMENTATION.md** for API integration examples

## Support

For issues or questions:
1. Check **TROUBLESHOOTING.md**
2. Review browser console for errors
3. Check WordPress debug log
4. Review Docker logs

---

**You're now ready to test the SEO & LLM Optimizer plugin!**

Happy testing! 🚀
