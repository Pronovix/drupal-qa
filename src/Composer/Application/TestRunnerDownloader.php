<?php

declare(strict_types=1);

/**
 * Copyright (C) 2019-2022 PRONOVIX GROUP.
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

namespace Pronovix\DrupalQa\Composer\Application;

use Composer\IO\IOInterface;
use Composer\Repository\Vcs\GitHubDriver;
use Composer\Util\Filesystem as ComposerFilesystem;
use Composer\Util\RemoteFilesystem;
use Pronovix\DrupalQa\Exception\FileExistsException;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Pronovix\DrupalQa\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @todo Hexagonalize this and make it covered by tests. (Depend on domain
 *   services, etc.)
 * @todo Logging is a secondary and optional task, trigger an event instead that
 *   can be used for this purpose.
 */
final class TestRunnerDownloader
{
    public const TESTRUNNER_REPO_NAME = 'Pronovix/testrunner';

    private const TESTRUNNER_FILE_NAME = 'testrunner';

    /**
     * @var \Composer\Util\Filesystem
     */
    private $composerFileSystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Composer\Util\RemoteFilesystem
     */
    private $downloader;

    /**
     * @var \Composer\Repository\Vcs\GitHubDriver
     */
    private $githubDriver;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * TestRunnerDownloader constructor.
     */
    public function __construct(GitHubDriver $gitHubDriver, RemoteFilesystem $remoteFilesystem, IOInterface $io, ComposerFilesystem $composerFileSystem, Filesystem $filesystem, LoggerInterface $logger)
    {
        $this->composerFileSystem = $composerFileSystem;
        $this->logger = $logger;
        // TODO Should we use HttpDownloader with Composer 2.x instead?
        $this->downloader = $remoteFilesystem;
        // Make sure the driver is initialized.
        $this->githubDriver = $gitHubDriver;
        $this->io = $io;
        $this->filesystem = $filesystem;
    }

    /**
     * Downloads the TestRunner to the given directory.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     * @throws \Pronovix\DrupalQa\Exception\InvalidArgumentException
     * @throws \Pronovix\DrupalQa\Exception\FileExistsException
     */
    public function download(string $directory, bool $overwrite = false, bool $show_progress = true): void
    {
        try {
            $this->githubDriver->initialize();
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('Github driver initialization error. Reason: %s', $e->getMessage()), 0, $e);
        }

        $file_location = $this->getDestinationFilePath($directory, !$overwrite);

        $latest_version_id = $this->getLatestVersionId();

        // File name is also hardcoded at this moment.
        $release_url = $this->githubDriver->getRepositoryUrl() . "/releases/download/$latest_version_id/testrunner-linux-amd64";
        $this->logger->info('Downloading TestRunner from {url}.', ['url' => $release_url]);

        try {
            $this->downloader->copy($release_url, $release_url, $file_location, $show_progress);
            if ($show_progress) {
                // Fix output. Write a new empty line to the screen if download progress is visible.
                $this->io->write(['']);
            }
            $this->filesystem->chmod($file_location, 0755);
        } catch (IOException $e) {
            throw new RuntimeException(sprintf('TestRunner could be downloaded but we could not make it executable. Reason: %s', $e->getMessage()), 0, $e);
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('TestRunner could not be downloaded. Reason: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * Retrieves the latest release number of the TestRunner.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     */
    private function getLatestVersionId(): string
    {
        // For now, let's assume we have a published release for all tags.
        // Later we can revisit this and make this process more stable and
        // error prone.
        try {
            $tags = $this->githubDriver->getTags();
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf("TestRunner's latest version number could not be fetched. Reason: %s", $e->getMessage()), 0, $e);
        }
        $tags = array_keys($tags);

        // @todo Is this what we really want?
        if (empty($tags)) {
            return '';
        }

        return (string) reset($tags);
    }

    /**
     * Returns the path with the filename where the symlink should be created.
     *
     * @throws \Pronovix\DrupalQa\Exception\FileExistsException
     * @throws \Pronovix\DrupalQa\Exception\InvalidArgumentException
     */
    private function getDestinationFilePath(string $directory, bool $check_file_exists = true): string
    {
        $directory = $this->composerFileSystem->normalizePath($this->composerFileSystem->isAbsolutePath($directory) ? $directory : getcwd() . '/' . $directory);
        if ('' === $directory) {
            throw new InvalidArgumentException('Destination argument should not be an empty string');
        }

        try {
            $this->composerFileSystem->ensureDirectoryExists($directory);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("{$directory} does not exist and could not be created.");
        }

        if (!is_dir($directory)) {
            throw new InvalidArgumentException("{$directory} is not a directory.");
        }

        if (!is_writable($directory)) {
            throw new InvalidArgumentException("{$directory} is not writable.");
        }

        $destinationFile = realpath($directory) . '/' . self::TESTRUNNER_FILE_NAME;

        if ($check_file_exists && file_exists($destinationFile)) {
            throw new FileExistsException($destinationFile);
        }

        return $destinationFile;
    }
}
