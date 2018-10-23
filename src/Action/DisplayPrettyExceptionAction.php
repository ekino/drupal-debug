<?php

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Controller\ExceptionController;
use Ekino\Drupal\Debug\Event\ContainerEvent;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Logger\DefaultLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

class DisplayPrettyExceptionAction implements CompilerPassActionInterface, EventSubscriberActionInterface
{
    /**
     * @var string
     */
    const LOGGER_SERVICE_ID = 'ekino.drupal.debug.action.display_pretty_exception.logger';

    /**
     * @var string|null
     */
    private $charset;

    /**
     * @var string|null
     */
    private $fileLinkFormat;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return array(
            DebugKernelEvents::AFTER_CONTAINER_INITIALIZATION => 'setLogger'
        );
    }

    /**
     * @param string|null $charset
     * @param string|null $fileLinkFormat
     * @param LoggerInterface|null $logger
     */
    public function __construct($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('event_dispatcher')) {
            throw new NotSupportedException();
        }

        $definition = $container->getDefinition('event_dispatcher');
        $class = $definition->getClass();
        if (!is_string($class)) {
            throw new NotSupportedException();
        }

        $refl = new \ReflectionClass($class);
        if (!$refl->implementsInterface(EventDispatcherInterface::class)) {
            throw new NotSupportedException();
        }

        $loggerReference = null;
        if ($this->logger instanceof LoggerInterface) {
            if ($container->hasDefinition(self::LOGGER_SERVICE_ID)) {
                throw new NotSupportedException();
            }

            $loggerDefinition = new Definition();
            $loggerDefinition->setPrivate(true);
            $loggerDefinition->setSynthetic(true);

            $container->setDefinition(self::LOGGER_SERVICE_ID, $loggerDefinition);

            $loggerReference = new Reference(self::LOGGER_SERVICE_ID);
        }

        $definition->addMethodCall('addSubscriber', array(
            new Definition(ExceptionListener::class, array(
                new Definition(ExceptionController::class, array(
                    new Definition(ExceptionHandler::class, array(
                        true,
                        $this->charset,
                        $this->fileLinkFormat,
                    )),
                )),
                $loggerReference,
                true,
            ))
        ));
    }

    /**
     * @param ContainerEvent $event
     */
    public function setLogger(ContainerEvent $event)
    {
      if (!$this->logger instanceof LoggerInterface) {
          return;
      }

      $container = $event->getContainer();
      if (!$container->has(self::LOGGER_SERVICE_ID)) {
          throw new \LogicException();
      }

      $container->set(self::LOGGER_SERVICE_ID, $this->logger);
    }

    /**
     * @param string $appRoot
     *
     * @return DisplayPrettyExceptionAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self(null, null, DefaultLogger::get($appRoot));
    }
}
