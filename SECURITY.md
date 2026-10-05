# Security Policy

## Supported versions

Security fixes are applied to the latest tagged release and the active development branch.

## Reporting a vulnerability

Do not open a public issue for a suspected vulnerability. Send a private report to the package maintainer with the affected version, reproducible steps, impact, and suggested mitigation.

Never include production secrets, real tokens, passwords, or personal data in a report.

## Package security requirements

- Set `LI_SECRET_KEY` before enabling JWT authentication.
- Use HTTPS when cookies are enabled.
- Keep built-in routes disabled unless the application needs them.
- Review and customize route middleware before exposing RBAC endpoints.
- Keep dependency updates and Composer security checks in CI.
