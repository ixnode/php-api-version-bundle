# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Releases

### [0.1.25] - 2024-06-06

* Disable 404 status on version endpoint
* Add new description to version endpoint

### [0.1.24] - 2024-05-31

* Update symfony to 7.1

### [0.1.23] - 2024-05-31

* Update symfony to 7.0

### [0.1.22] - 2023-12-30

* Fix isFilterBoolean method

### [0.1.21] - 2023-12-30

* Add isFilterBoolean method

### [0.1.20] - 2023-12-30

* Update Symfony from v6.3.0 to v6.3.11
* Update php-json-schema-validator from 0.1.2 to 0.1.4 and make some adoption to this version

### [0.1.19] - 2023-12-30

* Add SerializerInterface to BaseFunctionalCommandTest.php

### [0.1.18] - 2023-08-14

* Add environment and db driver name to version

### [0.1.17] - 2023-08-14

* Make service classes configurable

### [0.1.16] - 2023-08-14

* Refactoring

### [0.1.15] - 2023-08-14

* Add setConfigUseRepository method

### [0.1.14] - 2023-08-14

* Add setConfigUseRequestStack and  setConfigUseTranslator methods

### [0.1.13] - 2023-08-14

* Add RequestStack and TranslatorInterface to base functional command test

### [0.1.12] - 2023-07-01

* Switch to protected methods

### [0.1.11] - 2023-07-01

* Add ApiPlatform State Helper

### [0.1.10] - 2023-06-25

* Add doctrine and api platform version to command output

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
