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

namespace Pronovix\DrupalQa\Logger;

use Composer\IO\IOInterface;
use Pronovix\ComposerLogger\Logger as ComposerLogger;
use Psr\Log\AbstractLogger;

/**
 * PSR-3 logger wrapper around IOInterface.
 */
final class Logger extends AbstractLogger
{
    /**
     * @var \Pronovix\ComposerLogger\Logger
     */
    private $decorated;

    /**
     * @inheritDoc
     */
    public function __construct(IOInterface $io)
    {
        $this->decorated = new ComposerLogger('Drupal QA', $io);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        $this->decorated->log($level, $message, $context);
    }
}
