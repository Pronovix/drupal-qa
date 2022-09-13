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

namespace Pronovix\DrupalQa\Composer\Command;

use Composer\Command\BaseCommand;
use Pronovix\DrupalQa\Composer\Application\ReplaceDrupalCheckBinaryWithPhpStanBridge;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DrupalCheckPhpStanBridgeInstallerCommand extends BaseCommand
{
    private ReplaceDrupalCheckBinaryWithPhpStanBridge $replacer;

    public function __construct(ReplaceDrupalCheckBinaryWithPhpStanBridge $handler)
    {
        parent::__construct('drupalqa:phpstan:install-drupal-check-bridge');
        $this->replacer = $handler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            ($this->replacer)();
        } catch (\Exception $e) {
            $this->getIO()->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
