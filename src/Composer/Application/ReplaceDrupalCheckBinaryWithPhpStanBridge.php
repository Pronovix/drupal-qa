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

namespace Pronovix\DrupalQa\Composer\Application;

use Pronovix\DrupalQa\Composer\Domain\Service\BinDirPathProviderInterface;
use Pronovix\DrupalQa\Composer\Domain\Service\DrupalQaPathProviderInterface;
use Pronovix\DrupalQa\Domain\FileSystem\FileSystemInterface;

final class ReplaceDrupalCheckBinaryWithPhpStanBridge
{
    private const CONFIG_FILE_NAME = 'drupal-check-phpstan-bridge.php';

    private const CONFIG_FILE_PATH_IN_PACKAGE = 'config/skeletons/' . self::CONFIG_FILE_NAME;

    private DrupalQaPathProviderInterface $qaPathProvider;

    private BinDirPathProviderInterface $binDirPathProvider;

    private FileSystemInterface $filesystem;

    /**
     * Constructs a new object.
     */
    public function __construct(DrupalQaPathProviderInterface $qa_path_provider, BinDirPathProviderInterface $bin_dir_path_provider, FileSystemInterface $filesystem)
    {
        $this->qaPathProvider = $qa_path_provider;
        $this->binDirPathProvider = $bin_dir_path_provider;
        $this->filesystem = $filesystem;
    }

    public function __invoke(): void
    {
        $destination_path = $this->binDirPathProvider->getPath() . '/drupal-check';
        $this->filesystem->copy($this->qaPathProvider->getFilePathInPackage(self::CONFIG_FILE_PATH_IN_PACKAGE), $destination_path);
        // Do the same trick as Composer does when installs binaries.
        // @see \Composer\Installer\BinaryInstaller::installBinaries()
        $this->filesystem->chmod($destination_path, 0777);
    }
}
