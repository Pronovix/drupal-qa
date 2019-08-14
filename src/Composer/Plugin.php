<?php

declare(strict_types=1);

/**
 * Copyright (C) 2019 PRONOVIX GROUP BVBA.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *  *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301,
 * USA.
 */

namespace Pronovix\DrupalQa\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Util\Filesystem;
use Pronovix\DrupalQa\Composer\Command\CommandProvider;
use Pronovix\DrupalQa\Composer\Handler\PhpCsConfigInstaller;
use Pronovix\DrupalQa\Exception\FileExistsException;
use Pronovix\DrupalQa\Logger\Logger;

final class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    /** @var \Pronovix\DrupalQa\Logger\Logger */
    private $logger;

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        // On global installations, if the pronovix/composer-logger library
        // has not been installed earlier then the install could fail because
        // the autoloader has not been updated yet with the newly installed
        // dependency.
        if (!class_exists('\Pronovix\ComposerLogger\Logger')) {
            $loggerFile = $composer->getConfig()->get('vendor-dir') . '/pronovix/composer-logger/src/Logger.php';
            if (file_exists($loggerFile)) {
                require_once $loggerFile;
            } else {
                $io->writeError('composer-logger was missing and it could not be autoloaded.');

                return;
            }
        }

        $this->logger = new Logger($io);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
          PackageEvents::POST_PACKAGE_INSTALL => 'postPackageInstall',
          PackageEvents::POST_PACKAGE_UPDATE => 'postPackageInstall',
          PackageEvents::PRE_PACKAGE_UNINSTALL => 'prePackageUninstall',
        ];
    }

    /**
     * Reacts to package removals.
     *
     * @param \Composer\Installer\PackageEvent $event
     */
    public function prePackageUninstall(PackageEvent $event): void
    {
        /** @var \Composer\DependencyResolver\Operation\UninstallOperation $operation */
        $operation = $event->getOperation();
        if (PhpCsConfigInstaller::PACKAGE_NAME === $operation->getPackage()->getName()) {
            // It is important to pass the instance of Composer that belongs
            // to the event instead of $this->composer because that instance
            // knows where this library was installed.
            $installer = new PhpCsConfigInstaller($event->getComposer(), new Filesystem(), $this->logger);
            try {
                $installer->uninstall();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Reacts to post package install/updates.
     *
     * @param \Composer\Installer\PackageEvent $event
     */
    public function postPackageInstall(PackageEvent $event): void
    {
        $package = null;
        $operation = $event->getOperation();
        if ($operation instanceof InstallOperation) {
            $package = $operation->getPackage();
        } elseif ($operation instanceof UpdateOperation) {
            $package = $operation->getTargetPackage();
        }

        if ($package instanceof PackageInterface && PhpCsConfigInstaller::PACKAGE_NAME === $package->getName()) {
            $installer = new PhpCsConfigInstaller($event->getComposer(), new Filesystem(), $this->logger);
            try {
                $installer->install();
            } catch (FileExistsException $e) {
                if (is_link($e->getPath()) && readlink($e->getPath()) === $installer->getConfigFilePath()) {
                    $this->logger->info('PHPCS configuration is already symlinked.');
                } else {
                    $this->logger->warning(sprintf('phpcs.yml.dist already exists in %s. Configuration shipped with this package has not been symlinked.', getcwd()));
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities()
    {
        return [
          ComposerCommandProvider::class => CommandProvider::class,
        ];
    }
}
