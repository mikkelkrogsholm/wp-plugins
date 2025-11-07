# SEO & LLM Optimizer for WordPress

A comprehensive WordPress plugin that optimizes your content for both search engines and Large Language Model (LLM) systems. Transform your WordPress posts into clean, semantic content suitable for AI training, RAG systems, and next-generation search.

## Features

### Content Conversion
- **Markdown Export** - Clean markdown conversion with YAML frontmatter metadata
- **Smart Content Cleaning** - Removes WordPress cruft while preserving semantic structure
- **Gutenberg & Classic Editor Support** - Works with both block and classic content

### RAG-Ready Chunking
- **3 Chunking Strategies**:
  - **Hierarchical**: Splits by markdown headers, preserves document structure
  - **Fixed Size**: Fixed-size chunks with sentence-boundary overlap
  - **Semantic**: Paragraph-based chunking keeps related content together
- **Configurable chunk sizes** (128-2048 tokens)
- **Smart overlap** for context preservation

### Frontend Interface
- **One-Click Copy Button** - Accessible "Copy for AI" button on posts/pages
- **Interactive Modal** - User-friendly interface with 3 tabs:
  - Quick Copy: Instant markdown copy
  - Format Options: Choose markdown settings
  - RAG Chunks: View and copy individual chunks
- **Mobile Responsive** - Works seamlessly on all devices
- **WCAG 2.1 AA Compliant** - Fully accessible interface

### REST API
- **8 Comprehensive Endpoints** for programmatic access
- **Multiple Export Formats**:
  - Universal (standard JSON format)
  - LangChain (Python library compatible)
  - LlamaIndex (direct integration support)
- **Batch Processing** - Convert up to 50 posts at once
- **Rate Limiting** - Configurable protection (default: 60 req/hour)
- **Application Password Support** - Secure authentication

### Performance & Caching
- **Intelligent Caching** - WordPress transient-based caching
- **Configurable Cache Duration** - Optimize for your needs
- **Cache Management API** - Clear caches via REST or admin

### Security
- **Nonce Verification** - All AJAX requests protected
- **Input Sanitization** - Comprehensive data validation
- **Output Escaping** - XSS prevention
- **Capability Checks** - Proper permission enforcement
- **Rate Limiting** - Abuse prevention

## Requirements

- **WordPress**: 6.4 or higher
- **PHP**: 7.4 or higher
- **Composer**: For dependency management (development)

## Installation

### From GitHub

1. **Download or clone** this repository:
   ```bash
   git clone https://github.com/mikkelkrogsholm/wp-plugins.git
   cd wp-plugins/seo-llm-optimizer
   ```

2. **Install dependencies**:
   ```bash
   composer install --no-dev
   ```

3. **Upload to WordPress**:
   - Upload the entire `seo-llm-optimizer` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

4. **Activate the plugin**:
   - Go to WordPress Admin > Plugins
   - Find "SEO & LLM Optimizer"
   - Click "Activate"

### Via WP-CLI

```bash
wp plugin install /path/to/seo-llm-optimizer.zip --activate
```

## Quick Start

### For End Users

1. **Activate the plugin** in WordPress Admin > Plugins

2. **Configure settings** (optional):
   - Go to Settings > LLM Optimizer
   - Adjust chunk size, cache duration, and features

3. **View any post or page** - You'll see a "Copy for AI" button

4. **Click the button** to open the modal:
   - **Quick Copy tab**: Instant markdown copy
   - **Format Options tab**: Customize export settings
   - **RAG Chunks tab**: View and copy individual chunks

5. **Paste into your AI tool** (ChatGPT, Claude, etc.)

### For Developers

**Get markdown via REST API:**
```bash
curl -u "username:app-password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/markdown"
```

**Get chunks for RAG system:**
```bash
curl -u "username:app-password" \
  "https://your-site.com/wp-json/slo/v1/posts/123/chunks?strategy=hierarchical&format=langchain"
```

**Batch export multiple posts:**
```bash
curl -X POST \
  -u "username:app-password" \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 456, 789], "strategy": "hierarchical"}' \
  https://your-site.com/wp-json/slo/v1/batch/chunks
```

## Configuration

### Settings Location
**WordPress Admin > Settings > LLM Optimizer**

### Available Settings

#### Feature Settings Tab
- **Enable Frontend Button** - Show/hide the "Copy for AI" button
- **Enabled Post Types** - Select which post types support optimization
- **Button Visibility** - Control who sees the button (all users, logged in only)
- **Enable REST API** - Toggle REST API access

#### Export Options Tab
- **Default Chunk Size** - Target size for content chunks (128-2048 tokens)
- **Chunk Overlap** - Overlap between chunks for context (0-512 tokens)
- **Default Chunking Strategy** - Choose default strategy (hierarchical, fixed, semantic)
- **Include Metadata** - Add YAML frontmatter to exports

#### Advanced Tab
- **Cache Duration** - How long to cache processed content (seconds)
- **Rate Limit** - API requests per hour (1-1000)
- **Enable Caching** - Toggle caching system
- **Clear Cache** - Manual cache clearing button

## Documentation

### 📚 User Documentation
- **[User Guide](docs/user/USER_GUIDE.md)** - Complete guide for end users
- **[Quick Start](docs/user/QUICK_START.md)** - Get started in 5 minutes
- **[Installation](docs/user/INSTALLATION.md)** - Detailed installation instructions
- **[Troubleshooting](docs/user/TROUBLESHOOTING.md)** - Common issues and solutions

### 🔧 Developer Documentation
- **[Developer Guide](docs/developer/DEVELOPER_GUIDE.md)** - Technical documentation
- **[Code Structure](docs/developer/STRUCTURE.md)** - File organization and architecture
- **[Contributing](docs/developer/CONTRIBUTING.md)** - How to contribute
- **[Code of Conduct](docs/developer/CODE_OF_CONDUCT.md)** - Community guidelines

### 🔌 API Documentation
- **[REST API Reference](docs/api/REST_API_DOCUMENTATION.md)** - Complete API reference
- **[API Quick Start](docs/api/REST_API_QUICK_START.md)** - Get started with the API

### ⚙️ Implementation Guides
- **[Chunking Engine](docs/implementation/CHUNKING_ENGINE_IMPLEMENTATION.md)** - Chunking strategies
- **[Chunking Quick Start](docs/implementation/CHUNKING_QUICK_START.md)** - Quick chunking guide

### 🧪 Testing
- **[Testing Guide](docs/testing/TESTING_GUIDE.md)** - Comprehensive testing procedures
- **[Testing Setup](docs/testing/TESTING_NOTE.md)** - Docker testing environment
- **[Test Content Samples](docs/testing/test-content-samples.md)** - Sample posts for testing

### 📋 Meta
- **[Changelog](docs/meta/CHANGELOG.md)** - Version history and updates
- **[Security Policy](docs/meta/SECURITY.md)** - Security and vulnerability reporting

## Use Cases

### 1. Content Analysis with AI
Copy post content directly into ChatGPT, Claude, or other LLMs for:
- Content improvement suggestions
- SEO analysis
- Tone and style evaluation
- Translation assistance

### 2. RAG System Integration
Export your WordPress content for Retrieval Augmented Generation:
- Build custom AI assistants trained on your content
- Semantic search over your blog posts
- Question-answering systems
- Knowledge base integration

### 3. Training Data Preparation
Clean, structured content for:
- Fine-tuning language models
- Creating training datasets
- Content embeddings
- Vector database population

### 4. Content Archiving
Export clean markdown versions:
- Future-proof content format
- Version control friendly
- Platform-independent backups
- Content migration

### 5. Multi-Platform Publishing
- Export to static site generators (Hugo, Jekyll)
- Import to other CMSs
- Newsletter content preparation
- Documentation generation

## Examples

### Frontend Button Usage

1. Navigate to any published post
2. Find the floating "Copy for AI" button (bottom right)
3. Click to open modal
4. Choose your format and copy
5. Paste into your AI tool

### Python Integration (LangChain)

```python
import requests
from requests.auth import HTTPBasicAuth
from langchain.schema import Document

# Fetch chunks from WordPress
response = requests.get(
    "https://your-site.com/wp-json/slo/v1/posts/123/chunks",
    auth=HTTPBasicAuth("username", "app-password"),
    params={
        "strategy": "hierarchical",
        "format": "langchain"
    }
)

data = response.json()

# Convert to LangChain documents
documents = [
    Document(
        page_content=doc['page_content'],
        metadata=doc['metadata']
    )
    for doc in data['documents']
]

# Use in your RAG pipeline
from langchain.vectorstores import FAISS
from langchain.embeddings import OpenAIEmbeddings

vectorstore = FAISS.from_documents(documents, OpenAIEmbeddings())
```

### JavaScript/Node.js Integration

```javascript
const axios = require('axios');

async function fetchWordPressContent(postId) {
  const response = await axios.get(
    `https://your-site.com/wp-json/slo/v1/posts/${postId}/markdown`,
    {
      auth: {
        username: 'your-username',
        password: 'your-app-password'
      },
      params: {
        include_metadata: true
      }
    }
  );

  return response.data.markdown;
}

// Use the markdown
fetchWordPressContent(123)
  .then(markdown => console.log(markdown))
  .catch(err => console.error(err));
```

### Bulk Export Script

```bash
#!/bin/bash

# Export all posts to markdown files

SITE_URL="https://your-site.com"
USERNAME="your-username"
PASSWORD="your-app-password"

# Get all post IDs
POST_IDS=$(curl -s "$SITE_URL/wp-json/wp/v2/posts?per_page=100&fields=id" | jq -r '.[].id')

# Export each post
for POST_ID in $POST_IDS; do
  echo "Exporting post $POST_ID..."
  curl -s -u "$USERNAME:$PASSWORD" \
    "$SITE_URL/wp-json/slo/v1/posts/$POST_ID/markdown" \
    | jq -r '.markdown' > "post-$POST_ID.md"
done

echo "Export complete!"
```

## Architecture

### Core Components

- **SLO_Content_Processor** - Markdown conversion and content processing
- **SLO_Chunking_Engine** - Content chunking with multiple strategies
- **SLO_Cache_Manager** - Caching system for performance
- **SLO_REST_API** - RESTful API endpoints
- **SLO_Frontend_Button** - Frontend UI and modal
- **SLO_Admin_Settings** - Admin settings page

### File Structure

```
seo-llm-optimizer/
├── seo-llm-optimizer.php        # Main plugin file
├── uninstall.php                 # Cleanup on uninstall
├── composer.json                 # Composer dependencies
├── includes/                     # Core classes
│   ├── class-content-processor.php
│   ├── class-content-cleaner.php
│   ├── class-chunking-engine.php
│   ├── class-cache-manager.php
│   ├── class-rest-api.php
│   ├── class-frontend-button.php
│   ├── class-modal-handler.php
│   └── class-admin-settings.php
├── admin/                        # Admin-specific classes
│   └── class-meta-boxes.php
├── assets/                       # Frontend assets
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       ├── frontend.js
│       └── admin.js
├── templates/                    # PHP templates
│   ├── frontend/
│   │   └── modal.php
│   └── admin/
│       └── settings-page.php
├── languages/                    # Translation files
└── vendor/                       # Composer dependencies
```

## Performance

- **Caching**: All processed content is cached (default: 1 hour)
- **On-Demand Processing**: Content only processed when requested
- **Rate Limiting**: Prevents API abuse (60 req/hour default)
- **Efficient Chunking**: Optimized algorithms for large content
- **Minimal Database Impact**: Uses WordPress transients

## Accessibility

- **WCAG 2.1 AA Compliant**
- Keyboard navigation support
- Screen reader compatible
- Focus management
- Proper ARIA labels
- High contrast mode support

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Internationalization

The plugin is translation-ready with:
- Text domain: `seo-llm-optimizer`
- POT file included in `/languages/`
- All strings wrapped in translation functions

To translate:
1. Use a tool like Poedit
2. Open `/languages/seo-llm-optimizer.pot`
3. Create translations
4. Save as `seo-llm-optimizer-{locale}.mo`

## Support

### Getting Help

- **Documentation**: Start with the [User Guide](USER_GUIDE.md)
- **Troubleshooting**: Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **GitHub Issues**: [Report bugs or request features](https://github.com/mikkelkrogsholm/wp-plugins/issues)

### Reporting Bugs

When reporting bugs, please include:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs actual behavior
- Any error messages

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

### Current Version: 1.0.0

**Release Date**: 2025-11-07

**Initial Release Features**:
- Markdown conversion with YAML frontmatter
- Three chunking strategies (hierarchical, fixed, semantic)
- Frontend copy button with modal interface
- REST API with 8 endpoints
- Multiple export formats (Universal, LangChain, LlamaIndex)
- Admin settings page
- Caching system
- Rate limiting
- Full accessibility support

## License

This plugin is licensed under the **GNU General Public License v2 or later**.

```
SEO & LLM Optimizer
Copyright (C) 2025 Mikkel Krogsholm

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

Full license: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## Credits

### Author
**Mikkel Krogsholm**
- GitHub: [@mikkelkrogsholm](https://github.com/mikkelkrogsholm)

### Dependencies
- [league/html-to-markdown](https://github.com/thephpleague/html-to-markdown) - HTML to Markdown conversion

### Inspiration
This plugin was created to bridge the gap between traditional WordPress content management and modern AI systems, making WordPress content AI-ready for the next generation of search and knowledge systems.

## Roadmap

### Planned Features

**Version 1.1**
- WP-CLI commands for bulk operations
- Advanced token estimation (multiple models)
- Custom export templates
- Webhook support for real-time updates

**Version 1.2**
- Additional vector database formats (Pinecone, Weaviate, Milvus)
- Embeddings generation
- Similarity search
- Content recommendations

**Version 2.0**
- Visual chunk editor
- A/B testing for chunking strategies
- Analytics dashboard
- Multi-language support
- Custom AI integrations

### Community Requests

Want to see a feature? [Open an issue](https://github.com/mikkelkrogsholm/wp-plugins/issues) with the "enhancement" label!

## FAQ

**Q: Does this work with Gutenberg blocks?**
A: Yes! The plugin fully supports both Gutenberg blocks and Classic Editor content.

**Q: Will this slow down my site?**
A: No. Processing only happens when you request it, and results are cached for performance.

**Q: Can I use this with custom post types?**
A: Yes! Configure enabled post types in Settings > LLM Optimizer.

**Q: Is the REST API secure?**
A: Yes. The API uses WordPress authentication, includes rate limiting, and validates all inputs.

**Q: Can I customize the export format?**
A: Yes. Use WordPress filters to modify the processing pipeline, or request custom formats via the API.

**Q: Does this work with page builders?**
A: Yes, but results vary by page builder. It works best with native WordPress content.

**Q: How do I generate Application Passwords?**
A: Go to Users > Your Profile > Application Passwords in WordPress Admin (requires WordPress 5.6+).

**Q: Can I export my entire site at once?**
A: Yes! Use the batch API endpoints or create a custom WP-CLI command.

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on:
- How to submit bug reports
- How to propose new features
- Development setup
- Coding standards
- Pull request process

## Security

Security is a top priority. If you discover a security vulnerability:

1. **DO NOT** open a public GitHub issue
2. Email security details privately (see [SECURITY.md](SECURITY.md))
3. Include full details and steps to reproduce
4. Allow time for a fix before public disclosure

See our [Security Policy](SECURITY.md) for more information.

## Star History

If you find this plugin useful, please star the repository! It helps others discover the project.

## Related Projects

- **WordPress REST API** - [wordpress.org/plugins/rest-api](https://wordpress.org/plugins/rest-api/)
- **LangChain** - [langchain.com](https://langchain.com)
- **LlamaIndex** - [llamaindex.ai](https://llamaindex.ai)

---

**Made with care for the WordPress and AI communities.**

*Bridging traditional content management with next-generation AI systems.*
