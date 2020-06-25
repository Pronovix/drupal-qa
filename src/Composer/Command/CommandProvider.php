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

use Composer\Plugin\Capability\CommandProvider as BaseCommandProvider;
use Composer\Plugin\PluginInterface;
use Composer\Repository\Vcs\GitHubDriver;
use Composer\Util\Filesystem as ComposerFileSystem;
use Composer\Util\HttpDownloader;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use Pronovix\DrupalQa\Composer\Handler\PhpCsConfigInstaller;
use Pronovix\DrupalQa\Composer\Handler\TestRunnerDownloader;
use Pronovix\DrupalQa\Logger\Logger;
use Symfony\Component\Filesystem\Filesystem;

final class CommandProvider implements BaseCommandProvider
{
    /** @var \Composer\Composer */
    private $composer;

    /** @var \Pronovix\DrupalQa\Logger\Logger */
    private $logger;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * @var \Composer\Util\ProcessExecutor
     */
    private $process;

    /**
     * @var \Composer\Util\Filesystem
     */
    private $fileSystem;

    /**
     * CommandProvider constructor.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->composer = $args['composer'];
        $this->io = $args['io'];
        $this->logger = new Logger($args['io']);
        $this->process = new ProcessExecutor($args['io']);
        $this->fileSystem = new ComposerFilesystem();
    }

    /**
     * @inheritDoc
     */
    public function getCommands(): array
    {
        $remoteFileSystem = new RemoteFilesystem($this->io, $this->composer->getConfig());
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '<')) {
            $gitHubDriver = new GitHubDriver(['url' => 'https://github.com/' . TestRunnerDownloader::TESTRUNNER_REPO_NAME], $this->io, $this->composer->getConfig(), $this->process, $remoteFileSystem);
        } else {
            $gitHubDriver = new GitHubDriver(['url' => 'https://github.com/' . TestRunnerDownloader::TESTRUNNER_REPO_NAME], $this->io, $this->composer->getConfig(), new HttpDownloader($this->io, $this->composer->getConfig()), $this->process);
        }

        return [
            new InstallPhpCsConfigCommand(new PhpCsConfigInstaller($this->composer, $this->fileSystem, $this->logger), $this->logger),
            new DownloadTestRunnerCommand(new TestRunnerDownloader($gitHubDriver, $remoteFileSystem, $this->io, $this->fileSystem, new Filesystem(), $this->logger), $this->logger),
        ];
    }
}
