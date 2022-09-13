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

namespace Pronovix\DrupalQa\Tests\FileSystem\Callbacks;

/**
 * @phpstan-import-type FileExistsCallback from \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem
 */
final class FileExistsCallbackCallCounterCallback
{
    private int $count = 0;

    /**
     * @var callable
     * @phpstan-var FileExistsCallback
     */
    private $callback;

    /**
     * Constructs a new object.
     *
     * @param callable $callback
     * @phpstan-param FileExistsCallback $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(string $path): bool
    {
        $result = ($this->callback)($path);
        ++$this->count;

        return $result;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
