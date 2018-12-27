<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Composer\Plugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Ekino\Drupal\Debug\Composer\Command\CommandProvider;

class RegisterCommandsPlugin implements PluginInterface, Capable
{
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities()
    {
        return array(
            CommandProviderCapability::class => CommandProvider::class,
        );
    }
}
