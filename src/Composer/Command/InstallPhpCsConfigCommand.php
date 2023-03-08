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

namespace Pronovix\DrupalQa\Composer\Command;

use Composer\Command\BaseCommand;
use Pronovix\DrupalQa\Composer\Application\PhpCsConfigInstaller;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InstallPhpCsConfigCommand extends BaseCommand
{
    private PhpCsConfigInstaller $phpCsConfigInstaller;

    private LoggerInterface $logger;

    public function __construct(
      PhpCsConfigInstaller $phpCsConfigInstaller,
      LoggerInterface $logger
    ) {
        parent::__construct('drupalqa:phpcs:config-install');
        $this->phpCsConfigInstaller = $phpCsConfigInstaller;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this
          ->setDescription('Initializes PHP CodeSniffer configuration in the current directory or the given destination.')
          ->setDefinition([
            new InputArgument(
                'destination',
                InputArgument::OPTIONAL,
                'Directory where the file should be symlinked instead of the current directory.'
            ),
          ]);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress PossiblyInvalidArgument $destination cannot be something
     *   else than string or null.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $destination = $input->getArgument('destination');
            if (is_array($destination)) {
                throw new InvalidArgumentException('"destination" parameter must be a string.');
            }
            $this->phpCsConfigInstaller->install($destination);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
