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

use Composer\Util\Filesystem;
use Composer\Util\Silencer;
use Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCopyFile;
use Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCreateDirectory;
use Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCreateSymlink;
use Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToDeleteFile;
use Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToSetVisibility;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;

final class ComposerFileSystemAdapter implements FileSystemInterface
{
    private Filesystem $filesystem;

    /**
     * Constructs a new object.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     */
    public function copy(string $source_path, string $destination_path): void
    {
        $result = $this->filesystem->copy($source_path, $destination_path);
        if (!$result) {
            throw UnableToCopyFile::fromLocationTo($source_path, $destination_path);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function chmod(string $path, int $visibility): void
    {
        try {
            // Composer uses this method, just some extra guards were added.
            // @see \Composer\Installer\BinaryInstaller::installBinaries()
            // chmod() does not respect umask behind the scenes therefore we
            // have to enforce it.
            // @see https://developers.shopware.com/blog/2016/02/26/file-permissions-and-umask-in-php-and-shopware/
            // @todo umask() might need to be wrapped as well.
            $result = Silencer::call('chmod', $path, $visibility & ~umask());
        } catch (\Exception $e) {
            throw UnableToSetVisibility::atLocation($path, $visibility, $e->getMessage());
        }

        if (!$result) {
            throw UnableToSetVisibility::atLocation($path, $visibility, error_get_last()['message'] ?? '');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fileExists(string $path): bool
    {
        return \file_exists($path);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFile(string $path): void
    {
        try {
            $result = $this->filesystem->unlink($path);
        } catch (\Exception $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }

        if (false === $result) {
            throw UnableToDeleteFile::atLocation($path, error_get_last()['message'] ?? '');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function relativeSymlink(string $symlink_to, string $create_at): void
    {
        $result = $this->filesystem->relativeSymlink($symlink_to, $create_at);
        if (!$result) {
            throw new UnableToCreateSymlink($symlink_to, $create_at);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function ensureDirectoryExists(string $path, int $visibility): void
    {
        try {
            $this->filesystem->ensureDirectoryExists($path);
        } catch (\Exception $e) {
            // Some extra hardening and sanity checks based on https://github.com/thephpleague/flysystem/blob/21a9f9efab1c791e836bc0827d77bb4dea0ae778/src/Local/LocalFilesystemAdapter.php#L312.
            $mkdirError = \error_get_last();
            \clearstatcache(true, $path);

            if (!\is_dir($path)) {
                if (\file_exists($path)) {
                    throw UnableToCreateDirectory::atLocation($path, $e->getMessage());
                }
                throw UnableToCreateDirectory::atLocation($path, $mkdirError['message'] ?? '');
            }
        }

        $this->chmod($path, $visibility);
    }
}
