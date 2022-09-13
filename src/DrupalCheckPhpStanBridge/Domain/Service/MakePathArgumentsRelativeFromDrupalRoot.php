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

final class MakePathArgumentsRelativeFromDrupalRoot
{
    private string $drupalRoot;

    /**
     * Constructs a new object.
     */
    public function __construct(string $drupalRoot)
    {
        $this->drupalRoot = $drupalRoot;
    }

    /**
     * @param array<int,string> $args
     *
     * @return array<int,string>
     */
    public function __invoke(array $args): array
    {
        $result = [];
        $generate_drupal_root_regexp = static function (string $drupal_root): string {
            $delimiter = '/';
            $drupal_root = rtrim($drupal_root, $delimiter);
            $drupal_root = preg_quote($drupal_root, $delimiter);

            return '/^(' . $drupal_root . '\/)(.*)$/';
        };

        $drupal_root_regexp = $generate_drupal_root_regexp($this->drupalRoot);

        foreach ($args as $value) {
            if (preg_match($drupal_root_regexp, $value) > 0) {
                $value = preg_replace($drupal_root_regexp, '$2', $value, 1);
                \assert(null !== $value);
            }

            $result[] = $value;
        }

        return $result;
    }
}
