<?php

declare(strict_types=1);

/**
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
 */

namespace Pronovix\DrupalQa\Drupal\PhpUnit;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator as DrupalRecursiveExtensionFilterIterator;

/**
 * Improved version of Drupal's RecursiveExtensionFilterIterator for PHPUnit.
 *
 * This class and bootstrap.php was created to be able to run PHPUnit tests in
 * setups where modules, themes or profiles may contain a "build" folder with
 * a Drupal core and a symlink that to themselves which could lead to infinite
 * recursions when the original PHPUnit bootstrap script tries to discovery
 * available tests.
 *
 * Example structure:
 * ├── build
 * │   └── web
 * │       ├── core
 * │       ├── index.php
 * │       ├── modules
 * │       |    ├── drupal_module -> ../../../
 * └──  my_module.info.yml
 *
 * Related issues on Drupal.org:
 * https://www.drupal.org/project/drupal/issues/2943172
 * https://www.drupal.org/project/drupal/issues/3050881
 */
final class DrupalExtensionFilterIterator extends DrupalRecursiveExtensionFilterIterator
{
    /**
     * DrupalExtensionFilterIterator constructor.
     *
     * @param \RecursiveIterator $iterator
     *   The iterator to filter.
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        // We should not initialize Settings here to retrieve
        // `file_scan_ignore_directories` here although that would remove some
        // code duplications.
        parent::__construct($iterator, ['build', 'node_modules', 'bower_components']);
    }
}
