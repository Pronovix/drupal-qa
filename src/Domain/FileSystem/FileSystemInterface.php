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

namespace Pronovix\DrupalQa\Domain\FileSystem;

/**
 * Limited, but good enough filesystem definition for this project.
 */
interface FileSystemInterface
{
    /**
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCopyFile
     */
    public function copy(string $source_path, string $destination_path): void;

    /**
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToDeleteFile
     */
    public function deleteFile(string $path): void;

    /**
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCreateSymlink
     */
    public function relativeSymlink(string $symlink_to, string $create_at): void;

    /**
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCreateDirectory
     */
    public function ensureDirectoryExists(string $path, int $visibility): void;

    public function fileExists(string $path): bool;

    /**
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToSetVisibility
     */
    public function chmod(string $path, int $visibility): void;
}
