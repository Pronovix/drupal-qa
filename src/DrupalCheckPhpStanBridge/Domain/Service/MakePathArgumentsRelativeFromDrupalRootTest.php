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

namespace Pronovix\DrupalQa\DrupalCheckPhpStanBridge\Domain\Service;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Pronovix\DrupalQa\DrupalCheckPhpStanBridge\Domain\Service\MakePathArgumentsRelativeFromDrupalRoot
 */
final class MakePathArgumentsRelativeFromDrupalRootTest extends TestCase
{
    /**
     * @dataProvider pathCombinations
     */
    public function testWithPaths(string $drupal_root, string $path, string $expected_path): void
    {
        $argv = [
          // This is not a path, it must not be changed.
          '--verbose',
          $path,
        ];

        $result = (new MakePathArgumentsRelativeFromDrupalRoot($drupal_root))($argv);

        self::assertEquals(['--verbose', $expected_path], $result);
    }

    public function pathCombinations(): \Generator
    {
        yield [
          '/',
          'foo/bar/baz',
          'foo/bar/baz',
        ];
        yield [
          '/foo',
          '/foo/bar/baz',
          'bar/baz',
        ];
        yield [
          'foo',
          'foo/bar/baz',
          'bar/baz',
        ];
        yield [
          '/foo/',
          '/foo/bar/baz',
          'bar/baz',
        ];
        yield [
          'foo/bar',
          'foo/bar/baz',
          'baz',
        ];
    }
}
