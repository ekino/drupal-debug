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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisplayPrettyExceptions;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsAction;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\ExceptionController;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class DisplayPrettyExceptionsActionTest extends TestCase
{
    /**
     * @var MockObject|DisplayPrettyExceptionsOptions
     */
    private $displayPrettyExceptionsOptions;

    /**
     * @var DisplayPrettyExceptionsAction
     */
    private $displayPrettyExceptionsAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayPrettyExceptionsOptions = $this->createMock(DisplayPrettyExceptionsOptions::class);

        $this->displayPrettyExceptionsAction = new DisplayPrettyExceptionsAction($this->displayPrettyExceptionsOptions);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_attach_synthetic' => 'setLogger',
        ), DisplayPrettyExceptionsAction::getSubscribedEvents());
    }

    /**
     * @dataProvider processWhenThereIsANotSupportedExceptionProvider
     */
    public function testProcessWhenThereIsANotSupportedException(
        string $expectedExceptionMessage,
        bool $eventDispatcherServiceDefinitionExists,
        ?bool $eventDispatcherServiceDefinitionClassIsString = null,
        ?bool $eventDispatcherServiceDefinitionClassImplementsTheRightInterface = null
    ): void {
        list($containerBuilder) = $this->setUpTestProcess($eventDispatcherServiceDefinitionExists, $eventDispatcherServiceDefinitionClassIsString, $eventDispatcherServiceDefinitionClassImplementsTheRightInterface);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->displayPrettyExceptionsAction->process($containerBuilder);
    }

    public function processWhenThereIsANotSupportedExceptionProvider(): array
    {
        return array(
            array('The "event_dispatcher" service should already be set in the container.', false),
            array('The "event_dispatcher" service class should be a string.', true, false),
            array('The "event_dispatcher" service class should implement the "Symfony\Component\EventDispatcher\EventDispatcherInterface" interface', true, true, false),
        );
    }

    /**
     * @dataProvider processProvider
     */
    public function testProcess(?string $charset, ?string $fileLinkFormat, ?LoggerInterface $logger): void
    {
        /** @var MockObject|ContainerBuilder $containerBuilder */
        list($containerBuilder, $eventDispatcherDefinition) = $this->setUpTestProcess(true, true, true);

        $this->setUpDisplayPrettyExceptionsOptions($charset, $fileLinkFormat, $logger);

        $this->assertEmpty($eventDispatcherDefinition->getMethodCalls());

        $this->displayPrettyExceptionsAction->process($containerBuilder);

        $hasLogger = $logger instanceof LoggerInterface;
        if ($hasLogger) {
            $this->assertTrue($containerBuilder->hasDefinition('ekino.drupal.debug.action.display_pretty_exceptions.logger'));

            $loggerDefinition = $containerBuilder->getDefinition('ekino.drupal.debug.action.display_pretty_exceptions.logger');

            $this->assertTrue($loggerDefinition->isSynthetic());
        }

        $this->assertEquals(array(
            array(
                'addSubscriber',
                array(
                    new Definition(ExceptionListener::class, array(
                        new Definition(ExceptionController::class, array(
                            new Definition(ExceptionHandler::class, array(
                                true,
                                $charset,
                                $fileLinkFormat,
                            )),
                        )),
                        $hasLogger ? new Reference('ekino.drupal.debug.action.display_pretty_exceptions.logger') : null,
                        true,
                    )),
                ),
            ),
        ), $eventDispatcherDefinition->getMethodCalls());
    }

    public function processProvider(): array
    {
        return array(
            array(null, null, null),
            array('utf-8', null, null),
            array(null, 'myide://open?url=file://%%f&line=%%l', null),
            array(null, null, $this->createMock(LoggerInterface::class)),
            array('utf-8', 'myide://open?url=file://%%f&line=%%l', null),
            array(null, 'myide://open?url=file://%%f&line=%%l', $this->createMock(LoggerInterface::class)),
            array('utf-8', null, $this->createMock(LoggerInterface::class)),
            array('utf-8', 'myide://open?url=file://%%f&line=%%l', $this->createMock(LoggerInterface::class)),
        );
    }

    public function testSetLoggerWhenThereIsNoLogger(): void
    {
        /** @var MockObject|ContainerInterface $container */
        list($afterAttachSyntheticEvent, $container) = $this->setUpTestSetLogger(false, false);

        $this->assertFalse($container->has('ekino.drupal.debug.action.display_pretty_exceptions.logger'));

        $this->displayPrettyExceptionsAction->setLogger($afterAttachSyntheticEvent);

        $this->assertFalse($container->has('ekino.drupal.debug.action.display_pretty_exceptions.logger'));
    }

    /**
     * @dataProvider setLoggerWhenThereIsALogicExceptionProvider
     */
    public function testSetLoggerWhenThereIsALogicException(string $expectedExceptionMessage, bool $loggerServiceExists, bool $loggerInOptionsExists): void
    {
        list($afterAttachSyntheticEvent) = $this->setUpTestSetLogger($loggerServiceExists, $loggerInOptionsExists);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->displayPrettyExceptionsAction->setLogger($afterAttachSyntheticEvent);
    }

    public function setLoggerWhenThereIsALogicExceptionProvider(): array
    {
        return array(
            array('The container should have a synthetic service with the id "ekino.drupal.debug.action.display_pretty_exceptions.logger".', false, true),
            array('The options should return a concrete logger.', true, false),
        );
    }

    public function testSetLogger(): void
    {
        /** @var Container $container */
        list($afterAttachSyntheticEvent, $container, $logger) = $this->setUpTestSetLogger(true, true);

        $this->displayPrettyExceptionsAction->setLogger($afterAttachSyntheticEvent);

        $this->assertSame($logger, $container->get('ekino.drupal.debug.action.display_pretty_exceptions.logger'));
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions', DisplayPrettyExceptionsAction::getOptionsClass());
    }

    private function setUpTestProcess(
        bool $eventDispatcherServiceDefinitionExists,
        ?bool $eventDispatcherServiceDefinitionClassIsString,
        ?bool $eventDispatcherServiceDefinitionClassImplementsTheRightInterface
    ): array {
        $containerBuilder = new ContainerBuilder();
        $eventDispatcherDefinition = null;

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

            $eventDispatcherDefinition = new Definition($eventDispatcherDefinitionClass);

            $containerBuilder->setDefinition('event_dispatcher', $eventDispatcherDefinition);
        }

        return array(
            $containerBuilder,
            $eventDispatcherDefinition,
        );
    }

    /**
     * @param string|false|null    $charset
     * @param string|false|null    $fileLinkFormat
     * @param LoggerInterface|null $logger
     */
    private function setUpDisplayPrettyExceptionsOptions($charset, $fileLinkFormat, ?LoggerInterface $logger): void
    {
        if (false !== $charset) {
            $this->displayPrettyExceptionsOptions
                ->expects($this->atLeastOnce())
                ->method('getCharset')
                ->willReturn($charset);
        }

        if (false !== $fileLinkFormat) {
            $this->displayPrettyExceptionsOptions
                ->expects($this->atLeastOnce())
                ->method('getFileLinkFormat')
                ->willReturn($fileLinkFormat);
        }

        $this->displayPrettyExceptionsOptions
            ->expects($this->atLeastOnce())
            ->method('getLogger')
            ->willReturn($logger);
    }

    private function setUpTestSetLogger(bool $loggerServiceExists, bool $loggerInOptionsExists): array
    {
        $containerDefinition = array();

        if ($loggerServiceExists) {
            $containerDefinition = array(
                'machine_format' => true,
                'services' => array(
                    'ekino.drupal.debug.action.display_pretty_exceptions.logger' => array(
                        'synthetic' => true,
                    ),
                ),
            );
        }

        $container = new Container($containerDefinition);

        $logger = $loggerInOptionsExists ? $this->createMock(LoggerInterface::class) : null;

        $this->setUpDisplayPrettyExceptionsOptions(false, false, $logger);

        return array(
            new AfterAttachSyntheticEvent(false, $container, array(), array()),
            $container,
            $logger,
        );
    }
}
