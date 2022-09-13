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

use PHPUnit\Framework\TestCase;
use Pronovix\DrupalQa\Tests\DummyBinDirPathProvider;
use Pronovix\DrupalQa\Tests\DummyDrupalQaPathProvider;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\CopyCallbackSpy;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\NullCopyCallback;
use Pronovix\DrupalQa\Tests\FileSystem\ConfigurableFakeFileSystem;

/**
 * @covers \Pronovix\DrupalQa\Composer\Application\ReplaceDrupalCheckBinaryWithPhpStanBridge
 */
class ReplaceDrupalCheckBinaryWithPhpStanBridgeTest extends TestCase
{
    public function testItReplacesDrupalCheckWhenItExists(): void
    {
        $copy_spy = new CopyCallbackSpy(new NullCopyCallback());
        $chmod_calls = [];
        $chmod_operation_spy = static function (string $src, int $mask) use (&$chmod_calls): void {
            $chmod_calls[$src] = $mask;
        };
        $bin_dir_path_provider = new DummyBinDirPathProvider();
        $handler = new ReplaceDrupalCheckBinaryWithPhpStanBridge(
          new DummyDrupalQaPathProvider(),
          $bin_dir_path_provider,
          (new ConfigurableFakeFileSystem())
            ->addCopyCallbacks($copy_spy)
            ->addChmodCallbacks($chmod_operation_spy)
        );

        $handler();
        $this->assertCount(1, $copy_spy->getCalls(), 'Assert that only one copy operation was needed.');
        $copy_calls = $copy_spy->getCalls();
        // @todo Assert that the bin was made executable.
        $this->assertArrayHasKey($bin_dir_path_provider->getPath() . '/drupal-check', $chmod_calls, 'Chmod was called on the copied binary.');
        $this->assertEquals($bin_dir_path_provider->getPath() . '/drupal-check', reset($copy_calls), 'Bridge file was copied with the correct name.');
    }
}
