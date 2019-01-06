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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions;
use PHPUnit\Framework\TestCase;

class DisplayPrettyExceptionsASAPActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_environment_boot' => 'process',
        ), DisplayPrettyExceptionsASAPAction::getSubscribedEvents());
    }

    public function testProcess(): void
    {
        \set_exception_handler(null);

        (new DisplayPrettyExceptionsASAPAction($this->createMock(DisplayPrettyExceptionsASAPOptions::class)))->process();

        $this->assertInstanceOf(\Closure::class, \set_exception_handler(null));
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions', DisplayPrettyExceptionsASAPAction::getOptionsClass());
    }
}
