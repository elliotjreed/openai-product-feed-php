# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP library for generating product feeds according to the OpenAI specification.

The authoritative sources are https://developers.openai.com/commerce/specs/file-upload/products and
https://developers.openai.com/ads/product-feeds. Note that OpenAI has renamed several fields since an earlier
revision of the specification: `id` is now `item_id`, `link` is `url`, `image_link` is `image_url`, `enable_search`
is `is_eligible_search` and `enable_checkout` is `is_eligible_checkout`. The old names are accepted by OpenAI as
legacy aliases but this library always writes the current ones. `Internal/Columns.php` is the single source of truth
for the field set and the order it is written in; adding an attribute means adding it there, to the relevant trait in
`Product/`, and to the attribute reference table in the README.

## Requirements

- PHP 8.4 or above
- Composer for dependency management

## Development Commands

### Installing Dependencies
```bash
composer install
```

### Running Tests
```bash
# Run all tests (includes PHPUnit with coverage + code style checks)
composer test

# Run unit tests only with coverage report
composer phpunit

# Run unit tests with coverage (HTML + text output)
composer phpunit:coverage

# Debug mode (stops on first failure)
composer phpunit:debug

# CI mode (coverage text only)
composer phpunit:ci
```

### Code Style and Formatting
```bash
# Run PHP-CS-Fixer and PHP Code Sniffer
composer phpcs

# CI mode (dry-run for PHP-CS-Fixer)
composer phpcs:ci
```

### Other Commands
```bash
# Check for outdated dependencies
composer outdated

# Validate composer.json
composer validate --no-check-publish
```

### Using GNU Make
All composer commands can alternatively be run via Make:
```bash
make test          # Runs phpunit:coverage, phpcs, composer validate, and composer outdated
make phpunit       # Run unit tests with coverage
make debug         # Run tests in debug mode
make phpcs         # Run code style checks
make clean         # Remove build/ and vendor/ directories
```

## Code Standards

The project enforces strict code quality standards:

### PHP-CS-Fixer Configuration
- PSR-2 and PSR-12 compliant
- Symfony coding standards with risky rules enabled
- Specific rules include:
  - Single space for type declarations
  - Global namespace imports for classes
  - Concatenation with single spacing
  - Native function invocation optimization
  - `declare_strict_types` set to `add_when_missing`. Without this the fixer strips the `declare(strict_types=1)`
    line from every file, so do not remove it.

### PHP Code Sniffer
- PSR-2 and PSR-12 standards
- Line length checks are explicitly excluded from PSR-2
- Any issues resulting from the use of property hooks should be ignored, PHP Code Sniffer currently does not support this

### PHPUnit
- All tests located in `tests/` directory
- Bootstrap file: `vendor/autoload.php`
- Configuration: `phpunit.xml`
- The two tests which stream a 200,000 product catalogue are in the `slow` group and take around 30 seconds. Run
  `vendor/bin/phpunit --exclude-group slow` while iterating.
- Coverage is complete; keep it that way. Internal classes are only attributed coverage when a test names them in a
  `#[CoversClass]` attribute, so a new internal class needs one adding to whichever test exercises it.

## Project Structure

```
src/ElliotJReed/OpenAiProductFeed/
    Product.php            # A single item, composed of one trait per specification section
    Feed.php               # JSON Lines feed (OpenAI's preferred format)
    CsvFeed.php            # CSV / TSV feed
    Attribute/             # The attributes richer than a single value (Dimensions, VariantOptions, ...)
    Enum/                  # One enum per restricted value set in the specification
    Exception/             # InvalidAttribute, InvalidFeedState, and the shared interface
    Internal/              # Validation, column order, writers - not part of the public API
    Product/               # The attribute traits Product composes
tests/ElliotJReed/         # Test classes (mirrors src/ structure)
```

### Autoloading
- Source code: PSR-4 autoloading with namespace `ElliotJReed\` mapped to `src/ElliotJReed/`
- Tests: PSR-4 autoloading with namespace `ElliotJReed\Tests\` mapped to `tests/ElliotJReed/`

## CI/CD

GitHub Actions workflow (`.github/workflows/php.yml`) runs on every push:
- Tests against PHP 8.4 and 8.5
- Executes unit tests with coverage
