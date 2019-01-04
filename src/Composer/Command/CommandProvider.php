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

namespace Ekino\Drupal\Debug\Composer\Command;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{
    /**
     * {@inheritdoc}
     */
    public function getCommands(): array
    {
        return array(
            new DisableOriginalDrupalKernelSubstitutionCommand(),
            new DumpReferenceConfigurationFileCommand(),
            new EnableOriginalDrupalKernelSubstitutionCommand(),
        );
    }
}
