# Drupal QA

Set of modules and libraries and configurations that helps quality assurance in Drupal.

## Available commands

* `composer drupalqa:phpcs:config-install` - installs Pronovix's PHP CodeSniffer configuration for Drupal projects
(the plugin tries to install it automatically when it gets installed)
* `composer drupalqa:testrunner:download` - installs latest version of Pronovix's TestRunner Go application from [Github](https://github.com/Pronovix/testrunner).
(You can avoid API rate limit error if you [configure our Github OAuth access token](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).)
* `composer normalize` - Normalizes the composer.json (provided by `localheinz/composer-normalize`)
* `./vendor/bin/drupal-check` - Checks Drupal 8 code for deprecations and code quality issues. (provided by `mglaman/drupal-check`)
* `./vendor/bin/twigcs` - Checks TWIG files for violations on coding standards. (provided by `friendsoftwig/twigcs`)

## Packages included

### Code quality

* Composer Normalize: https://github.com/localheinz/composer-normalize
* PHP CodeSniffer Standards Composer Installer Plugin: https://github.com/Dealerdirect/phpcodesniffer-composer-installer
* Drupal Check: https://github.com/mglaman/drupal-check
* Slevomat Coding Standard: https://github.com/slevomat/coding-standard

### Testing

* Behat Screenshot Extension: https://github.com/elvetemedve/behat-screenshot
* Behat Drupal Extension: https://github.com/jhedstrom/drupalextension

Plus various other packages (like Drupal Coder, PHPUnit, etc.) required by [webflo/drupal-core-require-dev](https://github.com/webflo/drupal-core-require-dev).

## Notes

* [Introduce new --drupal-root option](https://github.com/mglaman/drupal-check/pull/98.diff) patch is required for `mglaman/drupal-check` if
you use a monorepo structure where your modules are symlinked from the monorepo root.
