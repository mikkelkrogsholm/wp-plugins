# REST API Quick Start Guide

Get started with the SEO & LLM Optimizer REST API in 5 minutes.

## Step 1: Enable the API

1. Log in to WordPress Admin
2. Go to **Settings > LLM Optimizer**
3. Check **"Enable REST API"**
4. Click **Save Changes**

## Step 2: Create Application Password

1. Go to **Users > Your Profile**
2. Scroll to **Application Passwords**
3. Enter name: "RAG System" or "API Access"
4. Click **Add New Application Password**
5. Copy the generated password (you won't see it again!)

## Step 3: Test the API

### Health Check (No Authentication)
```bash
curl https://your-site.com/wp-json/slo/v1/health
```

Expected response:
```json
{
  "status": "ok",
  "version": "1.0.0",
  "api_enabled": true
}
```

### Get Markdown (With Authentication)
```bash
curl -u "username:YOUR_APP_PASSWORD" \
  "https://your-site.com/wp-json/slo/v1/posts/1/markdown"
```

Replace:
- `username` with your WordPress username
- `YOUR_APP_PASSWORD` with the generated password
- `1` with an actual post ID

## Step 4: Choose Your Use Case

### Use Case 1: Simple Markdown Export

Export a post as clean markdown:

```bash
curl -u "username:password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/markdown" \
  | jq -r '.markdown' > post.md
```

### Use Case 2: RAG System (LangChain)

Get chunks ready for LangChain:

```bash
curl -u "username:password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/chunks?format=langchain" \
  > chunks.json
```

Python integration:
```python
import requests
from langchain.schema import Document

response = requests.get(
    'https://your-site.com/wp-json/slo/v1/posts/123/chunks',
    auth=('username', 'password'),
    params={'format': 'langchain'}
)

docs = [Document(**d) for d in response.json()['documents']]
```

### Use Case 3: Batch Processing

Export multiple posts at once:

```bash
curl -X POST \
  -u "username:password" \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [1, 2, 3, 4, 5]}' \
  https://your-site.com/wp-json/slo/v1/batch/markdown
```

## Common Parameters

### Markdown Endpoint
- `include_metadata=true` - Include YAML frontmatter
- `include_images=true` - Keep image markdown

### Chunks Endpoint
- `strategy=hierarchical` - Split by headers (default)
- `strategy=fixed` - Fixed-size chunks
- `strategy=semantic` - Split by paragraphs
- `format=universal` - Full metadata (default)
- `format=langchain` - LangChain format
- `format=llamaindex` - LlamaIndex format
- `chunk_size=512` - Target chunk size in tokens
- `overlap=128` - Overlap between chunks

## Error Handling

### Rate Limited (429)
```json
{
  "code": "rate_limit_exceeded",
  "message": "Rate limit exceeded. Please try again later."
}
```

**Solution**: Wait 1 hour or increase rate limit in settings.

### Not Found (404)
```json
{
  "code": "post_not_found",
  "message": "Post not found"
}
```

**Solution**: Verify post ID exists and is published.

### Unauthorized (401)
```json
{
  "code": "rest_forbidden",
  "message": "You must be logged in to access this endpoint"
}
```

**Solution**: Check username and application password.

## Python Example (Complete)

```python
#!/usr/bin/env python3
import requests
from requests.auth import HTTPBasicAuth

# Configuration
SITE_URL = "https://your-site.com"
USERNAME = "your-username"
APP_PASSWORD = "your-app-password"

# Create session
session = requests.Session()
session.auth = HTTPBasicAuth(USERNAME, APP_PASSWORD)

# Health check
health = session.get(f"{SITE_URL}/wp-json/slo/v1/health")
print(f"API Status: {health.json()['status']}")

# Get markdown
response = session.get(
    f"{SITE_URL}/wp-json/slo/v1/posts/123/markdown",
    params={"include_metadata": True}
)
markdown = response.json()['markdown']
print(f"Markdown length: {len(markdown)} characters")

# Get chunks for RAG
response = session.get(
    f"{SITE_URL}/wp-json/slo/v1/posts/123/chunks",
    params={
        "strategy": "hierarchical",
        "format": "langchain"
    }
)
chunks = response.json()['documents']
print(f"Generated {len(chunks)} chunks")

# Batch process
response = session.post(
    f"{SITE_URL}/wp-json/slo/v1/batch/markdown",
    json={"post_ids": [1, 2, 3, 4, 5]}
)
results = response.json()
print(f"Processed: {results['success_count']} posts")
```

## Node.js Example (Complete)

```javascript
const axios = require('axios');

const client = axios.create({
  baseURL: 'https://your-site.com/wp-json/slo/v1',
  auth: {
    username: 'your-username',
    password: 'your-app-password'
  }
});

// Health check
client.get('/health')
  .then(res => console.log('API Status:', res.data.status));

// Get markdown
client.get('/posts/123/markdown')
  .then(res => console.log('Markdown:', res.data.markdown));

// Get chunks
client.get('/posts/123/chunks', {
  params: {
    strategy: 'hierarchical',
    format: 'langchain'
  }
})
  .then(res => console.log('Chunks:', res.data.documents.length));

// Batch process
client.post('/batch/markdown', {
  post_ids: [1, 2, 3, 4, 5]
})
  .then(res => console.log('Processed:', res.data.success_count));
```

## Settings

Adjust these in **Settings > LLM Optimizer > Advanced Settings**:

- **Enable REST API**: Turn API on/off
- **Rate Limit**: Requests per hour (default: 60)
- **Cache Duration**: How long to cache results (default: 1 hour)

## Next Steps

1. Read full documentation: `REST_API_DOCUMENTATION.md`
2. Test with your RAG system
3. Adjust chunk size and strategy based on results
4. Monitor cache usage: `GET /slo/v1/cache/stats`
5. Set up automated exports

## Support

- Full docs: See `REST_API_DOCUMENTATION.md`
- Implementation details: See `REST_API_IMPLEMENTATION.json`
- Plugin settings: **Settings > LLM Optimizer**

## Security Reminders

- Always use HTTPS in production
- Never commit application passwords to git
- Rotate passwords regularly
- Use environment variables for credentials
- Monitor API usage via cache stats

---

Happy optimizing!
