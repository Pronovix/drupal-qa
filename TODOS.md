#### Evaluate `drupol/phpcsfixer-configs-drupal`

Link:  https://github.com/drupol/phpcsfixer-configs-drupal

(Minimum required version should be 1.0.1 because https://github.com/drupol/phpcsfixer-configs-drupal/pull/1)

Questions:
* Does it provide any value in comparison with our enhanced PHP CodeSniffer configuration?

So far we know:
  * PRO: It can add copyright headers to PHP files (only), but only PHP files.
  * CON: It must be executed in `$ ABS_PATH_TO_PHPCSFIXER/php-cs-fixer --config=PATH_TO_CONFIG_FILE FOLDER_TO_BE_CHECKED` because if we provide
    a path argument to the `php-cs-fixer` (ex.: `$ ./vendor/bin/php-cs-fixer fix path_to_module` then it overrides Drupol's Symfony Finder configuration
    and PHP CS Fixer only process `*.php` files instead of other Drupal specific files. https://github.com/drupol/phpcsfixer-configs-drupal/issues/2

Sample configuration:

```php
<?php

use drupol\PhpCsFixerConfigsDrupal\Config\Drupal8;
use drupol\PhpCsFixerConfigsPhp\Config\Php71;

$header = <<<HEADER
Copyright (C) 2019 PRONOVIX GROUP BVBA.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 *
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 *
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
USA.
HEADER;

$config = new \drupol\PhpCsFixerConfigsDrupal\Config\Drupal();
$px_additions_overrides = PhpCsFixer\Config::create()
  ->setRules([
    'class_definition' => ['singleLine' => true, 'singleItemSingleLine' => true],
    'declare_strict_types' => true,
    'header_comment' => ['header' => $header, 'commentType' => 'PHPDoc'],
    'general_phpdoc_annotation_remove' => ['author', 'package'],
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'phpdoc_indent' => false,
    'phpdoc_order' => true,
    'phpdoc_to_return_type' => ['scalar_types' => true],
    'void_return' => true,
  ]);
$config = $config->withRulesFromConfig(Php71::create(), Drupal8::create(), $px_additions_overrides);

return Drupal8::create();
```

#### Evaluate `vimeo/psalm`

Link: https://github.com/vimeo/psalm

Possibly requires a custom autoloading solution: https://github.com/vimeo/psalm/issues/841

#### Introduce a new `pronovix/php-qa` package

For those PHP projects that does not follow Drupal's CS.

Add common dev dependencies and Composer scripts.
