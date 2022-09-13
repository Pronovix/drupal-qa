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

namespace Pronovix\DrupalQa\Composer\Infrastructure;

use Composer\Installer\InstallationManager;
use Composer\Repository\InstalledRepositoryInterface;
use Pronovix\DrupalQa\Composer\Domain\Service\DrupalQaPathProviderInterface;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Pronovix\DrupalQa\Exception\LogicException;

final class InstalledDrupalQaPathProvider implements DrupalQaPathProviderInterface
{
    private InstalledRepositoryInterface $repository;

    private InstallationManager $installationManager;

    private FileSystemInterface $fileSystem;

    public function __construct(InstalledRepositoryInterface $repository, InstallationManager $install_manager, FileSystemInterface $file_system)
    {
        $this->repository = $repository;
        $this->installationManager = $install_manager;
        $this->fileSystem = $file_system;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackagePath(): string
    {
        $drupal_qa_package = $this->repository->findPackage(self::PACKAGE_NAME, '*');
        if (null === $drupal_qa_package) {
            throw new LogicException(self::PACKAGE_NAME . ' not found in installed packages');
        }

        return $this->installationManager->getInstallPath($drupal_qa_package);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilePathInPackage(string $relative_file_path): string
    {
        $file_path = $this->getPackagePath() . '/' . $relative_file_path;

        if (!$this->fileSystem->fileExists($file_path)) {
            throw new InvalidArgumentException(sprintf('%s file not found in Drupal QA package. Full path: %s.', $relative_file_path, $file_path));
        }

        return $file_path;
    }
}
