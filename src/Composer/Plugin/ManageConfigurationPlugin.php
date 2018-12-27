<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
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
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->manageConfigurationHelper = new ManageConfigurationHelper($composer, $io);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
            PackageEvents::POST_PACKAGE_UNINSTALL => 'onPostPackageUninstall',
        );
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageUpdate(PackageEvent $event)
    {
        if ($this->shouldProcess($event)) {
            $this->manageConfigurationHelper->warnAboutPotentialConfigurationChanges();
        }
    }

    /**
     * @param PackageEvent $event
     */
    public function onPostPackageUninstall(PackageEvent $event)
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
    private function shouldProcess(PackageEvent $event)
    {
        if ($event->isPropagationStopped()) {
            return false;
        }

        $operation = $event->getOperation();
        switch (\get_class($operation)) {
            case InstallOperation::class:
            case UninstallOperation::class:
                /** @var InstallOperation|UninstallOperation $operation */
                $package = $operation->getPackage();

                break;
            case UpdateOperation::class:
                /** @var UpdateOperation $operation */
                $package = $operation->getTargetPackage();

                break;
            default:
                return false;
        }

        /* @var PackageInterface $package  */
        return self::THIS_PACKAGE_NAME === $package->getName();
    }
}
