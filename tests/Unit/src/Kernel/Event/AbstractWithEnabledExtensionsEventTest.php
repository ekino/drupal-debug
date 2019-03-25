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

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\Event;

use Ekino\Drupal\Debug\Kernel\Event\AbstractWithEnabledExtensionsEvent;
use PHPUnit\Framework\TestCase;

class AbstractWithEnabledExtensionsEventTest extends TestCase
{
    /**
     * @var TestAbstractWithEnabledExtensionsEvent
     */
    private $abstractWithEnabledExtensionsEvent;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->abstractWithEnabledExtensionsEvent = new TestAbstractWithEnabledExtensionsEvent(false, array('foo'), array('bar'));
    }

    public function testGetEnabledModules(): void
    {
        $this->assertSame(array('foo'), $this->abstractWithEnabledExtensionsEvent->getEnabledModules());
    }

    public function testGetEnabledThemes(): void
    {
        $this->assertSame(array('bar'), $this->abstractWithEnabledExtensionsEvent->getEnabledThemes());
    }
}

class TestAbstractWithEnabledExtensionsEvent extends AbstractWithEnabledExtensionsEvent
{
}
