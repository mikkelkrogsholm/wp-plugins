# SEO & LLM Optimizer REST API Documentation

Version: 1.0.0
Namespace: `slo/v1`

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Endpoints](#endpoints)
  - [Health Check](#health-check)
  - [Get Markdown](#get-markdown)
  - [Get Chunks](#get-chunks)
  - [Batch Markdown](#batch-markdown)
  - [Batch Chunks](#batch-chunks)
  - [Cache Statistics](#cache-statistics)
  - [Clear Post Cache](#clear-post-cache)
  - [Clear All Cache](#clear-all-cache)
- [Response Formats](#response-formats)
- [Error Handling](#error-handling)
- [Examples](#examples)

---

## Overview

The SEO & LLM Optimizer REST API provides programmatic access to convert WordPress posts into markdown format and generate semantic chunks suitable for RAG (Retrieval Augmented Generation) systems.

**Base URL**: `https://your-site.com/wp-json/slo/v1`

**Features**:
- Markdown conversion with metadata
- Multiple chunking strategies (hierarchical, fixed, semantic)
- Multiple export formats (Universal, LangChain, LlamaIndex)
- Batch processing
- Cache management
- Rate limiting

---

## Authentication

The API supports WordPress authentication methods:

1. **Cookie Authentication** (for logged-in users)
2. **Application Passwords** (recommended for external applications)
3. **Basic Authentication** (requires plugin)

### Enabling the API

The REST API must be enabled in plugin settings:

1. Go to **Settings > LLM Optimizer**
2. Enable **"Enable REST API"** option
3. Configure rate limit (default: 60 requests/hour)

### Application Passwords (Recommended)

```bash
# Generate application password in WordPress admin
# User Profile > Application Passwords

# Use with cURL
curl -u "username:APPLICATION_PASSWORD" \
  https://your-site.com/wp-json/slo/v1/health
```

---

## Rate Limiting

Rate limits are enforced per client (IP address or user ID):

- **Default**: 60 requests per hour
- **Configurable**: 1-1000 requests per hour (in settings)
- **Tracking**: Uses WordPress transients
- **Response**: HTTP 429 when exceeded

**Rate Limit Headers** (custom implementation):
- Rate limits are tracked server-side
- Check remaining requests by monitoring 429 responses

---

## Endpoints

### Health Check

Check API status and availability.

**Endpoint**: `GET /slo/v1/health`
**Permission**: Public (no authentication required)

**Request**:
```bash
curl https://your-site.com/wp-json/slo/v1/health
```

**Response** (200 OK):
```json
{
  "status": "ok",
  "version": "1.0.0",
  "wordpress_version": "6.8.0",
  "api_enabled": true,
  "cache_enabled": true,
  "timestamp": "2025-01-15T10:30:00+00:00",
  "endpoints": {
    "markdown": "https://your-site.com/wp-json/slo/v1/posts/{id}/markdown",
    "chunks": "https://your-site.com/wp-json/slo/v1/posts/{id}/chunks",
    "batch": "https://your-site.com/wp-json/slo/v1/batch/markdown"
  }
}
```

---

### Get Markdown

Convert a single post to markdown format.

**Endpoint**: `GET /slo/v1/posts/{id}/markdown`
**Permission**: Logged-in users with `read` capability

**Parameters**:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | required | Post ID |
| `include_metadata` | boolean | `true` | Include YAML frontmatter |
| `include_images` | boolean | `true` | Preserve image markdown |

**Request**:
```bash
curl -u "username:password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/markdown?include_metadata=true"
```

**Response** (200 OK):
```json
{
  "post_id": 123,
  "markdown": "---\ntitle: \"Sample Post\"\ndate: \"2025-01-15 10:00:00\"\nauthor: \"John Doe\"\nurl: \"https://your-site.com/sample-post/\"\ncategories:\n  - \"Technology\"\ntags:\n  - \"WordPress\"\n  - \"API\"\n---\n\n# Introduction\n\nThis is the post content...",
  "post_title": "Sample Post",
  "post_url": "https://your-site.com/sample-post/",
  "post_type": "post"
}
```

**Error Responses**:
- `401` - Not authenticated
- `403` - Post not accessible (draft/private)
- `404` - Post not found
- `429` - Rate limit exceeded
- `500` - Conversion failed

---

### Get Chunks

Generate semantic chunks from a post.

**Endpoint**: `GET /slo/v1/posts/{id}/chunks`
**Permission**: Logged-in users with `read` capability

**Parameters**:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | required | Post ID |
| `strategy` | string | `hierarchical` | Chunking strategy: `hierarchical`, `fixed`, `semantic` |
| `format` | string | `universal` | Output format: `universal`, `langchain`, `llamaindex` |
| `chunk_size` | integer | `512` | Target chunk size in tokens |
| `overlap` | integer | `128` | Overlap size in tokens (fixed strategy only) |

**Chunking Strategies**:

1. **Hierarchical**: Splits by markdown headers (H1-H6), preserves document structure
2. **Fixed**: Fixed-size chunks with sentence-boundary overlap
3. **Semantic**: Paragraph-based chunking, keeps related content together

**Request**:
```bash
curl -u "username:password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/chunks?strategy=hierarchical&format=universal"
```

**Response** (200 OK) - Universal Format:
```json
{
  "format_version": "1.0",
  "export_info": {
    "exported_at": "2025-01-15T10:30:00+00:00",
    "plugin_version": "1.0.0",
    "wordpress_version": "6.8.0",
    "format": "universal"
  },
  "source_document": {
    "id": 123,
    "type": "post",
    "url": "https://your-site.com/sample-post/",
    "title": "Sample Post",
    "total_chunks": 5,
    "date": "2025-01-15 10:00:00",
    "modified": "2025-01-15 12:00:00"
  },
  "chunks": [
    {
      "content": "# Introduction\n\nThis is the introduction section...",
      "metadata": {
        "post_id": 123,
        "chunk_index": 0,
        "total_chunks": 5,
        "source_type": "post",
        "title": "Sample Post",
        "url": "https://your-site.com/sample-post/",
        "section_title": "Introduction",
        "heading_level": 1,
        "token_count": 150,
        "char_count": 600,
        "chunking_strategy": "hierarchical",
        "categories": ["Technology"],
        "tags": ["WordPress", "API"]
      }
    }
  ]
}
```

**Response** - LangChain Format:
```json
{
  "documents": [
    {
      "page_content": "# Introduction\n\nThis is the introduction...",
      "metadata": {
        "post_id": 123,
        "chunk_index": 0,
        "title": "Sample Post",
        "url": "https://your-site.com/sample-post/"
      }
    }
  ],
  "export_metadata": {
    "exported_at": "2025-01-15 10:30:00",
    "plugin_version": "1.0.0",
    "total_documents": 5,
    "format": "langchain"
  }
}
```

**Response** - LlamaIndex Format:
```json
{
  "documents": [
    {
      "text": "# Introduction\n\nThis is the introduction...",
      "metadata": {
        "post_id": 123,
        "chunk_index": 0,
        "title": "Sample Post"
      },
      "id_": "post_123_chunk_0",
      "embedding": null
    }
  ],
  "export_metadata": {
    "exported_at": "2025-01-15 10:30:00",
    "plugin_version": "1.0.0",
    "total_documents": 5,
    "format": "llamaindex"
  }
}
```

---

### Batch Markdown

Convert multiple posts to markdown in a single request.

**Endpoint**: `POST /slo/v1/batch/markdown`
**Permission**: Logged-in users with `read` capability
**Limit**: Maximum 50 posts per request

**Request Body**:
```json
{
  "post_ids": [123, 456, 789],
  "include_metadata": true
}
```

**Request**:
```bash
curl -X POST \
  -u "username:password" \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 456, 789], "include_metadata": true}' \
  https://your-site.com/wp-json/slo/v1/batch/markdown
```

**Response** (200 OK):
```json
{
  "success_count": 2,
  "error_count": 1,
  "results": [
    {
      "post_id": 123,
      "markdown": "---\ntitle: \"Post 1\"\n---\n\nContent...",
      "post_title": "Post 1",
      "post_url": "https://your-site.com/post-1/"
    },
    {
      "post_id": 456,
      "markdown": "---\ntitle: \"Post 2\"\n---\n\nContent...",
      "post_title": "Post 2",
      "post_url": "https://your-site.com/post-2/"
    }
  ],
  "errors": [
    {
      "post_id": 789,
      "error": "Post not found"
    }
  ]
}
```

---

### Batch Chunks

Generate chunks for multiple posts in a single request.

**Endpoint**: `POST /slo/v1/batch/chunks`
**Permission**: Logged-in users with `read` capability
**Limit**: Maximum 20 posts per request

**Request Body**:
```json
{
  "post_ids": [123, 456],
  "strategy": "hierarchical",
  "format": "universal"
}
```

**Request**:
```bash
curl -X POST \
  -u "username:password" \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 456], "strategy": "hierarchical", "format": "universal"}' \
  https://your-site.com/wp-json/slo/v1/batch/chunks
```

**Response** (200 OK):
```json
{
  "success_count": 2,
  "error_count": 0,
  "results": [
    {
      "format_version": "1.0",
      "source_document": {
        "id": 123,
        "title": "Post 1"
      },
      "chunks": [...]
    },
    {
      "format_version": "1.0",
      "source_document": {
        "id": 456,
        "title": "Post 2"
      },
      "chunks": [...]
    }
  ],
  "errors": []
}
```

---

### Cache Statistics

Get cache statistics and information.

**Endpoint**: `GET /slo/v1/cache/stats`
**Permission**: Administrators with `manage_options` capability

**Request**:
```bash
curl -u "admin:password" \
  https://your-site.com/wp-json/slo/v1/cache/stats
```

**Response** (200 OK):
```json
{
  "cache_enabled": true,
  "cache_count": 42,
  "cache_duration": 3600,
  "sample_keys": [
    "slo_chunks_123_hierarchical_512_128",
    "slo_chunks_456_fixed_512_128",
    "slo_markdown_789"
  ],
  "cache_group": "seo_llm_optimizer"
}
```

---

### Clear Post Cache

Clear all cached data for a specific post.

**Endpoint**: `DELETE /slo/v1/cache/{post_id}`
**Permission**: Administrators with `manage_options` capability

**Request**:
```bash
curl -X DELETE \
  -u "admin:password" \
  https://your-site.com/wp-json/slo/v1/cache/123
```

**Response** (200 OK):
```json
{
  "message": "Cache cleared for post 123",
  "post_id": 123
}
```

---

### Clear All Cache

Clear all plugin caches.

**Endpoint**: `DELETE /slo/v1/cache`
**Permission**: Administrators with `manage_options` capability

**Request**:
```bash
curl -X DELETE \
  -u "admin:password" \
  https://your-site.com/wp-json/slo/v1/cache
```

**Response** (200 OK):
```json
{
  "message": "All caches cleared successfully",
  "deleted_count": 42
}
```

---

## Response Formats

### Universal Format

Best for custom implementations or new RAG systems.

**Features**:
- Comprehensive metadata
- Export information
- Source document details
- Full chunk metadata

**Use Case**: When you need maximum flexibility and all available information.

### LangChain Format

Optimized for Python LangChain library.

**Features**:
- `page_content` field for document text
- `metadata` dict with essential information
- Compatible with LangChain Document objects

**Use Case**: Direct integration with LangChain document loaders.

**Python Example**:
```python
from langchain.schema import Document

response = requests.get('https://your-site.com/wp-json/slo/v1/posts/123/chunks?format=langchain')
data = response.json()

documents = [
    Document(page_content=doc['page_content'], metadata=doc['metadata'])
    for doc in data['documents']
]
```

### LlamaIndex Format

Optimized for Python LlamaIndex library.

**Features**:
- `text` field for document content
- `id_` field for unique document identifier
- `metadata` dict
- `embedding` field (null by default)

**Use Case**: Direct integration with LlamaIndex document readers.

**Python Example**:
```python
from llama_index.schema import Document

response = requests.get('https://your-site.com/wp-json/slo/v1/posts/123/chunks?format=llamaindex')
data = response.json()

documents = [
    Document(text=doc['text'], doc_id=doc['id_'], metadata=doc['metadata'])
    for doc in data['documents']
]
```

---

## Error Handling

### Error Response Format

```json
{
  "code": "error_code",
  "message": "Human-readable error message",
  "data": {
    "status": 400
  }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `rest_disabled` | 403 | REST API is disabled in plugin settings |
| `rest_forbidden` | 403 | User lacks required permissions |
| `rest_cookie_invalid_nonce` | 403 | Invalid authentication nonce |
| `rate_limit_exceeded` | 429 | Too many requests, try again later |
| `post_not_found` | 404 | Requested post does not exist |
| `post_not_accessible` | 403 | Post is not published or accessible |
| `batch_too_large` | 400 | Batch size exceeds maximum limit |
| `conversion_failed` | 500 | Error during markdown conversion |
| `chunking_failed` | 500 | Error during chunk generation |

---

## Examples

### Example 1: Get Markdown for a Post

```bash
#!/bin/bash

# Configuration
SITE_URL="https://your-site.com"
USERNAME="your-username"
PASSWORD="your-app-password"
POST_ID=123

# Get markdown
curl -u "$USERNAME:$PASSWORD" \
  "$SITE_URL/wp-json/slo/v1/posts/$POST_ID/markdown?include_metadata=true" \
  | jq '.markdown' > post-$POST_ID.md

echo "Markdown saved to post-$POST_ID.md"
```

### Example 2: Batch Export to LangChain

```python
import requests
from requests.auth import HTTPBasicAuth
from langchain.schema import Document

# Configuration
SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"

# Get chunks for multiple posts
response = requests.post(
    f"{SITE_URL}/wp-json/slo/v1/batch/chunks",
    auth=HTTPBasicAuth(USERNAME, PASSWORD),
    json={
        "post_ids": [123, 456, 789],
        "strategy": "hierarchical",
        "format": "langchain"
    }
)

data = response.json()

# Convert to LangChain documents
all_documents = []
for result in data['results']:
    documents = [
        Document(
            page_content=doc['page_content'],
            metadata=doc['metadata']
        )
        for doc in result['documents']
    ]
    all_documents.extend(documents)

print(f"Loaded {len(all_documents)} documents from {data['success_count']} posts")
```

### Example 3: Export All Posts with Error Handling

```python
import requests
from requests.auth import HTTPBasicAuth
import json

SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"

def get_all_post_ids():
    """Fetch all published post IDs using WordPress REST API"""
    response = requests.get(
        f"{SITE_URL}/wp-json/wp/v2/posts",
        params={"per_page": 100, "fields": "id"}
    )
    return [post['id'] for post in response.json()]

def batch_export_chunks(post_ids, batch_size=20):
    """Export chunks for posts in batches"""
    results = []

    for i in range(0, len(post_ids), batch_size):
        batch = post_ids[i:i + batch_size]

        try:
            response = requests.post(
                f"{SITE_URL}/wp-json/slo/v1/batch/chunks",
                auth=HTTPBasicAuth(USERNAME, PASSWORD),
                json={
                    "post_ids": batch,
                    "strategy": "hierarchical",
                    "format": "universal"
                },
                timeout=60
            )

            if response.status_code == 200:
                data = response.json()
                results.extend(data['results'])
                print(f"Exported batch {i//batch_size + 1}: {data['success_count']} success, {data['error_count']} errors")
            elif response.status_code == 429:
                print("Rate limit exceeded. Waiting...")
                time.sleep(3600)  # Wait 1 hour
            else:
                print(f"Error: {response.status_code} - {response.text}")

        except requests.exceptions.RequestException as e:
            print(f"Request failed: {e}")

    return results

# Execute export
post_ids = get_all_post_ids()
print(f"Found {len(post_ids)} posts")

chunks = batch_export_chunks(post_ids)
print(f"Exported {len(chunks)} post chunks")

# Save to file
with open('wordpress_chunks.json', 'w') as f:
    json.dump(chunks, f, indent=2)
```

### Example 4: Monitor Cache Usage

```bash
#!/bin/bash

# Configuration
SITE_URL="https://your-site.com"
USERNAME="admin-username"
PASSWORD="admin-app-password"

# Get cache statistics
curl -u "$USERNAME:$PASSWORD" \
  "$SITE_URL/wp-json/slo/v1/cache/stats" \
  | jq '{
      cache_count: .cache_count,
      cache_duration_hours: (.cache_duration / 3600),
      sample_keys: .sample_keys
    }'

# Clear cache if needed
# curl -X DELETE -u "$USERNAME:$PASSWORD" "$SITE_URL/wp-json/slo/v1/cache"
```

### Example 5: JavaScript/Node.js Integration

```javascript
const axios = require('axios');

class SLOClient {
  constructor(siteUrl, username, password) {
    this.client = axios.create({
      baseURL: `${siteUrl}/wp-json/slo/v1`,
      auth: { username, password },
      headers: { 'Content-Type': 'application/json' }
    });
  }

  async getMarkdown(postId, options = {}) {
    const response = await this.client.get(`/posts/${postId}/markdown`, {
      params: {
        include_metadata: options.includeMetadata ?? true,
        include_images: options.includeImages ?? true
      }
    });
    return response.data;
  }

  async getChunks(postId, options = {}) {
    const response = await this.client.get(`/posts/${postId}/chunks`, {
      params: {
        strategy: options.strategy ?? 'hierarchical',
        format: options.format ?? 'universal',
        chunk_size: options.chunkSize ?? 512,
        overlap: options.overlap ?? 128
      }
    });
    return response.data;
  }

  async batchMarkdown(postIds, includeMetadata = true) {
    const response = await this.client.post('/batch/markdown', {
      post_ids: postIds,
      include_metadata: includeMetadata
    });
    return response.data;
  }

  async health() {
    const response = await this.client.get('/health');
    return response.data;
  }
}

// Usage
const client = new SLOClient(
  'https://your-site.com',
  'username',
  'app-password'
);

// Get markdown
client.getMarkdown(123)
  .then(data => console.log(data.markdown))
  .catch(err => console.error(err.response.data));

// Get chunks
client.getChunks(123, { strategy: 'semantic', format: 'langchain' })
  .then(data => console.log(`Got ${data.documents.length} chunks`))
  .catch(err => console.error(err.response.data));
```

---

## Best Practices

### 1. Use Application Passwords

Don't use your main WordPress password. Create application-specific passwords:
- WordPress Admin > Users > Your Profile > Application Passwords
- Name it (e.g., "RAG System")
- Save the generated password securely

### 2. Respect Rate Limits

- Default is 60 requests/hour
- Implement exponential backoff for 429 responses
- Use batch endpoints when possible

### 3. Cache Results

- API responses are cached by the plugin
- Cache your own results on the client side
- Use appropriate cache invalidation strategies

### 4. Handle Errors Gracefully

- Always check HTTP status codes
- Parse error messages from response body
- Implement retry logic with backoff

### 5. Use Appropriate Formats

- **Universal**: Maximum flexibility, custom systems
- **LangChain**: Python LangChain integration
- **LlamaIndex**: Python LlamaIndex integration

### 6. Optimize Batch Sizes

- Markdown: Up to 50 posts per request
- Chunks: Up to 20 posts per request
- Start smaller and increase based on server capacity

### 7. Monitor Cache Usage

- Use `/cache/stats` endpoint regularly
- Clear caches when content updates
- Adjust cache duration based on update frequency

---

## Security Considerations

1. **HTTPS Only**: Always use HTTPS in production
2. **Strong Passwords**: Use WordPress application passwords
3. **Permission Checks**: API respects WordPress capabilities
4. **Rate Limiting**: Prevents abuse
5. **Input Validation**: All inputs are sanitized
6. **Output Escaping**: Markdown is sanitized during generation

---

## Troubleshooting

### API Returns 403 (Forbidden)

**Cause**: REST API is disabled in settings
**Solution**: Go to Settings > LLM Optimizer and enable "Enable REST API"

### Rate Limit Exceeded (429)

**Cause**: Too many requests in the time window
**Solution**: Wait for the limit to reset (1 hour) or adjust rate limit in settings

### Authentication Fails

**Cause**: Invalid credentials or authentication method
**Solution**:
- Verify username and application password
- Check authentication header format
- Ensure user has appropriate capabilities

### Empty or Incomplete Results

**Cause**: Post content processing error
**Solution**:
- Check WordPress error logs
- Verify post is published
- Check for shortcodes or complex content

### Slow Response Times

**Cause**: First request or cache miss
**Solution**:
- Subsequent requests will be faster (cached)
- Adjust cache duration in settings
- Use batch endpoints for multiple posts

---

## Support

For issues, feature requests, or questions:
- GitHub: https://github.com/mikkelkrogsholm/wp-plugins
- Plugin Settings: Settings > LLM Optimizer

---

## Common Use Cases

### Use Case 1: Building a Documentation Site

Export all WordPress posts to markdown files for a static site generator (Hugo, Jekyll, etc.):

```python
import requests
import os
from requests.auth import HTTPBasicAuth

SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"
OUTPUT_DIR = "./docs"

# Create output directory
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Get all post IDs from WordPress
response = requests.get(
    f"{SITE_URL}/wp-json/wp/v2/posts",
    params={"per_page": 100, "fields": "id,slug"}
)
posts = response.json()

# Export each post
for post in posts:
    response = requests.get(
        f"{SITE_URL}/wp-json/slo/v1/posts/{post['id']}/markdown",
        auth=HTTPBasicAuth(USERNAME, PASSWORD),
        params={"include_metadata": True}
    )

    if response.status_code == 200:
        data = response.json()
        filename = f"{OUTPUT_DIR}/{post['slug']}.md"
        with open(filename, 'w') as f:
            f.write(data['markdown'])
        print(f"Exported: {filename}")
    else:
        print(f"Failed: {post['id']}")
```

### Use Case 2: Populating a Vector Database

Export chunks and generate embeddings for semantic search:

```python
import requests
import openai
from requests.auth import HTTPBasicAuth

SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"
OPENAI_KEY = "your-openai-key"

openai.api_key = OPENAI_KEY

def get_embeddings(text):
    """Get embeddings from OpenAI"""
    response = openai.Embedding.create(
        model="text-embedding-ada-002",
        input=text
    )
    return response['data'][0]['embedding']

def process_post(post_id):
    """Get chunks and generate embeddings"""
    # Get chunks from WordPress
    response = requests.get(
        f"{SITE_URL}/wp-json/slo/v1/posts/{post_id}/chunks",
        auth=HTTPBasicAuth(USERNAME, PASSWORD),
        params={
            "strategy": "hierarchical",
            "format": "universal",
            "chunk_size": 512
        }
    )

    if response.status_code != 200:
        return None

    data = response.json()
    embedded_chunks = []

    # Generate embeddings for each chunk
    for chunk in data['chunks']:
        embedding = get_embeddings(chunk['content'])
        embedded_chunks.append({
            'id': f"post_{post_id}_chunk_{chunk['chunk_index']}",
            'content': chunk['content'],
            'embedding': embedding,
            'metadata': chunk['metadata']
        })

    return embedded_chunks

# Process posts
post_ids = [1, 2, 3, 4, 5]  # Your post IDs
all_chunks = []

for post_id in post_ids:
    print(f"Processing post {post_id}...")
    chunks = process_post(post_id)
    if chunks:
        all_chunks.extend(chunks)

print(f"Total chunks with embeddings: {len(all_chunks)}")

# Now store in your vector database (Pinecone, Weaviate, etc.)
```

### Use Case 3: Content Analysis Pipeline

Analyze all content for insights using AI:

```python
import requests
from requests.auth import HTTPBasicAuth
from openai import OpenAI

SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"

client = OpenAI(api_key="your-openai-key")

def analyze_content(markdown):
    """Analyze content with GPT"""
    response = client.chat.completions.create(
        model="gpt-4",
        messages=[
            {"role": "system", "content": "Analyze this content for SEO quality, readability, and improvement suggestions."},
            {"role": "user", "content": markdown}
        ]
    )
    return response.choices[0].message.content

def batch_analyze_posts(post_ids):
    """Analyze multiple posts"""
    # Get markdown for all posts
    response = requests.post(
        f"{SITE_URL}/wp-json/slo/v1/batch/markdown",
        auth=HTTPBasicAuth(USERNAME, PASSWORD),
        json={"post_ids": post_ids, "include_metadata": True}
    )

    if response.status_code != 200:
        return None

    data = response.json()
    analyses = []

    for result in data['results']:
        print(f"Analyzing post {result['post_id']}...")
        analysis = analyze_content(result['markdown'])
        analyses.append({
            'post_id': result['post_id'],
            'title': result['post_title'],
            'analysis': analysis
        })

    return analyses

# Run analysis
post_ids = [1, 2, 3, 4, 5]
results = batch_analyze_posts(post_ids)

for result in results:
    print(f"\n=== {result['title']} ===")
    print(result['analysis'])
```

### Use Case 4: Real-Time Content Sync

Keep an external system in sync with WordPress content:

```python
import requests
import time
from requests.auth import HTTPBasicAuth

SITE_URL = "https://your-site.com"
USERNAME = "your-username"
PASSWORD = "your-app-password"
CHECK_INTERVAL = 300  # Check every 5 minutes

processed_posts = set()

def sync_new_content():
    """Check for new/updated posts and sync them"""
    # Get recently modified posts
    response = requests.get(
        f"{SITE_URL}/wp-json/wp/v2/posts",
        params={
            "per_page": 10,
            "orderby": "modified",
            "order": "desc"
        }
    )

    posts = response.json()

    for post in posts:
        post_id = post['id']
        modified = post['modified']

        # Check if we've processed this version
        cache_key = f"{post_id}_{modified}"
        if cache_key in processed_posts:
            continue

        print(f"Syncing post {post_id}...")

        # Get chunks
        response = requests.get(
            f"{SITE_URL}/wp-json/slo/v1/posts/{post_id}/chunks",
            auth=HTTPBasicAuth(USERNAME, PASSWORD),
            params={
                "strategy": "hierarchical",
                "format": "langchain"
            }
        )

        if response.status_code == 200:
            data = response.json()
            # Update your external system here
            # update_vector_database(data)
            processed_posts.add(cache_key)
            print(f"Synced post {post_id}")

while True:
    sync_new_content()
    time.sleep(CHECK_INTERVAL)
```

## Rate Limiting Details

### Understanding Rate Limits

The API implements sliding window rate limiting:

**Default Configuration**:
- 60 requests per hour per client
- Tracked by IP address (anonymous) or User ID (authenticated)
- Window slides continuously (not reset at fixed times)

**Tracking**:
```
Request at 10:00 - Count: 1
Request at 10:15 - Count: 2
Request at 10:30 - Count: 3
...
Request at 11:01 - Count: Still includes requests from 10:01 onwards
```

### Rate Limit Response

When rate limit is exceeded, you receive:

```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit exceeded. Try again later.",
  "data": {
    "status": 429
  }
}
```

**HTTP Headers** (custom implementation):
- No rate limit headers in response (WordPress limitation)
- Track requests on your end
- Implement exponential backoff

### Handling Rate Limits

**Best Practices**:

```python
import time
import requests
from requests.auth import HTTPBasicAuth

class RateLimitedAPI:
    def __init__(self, site_url, username, password):
        self.site_url = site_url
        self.auth = HTTPBasicAuth(username, password)
        self.request_count = 0
        self.window_start = time.time()
        self.rate_limit = 60  # requests per hour

    def make_request(self, endpoint, **kwargs):
        # Check if we need to wait
        elapsed = time.time() - self.window_start
        if elapsed < 3600 and self.request_count >= self.rate_limit:
            wait_time = 3600 - elapsed
            print(f"Rate limit reached. Waiting {wait_time:.0f}s...")
            time.sleep(wait_time)
            self.request_count = 0
            self.window_start = time.time()

        # Make request
        response = requests.get(
            f"{self.site_url}/wp-json/slo/v1/{endpoint}",
            auth=self.auth,
            **kwargs
        )

        # Handle rate limit response
        if response.status_code == 429:
            print("Rate limited by server. Waiting 1 hour...")
            time.sleep(3600)
            return self.make_request(endpoint, **kwargs)

        # Track request
        self.request_count += 1
        return response

# Usage
api = RateLimitedAPI("https://site.com", "user", "pass")
response = api.make_request("posts/123/markdown")
```

**Exponential Backoff**:

```python
import time
import requests

def make_request_with_backoff(url, auth, max_retries=5):
    """Make request with exponential backoff on rate limit"""
    for attempt in range(max_retries):
        response = requests.get(url, auth=auth)

        if response.status_code == 429:
            # Exponential backoff: 1s, 2s, 4s, 8s, 16s
            wait_time = 2 ** attempt
            print(f"Rate limited. Retry in {wait_time}s...")
            time.sleep(wait_time)
            continue

        return response

    raise Exception("Max retries exceeded")
```

## Error Handling Guide

### Comprehensive Error Handling

```python
import requests
from requests.auth import HTTPBasicAuth

class APIError(Exception):
    """Base exception for API errors"""
    pass

class RateLimitError(APIError):
    """Rate limit exceeded"""
    pass

class AuthenticationError(APIError):
    """Authentication failed"""
    pass

class NotFoundError(APIError):
    """Resource not found"""
    pass

class ServerError(APIError):
    """Server-side error"""
    pass

def make_safe_request(url, auth):
    """Make request with comprehensive error handling"""
    try:
        response = requests.get(url, auth=auth, timeout=30)

        # Handle different status codes
        if response.status_code == 200:
            return response.json()

        elif response.status_code == 401 or response.status_code == 403:
            raise AuthenticationError(
                f"Authentication failed: {response.json().get('message', 'Unknown error')}"
            )

        elif response.status_code == 404:
            raise NotFoundError(
                f"Resource not found: {response.json().get('message', 'Unknown error')}"
            )

        elif response.status_code == 429:
            raise RateLimitError(
                "Rate limit exceeded. Wait before retrying."
            )

        elif response.status_code >= 500:
            raise ServerError(
                f"Server error ({response.status_code}): {response.text}"
            )

        else:
            raise APIError(
                f"Unexpected error ({response.status_code}): {response.text}"
            )

    except requests.exceptions.Timeout:
        raise APIError("Request timed out")

    except requests.exceptions.ConnectionError:
        raise APIError("Connection failed")

    except requests.exceptions.RequestException as e:
        raise APIError(f"Request failed: {str(e)}")

# Usage with error handling
try:
    data = make_safe_request(
        "https://site.com/wp-json/slo/v1/posts/123/markdown",
        HTTPBasicAuth("user", "pass")
    )
    print("Success:", data['markdown'][:100])

except RateLimitError as e:
    print(f"Rate limit: {e}")
    # Wait and retry

except AuthenticationError as e:
    print(f"Auth error: {e}")
    # Check credentials

except NotFoundError as e:
    print(f"Not found: {e}")
    # Handle missing resource

except ServerError as e:
    print(f"Server error: {e}")
    # Retry or alert

except APIError as e:
    print(f"API error: {e}")
    # General error handling
```

## Changelog

### Version 1.0.0
- Initial REST API release
- Markdown conversion endpoints
- Chunking with multiple strategies
- Batch processing support
- Cache management
- Rate limiting
- LangChain and LlamaIndex format support
