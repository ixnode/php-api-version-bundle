# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Releases

### [0.1.9] - 2023-06-25

* Add symfony profiler

### [0.1.8] - 2023-06-24

* Add new docker setup with PHP 8.2, Apache 2.4, Cron and Supervisord

### [0.1.7] - 2023-06-24

* Add monolog

### [0.1.6] - 2023-06-24

* Composer update
* Add TypeCastingHelper

### [0.1.5] - 2023-01-09

* Port refactoring

### [0.1.4] - 2023-01-09

* General refactoring

### [0.1.3] - 2023-01-01

* DependencyInjection refactoring

### [0.1.2] - 2023-01-01

* Add DependencyInjection

### [0.1.1] - 2023-01-01

* Add composer bin configuration

### [0.1.0] - 2023-01-01

* Initial release
* Add src
* Add tests
  * PHP Coding Standards Fixer
  * PHPMND - PHP Magic Number Detector
  * PHPStan - PHP Static Analysis Tool
  * PHPUnit - The PHP Testing Framework
  * Rector - Instant Upgrades and Automated Refactoring
* Add README.md
* Add LICENSE.md
* Docker environment
* Composer requirements

## Add new version

```bash
# Checkout master branch
$ git checkout main && git pull

# Check current version
$ vendor/bin/version-manager --current

# Increase patch version
$ vendor/bin/version-manager --patch

# Change changelog
$ vi CHANGELOG.md

# Push new version
$ git add CHANGELOG.md VERSION && git commit -m "Add version $(cat VERSION)" && git push

# Tag and push new version
$ git tag -a "$(cat VERSION)" -m "Version $(cat VERSION)" && git push origin "$(cat VERSION)"
```
