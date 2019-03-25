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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\WatchRoutingDefinitions;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Routing\RouteBuilderInterface;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsAction;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsOptions;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Kernel\Event\AfterRequestPreHandleEvent;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WatchRoutingDefinitionsActionTest extends TestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const ROUTING_IS_FRESH_RESOURCES_FILE_PATH = __DIR__.'/fixtures/__routing_is_fresh_resources.meta';

    /**
     * @var string
     */
    private const ROUTING_IS_NOT_FRESH_RESOURCES_FILE_PATH = __DIR__.'/fixtures/__routing_is_not_fresh_resources.meta';

    /**
     * @var string
     */
    private const RESOURCE_1_FILE_PATH = __DIR__.'/fixtures/File1.php';

    /**
     * @var MockObject|WatchRoutingDefinitionsOptions
     */
    private $watchRoutingDefinitionsOptions;

    /**
     * @var WatchRoutingDefinitionsAction
     */
    private $watchRoutingDefinitionsAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->watchRoutingDefinitionsOptions = $this->createMock(WatchRoutingDefinitionsOptions::class);

        $this->watchRoutingDefinitionsAction = new WatchRoutingDefinitionsAction($this->watchRoutingDefinitionsOptions);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        self::deleteFile(self::ROUTING_IS_FRESH_RESOURCES_FILE_PATH);
        self::deleteFile(self::ROUTING_IS_NOT_FRESH_RESOURCES_FILE_PATH);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_request_pre_handle' => 'process',
        ), WatchRoutingDefinitionsAction::getSubscribedEvents());
    }

    public function testProcessWhenTheRoutingIsFresh(): void
    {
        list($afterRequestPreHandleEvent, $routerBuilder) = $this->setUpTestProcess(true, true, true);

        $routerBuilder
            ->expects($this->never())
            ->method('rebuild');

        $this->watchRoutingDefinitionsAction->process($afterRequestPreHandleEvent);
    }

    /**
     * @dataProvider processWhenThereIsANotSupportedException
     */
    public function testProcessWhenThereIsANotSupportedException(string $expectedExceptionMessage, bool $routerBuilderServiceExists, ?bool $routerBuilderServiceImplementsTheRightInterface = null): void
    {
        list($afterRequestPreHandleEvent) = $this->setUpTestProcess(false, $routerBuilderServiceExists, $routerBuilderServiceImplementsTheRightInterface);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->watchRoutingDefinitionsAction->process($afterRequestPreHandleEvent);
    }

    public function processWhenThereIsANotSupportedException(): array
    {
        return array(
            array('The "router.builder" service should already be set in the container.', false),
            array('The "router.builder" service class should implement the "Drupal\Core\Routing\RouteBuilderInterface" interface.', true, false),
        );
    }

    public function testProcess(): void
    {
        list($afterRequestPreHandleEvent, $routerBuilder) = $this->setUpTestProcess(false, true, true);

        $routerBuilder
            ->expects($this->once())
            ->method('rebuild');

        $this->watchRoutingDefinitionsAction->process($afterRequestPreHandleEvent);
        $this->watchRoutingDefinitionsAction->process($afterRequestPreHandleEvent);
    }

    private function setUpTestProcess(bool $routingIsFresh, bool $routerBuilderServiceExists, ?bool $routerBuilderServiceImplementsTheRightInterface): array
    {
        $resourcesCollection = new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, new CustomModule('/foo', 'custom_module')),
        ));

        $this->watchRoutingDefinitionsOptions
            ->expects($this->atLeastOnce())
            ->method('getCacheFilePath')
            ->willReturn($routingIsFresh ? self::ROUTING_IS_FRESH_RESOURCES_FILE_PATH : self::ROUTING_IS_NOT_FRESH_RESOURCES_FILE_PATH);
        $this->watchRoutingDefinitionsOptions
            ->expects($this->atLeastOnce())
            ->method('getFilteredResourcesCollection')
            ->with(array('foo_1', 'custom_module'), array('themeee'))
            ->willReturn($resourcesCollection);

        if ($routingIsFresh) {
            self::writeFile(self::ROUTING_IS_FRESH_RESOURCES_FILE_PATH, \serialize($resourcesCollection));
        }

        $container = new Container();
        $routerBuilder = null;

        if ($routerBuilderServiceExists) {
            if ($routerBuilderServiceImplementsTheRightInterface) {
                $routeBuilderClass = RouteBuilderInterface::class;
            } else {
                $routeBuilderClass = __CLASS__;

                $this->assertFalse((new \ReflectionClass($routeBuilderClass))->implementsInterface(RouteBuilderInterface::class));
            }

            $routerBuilder = $this->createMock($routeBuilderClass);

            $container->set('router.builder', $routerBuilder);
        }

        return array(
            new AfterRequestPreHandleEvent(false, $container, array('foo_1', 'custom_module'), array('themeee')),
            $routerBuilder,
        );
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsOptions', WatchRoutingDefinitionsAction::getOptionsClass());
    }
}
