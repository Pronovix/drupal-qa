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
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileDoesNotExistFileExistsCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistFileExistsCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistsCallbackCallCounterCallback;

/**
 * @covers \Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem
 */
class ConfigurableFakeFileSystemTest extends TestCase
{
    public function testEnsureDirectoryExists(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->ensureDirectoryExists('foo', 1111);
    }

    public function testCopyThrowsAnExceptionWhenItIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->copy('foo.txt', 'bar.txt');
    }

    public function testCopyCallsAllConfiguredCallbacksOnceAndInOrder(): void
    {
        $obj = new ConfigurableFakeFileSystem();

        $callback_builder = static function (int &$called_at): callable {
            return static function (string $src, string $dest) use (&$called_at): void {
                $called_at = microtime(true);
            };
        };

        $first_callback_was_called_at = $second_callback_was_called_at = 0;
        $first_callback = $callback_builder($first_callback_was_called_at);
        $second_callback = $callback_builder($second_callback_was_called_at);
        $obj->addCopyCallbacks($first_callback, $second_callback);

        $obj->copy('foo.txt', 'bar.txt');
        $obj->copy('bar.txt', 'baz.txt');

        $this->assertNotEquals(0, $first_callback_was_called_at, 'First callback was called.');
        $this->assertNotEquals(0, $second_callback_was_called_at, 'Second callback was called.');
        $this->assertTrue($first_callback_was_called_at < $second_callback_was_called_at, 'The first callback was called sooner than the second.');
    }

    public function testChmodThrowsAnExceptionWhenItIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->chmod('foo.txt', 0777);
    }

    public function testChmodCallsAllConfiguredCallbacksOnceAndInOrder(): void
    {
        $obj = new ConfigurableFakeFileSystem();

        $callback_builder = static function (int &$called_at): callable {
            return static function (string $path, int $visibility) use (&$called_at): void {
                $called_at = microtime(true);
            };
        };

        $first_callback_was_called_at = $second_callback_was_called_at = 0;
        $first_callback = $callback_builder($first_callback_was_called_at);
        $second_callback = $callback_builder($second_callback_was_called_at);
        $obj->addChmodCallbacks($first_callback, $second_callback);

        $obj->chmod('foo.txt', 0777);
        $obj->chmod('bar.txt', 0777);

        $this->assertNotEquals(0, $first_callback_was_called_at, 'First callback was called.');
        $this->assertNotEquals(0, $second_callback_was_called_at, 'Second callback was called.');
        $this->assertTrue($first_callback_was_called_at < $second_callback_was_called_at, 'The first callback was called sooner than the second.');
    }

    public function testFileExistsThrowsAnExceptionWhenItIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->fileExists('foo.txt');
    }

    /**
     * @covers \Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileDoesNotExistFileExistsCallback
     * @covers \Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistFileExistsCallback
     * @covers \Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistsCallbackCallCounterCallback
     */
    public function testFileExistsCallsAllConfiguredCallbacksOnceAndInOrder(): void
    {
        $obj = new ConfigurableFakeFileSystem();

        $first_callback = new FileExistsCallbackCallCounterCallback(new FileExistFileExistsCallback());
        $second_callback = new FileExistsCallbackCallCounterCallback(new FileDoesNotExistFileExistsCallback());

        $obj->addFileExistsCallbacks($first_callback, $second_callback, $first_callback);

        $this->assertEquals(true, $obj->fileExists('foo.txt'));
        $this->assertEquals(false, $obj->fileExists('bar.txt'));
        $this->assertEquals(1, $first_callback->getCount());
        $this->assertEquals(1, $second_callback->getCount());

        $obj->fileExists('baz.txt');

        $this->assertEquals(2, $first_callback->getCount());
    }

    public function testRelativeSymlinkThrowsAnExceptionWhenItIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->relativeSymlink('./foo.txt', '/foo/bar/baz');
    }

    public function testDeleteFileThrowsAnExceptionWhenItIsNotConfigured(): void
    {
        $this->expectException(\LogicException::class);
        $obj = new ConfigurableFakeFileSystem();
        $obj->deleteFile('foo.txt');
    }
}
