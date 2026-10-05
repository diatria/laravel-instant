# Contributing

## Local checks

Run the complete check in Docker:

```bash
docker compose run --rm package-test
```

Or run individual commands when PHP and Composer are installed:

```bash
composer validate --strict
composer test
composer analyse
```

New behavior should include a focused test. Changes to HTTP behavior, configuration, routes, models, migrations, authentication, or permissions must update the README and release notes.

## Compatibility

The v1 compatibility target is PHP 7.4+ and Laravel 6–8. Avoid APIs introduced after Laravel 6 unless a compatibility guard and test are provided.
