# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP library for generating product feeds according to the OpenAI specification.

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

### PHP Code Sniffer
- PSR-2 and PSR-12 standards
- Line length checks are explicitly excluded from PSR-2
- Any issues resulting from the use of property hooks should be ignored, PHP Code Sniffer currently does not support this

### PHPUnit
- All tests located in `tests/` directory
- Bootstrap file: `vendor/autoload.php`
- Configuration: `phpunit.xml`

## Project Structure

```
src/ElliotJReed/Entity/    # Entity classes
tests/ElliotJReed/         # Test classes (mirrors src/ structure)
```

### Autoloading
- Source code: PSR-4 autoloading with namespace `ElliotJReed\` mapped to `src/ElliotJReed/`
- Tests: PSR-4 autoloading with namespace `ElliotJReed\Tests\` mapped to `tests/ElliotJReed/`

## CI/CD

GitHub Actions workflow (`.github/workflows/php.yml`) runs on every push:
- Tests against PHP 8.4 and 8.5
- Executes unit tests with coverage
