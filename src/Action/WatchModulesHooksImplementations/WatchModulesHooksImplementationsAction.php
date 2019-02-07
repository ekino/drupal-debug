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

namespace Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations;

use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Action\ValidateContainerDefinitionTrait;
use Ekino\Drupal\Debug\Cache\Event\FileBackendEvents;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

class WatchModulesHooksImplementationsAction implements CompilerPassActionInterface, EventSubscriberActionInterface, ActionWithOptionsInterface
{
    use ValidateContainerDefinitionTrait;

    /**
     * @var string
     */
    private const RESOURCES_SERVICE_ID = 'ekino.drupal.debug.action.watch_modules_hooks_implementations.resources';

    /**
     * @var string
     */
    private const EVENT_DISPATCHER_SERVICE_ID = 'ekino.drupal.debug.action.watch_modules_hooks_implementations.event_dispatcher';

    /**
     * @var WatchModulesHooksImplementationsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            DebugKernelEvents::AFTER_ATTACH_SYNTHETIC => array(array('setResources'), array('addListener')),
        );
    }

    /**
     * @param WatchModulesHooksImplementationsOptions $options
     */
    public function __construct(WatchModulesHooksImplementationsOptions $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $moduleHandlerDefinition = $this->validateContainerDefinitionClassIs($container, 'module_handler', ModuleHandler::class);

        $resourcesDefinition = new Definition();
        $resourcesDefinition->setSynthetic(true);
        $container->setDefinition(self::RESOURCES_SERVICE_ID, $resourcesDefinition);

        $fileBackendDefinition = new Definition(FileBackend::class, array(
            new Definition(FileCache::class, array(
                $this->options->getCacheFilePath(),
                new Reference(self::RESOURCES_SERVICE_ID),
            )),
        ));

        $eventDispatcherDefinition = new Definition();
        $eventDispatcherDefinition->setSynthetic(true);
        $container->setDefinition(self::EVENT_DISPATCHER_SERVICE_ID, $eventDispatcherDefinition);

        $fileBackendDefinition->addMethodCall('setEventDispatcher', array(
            new Reference(self::EVENT_DISPATCHER_SERVICE_ID),
        ));

        $moduleHandlerDefinition->replaceArgument(2, $fileBackendDefinition);
    }

    /**
     * @param AfterAttachSyntheticEvent $event
     */
    public function setResources(AfterAttachSyntheticEvent $event): void
    {
        $event->getContainer()->set(self::RESOURCES_SERVICE_ID, $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes()));
    }

    /**
     * @param AfterAttachSyntheticEvent $event
     *
     * @throws NotSupportedException
     */
    public function addListener(AfterAttachSyntheticEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $event->getContainer()->set(self::EVENT_DISPATCHER_SERVICE_ID, $eventDispatcher);

        $moduleHandler = $event->getContainer()->get('module_handler');
        if (!$moduleHandler instanceof ModuleHandler) {
            throw new NotSupportedException(\sprintf('The "module_handler" service class should be "%s".', ModuleHandler::class));
        }

        $kernel = $event->getContainer()->get('kernel');
        if (!$kernel instanceof DrupalKernelInterface) {
            throw new NotSupportedException(\sprintf('The "kernel" service class should implement the "%s" interface.', DrupalKernelInterface::class));
        }

        $eventDispatcher->addListener(
            FileBackendEvents::ON_CACHE_NOT_FRESH,
            new LoadNewModuleFile(
                $moduleHandler,
                $kernel
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return WatchModulesHooksImplementationsOptions::class;
    }
}
