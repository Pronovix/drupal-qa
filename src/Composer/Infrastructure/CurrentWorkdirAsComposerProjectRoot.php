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

namespace Pronovix\DrupalQa\Composer\Infrastructure;

use Composer\Factory;
use Pronovix\DrupalQa\Composer\Domain\Service\ComposerProjectRootLocatorInterface;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;
use Pronovix\DrupalQa\Exception\LogicException;

final class CurrentWorkdirAsComposerProjectRoot implements ComposerProjectRootLocatorInterface
{
    private FileSystemInterface $fileSystem;

    /**
     * Constructs a new object.
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        // Get the project root's absolute path from the root composer file path.
        // This is given as a relative path.
        $composer_file_path = Factory::getComposerFile();

        if (!$this->fileSystem->fileExists($composer_file_path)) {
            throw new LogicException(sprintf('%s file not found in %s path.', $composer_file_path, \getcwd()));
        }

        return \realpath(\dirname($composer_file_path));
    }
}
