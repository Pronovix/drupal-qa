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

namespace Pronovix\DrupalQa\Tests\FileSystem;

use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;

/**
 * @phpstan-type FileExistsCallback callable(string):bool
 * @phpstan-type CopyCallback callable(string,string):void
 */
final class ConfigurableFakeFileSystem implements FileSystemInterface
{
    /**
     * @var array<callable>
     * @phpstan-var array<FileExistsCallback>
     */
    private array $fileExistsCallbackQueue = [];

    /**
     * @var array<callable>
     * @phpstan-var array<CopyCallback>
     */
    private array $copyCallbackQueue = [];

    /**
     * @var array<callable(string,int):void>
     */
    private array $chmodCallbackQueue = [];

    /**
     * {@inheritDoc}
     */
    public function copy(string $source_path, string $destination_path): void
    {
        if ([] === $this->copyCallbackQueue) {
            throw new \LogicException('No callback is set for ' . __METHOD__);
        }

        $callback = array_shift($this->copyCallbackQueue);
        $callback($source_path, $destination_path);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFile(string $path): void
    {
        throw new \LogicException('Unimplemented method: ' . __METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function relativeSymlink(string $symlink_to, string $create_at): void
    {
        throw new \LogicException('Unimplemented method: ' . __METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function ensureDirectoryExists(string $path, int $visibility): void
    {
        throw new \LogicException('Unimplemented method: ' . __METHOD__);
    }

    /**
     * {@inheritDoc}
     */
    public function fileExists(string $path): bool
    {
        if ([] === $this->fileExistsCallbackQueue) {
            throw new \LogicException('No callback is set for ' . __METHOD__);
        }

        $callback = array_shift($this->fileExistsCallbackQueue);

        return $callback($path);
    }

    /**
     * {@inheritDoc}
     */
    public function chmod(string $path, int $visibility): void
    {
        if ([] === $this->chmodCallbackQueue) {
            throw new \LogicException('No callback is set for ' . __METHOD__);
        }

        $callback = array_shift($this->chmodCallbackQueue);
        $callback($path, $visibility);
    }

    /**
     * @param callable ...$callbacks
     * @phpstan-param FileExistsCallback ...$callbacks
     */
    public function addFileExistsCallbacks(callable ...$callbacks): ConfigurableFakeFileSystem
    {
        $this->fileExistsCallbackQueue = [...$this->fileExistsCallbackQueue, ...$callbacks];

        return $this;
    }

    /**
     * @param callable ...$callbacks
     * @phpstan-param CopyCallback ...$callbacks
     */
    public function addCopyCallbacks(callable ...$callbacks): ConfigurableFakeFileSystem
    {
        $this->copyCallbackQueue = [...$this->copyCallbackQueue, ...$callbacks];

        return $this;
    }

    /**
     * @param callable ...$callbacks
     *
     * @return $this
     */
    public function addChmodCallbacks(callable ...$callbacks): self
    {
        $this->chmodCallbackQueue = [...$this->chmodCallbackQueue, ...$callbacks];

        return $this;
    }
}
