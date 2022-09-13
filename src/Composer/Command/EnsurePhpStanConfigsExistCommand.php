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
use Pronovix\DrupalQa\Composer\Application\EnsurePhpStanConfigsExist;
use Pronovix\DrupalQa\Composer\Domain\Service\ComposerProjectRootLocatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EnsurePhpStanConfigsExistCommand extends BaseCommand
{
    private EnsurePhpStanConfigsExist $handler;

    private ComposerProjectRootLocatorInterface $projectRootLocator;

    public function __construct(EnsurePhpStanConfigsExist $handler, ComposerProjectRootLocatorInterface $project_root_locator)
    {
        parent::__construct('drupalqa:phpstan:ensure-configs-exist');
        $this->handler = $handler;
        $this->projectRootLocator = $project_root_locator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            ($this->handler)($this->projectRootLocator->getPath());
        } catch (\Exception $e) {
            $this->getIO()->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
