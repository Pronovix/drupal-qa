<?php

declare(strict_types=1);

/**
 * Copyright (C) 2019-2022 PRONOVIX GROUP.
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

namespace Pronovix\DrupalQa\Domain\FileSystem\Exception;

use Pronovix\DrupalQa\Exception\RuntimeException;

class UnableToCreateSymlink extends RuntimeException
{
    private string $symlinkTo;

    private string $createAt;

    /**
     * UnableCreateSymlink constructor.
     */
    public function __construct(string $symlink_to, string $create_at, \Throwable $previous = null)
    {
        $this->symlinkTo = $symlink_to;
        $this->createAt = $create_at;
        $error_message = 'Unable to create symlink to "{symlink_to}" at "{location}".';
        $error_context = ['{symlink_to}' => $symlink_to, '{location}' => $create_at];
        if (null !== $previous) {
            $error_context['reason'] = $previous->getMessage();
            $error_message .= ' Reason: {reason}.';
        }

        parent::__construct(strtr($error_message, $error_context), 0, $previous);
    }

    public function symlinkTo(): string
    {
        return $this->symlinkTo;
    }

    public function createAt(): string
    {
        return $this->createAt;
    }
}
