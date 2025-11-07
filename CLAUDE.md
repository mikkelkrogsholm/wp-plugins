# WordPress Plugins Development with Claude Code

This repository is a custom WordPress plugin development workspace designed for building tailored WordPress plugins to meet specific needs and requirements.

## Purpose

Build custom WordPress plugins that solve real problems, tested in a containerized WordPress environment before deployment. Each plugin addresses specific functionality gaps or enhances WordPress capabilities in unique ways.

## Development Principles

### KISS (Keep It Simple, Stupid)
- Write simple, straightforward code
- Avoid over-engineering solutions
- Prefer clarity over cleverness
- One feature at a time

### DRY (Don't Repeat Yourself)
- Reuse code through functions and classes
- Abstract common patterns
- Single source of truth
- Minimize redundancy

### Workflow
We use **Claude Code agents and skills** throughout the development process:
- Planning with specialized agents
- Parallel task execution where possible
- Verification agents for quality assurance
- Comprehensive documentation generation

## Repository Structure

```
wp-plugins/
├── docker-compose.yml      # Shared WordPress testing environment
├── test.sh                 # Testing helper script
├── README.md               # Project overview
├── TESTING.md              # Testing guide
├── CLAUDE.md              # This file
│
├── seo-llm-optimizer/     # Plugin 1: SEO & LLM Optimizer
├── seo-cluster-links/     # Plugin 2: SEO Cluster Links
└── [future-plugins]/      # Additional plugins as needed
```

## Current Plugins

### 1. SEO & LLM Optimizer
**Purpose:** Convert WordPress content to AI-friendly formats (Markdown, RAG-ready chunks)

**Key Features:**
- Frontend "Copy for AI" button
- 3 chunking strategies (hierarchical, fixed-size, semantic)
- REST API with 8 endpoints
- Multiple export formats (Universal, LangChain, LlamaIndex)
- Rate limiting and caching

**Use Cases:**
- Prepare content for ChatGPT/Claude analysis
- Build RAG systems with WordPress content
- Export for vector databases
- Training data preparation

**Status:** ✅ Production ready (v1.0.0)

---

### 2. SEO Cluster Links
**Purpose:** Automatic internal linking for SEO content clusters

**Key Features:**
- Automatic keyword-based internal linking
- Cluster management
- Link tracking and analytics
- Customizable anchor text

**Use Cases:**
- Build topical authority
- Improve internal link structure
- SEO optimization
- Content cluster management

**Status:** ✅ Active development

---

## Docker Testing Environment

All plugins share a common Docker-based testing setup:

### Quick Start
```bash
# Start WordPress + MySQL + phpMyAdmin
./test.sh start

# Install WordPress
./test.sh install-wp

# Activate a plugin
./test.sh activate seo-llm-optimizer

# Open in browser
open http://localhost:8080
```

### Available Services
- **WordPress** (latest) - http://localhost:8080
- **MySQL 8.0** - Database server
- **phpMyAdmin** - http://localhost:8081
- **WP-CLI** - Command-line WordPress management

### Testing Commands
```bash
# Environment
./test.sh start              # Start containers
./test.sh stop               # Stop (keep data)
./test.sh reset              # Fresh start
./test.sh status             # Check status

# Plugins
./test.sh plugins            # List plugins
./test.sh activate <slug>    # Activate plugin
./test.sh deactivate <slug>  # Deactivate plugin

# WordPress
./test.sh wp <command>       # Run WP-CLI commands
./test.sh install-wp         # Quick WordPress install

# Debugging
./test.sh logs               # View logs
./test.sh debug              # Live debug.log
./test.sh shell              # Container shell
```

## Development Workflow

### 1. Planning Phase
- Define plugin purpose and requirements
- Brainstorm with Claude Code agents
- Create implementation plan following KISS/DRY
- Break down into parallel tasks where possible

### 2. Implementation Phase
- Use Claude Code skills for specialized tasks
- Build with WordPress best practices
- Follow security guidelines (sanitize, escape, nonces)
- Implement with PSR-4 autoloading and singleton pattern

### 3. Verification Phase
- Launch verification agents for quality assurance
- Security audit (target: 95/100)
- Code review and testing
- Documentation generation

### 4. Testing Phase
- Test in Docker environment
- Create sample content
- Verify all features work
- Check mobile responsiveness
- Security verification

### 5. Documentation Phase
- User guides
- Developer documentation
- API references
- Testing procedures

## Adding New Plugins

### 1. Create Plugin Directory
```bash
mkdir my-new-plugin
cd my-new-plugin
```

### 2. Create Main Plugin File
```php
<?php
/**
 * Plugin Name: My New Plugin
 * Description: Brief description
 * Version: 1.0.0
 * Author: Your Name
 */
```

### 3. Add to Docker Environment
Edit `docker-compose.yml`:
```yaml
volumes:
  - ./my-new-plugin:/var/www/html/wp-content/plugins/my-new-plugin
```

### 4. Restart and Test
```bash
./test.sh stop
./test.sh start
./test.sh activate my-new-plugin
```

### 5. Update This File
Add your plugin to the "Current Plugins" section above.

## Code Standards

### WordPress Coding Standards
- Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use `phpcs.xml` for automated checking
- PSR-4 autoloading for classes
- Singleton pattern for main classes

### Security Requirements
- ✅ Nonce verification for all forms
- ✅ Capability checks for admin actions
- ✅ Input sanitization (`sanitize_text_field`, etc.)
- ✅ Output escaping (`esc_html`, `esc_attr`, etc.)
- ✅ Prepared SQL statements (no direct queries)
- ✅ Rate limiting for public APIs

### File Organization
```
plugin-name/
├── plugin-name.php          # Main file
├── composer.json            # Dependencies
├── uninstall.php           # Cleanup
├── includes/               # PHP classes
├── assets/                 # CSS, JS
├── templates/              # PHP templates
├── languages/              # i18n
└── docs/                   # Documentation
    ├── user/              # User guides
    ├── developer/         # Dev docs
    ├── api/               # API docs
    └── testing/           # Test docs
```

## Quality Targets

Each plugin should achieve:
- **Security Score:** 95/100 minimum
- **Code Quality:** 95/100 minimum
- **Documentation:** Comprehensive (user + developer)
- **Testing:** Complete test procedures
- **Accessibility:** WCAG 2.1 AA compliance
- **Performance:** Optimized and cached

## Agent Usage

### Planning Agents
Use for brainstorming and architecture design:
- Feature planning
- Implementation strategy
- Task breakdown

### Specialized Agents
Use for specific tasks:
- **wp-plugin-scaffold:** New plugin setup
- **wp-feature-builder:** Add features to existing plugins
- **wp-security-reviewer:** Security audits (use proactively!)

### Verification Agents
Launch after major work:
- Code quality verification
- Security audits
- Feature completeness
- Documentation review

### Parallel Execution
Always run independent tasks in parallel:
```
Single message with multiple Task tool calls
```

## Maintenance

### Regular Updates
- WordPress compatibility testing
- Security patches
- Performance optimization
- Documentation updates

### Version Control
- Semantic versioning (MAJOR.MINOR.PATCH)
- Comprehensive commit messages
- Feature branches for development
- Pull requests for review

## Resources

### Internal Documentation
- [Main README](README.md) - Project overview
- [TESTING.md](TESTING.md) - Testing guide
- Plugin-specific docs in each plugin's `docs/` folder

### WordPress Resources
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Coding Standards](https://developer.wordpress.org/coding-standards/)
- [REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WP-CLI Commands](https://wp-cli.org/)

### Development Tools
- Docker Desktop
- WP-CLI
- phpMyAdmin
- Claude Code

---

## Getting Started

1. **Clone repository**
2. **Start Docker environment:** `./test.sh start`
3. **Install WordPress:** `./test.sh install-wp`
4. **Activate a plugin:** `./test.sh activate <plugin-slug>`
5. **Start developing!**

For detailed testing procedures, see [TESTING.md](TESTING.md).

---

**Remember:** Keep it simple (KISS), don't repeat yourself (DRY), and use agents and skills for efficient development.
