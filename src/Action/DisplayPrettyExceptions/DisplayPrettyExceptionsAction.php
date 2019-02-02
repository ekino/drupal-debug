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

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Action\ValidateContainerDefinitionTrait;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class DisplayPrettyExceptionsAction implements CompilerPassActionInterface, EventSubscriberActionInterface, ActionWithOptionsInterface
{
    use ValidateContainerDefinitionTrait;

    /**
     * @var string
     */
    const LOGGER_SERVICE_ID = 'ekino.drupal.debug.action.display_pretty_exceptions.logger';

    /**
     * @var DisplayPrettyExceptionsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            DebugKernelEvents::AFTER_ATTACH_SYNTHETIC => 'setLogger',
        );
    }

    /**
     * @param DisplayPrettyExceptionsOptions $options
     */
    public function __construct(DisplayPrettyExceptionsOptions $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $eventDispatcherDefinition = $this->validateContainerDefinitionClassImplements($container, 'event_dispatcher', EventDispatcherInterface::class);

        $loggerReference = null;
        if ($this->options->getLogger() instanceof LoggerInterface) {
            $loggerDefinition = new Definition();
            $loggerDefinition->setSynthetic(true);

            $container->setDefinition(self::LOGGER_SERVICE_ID, $loggerDefinition);

            $loggerReference = new Reference(self::LOGGER_SERVICE_ID);
        }

        $eventDispatcherDefinition->addMethodCall('addSubscriber', array(
            new Definition(ExceptionListener::class, array(
                new Definition(ExceptionController::class, array(
                    new Definition(ExceptionHandler::class, array(
                        true,
                        $this->options->getCharset(),
                        $this->options->getFileLinkFormat(),
                    )),
                )),
                $loggerReference,
                true,
            )),
        ));
    }

    /**
     * @param AfterAttachSyntheticEvent $event
     */
    public function setLogger(AfterAttachSyntheticEvent $event): void
    {
        $container = $event->getContainer();

        $logger = $this->options->getLogger();
        if ($logger instanceof LoggerInterface) {
            if (!$container->has(self::LOGGER_SERVICE_ID)) {
                throw new \LogicException(\sprintf('The container should have a synthetic service with the id "%s".', self::LOGGER_SERVICE_ID));
            }

            $container->set(self::LOGGER_SERVICE_ID, $logger);
        } elseif ($container->has(self::LOGGER_SERVICE_ID)) {
            throw new \LogicException('The options should return a concrete logger.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return DisplayPrettyExceptionsOptions::class;
    }
}
