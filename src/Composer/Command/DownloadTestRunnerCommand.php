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
use Pronovix\DrupalQa\Composer\Application\TestRunnerDownloader;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DownloadTestRunnerCommand extends BaseCommand
{
    /**
     * @var \Pronovix\DrupalQa\Composer\Application\TestRunnerDownloader
     */
    private $testRunnerDownloader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DownloadTestRunnerCommand constructor.
     */
    public function __construct(
      TestRunnerDownloader $testRunnerDownloader,
      LoggerInterface $logger
    ) {
        parent::__construct('drupalqa:testrunner:download');
        $this->testRunnerDownloader = $testRunnerDownloader;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();
        $this
          ->setDescription('Downloads latest version of testrunner.')
          ->setDefinition([
            new InputArgument(
              'destination',
              InputArgument::REQUIRED,
              'Directory where the file should be downloaded.'
            ),
            new InputOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite exiting file.'),
            new InputOption('no-progress', null, InputOption::VALUE_NONE, 'Do not output download progress.'),
          ]);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress PossiblyInvalidArgument $destination cannot be something
     *   else than string or null.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $destination = $input->getArgument('destination');
            if (is_array($destination) || empty($destination)) {
                throw new InvalidArgumentException('"destination" parameter must be a non-empty string.');
            }
            $this->testRunnerDownloader->download($destination, $input->getOption('overwrite'), !$input->getOption('no-progress'));

            return 0;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return 1;
        }
    }
}
