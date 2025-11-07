# Testing SEO & LLM Optimizer

This plugin uses the **shared testing environment** at the project root.

## Quick Start

From the **project root** (`wp-plugins/`):

```bash
# Start WordPress
../test.sh start

# Install WordPress
../test.sh install-wp

# Activate this plugin
../test.sh activate seo-llm-optimizer

# Open browser
open http://localhost:8080
```

## Documentation

- **[../TESTING.md](../TESTING.md)** - Main testing guide for all plugins
- **[../README.md](../README.md)** - Project documentation
- **[QUICK_START.md](./QUICK_START.md)** - Plugin-specific quick start
- **[TESTING_GUIDE.md](./TESTING_GUIDE.md)** - Detailed plugin testing procedures
- **[test-content-samples.md](./test-content-samples.md)** - Sample WordPress posts

## Plugin-Specific Testing

See **[TESTING_GUIDE.md](./TESTING_GUIDE.md)** for:
- Feature testing checklist
- REST API testing with examples
- Frontend button testing
- Export format verification
- Chunking strategy testing

## Test Content

Use sample posts from **[test-content-samples.md](./test-content-samples.md)**:
1. Complex Gutenberg post (web development)
2. Classic Editor HTML (SEO guide)
3. Simple post (performance tips)
4. Code-heavy post (API tutorial)
5. List-style post (plugins list)

## Docker Commands

All Docker commands are run from the **project root**:

```bash
cd ..                          # Go to project root
./test.sh start                # Start WordPress
./test.sh activate seo-llm-optimizer
```

---

**Note**: The Docker setup (`docker-compose.yml`, `test.sh`) is now at the **project root** to support testing all plugins in this repository.
