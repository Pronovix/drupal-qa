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

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Pronovix\DrupalQa\Exception\InvalidArgumentException;
use Pronovix\DrupalQa\Exception\LogicException;

final class InstalledDrupalQaPathProviderTest extends TestCase
{
    private InstalledDrupalQaPathProvider $pathProvider;

    private string $fixtureProjectAbsPath;

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\InstalledDrupalQaPathProvider::getPackagePath
     */
    public function testPackagePathCanBeIdentified(): void
    {
        $workdir = \getcwd();
        $this->setUpProvider($workdir . '/tests/fixtures/project-with-drupal-qa');
        $this->assertEquals($this->fixtureProjectAbsPath . '/vendor/pronovix/drupal-qa', $this->pathProvider->getPackagePath());
    }

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\InstalledDrupalQaPathProvider::getPackagePath
     */
    public function testExceptionIsThrownWhenPackageIsNotInstalled(): void
    {
        $this->expectException(LogicException::class);
        $this->setUpProvider(\getcwd());
        $this->pathProvider->getPackagePath();
    }

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\InstalledDrupalQaPathProvider::getFilePathInPackage
     */
    public function testAbsolutePathOfExistingFileReturned(): void
    {
        $workdir = \getcwd();
        $root_package_abs_path = $workdir . '/tests/fixtures/project-with-drupal-qa';
        $this->setUpProvider($root_package_abs_path);
        $file_path = $this->pathProvider->getFilePathInPackage('composer.json');
        $this->assertEquals($root_package_abs_path . '/vendor/pronovix/drupal-qa/composer.json', $file_path);
    }

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\InstalledDrupalQaPathProvider::getFilePathInPackage
     */
    public function testExceptionIsThrownWhenFileNotFoundInPackage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $workdir = \getcwd();
        $root_package_abs_path = $workdir . '/tests/fixtures/project-with-drupal-qa';
        $this->setUpProvider($root_package_abs_path);
        $this->pathProvider->getFilePathInPackage('file-that-does-not-exist-for-sure.txt');
    }

    private function setUpProvider(string $root_package_path): void
    {
        $this->fixtureProjectAbsPath = $root_package_path;
        \chdir($this->fixtureProjectAbsPath);
        $composer = Factory::create(new NullIO());
        $this->pathProvider = new InstalledDrupalQaPathProvider($composer->getRepositoryManager()->getLocalRepository(), $composer->getInstallationManager(), new ComposerFileSystemAdapter(new Filesystem()));
    }
}
