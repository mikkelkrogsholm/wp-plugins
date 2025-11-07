# Changelog

All notable changes to the SEO & LLM Optimizer plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned for Version 1.1

**Features**:
- WP-CLI commands for bulk operations
- Advanced token estimation with model-specific calculations
- Custom export templates via UI
- Webhook support for real-time content updates
- Bulk export functionality in admin
- Content versioning and history

**Improvements**:
- Enhanced error handling and logging
- Performance optimizations for large sites
- Better mobile UI/UX
- Improved accessibility features
- Extended REST API documentation

### Planned for Version 1.2

**Features**:
- Additional vector database formats (Pinecone, Weaviate, Milvus)
- Built-in embeddings generation (OpenAI, Cohere)
- Semantic similarity search
- Content recommendation engine
- Multi-language support for chunking
- Custom chunking rules UI

**Integrations**:
- Direct LangChain integration
- LlamaIndex connector
- Pinecone auto-sync
- OpenAI embeddings API
- Hugging Face models

### Planned for Version 2.0

**Major Features**:
- Visual chunk editor with live preview
- A/B testing for chunking strategies
- Analytics dashboard with insights
- Multi-site network support enhancements
- Advanced caching strategies (Redis, Memcached)
- Custom AI model integrations

**UI/UX**:
- Redesigned admin interface
- Dark mode support
- Advanced settings wizard
- Onboarding tutorial
- Contextual help system

---

## [1.0.0] - 2025-11-07

### Initial Release

First stable release of the SEO & LLM Optimizer plugin.

### Added

**Core Features**:
- Markdown conversion from WordPress HTML content
- YAML frontmatter with comprehensive metadata
- Support for both Gutenberg blocks and Classic Editor
- Clean HTML processing with WordPress cruft removal
- Semantic structure enhancement

**Chunking System**:
- Three chunking strategies:
  - Hierarchical: Split by markdown headers (H1-H6)
  - Fixed Size: Fixed-size chunks with sentence-boundary overlap
  - Semantic: Paragraph-based intelligent chunking
- Configurable chunk sizes (128-2048 tokens)
- Configurable overlap (0-512 tokens)
- Smart sentence boundary detection
- Token estimation for all chunks

**Export Formats**:
- Universal JSON format (standard, flexible)
- LangChain format (Python library compatible)
- LlamaIndex format (direct integration)
- Comprehensive metadata for all formats

**Frontend Interface**:
- Floating "Copy for AI" button on posts/pages
- Interactive modal with three tabs:
  - Quick Copy: Instant markdown export
  - Format Options: Customizable export settings
  - RAG Chunks: View and copy individual chunks
- Mobile-responsive design
- WCAG 2.1 AA accessibility compliance
- Keyboard navigation support
- Focus management and ARIA labels

**REST API**:
- 8 comprehensive endpoints:
  - GET `/slo/v1/health` - API health check
  - GET `/slo/v1/posts/{id}/markdown` - Get post markdown
  - GET `/slo/v1/posts/{id}/chunks` - Get post chunks
  - POST `/slo/v1/batch/markdown` - Batch markdown export
  - POST `/slo/v1/batch/chunks` - Batch chunk generation
  - GET `/slo/v1/cache/stats` - Cache statistics
  - DELETE `/slo/v1/cache/{id}` - Clear post cache
  - DELETE `/slo/v1/cache` - Clear all caches
- WordPress authentication support
- Application Password compatibility
- Rate limiting (configurable, default 60 req/hour)
- Comprehensive error handling
- Batch processing (up to 50 posts for markdown, 20 for chunks)

**Admin Features**:
- Settings page under Settings > LLM Optimizer
- Three settings tabs:
  - Feature Settings: Toggle features and post types
  - Export Options: Configure default export settings
  - Advanced: Performance and security settings
- Real-time settings validation
- Clear cache functionality
- Helpful descriptions and examples

**Performance & Caching**:
- WordPress transient-based caching system
- Configurable cache duration (300-86400 seconds)
- Automatic cache clearing on post updates
- Manual cache management via UI and API
- Efficient memory usage
- Optimized database queries

**Security**:
- Nonce verification for all AJAX requests
- Input sanitization and validation
- Output escaping for XSS prevention
- WordPress capability checks
- Rate limiting for API abuse prevention
- SQL injection prevention
- CSRF protection

**Developer Features**:
- WordPress hooks and filters for extensibility
- Singleton pattern for all main classes
- Comprehensive PHPDoc documentation
- Coding standards compliance (WordPress)
- Clean, maintainable code architecture
- Example code and integration patterns

**Internationalization**:
- Translation-ready with text domain
- POT file included in `/languages/`
- All user-facing strings translatable
- RTL language support

**Documentation**:
- Comprehensive README with examples
- Complete User Guide
- Detailed Developer Guide
- Full REST API documentation
- Installation guide
- Troubleshooting guide
- Contributing guidelines
- Security policy
- Code of Conduct

### Technical Details

**Requirements**:
- WordPress 6.4 or higher
- PHP 7.4 or higher
- Composer for dependency management

**Dependencies**:
- league/html-to-markdown ^5.1

**Browser Support**:
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

**File Structure**:
```
seo-llm-optimizer/
├── seo-llm-optimizer.php
├── uninstall.php
├── composer.json
├── includes/ (7 classes)
├── admin/ (1 class)
├── assets/ (CSS + JS)
├── templates/ (PHP templates)
├── languages/ (i18n)
└── vendor/ (dependencies)
```

**Database Impact**:
- Uses WordPress transients (no custom tables)
- Minimal database footprint
- Efficient option storage

**Performance**:
- Typical markdown conversion: 50-200ms (uncached)
- Typical markdown conversion: 1-5ms (cached)
- Memory usage: ~2-10MB per request
- Cache improvement: 50-100x faster

### Known Limitations

**Version 1.0**:
- Token estimation is approximate (1 token ≈ 4 chars)
- Page builders may have varying results
- Very large posts (>50,000 words) may be slow
- Embeddings must be generated externally
- No built-in vector database integration
- Single language support for chunking optimization

### Fixed in This Release

N/A - Initial release

### Deprecated in This Release

N/A - Initial release

### Removed in This Release

N/A - Initial release

### Security Updates

**Version 1.0.0**:
- Implemented comprehensive security measures
- Nonce verification for all AJAX
- Input sanitization throughout
- Output escaping everywhere
- Rate limiting to prevent abuse
- Capability checks on all operations

---

## Release Notes

### Version 1.0.0 - November 7, 2025

We're excited to announce the first stable release of the SEO & LLM Optimizer plugin!

**What's New**:

This release brings powerful content optimization features to WordPress:

1. **One-Click Content Export**: Copy your content in AI-ready markdown format with a single click.

2. **Three Chunking Strategies**: Choose the best way to split your content for RAG systems.

3. **Multiple Export Formats**: Support for Universal, LangChain, and LlamaIndex formats.

4. **Comprehensive REST API**: Full programmatic access with 8 endpoints.

5. **Beautiful, Accessible UI**: WCAG 2.1 AA compliant interface that works everywhere.

**Getting Started**:

1. Install and activate the plugin
2. Go to Settings > LLM Optimizer
3. Configure your preferences
4. Visit any post and click "Copy for AI"

**For Developers**:

Check out our extensive documentation:
- [Developer Guide](DEVELOPER_GUIDE.md)
- [REST API Documentation](REST_API_DOCUMENTATION.md)
- [Code Examples](DEVELOPER_GUIDE.md#code-examples)

**Next Steps**:

We're already working on version 1.1 with WP-CLI commands, advanced token estimation, and more. See the [Roadmap](README.md#roadmap) for details.

**Thank You**:

Special thanks to:
- The WordPress community
- Early testers and feedback providers
- Contributors to dependencies (League HTML-to-Markdown)

**Feedback**:

We'd love to hear from you! Please:
- Star the repository if you find it useful
- Report bugs on GitHub Issues
- Share your use cases and success stories
- Contribute improvements via Pull Requests

---

## Version History

| Version | Date       | Highlights |
|---------|------------|------------|
| 1.0.0   | 2025-11-07 | Initial release with markdown export, chunking, REST API |

---

## Upgrade Notes

### Upgrading to 1.0.0

N/A - Initial release. Fresh installation only.

### Future Upgrades

Upgrade instructions will be added here for future versions.

**Best Practices for Upgrades**:
1. Backup your WordPress site before upgrading
2. Review changelog for breaking changes
3. Test on staging environment first
4. Clear plugin caches after upgrade
5. Review and update settings if needed

---

## Compatibility

### WordPress Versions

| WordPress | Plugin Version | Status |
|-----------|----------------|--------|
| 6.4.x     | 1.0.0          | ✅ Supported |
| 6.5.x     | 1.0.0          | ✅ Supported |
| 6.6.x     | 1.0.0          | ✅ Supported |
| 6.7.x     | 1.0.0          | ✅ Supported |
| 6.8.x     | 1.0.0          | ✅ Supported |
| < 6.4     | 1.0.0          | ❌ Not supported |

### PHP Versions

| PHP   | Plugin Version | Status |
|-------|----------------|--------|
| 7.4   | 1.0.0          | ✅ Supported |
| 8.0   | 1.0.0          | ✅ Supported |
| 8.1   | 1.0.0          | ✅ Supported |
| 8.2   | 1.0.0          | ✅ Supported |
| 8.3   | 1.0.0          | ✅ Supported |
| < 7.4 | 1.0.0          | ❌ Not supported |

---

## Migration Guides

### From Other Plugins

Currently, there are no direct migration paths from other plugins. The SEO & LLM Optimizer is designed to work alongside other SEO and content plugins without conflicts.

**Compatible With**:
- Yoast SEO
- Rank Math
- All in One SEO
- Jetpack
- WooCommerce
- Most page builders (with limitations)

---

## Deprecation Notices

### Version 1.0.0

No deprecations in initial release.

### Future Deprecations

Deprecation notices for future versions will be posted here at least 2 versions before removal.

---

## Support

For questions, bug reports, or feature requests:

- **Documentation**: Start with [README.md](README.md) and [USER_GUIDE.md](USER_GUIDE.md)
- **Issues**: [GitHub Issues](https://github.com/mikkelkrogsholm/wp-plugins/issues)
- **Security**: See [SECURITY.md](SECURITY.md) for vulnerability reporting

---

**Maintained by**: Mikkel Krogsholm
**License**: GPL v2 or later
**Repository**: https://github.com/mikkelkrogsholm/wp-plugins
