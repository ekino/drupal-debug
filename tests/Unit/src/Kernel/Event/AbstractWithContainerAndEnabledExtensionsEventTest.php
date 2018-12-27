<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\Event;

use Ekino\Drupal\Debug\Kernel\Event\AbstractWithContainerAndEnabledExtensionsEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractWithContainerAndEnabledExtensionsEventTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * @var TestAbstractWithContainerAndEnabledExtensionsEvent
     */
    private $abstractWithContainerAndEnabledExtensionsEvent;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->abstractWithContainerAndEnabledExtensionsEvent = new TestAbstractWithContainerAndEnabledExtensionsEvent($this->container, array(), array());
    }

    public function testGetContainer()
    {
        $this->assertSame($this->container, $this->abstractWithContainerAndEnabledExtensionsEvent->getContainer());
    }
}

class TestAbstractWithContainerAndEnabledExtensionsEvent extends AbstractWithContainerAndEnabledExtensionsEvent
{
}
