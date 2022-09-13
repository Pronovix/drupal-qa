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

namespace Pronovix\DrupalQa\Domain\FileSystem\Exception;

use Pronovix\DrupalQa\Exception\RuntimeException;

final class UnableToSetVisibility extends RuntimeException
{
    private string $path;

    private int $visibility;

    private string $reason;

    public static function atLocation(string $path, int $visibility, string $reason = '', \Throwable $previous = null): self
    {
        $message = "Unable to set {$visibility} visibility for file at {$path}. $reason";
        $e = new static(rtrim($message), 0, $previous);
        $e->reason = $reason;
        $e->path = $path;
        $e->visibility = $visibility;

        return $e;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function visibility(): int
    {
        return $this->visibility;
    }
}
