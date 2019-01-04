<?php

declare(strict_types=1);

/*
 * This file is part of the ekino Drupal Debug project.
 *
 * (c) ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Drupal\Debug\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Ekino\Drupal\Debug\Composer\Helper\ManageConfigurationHelper;

class ManageConfigurationPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var string
     */
    const THIS_PACKAGE_NAME = 'ekino/drupal-debug';

    /**
     * @var ManageConfigurationHelper
     */
    private $manageConfigurationHelper;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->manageConfigurationHelper = new ManageConfigurationHelper($composer, $io);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPrePackageUninstall',
        );
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageUpdate(PackageEvent $event): void
    {
        if ($this->shouldProcess($event)) {
            $this->manageConfigurationHelper->warnAboutPotentialConfigurationChanges();
        }
    }

    /**
     * @param PackageEvent $event
     */
    public function onPrePackageUninstall(PackageEvent $event): void
    {
        if ($this->shouldProcess($event)) {
            $this->manageConfigurationHelper->askForConfigurationFileDeletion();
        }
    }

    /**
     * @param PackageEvent $event
     *
     * @return bool
     */
    private function shouldProcess(PackageEvent $event): bool
    {
        if ($event->isPropagationStopped()) {
            return false;
        }

        $operation = $event->getOperation();
        switch (\get_class($operation)) {
            case UpdateOperation::class:
                /** @var UpdateOperation $operation */
                $package = $operation->getTargetPackage();

                break;
            case UninstallOperation::class:
                /** @var UninstallOperation $operation */
                $package = $operation->getPackage();

                break;
            default:
                return false;
        }

        /* @var PackageInterface $package  */
        return self::THIS_PACKAGE_NAME === $package->getName();
    }
}
