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

namespace Ekino\Drupal\Debug\Tests\Unit\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Action\AbstractDisableDrupalCacheAction;
use Ekino\Drupal\Debug\Kernel\Event\AfterContainerInitializationEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractDisableDrupalCacheActionTest extends TestCase
{
    /**
     * @var AbstractDisableDrupalCacheActionTestClass
     */
    private $disableDrupalCacheAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->disableDrupalCacheAction = new AbstractDisableDrupalCacheActionTestClass();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_settings_initialization' => 'overrideSettings',
            'ekino.drupal.debug.debug_kernel.after_container_initialization' => 'setNullBackend',
        ), AbstractDisableDrupalCacheActionTestClass::getSubscribedEvents());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOverrideSettings(): void
    {
        new Settings(array(
            'cache' => array(
                'bins' => array(
                    'foo' => 'bar',
                ),
            ),
        ));

        $this->disableDrupalCacheAction->overrideSettings();

        $this->assertSame(array(
            'bins' => array(
                'foo' => 'ekino.drupal.debug.action.abstract_disable_cache.null_backend_factory',
            ),
        ), Settings::get('cache'));
    }

    public function testSetNullBackendOnFirstCall(): void
    {
        list($container, $afterContainerInitializationEvent) = $this->setUpTestSetNullBackend(false);

        $container
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with('ekino.drupal.debug.action.abstract_disable_cache.null_backend_factory', new NullBackendFactory());

        $this->disableDrupalCacheAction->setNullBackend($afterContainerInitializationEvent);
    }

    public function testSetNullBackendOnFollowingCalls(): void
    {
        list($container, $afterContainerInitializationEvent) = $this->setUpTestSetNullBackend(true);

        $container
          ->expects($this->never())
          ->method('set');

        $this->disableDrupalCacheAction->setNullBackend($afterContainerInitializationEvent);
    }

    private function setUpTestSetNullBackend(bool $serviceIsSet): array
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
              ->expects($this->atLeastOnce())
              ->method('has')
              ->with('ekino.drupal.debug.action.abstract_disable_cache.null_backend_factory')
              ->willReturn($serviceIsSet);

        $afterContainerInitializationEvent = $this->createMock(AfterContainerInitializationEvent::class);
        $afterContainerInitializationEvent
              ->expects($this->atLeastOnce())
              ->method('getContainer')
              ->willReturn($container);

        return array(
            $container,
            $afterContainerInitializationEvent,
        );
    }
}

class AbstractDisableDrupalCacheActionTestClass extends AbstractDisableDrupalCacheAction
{
    /**
     * {@inheritdoc}
     */
    protected function getBin(): string
    {
        return 'foo';
    }
}
