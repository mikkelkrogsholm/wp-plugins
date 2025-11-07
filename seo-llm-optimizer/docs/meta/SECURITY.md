# Security Policy

## Supported Versions

We release security updates for the following versions of the SEO & LLM Optimizer plugin:

| Version | Supported          | End of Support |
| ------- | ------------------ | -------------- |
| 1.0.x   | :white_check_mark: | TBD            |
| < 1.0   | :x:                | N/A            |

**Note**: We typically support the current major version and one previous major version. Security updates are provided for critical vulnerabilities only in older versions.

---

## Security Measures

The plugin implements multiple layers of security:

### Input Validation & Sanitization

✅ **All user inputs are sanitized**:
- `sanitize_text_field()` for text
- `absint()` for integers
- `esc_url()` for URLs
- `wp_kses_post()` for HTML content
- Array validation for complex inputs

### Output Escaping

✅ **All outputs are escaped**:
- `esc_html()` for HTML content
- `esc_attr()` for attributes
- `esc_url()` for URLs
- `esc_js()` for JavaScript strings
- `wp_json_encode()` for JSON

### Nonce Verification

✅ **All forms and AJAX requests use nonces**:
```php
// Form nonces
wp_nonce_field('slo_action', 'slo_nonce');

// AJAX nonces
check_ajax_referer('slo_get_content', 'nonce');

// URL nonces
wp_verify_nonce($_GET['nonce'], 'slo_action');
```

### Capability Checks

✅ **All operations check user capabilities**:
- `manage_options` for admin settings
- `edit_posts` for content operations
- `read` for viewing content
- Custom capability checks where appropriate

### Rate Limiting

✅ **API rate limiting prevents abuse**:
- Default: 60 requests per hour per user/IP
- Configurable from 1-1000 requests/hour
- Tracked via WordPress transients
- Returns HTTP 429 when exceeded

### SQL Injection Prevention

✅ **No direct SQL queries**:
- Uses WordPress database abstraction (`$wpdb`)
- Prepared statements for all queries
- No user input in SQL strings

### Cross-Site Scripting (XSS) Prevention

✅ **Comprehensive XSS protection**:
- All output escaped
- Content Security Policy friendly
- No inline JavaScript from user input
- Sanitized before storage

### Cross-Site Request Forgery (CSRF) Prevention

✅ **CSRF protection throughout**:
- Nonces on all forms
- Nonces on all AJAX requests
- Nonce verification before actions
- Proper referrer checking

### Authentication & Authorization

✅ **Proper authentication enforcement**:
- WordPress authentication only
- Application Password support
- No custom authentication
- Cookie-based authentication supported
- REST API requires authentication

### Data Privacy

✅ **Privacy-focused design**:
- No external API calls by plugin
- No tracking or analytics
- No data sent to third parties
- User content stays on user's server
- GDPR compliant (no personal data collection)

---

## Reporting a Vulnerability

### Important: Do Not Report Publicly

**DO NOT** open a public GitHub issue for security vulnerabilities. This could put users at risk.

### How to Report

**Email**: Send security reports to:
```
Security Contact: [Create GitHub Security Advisory]
```

Or use GitHub's private vulnerability reporting:
1. Go to the repository
2. Click "Security" tab
3. Click "Report a vulnerability"
4. Fill out the form with details

### What to Include

Please provide as much information as possible:

**Required Information**:
- **Description**: Clear description of the vulnerability
- **Impact**: Who is affected and how severe is it?
- **Type**: What kind of vulnerability (XSS, CSRF, SQL injection, etc.)?
- **Steps to Reproduce**: Detailed steps to reproduce the issue
- **Proof of Concept**: Code or screenshots demonstrating the vulnerability

**Optional but Helpful**:
- **Affected Versions**: Which versions are vulnerable?
- **Suggested Fix**: If you have ideas on how to fix it
- **CVE Number**: If you've already requested one
- **Discovery Context**: How you discovered the issue

**Example Report**:
```markdown
Subject: [SECURITY] SQL Injection in Post Export

Description:
SQL injection vulnerability in the post export functionality allows
authenticated users to execute arbitrary SQL queries.

Severity: High
Type: SQL Injection
Affected Versions: 1.0.0

Steps to Reproduce:
1. Log in as any authenticated user
2. Navigate to Settings > LLM Optimizer
3. In the browser console, execute: [poc code]
4. Observe SQL query is executed

Impact:
An authenticated user can read/modify database contents.
This could lead to data theft or privilege escalation.

Proof of Concept:
[Attach screenshot or code sample]

Suggested Fix:
Use $wpdb->prepare() for all database queries involving
user input on line 123 of file.php
```

### Response Timeline

We take security seriously and will respond according to this timeline:

| Timeframe | Action |
|-----------|--------|
| 24 hours | Initial response confirming receipt |
| 72 hours | Assessment and validation |
| 7 days | Initial fix or timeline provided |
| 30 days | Security release (for high/critical issues) |
| 90 days | Public disclosure (coordinated) |

**Severity Levels**:
- **Critical**: Immediate action, patch within 7 days
- **High**: High priority, patch within 14 days
- **Medium**: Normal priority, patch in next minor release
- **Low**: Next major release or documentation update

### What Happens Next?

1. **Acknowledgment**: We'll confirm we received your report
2. **Investigation**: We'll investigate and validate the issue
3. **Timeline**: We'll provide a timeline for the fix
4. **Development**: We'll develop and test a fix
5. **Coordination**: We'll coordinate disclosure timing with you
6. **Release**: We'll release a security update
7. **Disclosure**: We'll publish details after users have time to update
8. **Credit**: We'll credit you (if desired) in the security advisory

### Coordinated Disclosure

We believe in responsible disclosure:

- **Give us time to fix**: Please allow time for a patch before public disclosure
- **Responsible Timeline**: We aim to patch critical issues within 7-14 days
- **Communication**: We'll keep you updated on progress
- **Credit**: We'll publicly credit you when the issue is fixed (if you want)
- **CVE Assignment**: We'll request CVE numbers for significant vulnerabilities

---

## Security Best Practices for Users

### For Site Administrators

**Keep WordPress Updated**:
- Update to latest WordPress version
- Update this plugin when new versions are released
- Set up automatic updates for security releases

**Use Strong Authentication**:
- Use strong, unique passwords
- Enable two-factor authentication
- Use Application Passwords for API access (not main password)
- Regularly review user accounts and permissions

**Limit Access**:
- Only give admin access to trusted users
- Use principle of least privilege
- Review user roles regularly
- Remove unused accounts

**Secure Your Server**:
- Use HTTPS/SSL for all traffic
- Keep PHP updated (7.4 minimum, 8.0+ recommended)
- Use a web application firewall (WAF)
- Regular security scanning
- Keep all software updated

**Configure Plugin Securely**:
- Enable rate limiting (recommended: 60 req/hour or less)
- Restrict REST API if not needed
- Set button visibility appropriately (logged in users only if appropriate)
- Review settings regularly

**Monitor Activity**:
- Review WordPress admin activity logs
- Monitor REST API usage
- Watch for unusual patterns
- Set up error notifications

### For Developers

**When Using the REST API**:
- Always use HTTPS
- Use Application Passwords, not main passwords
- Implement rate limiting on your end too
- Validate all responses
- Handle errors gracefully
- Don't log sensitive data

**When Extending the Plugin**:
- Follow security best practices
- Sanitize all inputs
- Escape all outputs
- Use nonces for forms/AJAX
- Check capabilities before operations
- Review the security guidelines in CONTRIBUTING.md

**Code Reviews**:
- Review code changes for security issues
- Test with security tools (PHPCS with security rules)
- Consider security implications of new features
- Don't commit sensitive data (API keys, passwords)

---

## Known Security Considerations

### WordPress Multisite

The plugin is compatible with WordPress multisite. Each site in the network has independent settings and caches. Network administrators should be aware that:

- Site admins can configure plugin settings for their site
- REST API is available on each site independently
- Rate limiting is per-site
- Consider network-wide policies for API access

### Page Builders

Some page builders store content in non-standard ways. When using this plugin with page builders:

- Content extraction may vary by builder
- Some builder-specific elements may not convert perfectly
- Review exported content before using in production
- Report builder-specific issues for evaluation

### Large Files & Memory

Processing very large posts (50,000+ words) may consume significant memory:

- Monitor PHP memory limits
- Consider chunking very large content
- Use caching to improve performance
- Increase PHP memory limit if needed (`memory_limit = 256M`)

### Rate Limiting Bypass

Rate limiting uses IP address for anonymous users:

- Shared IPs (corporate networks) share rate limits
- VPNs can be used to bypass IP-based limits
- Consider using authenticated-only API access for sensitive sites
- Monitor for abuse patterns

---

## Security Checklist

Use this checklist to verify your installation is secure:

### Installation Security

- [ ] WordPress 6.4 or higher
- [ ] PHP 7.4 or higher (8.0+ recommended)
- [ ] HTTPS/SSL enabled
- [ ] Plugin installed from official source
- [ ] All dependencies up to date (`composer update`)
- [ ] File permissions set correctly (644 for files, 755 for directories)

### Configuration Security

- [ ] Strong admin password
- [ ] Two-factor authentication enabled
- [ ] Application Passwords used for API (not main password)
- [ ] REST API enabled only if needed
- [ ] Rate limit configured appropriately
- [ ] Button visibility set appropriately
- [ ] Only necessary post types enabled

### Operational Security

- [ ] Regular backups configured
- [ ] Security monitoring in place
- [ ] Error logging enabled (but logs secured)
- [ ] Regular security audits
- [ ] WordPress and plugins kept updated
- [ ] User roles and permissions reviewed
- [ ] Unused users/accounts removed

### Development Security (if customizing)

- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] Nonces used for forms/AJAX
- [ ] Capability checks in place
- [ ] No sensitive data in git
- [ ] Security testing performed
- [ ] Code review completed

---

## Security Resources

### WordPress Security

- [WordPress Security Codex](https://wordpress.org/support/article/hardening-wordpress/)
- [WordPress Security White Paper](https://wordpress.org/about/security/)
- [Plugin Security Best Practices](https://developer.wordpress.org/plugins/security/)

### PHP Security

- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

### General Web Security

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Web Security Academy](https://portswigger.net/web-security)

### Security Tools

- **PHPCS Security Scanning**: `composer require --dev phpcompatibility/phpcompatibility-wp`
- **WPScan**: [wpscan.com](https://wpscan.com/)
- **Sucuri**: [sucuri.net](https://sucuri.net/)

---

## Disclosure Policy

### Our Commitment

We are committed to:
- Promptly addressing security issues
- Transparent communication with researchers
- Credit for responsible disclosure
- Protecting our users

### Vulnerability Disclosure Timeline

1. **Day 0**: Vulnerability reported privately
2. **Day 1**: Initial response and acknowledgment
3. **Day 1-7**: Assessment and validation
4. **Day 7-30**: Fix developed and tested
5. **Day 30**: Security release published
6. **Day 30-90**: Coordinated public disclosure
7. **Day 90**: Full public disclosure (if not disclosed earlier)

### Public Disclosure

After a fix is released and users have had time to update:
- Security advisory published on GitHub
- Details added to CHANGELOG.md
- CVE number assigned (if applicable)
- Credit given to researcher (if desired)

---

## Security Hall of Fame

We'd like to thank the following security researchers for responsibly disclosing vulnerabilities:

*(No vulnerabilities reported yet for v1.0.0)*

**Want to be listed here?** Report a valid security vulnerability responsibly!

---

## Contact

**Security Issues**: Use GitHub Security Advisories (recommended)

**General Security Questions**: Open a public discussion on GitHub (for non-sensitive questions)

**PGP Key**: *(Coming soon)*

---

**Last Updated**: 2025-11-07
**Policy Version**: 1.0
**Plugin Version**: 1.0.0
