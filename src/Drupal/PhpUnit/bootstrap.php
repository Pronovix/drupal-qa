<?php

/**
 * @file
 * Custom autoloader for Drupal PHPUnit testing.
 *
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *  *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 * USA.
 *
 * Forked version of Drupal's PHPUnit bootstrap script.
 * https://github.com/drupal/core/commits/8.7.6/tests/bootstrap.php
 *
 * What we changed:
 * - This script uses the PhpUnitDrupalExtensionFilterIterator to discover
 * potential extension directories with tests but excludes build
 * directories that may contain a Drupal core and a symlinked version of the
 * tested components that could lead to infinite recursion. See more info in
 * PhpUnitDrupalExtensionFilterIterator.
 * - The Drupal root is automatically calculated from the install location of
 * this library (vendor/pronovix/drupal-qa) and it is expected to be in the
 * "web" folder. You can use the DRUPAL_QA_PHPUNIT_DRUPAL_ROOT environment
 * variable to override the webroot.
 *
 * Usage: ./vendor/bin/phpunit -c web/core --bootstrap vendor/pronovix/drupal-qa/src/Drupal/PhpUnit/bootstrap.php
 */

use Drupal\Component\Assertion\Handle;
use Drupal\Core\Composer\Composer;
use Drupal\TestTools\PhpUnitCompatibility\PhpUnit8\ClassWriter;
use PHPUnit\Runner\Version;
use Pronovix\DrupalQa\Drupal\PhpUnit\DrupalExtensionFilterIterator;

/**
 * Finds all valid extension directories recursively within a given directory.
 *
 * @param string $scan_directory
 *   The directory that should be recursively scanned.
 * @return array
 *   An associative array of extension directories found within the scanned
 *   directory, keyed by extension name.
 */
function drupal_phpunit_find_extension_directories($scan_directory) {
  $extensions = [];
  $filter = new DrupalExtensionFilterIterator(new \RecursiveDirectoryIterator($scan_directory, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_SELF));
  $filter->acceptTests(TRUE);
  $dirs = new \RecursiveIteratorIterator($filter);
  foreach ($dirs as $dir) {
    if (strpos($dir->getPathname(), '.info.yml') !== FALSE) {
      // Cut off ".info.yml" from the filename for use as the extension name. We
      // use getRealPath() so that we can scan extensions represented by
      // directory aliases.
      $extensions[substr($dir->getFilename(), 0, -9)] = $dir->getPathInfo()
        ->getRealPath();
    }
  }
  return $extensions;
}

/**
 * Returns directories under which contributed extensions may exist.
 *
 * @param string $root
 *   (optional) Path to the root of the Drupal installation.
 *
 * @return array
 *   An array of directories under which contributed extensions may exist.
 */
function drupal_phpunit_contrib_extension_directory_roots($root = NULL) {
  if ($root === NULL) {
    $root = dirname(dirname(__DIR__));
  }
  $paths = [
    $root . '/core',
    $root . '/',
  ];
  $sites_path = $root . '/sites';
  // Note this also checks sites/../modules and sites/../profiles.
  foreach (scandir($sites_path) as $site) {
    if ($site[0] === '.' || $site === 'simpletest') {
      continue;
    }
    $path = "$sites_path/$site";
    $paths[] = is_dir("$path/modules") ? realpath("$path/modules") : '';
    $paths[] = is_dir("$path/profiles") ? realpath("$path/profiles") : '';
    $paths[] = is_dir("$path/themes") ? realpath("$path/themes") : '';
  }
  return array_filter($paths, 'file_exists');
}

/**
 * Registers the namespace for each extension directory with the autoloader.
 *
 * @param array $dirs
 *   An associative array of extension directories, keyed by extension name.
 *
 * @return array
 *   An associative array of extension directories, keyed by their namespace.
 */
function drupal_phpunit_get_extension_namespaces($dirs) {
  $suite_names = ['Unit', 'Kernel', 'Functional', 'Build', 'FunctionalJavascript'];
  $namespaces = [];
  foreach ($dirs as $extension => $dir) {
    if (is_dir($dir . '/src')) {
      // Register the PSR-4 directory for module-provided classes.
      $namespaces['Drupal\\' . $extension . '\\'][] = $dir . '/src';
    }
    $test_dir = $dir . '/tests/src';
    if (is_dir($test_dir)) {
      foreach ($suite_names as $suite_name) {
        $suite_dir = $test_dir . '/' . $suite_name;
        if (is_dir($suite_dir)) {
          // Register the PSR-4 directory for PHPUnit-based suites.
          $namespaces['Drupal\\Tests\\' . $extension . '\\' . $suite_name . '\\'][] = $suite_dir;
        }
      }
      // Extensions can have a \Drupal\extension\Traits namespace for
      // cross-suite trait code.
      $trait_dir = $test_dir . '/Traits';
      if (is_dir($trait_dir)) {
        $namespaces['Drupal\\Tests\\' . $extension . '\\Traits\\'][] = $trait_dir;
      }
    }
  }
  return $namespaces;
}

// We define the COMPOSER_INSTALL constant, so that PHPUnit knows where to
// autoload from. This is needed for tests run in isolation mode, because
// phpunit.xml.dist is located in a non-default directory relative to the
// PHPUnit executable.
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
  define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../../autoload.php');
}

/**
 * Populate class loader with additional namespaces for tests.
 *
 * We run this in a function to avoid setting the class loader to a global
 * that can change. This change can cause unpredictable false positives for
 * phpunit's global state change watcher. The class loader can be retrieved from
 * composer at any time by requiring autoload.php.
 */
function drupal_phpunit_populate_class_loader() {
  /** @var \Composer\Autoload\ClassLoader $loader */
  $webroot = getenv('DRUPAL_QA_PHPUNIT_DRUPAL_ROOT') === FALSE ? __DIR__ . '/../../../../../../web' : getenv('DRUPAL_QA_PHPUNIT_DRUPAL_ROOT');
  $loader = require "{$webroot}/autoload.php";
  $core_tests_dir = "{$webroot}/core/tests";

  // Start with classes in known locations.
  $loader->add('Drupal\\Tests', $core_tests_dir);
  $loader->add('Drupal\\TestSite', $core_tests_dir);
  $loader->add('Drupal\\KernelTests', $core_tests_dir);
  $loader->add('Drupal\\FunctionalTests', $core_tests_dir);
  $loader->add('Drupal\\FunctionalJavascriptTests', $core_tests_dir);
  $loader->add('Drupal\\TestTools', $core_tests_dir);

  if (!isset($GLOBALS['namespaces'])) {
    // Scan for arbitrary extension namespaces from core and contrib.
    $extension_roots = drupal_phpunit_contrib_extension_directory_roots($webroot);

    $dirs = array_map('drupal_phpunit_find_extension_directories', $extension_roots);
    $dirs = array_reduce($dirs, 'array_merge', []);
    $GLOBALS['namespaces'] = drupal_phpunit_get_extension_namespaces($dirs);
  }
  foreach ($GLOBALS['namespaces'] as $prefix => $paths) {
    $loader->addPsr4($prefix, $paths);
  }

  return $loader;
}

// Do class loader population.
$loader = drupal_phpunit_populate_class_loader();

if (class_exists('Drupal\TestTools\PhpUnitCompatibility\PhpUnit8\ClassWriter')) {
  ClassWriter::mutateTestBase($loader);
}

if (version_compare(Version::id(), '6.5', '<')) {
  $message = "Minimum required PHPUnit version is 6.5.";
  echo "\033[31m" . $message . "\n\033[0m";
  exit(1);
}

if (!Composer::upgradePHPUnitCheck(Version::id())) {
  $message = "PHPUnit testing framework version and PHP version mismatch. Run the command 'composer run-script drupal-phpunit-upgrade' in order to fix this.";
  echo "\033[31m" . $message . "\n\033[0m";
  exit(1);
}

// Set sane locale settings, to ensure consistent string, dates, times and
// numbers handling.
// @see \Drupal\Core\DrupalKernel::bootEnvironment()
setlocale(LC_ALL, 'C');

// Set appropriate configuration for multi-byte strings.
mb_internal_encoding('utf-8');
mb_language('uni');

// Set the default timezone. While this doesn't cause any tests to fail, PHP
// complains if 'date.timezone' is not set in php.ini. The Australia/Sydney
// timezone is chosen so all tests are run using an edge case scenario (UTC+10
// and DST). This choice is made to prevent timezone related regressions and
// reduce the fragility of the testing system in general.
date_default_timezone_set('Australia/Sydney');

// Runtime assertions. PHPUnit follows the php.ini assert.active setting for
// runtime assertions. By default this setting is on. Ensure exceptions are
// thrown if an assert fails, but this call does not turn runtime assertions on
// if they weren't on already.
Handle::register();
