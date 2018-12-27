<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Composer\Command;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{
    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return array(
            new DisableOriginalDrupalKernelSubstitutionCommand(),
            new DumpReferenceConfigurationFileCommand(),
            new EnableOriginalDrupalKernelSubstitutionCommand(),
        );
    }
}
