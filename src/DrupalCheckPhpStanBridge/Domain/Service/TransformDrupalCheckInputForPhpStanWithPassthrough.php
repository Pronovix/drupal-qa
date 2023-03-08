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

/**
 * Based on https://github.com/mglaman/drupal-check/blob/e78eff7b10f79659c020a45baaa1f3035cb9a06a/src/Command/CheckCommand.php#L26-L46.
 *
 * Using \Symfony\Component\Console\Input\ArgvInput would not allow us the pass
 * through behavior because every possible option flags must have been defined,
 * when the parser finds an undefined option it throws an exception.
 */
final class TransformDrupalCheckInputForPhpStanWithPassthrough
{
    private const DRUPAL_CHECK_ONLY_OPTIONS_FLAGS_WITH_VALUES = [
      '--exclude-dir',
      '-e',
    ];

    private const DRUPAL_CHECK_ONLY_OPTION_FLAGS = [
      '--php8',
      '--style',
      '-s',
      '--analysis',
      '-a',
      '--deprecations',
      '-d',
    ];

    /**
     * @param array<int,string> $phpstan_command_args
     *   $argv array without the name of the command.
     *
     * @return array{drupal-root?: string, args: array<int,string>}
     */
    public function __invoke(array $phpstan_command_args): array
    {
        // Make sure that the given array is 0 indexed (and not associative).
        $phpstan_command_args = array_values($phpstan_command_args);
        $result = [
          'args' => [],
        ];

        $extract_optional_value = static function (array $haystack, int &$pointer, string $string_at_pointer): string {
            $value = null;
            if (false !== strpos($string_at_pointer, '=')) {
                [, $value] = explode('=', $string_at_pointer);
                ++$pointer;
            } elseif (false !== strpos($string_at_pointer, ' ')) {
                [, $value] = explode(' ', $string_at_pointer);
                ++$pointer;
            }

            if (null === $value) {
                $value = $haystack[$pointer + 1];
                $pointer += 2;
            }

            return $value;
        };

        $pointer = 0;
        while ($pointer < count($phpstan_command_args)) {
            $value = $phpstan_command_args[$pointer];
            if (0 === strpos($value, '--format')) {
                $result['args'][] = '--error-format=' . $extract_optional_value($phpstan_command_args, $pointer, $value);
            } elseif (0 === strpos($value, '--drupal-root')) {
                $result['drupal-root'] = $extract_optional_value($phpstan_command_args, $pointer, $value);
            } else {
                foreach (self::DRUPAL_CHECK_ONLY_OPTION_FLAGS as $flag) {
                    if ($value === $flag) {
                        ++$pointer;
                        continue 2;
                    }
                }
                foreach (self::DRUPAL_CHECK_ONLY_OPTIONS_FLAGS_WITH_VALUES as $option_key) {
                    if (0 === strpos($value, $option_key)) {
                        // By this call we ensure that "-e foo/bar" is also handled and the
                        // value is removed too.
                        $extract_optional_value($phpstan_command_args, $pointer, $value);
                        continue 2;
                    }
                }

                $result['args'][] = $value;
                ++$pointer;
            }
        }

        return $result;
    }
}
