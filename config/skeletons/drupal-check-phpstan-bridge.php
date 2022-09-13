#!/usr/bin/env php
<?php

/**
 * @file
 * Dummy, best-effort backward/forward compatibility bridge between Drupal Check
 * and PHPStan.
 */

declare(strict_types = 1);

use Composer\Factory;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Pronovix\DrupalQa\Composer\Infrastructure\ComposerFileSystemAdapter;
use Pronovix\DrupalQa\Composer\Infrastructure\CurrentWorkdirAsComposerProjectRoot;
use Pronovix\DrupalQa\DrupalCheckPhpStanBridge\Domain\Service\MakePathArgumentsRelativeFromDrupalRoot;
use Pronovix\DrupalQa\DrupalCheckPhpStanBridge\Domain\Service\TransformDrupalCheckInputForPhpStanWithPassthrough;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

require __DIR__ . '/../autoload.php';

$drupal_check_args = $argv;
// Remove script name.
unset($drupal_check_args[0]);

// Get the project root's absolute path from the root composer.json file path.
$file_system = new ComposerFileSystemAdapter(new Filesystem());
$project_root_path = (new CurrentWorkdirAsComposerProjectRoot($file_system))->getPath();

$drupal_check_to_phpstan_args = (new TransformDrupalCheckInputForPhpStanWithPassthrough())($drupal_check_args);
$drupal_root = $drupal_check_to_phpstan_args['drupal-root'] ?? 'web/';
$phpstan_command_args = $drupal_check_to_phpstan_args['args'];

$phpstan_command_args = (new MakePathArgumentsRelativeFromDrupalRoot($drupal_root))($phpstan_command_args);

// Sanitize the input.
foreach ($phpstan_command_args as $id => $value) {
    $phpstan_command_args[$id] = ProcessExecutor::escape($value);
}

// We set the workdir to help Drupal Finder to find Drupal root easier in a
// setup where the project root contains both web and vendor.
$workdir = $project_root_path . '/' . $drupal_root;

// By changing the workdir from project root to something else we cause a new
// problem that we need to handle, we have to tell PHPStan that the
// configuration lives in the project root not in workdir.
$configuration_candidates = [
  $project_root_path . '/phpstan.neon',
  $project_root_path . '/phpstan.neon.dist',
];

foreach ($configuration_candidates as $config_candidate) {
    if ($file_system->fileExists($config_candidate)) {
        $phpstan_command_args[] = '-c ' . $config_candidate;
        break;
    }
}

$runner = new ProcessExecutor();
$runner::setTimeout(0);
$output = Factory::createOutput();

$output_callback = static function ($type, $buffer) use ($output) {
    if (Process::ERR === $type) {
        $output->getErrorOutput()
          ->write($buffer, false, OutputInterface::OUTPUT_RAW);
    } else {
        $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
    }
};

// Known issue, changing the workdir causes a regression with ignore pattern
// generation and evaluation (eg.: in phpstan-baseline.neon files) in case
// of anonymous classes.
// PHPStan generates and interprets:
// message: "#^Return type \\(void\\) of method class@anonymous/web/modules/contrib/testtools/tests/src/Unit/AssertTest\\.php\\:58\\:\\:getAccountName\\(\\)
// whereas DrupalCheck-PHPStan bridge generates and interprets
// message: "#^Return type \\(void\\) of method class@anonymous/modules/contrib/testtools/tests/src/Unit/AssertTest\\.php\\:58\\:\\:getAccountName\\(\\)
// (Notice "class@anonymous/web/modules" vs "class@anonymous/modules").
// @see https://project.pronovix.net/issues/20176
$exit_code = $runner->execute(__DIR__ . '/phpstan analyze ' . implode(' ', $phpstan_command_args), $output_callback, $workdir);

exit($exit_code);
