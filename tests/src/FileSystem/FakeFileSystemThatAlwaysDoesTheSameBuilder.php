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

/**
 * @phpstan-import-type FileExistsCallback from \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem
 * @phpstan-import-type CopyCallback from \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem
 */
final class FakeFileSystemThatAlwaysDoesTheSameBuilder
{
    /**
     * @var callable|null
     * @phpstan-var FileExistsCallback|null
     */
    private $fileExistsCallback;

    /**
     * @var callable|null
     * @phpstan-var CopyCallback|null
     */
    private $copyCallback;

    /**
     * @phpstan-param FileExistsCallback $callback
     *
     * @param \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem $target
     *
     * @phpstan-return FileExistsCallback
     */
    public function loopFileExistsCallback(callable $callback, ConfigurableFakeFileSystem $target): callable
    {
        $that = $this;

        return static function (string $path) use ($callback, &$target, $that): bool {
            $target->addFileExistsCallbacks($that->loopFileExistsCallback($callback, $target));

            return $callback($path);
        };
    }

    /**
     * @phpstan-param CopyCallback $callback
     *
     * @param \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem $target
     *
     * @phpstan-return CopyCallback
     */
    public function loopCopyCallback(callable $callback, ConfigurableFakeFileSystem $target): callable
    {
        $that = $this;

        return static function (string $src, string $dest) use ($callback, &$target, $that): void {
            $target->addCopyCallbacks($that->loopCopyCallback($callback, $target));
            $callback($src, $dest);
        };
    }

    public function build(): ConfigurableFakeFileSystem
    {
        $obj = new ConfigurableFakeFileSystem();

        if (null !== $this->fileExistsCallback) {
            $obj->addFileExistsCallbacks($this->loopFileExistsCallback($this->fileExistsCallback, $obj));
        }

        if (null !== $this->copyCallback) {
            $obj->addCopyCallbacks($this->loopCopyCallback($this->copyCallback, $obj));
        }

        return $obj;
    }

    /**
     * @phpstan-param FileExistsCallback $fileExistsCallback
     */
    public function setFileExistsCallback(callable $fileExistsCallback): self
    {
        $this->fileExistsCallback = $fileExistsCallback;

        return $this;
    }

    public function setCopyCallback(callable $copyCallback): self
    {
        $this->copyCallback = $copyCallback;

        return $this;
    }
}
