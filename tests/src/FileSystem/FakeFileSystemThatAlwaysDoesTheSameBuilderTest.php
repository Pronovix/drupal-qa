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

namespace Pronovix\DrupalQa\Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\CopyCallbackCallCounterCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistFileExistsCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistsCallbackCallCounterCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\NullCopyCallback;

/**
 * @covers \Pronovix\DrupalQa\Tests\FileSystem\FakeFileSystemThatAlwaysDoesTheSameBuilder
 */
class FakeFileSystemThatAlwaysDoesTheSameBuilderTest extends TestCase
{
    /**
     * Why 3? Because if it works one time, that is expected. If it works two
     * times then it still can be a regression. Although if it works at least
     * three times then it most likely works N times as well.
     */
    private const REPEAT_TIME = 3;

    public function testItBuildsFakeFileSystemThatKeepsRepeatingTheSameFileExistCallback(): void
    {
        $builder = new FakeFileSystemThatAlwaysDoesTheSameBuilder();

        $file_exists_callback = new FileExistsCallbackCallCounterCallback(new FileExistFileExistsCallback());
        $builder->setFileExistsCallback($file_exists_callback);

        $fs = $builder->build();

        for ($i = 0; $i < self::REPEAT_TIME; ++$i) {
            $this->assertEquals(true, $fs->fileExists('foo.txt'));
        }

        $this->assertEquals(self::REPEAT_TIME, $file_exists_callback->getCount(), sprintf('Asserting that the one file exists callback was called %d times.', self::REPEAT_TIME));
    }

    public function testItBuildsFakeFileSystemThatKeepsRepeatingTheSameCopyCallback(): void
    {
        $builder = new FakeFileSystemThatAlwaysDoesTheSameBuilder();

        $copy_callback = new CopyCallbackCallCounterCallback(new NullCopyCallback());
        $builder->setCopyCallback($copy_callback);

        $fs = $builder->build();

        for ($i = 0; $i < self::REPEAT_TIME; ++$i) {
            $fs->copy('/foo/bar', '/bar/baz');
        }

        $this->assertEquals(self::REPEAT_TIME, $copy_callback->getCount(), sprintf('Asserting that the one copy callback was called %d times.', self::REPEAT_TIME));
    }
}
