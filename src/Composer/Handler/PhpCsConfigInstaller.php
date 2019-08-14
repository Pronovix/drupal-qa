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

namespace Pronovix\DrupalQa\Composer\Handler;

use Composer\Composer;
use Composer\Util\Filesystem;
use Pronovix\DrupalQa\Exception\CouldNotBeSymlinkedException;
use Pronovix\DrupalQa\Exception\FileExistsException;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Pronovix\DrupalQa\Exception\RuntimeException;
use Psr\Log\LoggerInterface;

final class PhpCsConfigInstaller
{
    public const PACKAGE_NAME = 'pronovix/drupal-qa';

    public const CONFIG_FILE_NAME = 'phpcs.xml.dist';

    public const CONFIG_FILE_PATH_IN_PACKAGE = 'config/' . self::CONFIG_FILE_NAME;

    /**
     * @var \Composer\Util\Filesystem
     */
    private $filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * PhpCsConfigInstaller constructor.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\Util\Filesystem $filesystem
     * @param \Pronovix\DrupalQa\Logger\Logger $logger
     */
    public function __construct(Composer $composer, Filesystem $filesystem, LoggerInterface $logger)
    {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->composer = $composer;
    }

    /**
     * Symlinks the phpcs.xml.dist file to destination.
     *
     * @param string|null $destinationPath
     *   The destination path, default is the current directory.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     * @throws \Pronovix\DrupalQa\Exception\InvalidArgumentException
     * @throws \Pronovix\DrupalQa\Exception\FileExistsException
     * @throws \Pronovix\DrupalQa\Exception\CouldNotBeSymlinkedException
     */
    public function install(string $destinationPath = null): void
    {
        $source = $this->getConfigFilePath();

        if (null === $destinationPath) {
            $destinationPath = getcwd();
        }
        $destination = $this->getDestinationFilePath($destinationPath);

        $this->logger->info('Symlinking file from {source} to {destination}', ['source' => $source, 'destination' => $destination]);

        try {
            $result = $this->filesystem->relativeSymlink($source, $destination);
        } catch (\InvalidArgumentException $e) {
            throw new CouldNotBeSymlinkedException($source, $destination, $e);
        }

        if (false === $result) {
            throw new CouldNotBeSymlinkedException($source, $destination);
        }
    }

    /**
     * Removes the symlinked phpcs.xml.dist file.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     */
    public function uninstall(): void
    {
        // This is only used by the post package uninstall event subscriber,
        // there is no command for this operation, so this is fine for now.
        $symlink = getcwd() . '/' . self::CONFIG_FILE_NAME;
        if (is_link($symlink) && readlink($symlink) === ltrim(str_replace(getcwd(), '', $this->composer->getConfig()->get('vendor-dir')), '/') . '/' . self::PACKAGE_NAME . '/' . self::CONFIG_FILE_PATH_IN_PACKAGE) {
            $this->logger->info('Removing symlink from {path}.', ['path' => $symlink]);
            try {
                $this->filesystem->unlink($symlink);
            } catch (\Exception $e) {
                throw new RuntimeException(sprintf('Could not unlink file, because: "%s".', $e->getMessage()), (int) $e->getCode(), $e);
            }
        } else {
            $this->logger->info('Symlink at {path} does not exist.', ['path' => $symlink]);
        }
    }

    /**
     * Retrieves the path of the phpcs.yml.dist from this library.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     *
     * @psalm-suppress PossiblyNullArgument $drupalQaPackage cannot be null.
     *
     * @return string
     */
    public function getConfigFilePath(): string
    {
        $drupalQaPackage = $this->composer->getRepositoryManager()->getLocalRepository()->findPackage(self::PACKAGE_NAME, '*');
        $installationManager = $this->composer->getInstallationManager();
        $drupalQaPackagePath = $installationManager->getInstallPath($drupalQaPackage);
        $drupalQaPackagePath .= '/' . self::CONFIG_FILE_PATH_IN_PACKAGE;

        if (!file_exists($drupalQaPackagePath)) {
            throw new RuntimeException("phpcs.xml.dist file not found at {$drupalQaPackagePath}.");
        }

        return $drupalQaPackagePath;
    }

    /**
     * Returns the path with the filename where the symlink should be created.
     *
     * @param string $directory
     *
     * @throws \Pronovix\DrupalQa\Exception\FileExistsException
     * @throws \Pronovix\DrupalQa\Exception\InvalidArgumentException
     *
     * @return string
     */
    private function getDestinationFilePath(string $directory): string
    {
        $directory = $this->filesystem->normalizePath($this->filesystem->isAbsolutePath($directory) ? $directory : getcwd() . '/' . $directory);
        if ('' === $directory) {
            throw new InvalidArgumentException('Destination argument should not be an empty string');
        }

        try {
            $this->filesystem->ensureDirectoryExists($directory);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("{$directory} does not exist and could not be created.");
        }

        if (!is_dir($directory)) {
            throw new InvalidArgumentException("{$directory} is not a directory.");
        }

        if (!is_writable($directory)) {
            throw new InvalidArgumentException("{$directory} is not writable.");
        }

        $destinationFile = realpath($directory) . '/' . self::CONFIG_FILE_NAME;

        if (file_exists($destinationFile)) {
            throw new FileExistsException($destinationFile);
        }

        return $destinationFile;
    }
}
