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
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): array
    {
        return array(
            CommandProviderCapability::class => CommandProvider::class,
        );
    }
}
