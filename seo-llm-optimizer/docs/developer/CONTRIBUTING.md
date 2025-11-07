# Contributing to SEO & LLM Optimizer

Thank you for your interest in contributing to the SEO & LLM Optimizer plugin! This document provides guidelines and instructions for contributing.

---

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [How Can I Contribute?](#how-can-i-contribute)
3. [Development Setup](#development-setup)
4. [Coding Standards](#coding-standards)
5. [Pull Request Process](#pull-request-process)
6. [Reporting Bugs](#reporting-bugs)
7. [Suggesting Features](#suggesting-features)
8. [Documentation](#documentation)

---

## Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

**Quick Summary**:
- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Respect differing opinions
- Report concerning behavior

---

## How Can I Contribute?

### Ways to Contribute

**Code Contributions**:
- Fix bugs
- Add new features
- Improve performance
- Enhance security
- Write tests

**Non-Code Contributions**:
- Report bugs
- Suggest features
- Improve documentation
- Translate the plugin
- Help others in issues/discussions
- Write tutorials or blog posts

### First-Time Contributors

Look for issues labeled `good first issue` or `help wanted`. These are great starting points!

**Good first contributions**:
- Fix typos in documentation
- Add code comments
- Write tests for existing features
- Update outdated documentation
- Improve error messages

---

## Development Setup

### Prerequisites

- **WordPress**: 6.4 or higher (local development environment)
- **PHP**: 7.4 or higher
- **Composer**: Latest version
- **Git**: For version control
- **Node.js & npm**: For frontend asset building (optional)

### Local Environment Setup

**Option 1: Using Local by Flywheel**

1. Install [Local by Flywheel](https://localwp.com/)
2. Create a new WordPress site
3. Clone the repository into `wp-content/plugins/`:
   ```bash
   cd /path/to/site/wp-content/plugins/
   git clone https://github.com/mikkelkrogsholm/wp-plugins.git
   cd seo-llm-optimizer
   ```

**Option 2: Using Docker (WP-ENV)**

```bash
# Install WP-ENV globally
npm install -g @wordpress/env

# Clone repository
git clone https://github.com/mikkelkrogsholm/wp-plugins.git
cd seo-llm-optimizer

# Start WordPress
wp-env start

# Access: http://localhost:8888
# Admin: admin / password
```

**Option 3: Using XAMPP/MAMP**

1. Install XAMPP or MAMP
2. Install WordPress in htdocs
3. Clone plugin into `wp-content/plugins/`

### Install Dependencies

```bash
# PHP dependencies
composer install

# Development dependencies
composer install --dev
```

### Activate Plugin

1. Log into WordPress admin
2. Go to Plugins > Installed Plugins
3. Activate "SEO & LLM Optimizer"

### Verify Installation

1. Check Settings > LLM Optimizer page loads
2. Create a test post
3. View the post and verify button appears
4. Test REST API: `curl http://localhost:8888/wp-json/slo/v1/health`

---

## Coding Standards

### WordPress Coding Standards

This plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/).

**Key Points**:
- Use tabs for indentation (not spaces)
- Opening braces on same line
- Space after control structures
- Yoda conditions for comparisons
- Single quotes for strings (unless interpolating)

**Example**:
```php
<?php
// Good
if ( 'value' === $variable ) {
    do_something();
}

// Bad
if ($variable == "value")
{
    do_something();
}
```

### PHP CodeSniffer

Install and run PHPCS to check coding standards:

```bash
# Install (if not already installed via composer)
composer require --dev squizlabs/php_codesniffer
composer require --dev wp-coding-standards/wpcs

# Configure PHPCS
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs

# Check files
./vendor/bin/phpcs --standard=WordPress seo-llm-optimizer.php

# Auto-fix issues (when possible)
./vendor/bin/phpcbf --standard=WordPress seo-llm-optimizer.php
```

### JavaScript Standards

Follow WordPress JavaScript standards:

```javascript
// Use === for comparisons
if ( value === 'test' ) {
    // Do something
}

// Use camelCase for variables
const myVariable = 'value';

// Document functions
/**
 * Does something useful
 *
 * @param {string} param - The parameter
 * @return {boolean} The result
 */
function doSomething( param ) {
    return true;
}
```

### Documentation Standards

**PHPDoc Blocks**:
```php
/**
 * Short description of function
 *
 * Longer description if needed, explaining what the function does,
 * any important notes, and usage examples.
 *
 * @since 1.0.0
 *
 * @param int    $post_id Post ID to process
 * @param array  $options Optional. Processing options. Default empty array.
 * @return string|WP_Error Markdown content or error object
 */
public function convert_to_markdown( $post_id, $options = array() ) {
    // Function code
}
```

**JSDoc Comments**:
```javascript
/**
 * Copy text to clipboard
 *
 * @param {string} text - Text to copy
 * @return {Promise<boolean>} True if successful
 */
async function copyToClipboard( text ) {
    // Function code
}
```

### Security Guidelines

**Always**:
- Sanitize input: `sanitize_text_field()`, `absint()`, `esc_url()`
- Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- Use nonces for forms and AJAX
- Check capabilities before operations
- Use prepared statements for database queries

**Never**:
- Trust user input
- Output unescaped data
- Use direct `$_POST` / `$_GET` without sanitization
- Skip capability checks
- Use string concatenation for SQL

**Example**:
```php
// Good
$value = sanitize_text_field( $_POST['value'] );
echo esc_html( $value );

// Bad
$value = $_POST['value'];
echo $value;
```

---

## Pull Request Process

### Before You Start

1. **Check existing issues**: Search for existing issues or PRs related to your contribution
2. **Create an issue first**: For major changes, create an issue to discuss the approach
3. **Fork the repository**: Create your own fork to work on

### Creating a Pull Request

**Step 1: Create a Branch**

```bash
# Update main branch
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/your-feature-name

# Or bug fix branch
git checkout -b fix/bug-description
```

**Branch Naming**:
- Features: `feature/feature-name`
- Bug fixes: `fix/bug-description`
- Documentation: `docs/what-you-changed`
- Refactoring: `refactor/what-you-refactored`

**Step 2: Make Your Changes**

```bash
# Make changes
# Test thoroughly
# Run coding standards check

# Stage changes
git add .

# Commit with descriptive message
git commit -m "Add feature: description of what was added"
```

**Commit Message Format**:
```
Type: Brief description (50 chars or less)

Longer explanation if needed (wrap at 72 characters).
Explain what and why, not how.

- Bullet points are okay
- Use present tense: "Add feature" not "Added feature"

Fixes #123
```

**Types**:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

**Step 3: Push and Create PR**

```bash
# Push branch to your fork
git push origin feature/your-feature-name

# Go to GitHub and create Pull Request
```

**Pull Request Template**:

```markdown
## Description

Brief description of what this PR does.

## Type of Change

- [ ] Bug fix (non-breaking change fixing an issue)
- [ ] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing

Describe the tests you ran:

- [ ] Tested on WordPress 6.4
- [ ] Tested with Gutenberg
- [ ] Tested with Classic Editor
- [ ] Tested REST API endpoints
- [ ] Checked coding standards
- [ ] Added/updated tests

## Checklist

- [ ] My code follows the WordPress coding standards
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have updated the documentation accordingly
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## Screenshots (if applicable)

Add screenshots to help explain your changes.

## Related Issues

Fixes #(issue number)
Related to #(issue number)
```

**Step 4: Code Review**

- Respond to feedback promptly
- Make requested changes
- Push updates to the same branch
- Be open to constructive criticism

**Step 5: Merge**

Once approved:
- Maintainer will merge your PR
- Delete your branch after merge
- Your changes will be included in the next release!

---

## Reporting Bugs

### Before Reporting

1. **Check if it's already reported**: Search existing issues
2. **Verify it's a bug**: Make sure it's not a configuration issue
3. **Test with default theme**: Rule out theme conflicts
4. **Test without other plugins**: Rule out plugin conflicts

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
 - WordPress version: [e.g. 6.4.2]
 - PHP version: [e.g. 8.1]
 - Plugin version: [e.g. 1.0.0]
 - Theme: [e.g. Twenty Twenty-Four]
 - Browser: [e.g. Chrome 120]

**Additional context**
Any other relevant information.

**Error logs**
If applicable, paste relevant error logs.
```

### Security Bugs

**IMPORTANT**: Do not report security vulnerabilities publicly.

- Email details to security contact (see [SECURITY.md](SECURITY.md))
- Wait for confirmation before public disclosure
- Allow time for a fix to be developed and released

---

## Suggesting Features

### Before Suggesting

1. **Check existing suggestions**: Search issues for similar requests
2. **Consider scope**: Is this appropriate for the core plugin?
3. **Think about alternatives**: Could this be an add-on or extension?

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem. Ex. I'm frustrated when [...]

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Any alternative solutions or features you've considered.

**Use cases**
Specific examples of how this would be used:
1. Use case 1
2. Use case 2

**Would you be willing to contribute this feature?**
- [ ] Yes, I can submit a PR
- [ ] I can help test
- [ ] I can help with documentation
- [ ] I need someone else to implement

**Additional context**
Any other context, screenshots, or examples.
```

---

## Documentation

### Types of Documentation

1. **Code Documentation**: PHPDoc and JSDoc comments
2. **User Documentation**: USER_GUIDE.md, README.md
3. **Developer Documentation**: DEVELOPER_GUIDE.md
4. **API Documentation**: REST_API_DOCUMENTATION.md

### Documentation Guidelines

**Be Clear and Concise**:
- Use simple language
- Provide examples
- Break complex topics into steps
- Use headings and lists

**Keep It Updated**:
- Update docs when changing features
- Note version numbers
- Mark deprecated features
- Update examples

**Consider Your Audience**:
- **Users**: Step-by-step instructions, screenshots
- **Developers**: Code examples, technical details
- **API Consumers**: Endpoints, parameters, responses

### Writing Documentation

**Good Example**:
```markdown
## Using the Copy Button

1. **Navigate to any post** on your site
2. **Click the "Copy for AI" button** in the bottom right corner
3. **Choose a tab**:
   - Quick Copy: Instant copy
   - Format Options: Customize settings
   - RAG Chunks: View chunks
4. **Click "Copy"** to copy to clipboard
5. **Paste** into your AI tool

**Tip**: The button only appears on single posts, not archives.
```

**Bad Example**:
```markdown
You can use the button to copy stuff. Click it and it copies.
```

---

## Translation

### Contributing Translations

1. **Get the POT file**: `languages/seo-llm-optimizer.pot`
2. **Use Poedit** or similar tool
3. **Translate strings**
4. **Submit PO/MO files** via pull request

**Translation Guidelines**:
- Maintain consistent terminology
- Respect context
- Keep formatting placeholders
- Test translations in WordPress

---

## Getting Help

**Questions About Contributing?**

- **Documentation**: Read this guide thoroughly
- **Issues**: Search existing issues
- **Discussions**: Start a discussion on GitHub
- **Code Examples**: Check DEVELOPER_GUIDE.md

**Stuck on Something?**

Don't hesitate to ask! Create a draft pull request with `[WIP]` in the title and ask for guidance.

---

## Recognition

Contributors are recognized in several ways:

- **Git History**: Your commits are permanently recorded
- **CONTRIBUTORS.md**: Listed in contributors file (coming soon)
- **Release Notes**: Mentioned in CHANGELOG.md
- **Credits**: Acknowledged in README.md

---

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license, the same license as the project.

---

## Thank You!

Every contribution, no matter how small, makes this plugin better for everyone. Thank you for taking the time to contribute!

**Questions?** Open an issue or discussion on GitHub.

**Ready to contribute?** Fork the repository and start coding!

---

**Maintained by**: Mikkel Krogsholm
**Repository**: https://github.com/mikkelkrogsholm/wp-plugins
**Last Updated**: 2025-11-07
