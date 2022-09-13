<?php

declare(strict_types=1);

/**
 * Copyright (C) 2022 PRONOVIX GROUP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 * USA.
 */

namespace Pronovix\DrupalQa\Composer\Application;

use Pronovix\DrupalQa\Composer\Domain\Service\DrupalQaPathProviderInterface;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;

final class EnsurePhpStanConfigsExist
{
    private const SKELETON_FILES_IN_DRUPAL_QA = [
      'config/skeletons/phpstan.neon.dist' => 'phpstan.neon.dist',
      'config/skeletons/phpstan-baseline.neon' => 'phpstan-baseline.neon',
    ];

    private DrupalQaPathProviderInterface $qaPathProvider;

    private FileSystemInterface $fileSystem;

    /**
     * Constructs a new object.
     */
    public function __construct(
      DrupalQaPathProviderInterface $qaPathProvider,
      FileSystemInterface $fileSystem
    ) {
        $this->qaPathProvider = $qaPathProvider;
        $this->fileSystem = $fileSystem;
    }

    public function __invoke(string $at_location): void
    {
        foreach (self::SKELETON_FILES_IN_DRUPAL_QA as $relative_drupalqa_file_path => $destination_file_name) {
            $destination = $at_location . '/' . $destination_file_name;
            if ($this->fileSystem->fileExists($destination)) {
                continue;
            }

            $this->fileSystem->copy($this->qaPathProvider->getFilePathInPackage($relative_drupalqa_file_path), $destination);
        }
    }
}
