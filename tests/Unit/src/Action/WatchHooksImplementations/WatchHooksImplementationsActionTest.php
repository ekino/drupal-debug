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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\WatchHooksImplementations;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsAction;
use Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsOptions;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WatchHooksImplementationsActionTest extends TestCase
{
    /**
     * @var MockObject|WatchHooksImplementationsOptions
     */
    private $watchHooksImplementationsOptions;

    /**
     * @var WatchHooksImplementationsAction
     */
    private $watchHooksImplementationsAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->watchHooksImplementationsOptions = $this->createMock(WatchHooksImplementationsOptions::class);

        $this->watchHooksImplementationsAction = new WatchHooksImplementationsAction($this->watchHooksImplementationsOptions);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_attach_synthetic' => 'setResources',
        ), WatchHooksImplementationsAction::getSubscribedEvents());
    }

    /**
     * @dataProvider processWhenThereIsANotSupportedException
     */
    public function testProcessWhenThereIsANotSupportedException(
        string $expectedExceptionMessage,
        bool $moduleHandlerServiceDefinitionExists,
        ?bool $moduleHandlerServiceDefinitionClassIsTheRightOne = null,
        ?bool $eventDispatcherServiceDefinitionExists = null,
        ?bool $eventDispatcherServiceDefinitionClassIsString = null,
        ?bool $eventDispatcherServiceDefinitionClassImplementsTheRightInterface = null
    ): void {
        list($containerBuilder) = $this->setUpTestProcess(
            $moduleHandlerServiceDefinitionExists,
            $moduleHandlerServiceDefinitionClassIsTheRightOne,
            $eventDispatcherServiceDefinitionExists,
            $eventDispatcherServiceDefinitionClassIsString,
            $eventDispatcherServiceDefinitionClassImplementsTheRightInterface
        );

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->watchHooksImplementationsAction->process($containerBuilder);
    }

    public function processWhenThereIsANotSupportedException(): array
    {
        return array(
            array('The "module_handler" service should already be set in the container.', false),
            array('The "module_handler" service class should be "Drupal\Core\Extension\ModuleHandler".', true, false),
            array('The "event_dispatcher" service should already be set in the container.', true, true, false),
            array('The "event_dispatcher" service class should be a string.', true, true, true, false),
            array('The "event_dispatcher" service class should implement the "Symfony\Component\EventDispatcher\EventDispatcherInterface" interface.', true, true, true, true, false),
        );
    }

    public function testProcess(): void
    {
        /**
         * @var ContainerBuilder
         * @var Definition       $moduleHandlerDefinition
         */
        list($containerBuilder, $moduleHandlerDefinition) = $this->setUpTestProcess(true, true, true, true, true);

        $this->watchHooksImplementationsOptions
            ->expects($this->atLeastOnce())
            ->method('getCacheFilePath')
            ->willReturn('/ccc');

        $this->assertSame('fcyccc', $moduleHandlerDefinition->getArgument(2));

        $this->watchHooksImplementationsAction->process($containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('ekino.drupal.debug.action.watch_hooks_implementations.resources'));
        $this->assertTrue($containerBuilder->getDefinition('ekino.drupal.debug.action.watch_hooks_implementations.resources')->isSynthetic());

        $fileBackendDefinition = $moduleHandlerDefinition->getArgument(2);

        $this->assertInstanceOf(Definition::class, $fileBackendDefinition);
        /* @var Definition $fileBackendDefinition */
        $this->assertSame(FileBackend::class, $fileBackendDefinition->getClass());
        $this->assertEquals(array(
            new Definition(FileCache::class, array(
                '/ccc',
                new Reference('ekino.drupal.debug.action.watch_hooks_implementations.resources'),
            )),
        ), $fileBackendDefinition->getArguments());
        $this->assertEquals(array(
            array(
                'setEventDispatcher',
                array(new Reference('event_dispatcher')),
            ),
        ), $fileBackendDefinition->getMethodCalls());
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsOptions', WatchHooksImplementationsAction::getOptionsClass());
    }

    private function setUpTestProcess(
        bool $moduleHandlerServiceDefinitionExists,
        ?bool $moduleHandlerServiceDefinitionClassIsTheRightOne,
        ?bool $eventDispatcherServiceDefinitionExists,
        ?bool $eventDispatcherServiceDefinitionClassIsString,
        ?bool $eventDispatcherServiceDefinitionClassImplementsTheRightInterface
    ): array {
        $containerBuilder = new ContainerBuilder();
        $moduleHandlerDefinition = null;
        $eventDispatcherDefinition = null;

        if ($moduleHandlerServiceDefinitionExists) {
            $moduleHandlerDefinition = new Definition($moduleHandlerServiceDefinitionClassIsTheRightOne ? ModuleHandler::class : __CLASS__, array(
                'foo',
                'bar',
                'fcyccc',
            ));

            $containerBuilder->setDefinition('module_handler', $moduleHandlerDefinition);

            if ($eventDispatcherServiceDefinitionExists) {
                if ($eventDispatcherServiceDefinitionClassIsString) {
                    if ($eventDispatcherServiceDefinitionClassImplementsTheRightInterface) {
                        $eventDispatcherDefinitionClass = EventDispatcher::class;
                    } else {
                        $eventDispatcherDefinitionClass = __CLASS__;

                        $this->assertFalse((new \ReflectionClass($eventDispatcherDefinitionClass))->implementsInterface(EventDispatcherInterface::class));
                    }
                } else {
                    $eventDispatcherDefinitionClass = null;
                }

                $containerBuilder->setDefinition('event_dispatcher', new Definition($eventDispatcherDefinitionClass));
            }
        }

        return array(
            $containerBuilder,
            $moduleHandlerDefinition,
        );
    }
}
