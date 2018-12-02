<?php

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
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
    public static function getSubscribedEvents()
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
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('event_dispatcher')) {
            throw new NotSupportedException('The "event_dispatcher" service should already be set in the container.');
        }

        $eventDispatcherDefinition = $container->getDefinition('event_dispatcher');
        $eventDispatcherClass = $eventDispatcherDefinition->getClass();
        if (!\is_string($eventDispatcherClass)) {
            throw new NotSupportedException('The "event_dispatcher" service class should be a string.');
        }

        if (!(new \ReflectionClass($eventDispatcherClass))->implementsInterface(EventDispatcherInterface::class)) {
            throw new NotSupportedException(\sprintf('The "event_dispatcher" service class should implement the "%s" interface', EventDispatcherInterface::class));
        }

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
    public function setLogger(AfterAttachSyntheticEvent $event)
    {
        $container = $event->getContainer();

        $serviceExists = $container->has(self::LOGGER_SERVICE_ID);

        $logger = $this->options->getLogger();
        $loggerExists = $logger instanceof LoggerInterface;

        if (!$serviceExists && !$loggerExists) {
            return;
        }

        if ($serviceExists && !$loggerExists) {
            throw new \LogicException('The options should return a concrete logger.');
        } elseif (!$serviceExists && $loggerExists) {
            throw new \LogicException(\sprintf('The container should have a synthetic service with the id "%s".', self::LOGGER_SERVICE_ID));
        }

        $container->set(self::LOGGER_SERVICE_ID, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass()
    {
        return DisplayPrettyExceptionsOptions::class;
    }
}
