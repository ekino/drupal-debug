<?php

namespace Ekino\Drupal\Debug\Action\WatchHooksImplementations;

use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantAction;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Cache\Event\FileBackendEvents;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Kernel\Event\AfterContainerInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WatchHooksImplementationsAction extends AbstractFileBackendDependantAction implements CompilerPassActionInterface, EventSubscriberActionInterface
{
    /**
     * @var string
     */
    const RESOURCES_SERVICE_ID = 'ekino.drupal.debug.action.watch_hook.resources';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_CONTAINER_INITIALIZATION => 'setResources'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotSupportedException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('module_handler')) {
            throw new NotSupportedException();
        }

        $moduleHandlerDefinition = $container->getDefinition('module_handler');
        if (ModuleHandler::class !== $moduleHandlerDefinition->getClass()) {
            throw new NotSupportedException();
        }

        if (!$container->has('event_dispatcher')) {
            throw new NotSupportedException();
        }

        $eventDispatcherDefinition = $container->getDefinition('event_dispatcher');
        if (!in_array(EventDispatcherInterface::class, class_implements($eventDispatcherDefinition->getClass()))) {
            throw new NotSupportedException();
        }

        $resourcesDefinition = new Definition();
        $resourcesDefinition->setSynthetic(true);
        $resourcesDefinition->setPrivate(true);
        $container->setDefinition(self::RESOURCES_SERVICE_ID, $resourcesDefinition);

        $fileBackendDefinition = new Definition(FileBackend::class, array(
            new Definition(FileCache::class, array(
                $this->cacheFilePath,
                new Reference(self::RESOURCES_SERVICE_ID),
            ))
        ));
        $fileBackendDefinition->addMethodCall('setEventDispatcher', array(
            new Reference('event_dispatcher'),
        ));

        $moduleHandlerDefinition->replaceArgument(2, $fileBackendDefinition);

        $eventDispatcherDefinition
            ->addMethodCall('addListener', array(
                FileBackendEvents::ON_CACHE_NOT_FRESH,
                new Definition(LoadNewExtensions::class, array(
                    new Reference('module_handler'),
                ))
            ));
    }

    /**
     * @param AfterContainerInitializationEvent $event
     */
    public function setResources(AfterContainerInitializationEvent $event)
    {
        $event->getContainer()->set(self::RESOURCES_SERVICE_ID, $this->resources);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array(
            '%machine_name%.module',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultThemeFileResourceMasks()
    {
        return array(
            '%machine_name%.theme',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName()
    {
        return 'hooks.php';
    }
}
