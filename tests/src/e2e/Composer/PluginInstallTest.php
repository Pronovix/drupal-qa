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

namespace Pronovix\DrupalQa\Tests\e2e\Composer;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Pronovix\DrupalQa\Composer\Plugin::postPackageInstall()
 */
final class PluginInstallTest extends TestCase
{
    private const E2E_ROOT = __DIR__ . '/../../../fixtures/e2e';

    public function testPhpCsConfigSymlinkedAfterPluginInstall(): void
    {
        self::assertTrue(is_link(self::E2E_ROOT . '/phpcs.xml.dist'));
        self::assertEquals('vendor/pronovix/drupal-qa/config/phpcs.xml.dist', readlink(self::E2E_ROOT . '/phpcs.xml.dist'), 'Assert that it is a relative symlink.');
    }

    public function testPhpStanConfigsWereInitializedAfterPluginInstall(): void
    {
        self::assertFileExists(self::E2E_ROOT . '/phpstan.neon.dist');
        self::assertFileExists(self::E2E_ROOT . '/phpstan-baseline.neon');
    }
}
