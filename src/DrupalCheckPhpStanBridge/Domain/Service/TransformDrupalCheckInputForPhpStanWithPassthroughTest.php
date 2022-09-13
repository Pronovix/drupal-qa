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
 * @covers \Pronovix\DrupalQa\DrupalCheckPhpStanBridge\Domain\Service\TransformDrupalCheckInputForPhpStanWithPassthrough
 */
final class TransformDrupalCheckInputForPhpStanWithPassthroughTest extends TestCase
{
    public function testItTransformsFormatOptionFlagToErrorFormat(): void
    {
        $argv = [
          '--format=json',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertArrayHasKey('--error-format=json', array_flip($transformed['args']));
    }

    public function testItCapturesAndRemovesDrupalRootOptionFlag(): void
    {
        $drupal_root_path = '/foo/bar/baz';
        $argv = [
          '--drupal-root=' . $drupal_root_path,
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertArrayNotHasKey('--drupal-root=' . $drupal_root_path, array_flip($transformed['args']));
        self::assertEquals($drupal_root_path, $transformed['drupal-root'] ?? '');
    }

    public function testItCapturesInputWhenTheValueOfAnOptionWithValueIsTheNextArgument(): void
    {
        $drupal_root_path = '/foo/bar/baz';
        $argv = [
          '--drupal-root',
          $drupal_root_path,
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertArrayNotHasKey('--drupal-root=' . $drupal_root_path, array_flip($transformed['args']));
        self::assertEquals($drupal_root_path, $transformed['drupal-root'] ?? '');
    }

    public function testItRemovesAllDrupalCheckOnlyOptionFlags(): void
    {
        $argv = [
          '--php8',
          '--style',
          '-s',
          '--analysis',
          '-a',
          '--deprecations',
          '-d',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEmpty($transformed['args']);
    }

    public function testItRemovesAllDrupalCheckOnlyOptionsWithValues(): void
    {
        $argv = [
          '-e /foo/bar',
          '-e=/foo/bar',
          '--exclude-dir /foo/bar',
          '--exclude-dir=/foo/bar',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEmpty($transformed['args']);
    }

    public function testItKeepsCommonOptionFlags(): void
    {
        $argv = [
          '--memory-limit 1G',
          '--no-progress',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEquals($argv, $transformed['args']);
    }

    public function testItKeepsPhpStanAnalyzeOnlyOptionFlags(): void
    {
        // Based on https://phpstan.org/user-guide/command-line-usage#analysing-code
        // PHPStan version 1.8.5
        $argv = [
          '--level',
          '-l',
          '--configuration',
          '-c',
          '--generate-baseline',
          '--autoload-file',
          '-a',
          '--debug',
          '--xdebug',
          '--ansi',
          '--no-ansi',
          '--quit',
          '-quit',
          '--version',
          '-V',
          '--help',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEquals(sort($argv), sort($transformed['args']));
    }

    public function testWithSimpleInput(): void
    {
        $argv = [
          '/web/modules/foo',
          '--format json',
          '--drupal-root web/',
          '--no-progress',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEquals('web/', $transformed['drupal-root'] ?? '', 'Drupal root was identified.');
        self::assertEquals(['/web/modules/foo', '--error-format=json', '--no-progress'], $transformed['args'], 'Option flags were properly transformed or passed through.');
    }

    public function testWithComplexInput(): void
    {
        $argv = [
          '--format json',
          '--exclude-dir=/foo/bar,/bar/baz',
          '--no-progress',
          '/web/modules/foo',
          // Paths are scattered in arguments by design.
          '/web/modules/bar',
          '--debug',
        ];

        $transformed = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($argv);

        self::assertEquals(['--error-format=json', '--no-progress', '/web/modules/foo', '/web/modules/bar', '--debug'], $transformed['args']);
    }
}
