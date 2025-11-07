# SEO & LLM Optimizer - Documentation

Complete documentation for the SEO & LLM Optimizer WordPress plugin.

## 📚 User Documentation

For end users and content creators:

- **[User Guide](user/USER_GUIDE.md)** - Complete user manual with examples
- **[Quick Start](user/QUICK_START.md)** - Get started in 5 minutes
- **[Installation](user/INSTALLATION.md)** - Step-by-step installation instructions
- **[Troubleshooting](user/TROUBLESHOOTING.md)** - Common issues and solutions

**Start here if you're new:** [Quick Start Guide](user/QUICK_START.md)

---

## 🔧 Developer Documentation

For developers and contributors:

- **[Developer Guide](developer/DEVELOPER_GUIDE.md)** - Technical architecture and implementation
- **[Code Structure](developer/STRUCTURE.md)** - Project file organization
- **[Contributing](developer/CONTRIBUTING.md)** - How to contribute to the project
- **[Code of Conduct](developer/CODE_OF_CONDUCT.md)** - Community guidelines

**Start here if you're developing:** [Developer Guide](developer/DEVELOPER_GUIDE.md)

---

## 🔌 API Documentation

For integrations and programmatic access:

- **[REST API Reference](api/REST_API_DOCUMENTATION.md)** - Complete API documentation
- **[API Quick Start](api/REST_API_QUICK_START.md)** - Get started with the REST API

**Includes examples for:**
- Python (LangChain, LlamaIndex)
- JavaScript/Node.js
- cURL commands
- Authentication setup

**Start here for API integration:** [API Quick Start](api/REST_API_QUICK_START.md)

---

## ⚙️ Implementation Guides

Deep dives into specific features:

- **[Chunking Engine](implementation/CHUNKING_ENGINE_IMPLEMENTATION.md)** - How chunking strategies work
- **[Chunking Quick Start](implementation/CHUNKING_QUICK_START.md)** - Quick guide to chunking

**Topics covered:**
- Hierarchical chunking (by headers)
- Fixed-size chunking (512 tokens)
- Semantic chunking (by paragraphs)
- Token estimation
- Export formats (Universal, LangChain, LlamaIndex)

---

## 🧪 Testing

For testing and quality assurance:

- **[Testing Guide](testing/TESTING_GUIDE.md)** - Comprehensive testing procedures
- **[Testing Setup](testing/TESTING_NOTE.md)** - Docker testing environment setup
- **[Test Content Samples](testing/test-content-samples.md)** - Sample posts for testing

**Includes:**
- Feature testing checklists
- REST API testing with examples
- Frontend testing procedures
- Security verification
- Performance testing

**Start here for testing:** [Testing Setup](testing/TESTING_NOTE.md)

---

## 📋 Meta

Project information:

- **[Changelog](meta/CHANGELOG.md)** - Version history and release notes
- **[Security Policy](meta/SECURITY.md)** - Security policy and vulnerability reporting

---

## Quick Links

### Most Common Tasks

**I want to...**

- **Use the plugin:** → [Quick Start](user/QUICK_START.md)
- **Install the plugin:** → [Installation Guide](user/INSTALLATION.md)
- **Fix an issue:** → [Troubleshooting](user/TROUBLESHOOTING.md)
- **Use the API:** → [API Quick Start](api/REST_API_QUICK_START.md)
- **Contribute code:** → [Contributing Guide](developer/CONTRIBUTING.md)
- **Test the plugin:** → [Testing Setup](testing/TESTING_NOTE.md)
- **Understand chunking:** → [Chunking Quick Start](implementation/CHUNKING_QUICK_START.md)

### By Audience

**End Users:**
1. [Quick Start](user/QUICK_START.md)
2. [User Guide](user/USER_GUIDE.md)
3. [Troubleshooting](user/TROUBLESHOOTING.md)

**Developers:**
1. [Developer Guide](developer/DEVELOPER_GUIDE.md)
2. [Code Structure](developer/STRUCTURE.md)
3. [API Reference](api/REST_API_DOCUMENTATION.md)

**Content Creators:**
1. [Quick Start](user/QUICK_START.md)
2. [User Guide](user/USER_GUIDE.md)
3. [Use Cases](../README.md#use-cases)

**Data Scientists / ML Engineers:**
1. [API Quick Start](api/REST_API_QUICK_START.md)
2. [Chunking Guide](implementation/CHUNKING_ENGINE_IMPLEMENTATION.md)
3. [REST API Reference](api/REST_API_DOCUMENTATION.md)

---

## Documentation Structure

```
docs/
├── README.md (this file)
│
├── user/                          # End user documentation
│   ├── USER_GUIDE.md             # Complete user manual
│   ├── QUICK_START.md            # 5-minute getting started
│   ├── INSTALLATION.md           # Installation instructions
│   └── TROUBLESHOOTING.md        # Common issues & solutions
│
├── developer/                     # Developer documentation
│   ├── DEVELOPER_GUIDE.md        # Technical architecture
│   ├── STRUCTURE.md              # Project structure
│   ├── CONTRIBUTING.md           # Contribution guidelines
│   └── CODE_OF_CONDUCT.md        # Community guidelines
│
├── api/                          # API documentation
│   ├── REST_API_DOCUMENTATION.md # Complete API reference
│   └── REST_API_QUICK_START.md   # API getting started
│
├── implementation/               # Feature deep-dives
│   ├── CHUNKING_ENGINE_IMPLEMENTATION.md
│   └── CHUNKING_QUICK_START.md
│
├── testing/                      # Testing documentation
│   ├── TESTING_GUIDE.md          # Testing procedures
│   ├── TESTING_NOTE.md           # Testing setup
│   └── test-content-samples.md  # Sample test posts
│
└── meta/                         # Project meta
    ├── CHANGELOG.md              # Version history
    └── SECURITY.md               # Security policy
```

---

## Contributing to Documentation

Found an error or want to improve the documentation?

1. Read the [Contributing Guide](developer/CONTRIBUTING.md)
2. Follow the [Code of Conduct](developer/CODE_OF_CONDUCT.md)
3. Submit a pull request or issue

---

**Back to:** [Main README](../README.md) | [Plugin Directory](../)
