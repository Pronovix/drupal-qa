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
use Pronovix\DrupalQa\Tests\DummyDrupalQaPathProvider;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\CopyCallbackSpy;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileDoesNotExistFileExistsCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistFileExistsCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\FileExistsCallbackCallCounterCallback;
use Pronovix\DrupalQa\Tests\FileSystem\Callbacks\NullCopyCallback;
use Pronovix\DrupalQa\Tests\FileSystem\FakeFileSystemThatAlwaysDoesTheSameBuilder;

/**
 * @covers \Pronovix\DrupalQa\Composer\Application\EnsurePhpStanConfigsExist
 */
class EnsurePhpStanConfigsExistTest extends TestCase
{
    public function testItCopiesAllFilesWhenTheyDoNotExistAtDestinationAsIs(): void
    {
        $copy_spy_callback = new CopyCallbackSpy(new NullCopyCallback());
        $handler = new EnsurePhpStanConfigsExist(
            new DummyDrupalQaPathProvider(),
            (new FakeFileSystemThatAlwaysDoesTheSameBuilder())
              ->setFileExistsCallback(new FileDoesNotExistFileExistsCallback())
              ->setCopyCallback($copy_spy_callback)
              ->build()
        );
        $destination_base_path = '/destination/base/path';

        $handler($destination_base_path);

        $expected_files_to_be_copied = [
          'phpstan.neon.dist',
          'phpstan-baseline.neon',
        ];
        $expected_files_to_be_copied = array_combine($expected_files_to_be_copied, $expected_files_to_be_copied);

        foreach ($copy_spy_callback->getCalls() as $copy_from => $copy_to) {
            $src_file_name = \basename($copy_from);
            $dest_file_name = \basename($copy_to);
            $this->assertEquals($src_file_name, $dest_file_name);
            unset($expected_files_to_be_copied[$src_file_name]);
        }

        $this->assertEmpty($expected_files_to_be_copied, 'Asserting that all expected files were copied.');
    }

    public function testItDoesNotOverrideExistingFiles(): void
    {
        $copy_must_not_be_called_callback = static function (string $src, string $dest): void {
            throw new \LogicException('Copy must not have been called because file exist.');
        };
        $file_exists_callback = new FileExistsCallbackCallCounterCallback(new FileExistFileExistsCallback());
        $handler = new EnsurePhpStanConfigsExist(
            new DummyDrupalQaPathProvider(),
            (new FakeFileSystemThatAlwaysDoesTheSameBuilder())
              ->setFileExistsCallback($file_exists_callback)
              ->setCopyCallback($copy_must_not_be_called_callback)
              ->build()
        );

        $handler('/wherever');

        $this->assertNotEquals(0, $file_exists_callback->getCount(), 'Asserting that file existence was checked at least once.');
    }
}
