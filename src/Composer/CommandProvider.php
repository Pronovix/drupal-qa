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

namespace Pronovix\DrupalQa\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as BaseCommandProvider;
use Composer\Plugin\PluginInterface;
use Composer\Repository\Vcs\GitHubDriver;
use Composer\Util\Filesystem as ComposerFileSystem;
use Composer\Util\HttpDownloader;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use Pronovix\DrupalQa\Composer\Application\EnsurePhpStanConfigsExist;
use Pronovix\DrupalQa\Composer\Application\PhpCsConfigInstaller;
use Pronovix\DrupalQa\Composer\Application\ReplaceDrupalCheckBinaryWithPhpStanBridge;
use Pronovix\DrupalQa\Composer\Application\TestRunnerDownloader;
use Pronovix\DrupalQa\Composer\Command\DownloadTestRunnerCommand;
use Pronovix\DrupalQa\Composer\Command\DrupalCheckPhpStanBridgeInstallerCommand;
use Pronovix\DrupalQa\Composer\Command\EnsurePhpStanConfigsExistCommand;
use Pronovix\DrupalQa\Composer\Command\InstallPhpCsConfigCommand;
use Pronovix\DrupalQa\Composer\Infrastructure\BinDirPathFromComposerConfigProvider;
use Pronovix\DrupalQa\Composer\Infrastructure\ComposerFileSystemAdapter;
use Pronovix\DrupalQa\Composer\Infrastructure\CurrentWorkdirAsComposerProjectRoot;
use Pronovix\DrupalQa\Composer\Infrastructure\InstalledDrupalQaPathProvider;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;
use Pronovix\DrupalQa\Logger\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class CommandProvider implements BaseCommandProvider
{
    private Composer $composer;

    private LoggerInterface $logger;

    private IOInterface $io;

    private ProcessExecutor $process;

    private FileSystemInterface $fileSystem;

    /**
     * CommandProvider constructor.
     */
    public function __construct(array $args)
    {
        $this->composer = $args['composer'];
        $this->io = $args['io'];
        $this->logger = new Logger($args['io']);
        $this->process = new ProcessExecutor($args['io']);
        $this->fileSystem = new ComposerFileSystemAdapter(new ComposerFilesystem());
    }

    /**
     * {@inheritDoc}
     */
    public function getCommands(): array
    {
        $remoteFileSystem = new RemoteFilesystem($this->io, $this->composer->getConfig());
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0', '<')) {
            $gitHubDriver = new GitHubDriver(['url' => 'https://github.com/' . TestRunnerDownloader::TESTRUNNER_REPO_NAME], $this->io, $this->composer->getConfig(), $this->process, $remoteFileSystem);
        } else {
            $gitHubDriver = new GitHubDriver(['url' => 'https://github.com/' . TestRunnerDownloader::TESTRUNNER_REPO_NAME], $this->io, $this->composer->getConfig(), new HttpDownloader($this->io, $this->composer->getConfig()), $this->process);
        }

        $qa_path_provider = new InstalledDrupalQaPathProvider(
            $this->composer->getRepositoryManager()->getLocalRepository(),
            $this->composer->getInstallationManager(),
            $this->fileSystem
        );

        $project_root_path_provider = new CurrentWorkdirAsComposerProjectRoot($this->fileSystem);

        return [
            new InstallPhpCsConfigCommand(new PhpCsConfigInstaller(
                $qa_path_provider,
                $project_root_path_provider,
                $this->fileSystem,
                $this->logger
            ), $this->logger),
            new DownloadTestRunnerCommand(
                new TestRunnerDownloader(
                    $gitHubDriver,
                    $remoteFileSystem,
                    $this->io,
                    new ComposerFilesystem(),
                    new Filesystem(),
                    $this->logger
                ), $this->logger),
            new DrupalCheckPhpStanBridgeInstallerCommand(
                new ReplaceDrupalCheckBinaryWithPhpStanBridge(
                    $qa_path_provider,
                    new BinDirPathFromComposerConfigProvider($this->composer->getConfig()),
                    $this->fileSystem,
                )),
            new EnsurePhpStanConfigsExistCommand(
                new EnsurePhpStanConfigsExist(
                    $qa_path_provider,
                    $this->fileSystem
                ),
                $project_root_path_provider
            ),
        ];
    }
}
