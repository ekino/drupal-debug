<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchHooksImplementations;

use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WatchHooksImplementationsAction implements CompilerPassActionInterface, EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var string
     */
    const RESOURCES_SERVICE_ID = 'ekino.drupal.debug.action.watch_hooks_implementations.resources';

    /**
     * @var WatchHooksImplementationsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_ATTACH_SYNTHETIC => 'setResources',
        );
    }

    /**
     * @param WatchHooksImplementationsOptions $options
     */
    public function __construct(WatchHooksImplementationsOptions $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('module_handler')) {
            throw new NotSupportedException('The "module_handler" service should already be set in the container.');
        }

        $moduleHandlerDefinition = $container->getDefinition('module_handler');
        if (ModuleHandler::class !== $moduleHandlerDefinition->getClass()) {
            throw new NotSupportedException(\sprintf('The "module_handler" service class should be "%s".', ModuleHandler::class));
        }

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

        $resourcesDefinition = new Definition();
        $resourcesDefinition->setSynthetic(true);
        $container->setDefinition(self::RESOURCES_SERVICE_ID, $resourcesDefinition);

        $fileBackendDefinition = new Definition(FileBackend::class, array(
            new Definition(FileCache::class, array(
                $this->options->getCacheFilePath(),
                new Reference(self::RESOURCES_SERVICE_ID),
            )),
        ));
        $fileBackendDefinition->addMethodCall('setEventDispatcher', array(
            new Reference('event_dispatcher'),
        ));

        $moduleHandlerDefinition->replaceArgument(2, $fileBackendDefinition);
    }

    /**
     * @param AfterAttachSyntheticEvent $event
     */
    public function setResources(AfterAttachSyntheticEvent $event)
    {
        $event->getContainer()->set(self::RESOURCES_SERVICE_ID, $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass()
    {
        return WatchHooksImplementationsOptions::class;
    }
}
