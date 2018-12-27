<?php

declare(strict_types=1);

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
    public function setUp()
    {
        $this->abstractWithEnabledExtensionsEvent = new TestAbstractWithEnabledExtensionsEvent(array('foo'), array('bar'));
    }

    public function testGetEnabledModules()
    {
        $this->assertSame(array('foo'), $this->abstractWithEnabledExtensionsEvent->getEnabledModules());
    }

    public function testGetEnabledThemes()
    {
        $this->assertSame(array('bar'), $this->abstractWithEnabledExtensionsEvent->getEnabledThemes());
    }
}

class TestAbstractWithEnabledExtensionsEvent extends AbstractWithEnabledExtensionsEvent
{
}
