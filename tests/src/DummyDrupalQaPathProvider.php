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

namespace Pronovix\DrupalQa\Tests;

use Pronovix\DrupalQa\Composer\Domain\Service\DrupalQaPathProviderInterface;

final class DummyDrupalQaPathProvider implements DrupalQaPathProviderInterface
{
    private const PACKAGE_PATH = '/tmp/foo/bar/baz/' . self::PACKAGE_NAME;

    /**
     * {@inheritDoc}
     */
    public function getPackagePath(): string
    {
        return self::PACKAGE_PATH;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilePathInPackage(string $relative_file_path): string
    {
        return $this->getPackagePath() . '/' . $relative_file_path;
    }
}
