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

namespace Ekino\Drupal\Debug\Tests\Unit\Composer\Plugin;

use Ekino\Drupal\Debug\Composer\Plugin\RegisterCommandsPlugin;
use PHPUnit\Framework\TestCase;

class RegisterCommandsPluginTest extends TestCase
{
    public function testGetCapabilities(): void
    {
        $this->assertSame(array(
            'Composer\Plugin\Capability\CommandProvider' => 'Ekino\Drupal\Debug\Composer\Command\CommandProvider',
        ), (new RegisterCommandsPlugin())->getCapabilities());
    }
}
