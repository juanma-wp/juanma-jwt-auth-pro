# JWT Auth Pro Documentation

Comprehensive documentation for JWT Auth Pro - WordPress JWT authentication with RFC 9700 compliance.

## Getting Started

- **Quick Start**: [../README.md](../README.md) - Installation and basic usage
- **Development Setup**: [DEVELOPMENT.md](./DEVELOPMENT.md) - Local development configuration
- **CORS & Cookies**: [cors-and-cookies.md](./cors-and-cookies.md) - Cross-origin setup guide

## Security & Compliance

- **RFC 9700 Compliance**: [RFC-9700-COMPLIANCE.md](./RFC-9700-COMPLIANCE.md) - OAuth 2.0 Security Best Practices
- **RFC 7009 Compliance**: [RFC-7009-COMPLIANCE.md](./RFC-7009-COMPLIANCE.md) - Token Revocation Standard

## Cookie Configuration

Multiple guides available depending on your needs:

- **API Reference**: [cookie-configuration.md](./cookie-configuration.md) - Complete technical reference (constants, filters, methods)
- **Tutorial Guide**: [cookie-configuration-guide.md](./cookie-configuration-guide.md) - Step-by-step learning guide
- **Cross-Origin Setup**: [cors-and-cookies.md](./cors-and-cookies.md) - CORS configuration for SPAs

## Development

- **JavaScript Client**: [advanced-usage.md](./advanced-usage.md) - Frontend integration examples
- **Dependency Management**: [DEPENDENCY_MANAGEMENT.md](./DEPENDENCY_MANAGEMENT.md) - Composer setup
- **Development Guide**: [DEVELOPMENT.md](./DEVELOPMENT.md) - Cross-origin development

## Architecture & Reference

- **OpenAPI Specification**: [../plugin/juanma-jwt-auth-pro/openapi.yml](../plugin/juanma-jwt-auth-pro/openapi.yml) - Complete API reference
- **Plugin Settings**: Admin configuration available at `wp-admin/options-general.php?page=juanma-jwt-auth-pro`

## Looking for Something Specific?

- **Setting up a React app?** → [advanced-usage.md](./advanced-usage.md) + [DEVELOPMENT.md](./DEVELOPMENT.md)
- **Production deployment?** → [cookie-configuration.md](./cookie-configuration.md) + [RFC-9700-COMPLIANCE.md](./RFC-9700-COMPLIANCE.md)
- **Understanding security?** → Start with [RFC-9700-COMPLIANCE.md](./RFC-9700-COMPLIANCE.md)
- **Troubleshooting cookies?** → [cors-and-cookies.md](./cors-and-cookies.md) or [cookie-configuration-guide.md](./cookie-configuration-guide.md)

## For Plugin Developers

- **Extending the plugin**: Filter hooks documented in each file
- **Security standards**: RFC 9700 and RFC 7009 implementation details
- **Database schema**: Token storage and rotation mechanism

## Archive

Historical documents preserved for reference:

- [archive/RESTRUCTURING_PLAN.md](./archive/RESTRUCTURING_PLAN.md) - Historical planning
- [archive/RESTRUCTURING_COMPLETE.md](./archive/RESTRUCTURING_COMPLETE.md) - Completion notes

## Issues & Planning

Documentation improvement tracking:

- [issues/README.md](./issues/README.md) - Issue tracking overview
- [issues/01-HIGH-PRIORITY.md](./issues/01-HIGH-PRIORITY.md) - Critical documentation issues
- [issues/02-MEDIUM-PRIORITY.md](./issues/02-MEDIUM-PRIORITY.md) - Moderate priority improvements
- [issues/03-LOW-PRIORITY.md](./issues/03-LOW-PRIORITY.md) - Organizational improvements

---

**Last Updated**: 2025-12-14
**Plugin Version**: 1.2.x
**Documentation Status**: Actively Maintained
