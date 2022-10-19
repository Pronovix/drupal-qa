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

use Pronovix\DrupalQa\Composer\Domain\Service\ComposerProjectRootLocatorInterface;
use Pronovix\DrupalQa\Composer\Domain\Service\DrupalQaPathProviderInterface;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;
use Pronovix\DrupalQa\Exception\FileExistsException;
use Psr\Log\LoggerInterface;

/**
 * @todo Hexagonalize this and make it covered by tests. (Depend on domain
 *   services, etc.)
 * @todo Logging is a secondary and optional task, trigger an event instead that
 *   can be used for this purpose.
 */
final class PhpCsConfigInstaller
{
    private const CONFIG_FILE_NAME = 'phpcs.xml.dist';

    private const CONFIG_FILE_PATH_IN_PACKAGE = 'config/' . self::CONFIG_FILE_NAME;

    private LoggerInterface $logger;

    private DrupalQaPathProviderInterface $qaPathProvider;

    private ComposerProjectRootLocatorInterface $projectRootLocator;

    private FileSystemInterface $fileSystem;

    public function __construct(
      DrupalQaPathProviderInterface $qa_path_provider,
      ComposerProjectRootLocatorInterface $project_root_locator,
      FileSystemInterface $file_system,
      LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->qaPathProvider = $qa_path_provider;
        $this->fileSystem = $file_system;
        $this->projectRootLocator = $project_root_locator;
    }

    /**
     * Symlinks the phpcs.xml.dist file to destination.
     *
     * @param string $create_at
     *   The destination path.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     * @throws \Pronovix\DrupalQa\Exception\InvalidArgumentException
     * @throws \Pronovix\DrupalQa\Exception\FileExistsException
     * @throws \Pronovix\DrupalQa\Domain\FileSystem\Exception\UnableToCreateSymlink
     */
    public function install(string $create_at): void
    {
        $symlink_to = $this->getConfigFilePath();

        $this->fileSystem->ensureDirectoryExists($create_at, 0777);

        $destination = $create_at . '/' . self::CONFIG_FILE_NAME;

        if ($this->fileSystem->fileExists($destination)) {
            throw new FileExistsException($destination);
        }

        $this->logger->info('Symlinking file from {symlink_to} to {create_at}', ['symlink_to' => $symlink_to, 'create_at' => $create_at]);

        $this->fileSystem->relativeSymlink($symlink_to, $destination);
    }

    /**
     * Removes the symlinked phpcs.xml.dist file.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     *
     * @todo In contradiction with install() that can install the file anywhere,
     *   with a fallback if the path is not specified to the project root, this
     *   method can only remove the symlink from the project root. Remove this
     *   contradicting behavior.
     */
    public function uninstall(): void
    {
        // This is only used by the post package uninstall event subscriber,
        // there is no command for this operation, so this is fine for now.
        $symlink = $this->projectRootLocator->getPath() . '/' . self::CONFIG_FILE_NAME;
        // Calling realpath() on readlink() would return an absolute link
        // inside the monorepo and not inside the parent project where this
        // package is installed.
        if ($this->fileSystem->fileExists($symlink) && is_link($symlink) && readlink($symlink) === str_replace($this->projectRootLocator->getPath() . '/', '', $this->getConfigFilePath())) {
            $this->logger->info('Removing symlink from {path}.', ['path' => $symlink]);
            $this->fileSystem->deleteFile($symlink);
        } else {
            $this->logger->info('Symlink at {path} does not exist.', ['path' => $symlink]);
        }
    }

    /**
     * Retrieves the path of the phpcs.xml.dist from this library.
     *
     * @throws \Pronovix\DrupalQa\Exception\RuntimeException
     *
     * @todo Make this public, if necessary trigger an event in install() or
     *   uninstall() that can be caught and used in
     *   \Pronovix\DrupalQa\Composer\Plugin::postPackageInstall().
     */
    public function getConfigFilePath(): string
    {
        return $this->qaPathProvider->getFilePathInPackage(self::CONFIG_FILE_PATH_IN_PACKAGE);
    }
}
