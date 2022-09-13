# Drupal QA

Set of modules and libraries and configurations that helps quality assurance in Drupal.

## Automated tasks executed when this plugin is installed
* [phpcs.xml.dist](./config/phpcs.xml.dist) gets symlinked to the Composer project root if it does not exist
* [phpstan.neon.dist](./config/skeletons/phpstan.neon.dist) and [phpstan.baseline.neon](./config/skeletons/phpstan-baseline.neon)
gets copied to the Composer project root if they do not exist
* **!!!** ./vendor/bin/drupal-check binary gets replaced with our custom [DrupalCheck-PHPStan bridge](./config/skeletons/drupal-check-phpstan-bridge.php)
for the sake of backward compatibility with previous 3.x versions

## Available commands

### Drupal QA commands
* `composer drupalqa:phpcs:config-install` - installs Pronovix's PHP CodeSniffer configuration for Drupal projects
(the plugin tries to install it automatically when it gets installed)
* `composer drupalqa:testrunner:download` - installs latest version of Pronovix's TestRunner Go application from [Github](https://github.com/Pronovix/testrunner).
(You can avoid API rate limit error if you [configure your Github OAuth access token](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).)
* `drupalqa:phpstan:install-drupal-check-bridge` Installs the [DrupalCheck-PHPStan bridge](./config/skeletons/drupal-check-phpstan-bridge.php)
* `drupalqa:phpstan:ensure-configs-exist` ensures base configurations for PHPStan (stored in [./config/skeletons/](./config/skeletons)) are
available in Composer project root

### 3rd-party commands
* `composer normalize` - Normalizes the composer.json (provided by `localheinz/composer-normalize`)
* ~~`./vendor/bin/drupal-check` - Checks Drupal 9+ code for deprecations and code quality issues.~~ **Deprecated**
 use `./vendor/bin/phpstan` directly instead either with the provided PHPStan configs or a custom one. `mglaman/drupal-check` package
 dependency was removed in 3.8.0 and replaced with a best effort but still dummy command that proxies every `./vendor/bin/drupal-check`
 call to `./vendor/bin/phpstan`.
* `./vendor/bin/twigcs` - Checks TWIG files for violations on coding standards. (provided by `friendsoftwig/twigcs`)

## Packages included

### Code quality

* Composer Normalize: https://github.com/localheinz/composer-normalize
* PHP CodeSniffer Standards Composer Installer Plugin: https://github.com/Dealerdirect/phpcodesniffer-composer-installer
* ~~Drupal Check: https://github.com/mglaman/drupal-check~~
* PHPStan: https://github.com/phpstan/phpstan
* Slevomat Coding Standard: https://github.com/slevomat/coding-standard

### Testing

* Behat Screenshot Extension: https://github.com/elvetemedve/behat-screenshot
* Behat Drupal Extension: https://github.com/jhedstrom/drupalextension

Plus various other packages (like Drupal Coder, PHPUnit, etc.) required by [webflo/drupal-core-require-dev](https://github.com/webflo/drupal-core-require-dev).

## Development notes

**ALL** classes, interfaces in this project are internal and not meant to be used by other projects.
**No backward-compatibility promise is given for these.**

Running QA checks:
* First fix auto-fixable issues with `composer lint:fix && composer static:fix`
* then run checks`composer lint:check && composer static:check`
