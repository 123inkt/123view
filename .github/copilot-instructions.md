# Agents.md

## Project Overview

123view is a Symfony-based code review and commit notification application built with PHP 8.3+ and Symfony 7.2. It provides features for creating code reviews, managing commit notifications, and integrating with version control systems.

## Core Architecture

**Backend (PHP/Symfony):**
- **Namespace**: `DR\Review\` - all PHP classes use this base namespace
- **Entity Layer**: Domain entities in `src/Entity/` organized by feature (Review, Repository, User, etc.)
- **Controller Layer**: Split into `Api/` and `App/` controllers, with mail controllers in `Mail/`
- **Service Layer**: Business logic in `src/Service/` with feature-based organization
- **Repository Layer**: Doctrine repositories in `src/Repository/` following entity structure
- **Message/Event System**: Async messaging in `src/Message/` with handlers in `src/MessageHandler/`
- **Form Layer**: Symfony forms in `src/Form/` organized by domain

**Frontend (TypeScript/Stimulus):**
- **Assets**: TypeScript controllers in `assets/ts/controllers/`
- **Styling**: SCSS files in `assets/styles/` with theme support (dark/light)
- **Build System**: Webpack Encore configuration in `webpack.config.js`

## Development Commands

**Frontend Development:**
```bash
npm run dev         # Development build
npm run watch       # Watch mode for development
npm run build       # Production build
npm run stylelint   # Lint SCSS files
npm run eslint      # Lint TypeScript files
```

**PHP Development:**
```bash
composer check          # Run all checks (PHPStan, PHPMD, PHPCS)
composer check:phpstan  # Static analysis
composer check:phpmd    # Mess detection
composer check:phpcs    # Code style check
composer fix:phpcbf     # Auto-fix code style
composer test           # Run all tests
composer test:unit      # Unit tests only
composer test:integration # Integration tests only
composer test:functional  # Functional tests only
```

**Docker Environment:**
```bash
./bin/start.sh      # Start development environment
./bin/install.sh    # Run installation wizard
```

## Key Domain Concepts

**Code Reviews:**
- Reviews are created from specific revisions (commits)
- Support for attaching/detaching revisions
- Comment system with threading and reactions
- Reviewer workflow with accept/reject states

**Repositories:**
- Git repository integration with credential management
- Branch and revision tracking
- External tool integrations (GitLab, etc.)

**Notifications:**
- Rule-based commit notifications
- Frequency controls (hourly, daily, weekly)
- Filter system for including/excluding commits
- Email delivery with theme support

## Test Structure

Tests are organized in three directories:
- `tests/Unit/` - Isolated unit tests
- `tests/Integration/` - Integration tests with database
- `tests/Functional/` - Full application tests

Use the specific test suites when working on particular areas to speed up feedback loops.

## Configuration Files

- `webpack.config.js` - Frontend build configuration
- `tsconfig.json` - TypeScript compiler settings
- `composer.json` - PHP dependencies and scripts
- `phpstan.neon` - Static analysis configuration
- `phpunit.xml.dist` - Test configuration
