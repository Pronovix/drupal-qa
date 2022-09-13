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

use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Pronovix\DrupalQa\Exception\LogicException;

class CurrentWorkdirAsComposerProjectRootTest extends TestCase
{
    private CurrentWorkdirAsComposerProjectRoot $locator;

    protected function setUp(): void
    {
        $this->locator = new CurrentWorkdirAsComposerProjectRoot(new ComposerFileSystemAdapter(new Filesystem()));
    }

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\CurrentWorkdirAsComposerProjectRoot::getPath()
     */
    public function testValidPathIdentified(): void
    {
        $workdir = \getcwd();
        $acme_project_root = $workdir . '/tests/fixtures/project-with-drupal-qa';
        \chdir($acme_project_root);
        $this->assertEquals($acme_project_root, $this->locator->getPath());
    }

    /**
     * @covers \Pronovix\DrupalQa\Composer\Infrastructure\CurrentWorkdirAsComposerProjectRoot::getPath()
     */
    public function testExceptionIsThrownWhenCalledInNonComposerProjectRoot(): void
    {
        $this->expectException(LogicException::class);
        $workdir = \getcwd();
        $acme_project_root = $workdir . '/tests/fixtures/not-composer-project';
        \chdir($acme_project_root);
        $this->locator->getPath();
    }
}
