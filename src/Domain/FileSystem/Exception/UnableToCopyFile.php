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
use Throwable;

final class UnableToCopyFile extends RuntimeException
{
    private string $source;

    private string $destination;

    public static function fromLocationTo(
    string $source_path,
    string $destination_path,
    Throwable $previous = null
  ): self {
        $e = new static("Unable to copy file from $source_path to $destination_path", 0 , $previous);
        $e->source = $source_path;
        $e->destination = $destination_path;

        return $e;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function destination(): string
    {
        return $this->destination;
    }
}
