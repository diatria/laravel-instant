# Upgrade guide

## Current development line

- Routes are disabled by default. Enable `laravel-instant.route.enabled` explicitly.
- Migration loading is disabled by default. Enable `laravel-instant.database.migrations.enabled` explicitly.
- Published models now target `app/Models/LaravelInstant` to avoid overwriting application models.
- The route prefix key is `laravel-instant.route.prefix`.
- Validation failures use HTTP 422.
- Unknown query fields and invalid order directions are rejected by default.

Before upgrading, publish the new config, review route/migration settings, and run the package test suite.
