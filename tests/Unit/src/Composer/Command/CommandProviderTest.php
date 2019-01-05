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

namespace Ekino\Drupal\Debug\Tests\Unit\Composer\Command;

use Ekino\Drupal\Debug\Composer\Command\CommandProvider;
use Ekino\Drupal\Debug\Composer\Command\DisableOriginalDrupalKernelSubstitutionCommand;
use Ekino\Drupal\Debug\Composer\Command\DumpReferenceConfigurationFileCommand;
use Ekino\Drupal\Debug\Composer\Command\EnableOriginalDrupalKernelSubstitutionCommand;
use PHPUnit\Framework\TestCase;

class CommandProviderTest extends TestCase
{
    public function testGetCommands(): void
    {
        $this->assertEquals(array(
            new DisableOriginalDrupalKernelSubstitutionCommand(),
            new DumpReferenceConfigurationFileCommand(),
            new EnableOriginalDrupalKernelSubstitutionCommand(),
        ), (new CommandProvider())->getCommands());
    }
}
