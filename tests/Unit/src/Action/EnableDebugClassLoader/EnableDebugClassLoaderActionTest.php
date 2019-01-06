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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\EnableDebugClassLoader;

use Ekino\Drupal\Debug\Action\EnableDebugClassLoader\EnableDebugClassLoaderAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\DebugClassLoader;

class EnableDebugClassLoaderActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.on_kernel_instantiation' => 'process',
        ), EnableDebugClassLoaderAction::getSubscribedEvents());
    }

    public function testProcess(): void
    {
        (new EnableDebugClassLoaderAction())->process();

        $splAutoloadFunctions = \spl_autoload_functions();
        if (!\is_array($splAutoloadFunctions) || empty($splAutoloadFunctions)) {
            $this->fail('There should be registered __autoload() functions.');
        }

        foreach ($splAutoloadFunctions as $callable) {
            $this->assertInstanceOf(DebugClassLoader::class, $callable[0]);
        }
    }
}
