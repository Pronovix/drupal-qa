<?php

declare(strict_types=1);

/**
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
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

namespace Pronovix\DrupalQa\Exception;

class CouldNotBeSymlinkedException extends RuntimeException
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $destination;

    /**
     * CouldNotBeSymlinkedException constructor.
     *
     * @param string $source
     * @param string $destination
     * @param \Throwable|null $previous
     */
    public function __construct(string $source, string $destination, \Throwable $previous = null)
    {
        $this->source = $source;
        $this->destination = $destination;
        $error_message = 'Unable to symlink PHPCS configuration from "{source}" to "{destination}".';
        $error_context = ['{source}' => $source, '{destination}' => $destination];
        if (null !== $previous) {
            $error_context['reason'] = $previous->getMessage();
            $error_message .= ' Reason: {reason}.';
        }

        parent::__construct(strtr($error_message, $error_context), 0, $previous);
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }
}
