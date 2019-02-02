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

use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Action\ValidateContainerDefinitionTrait;
use Ekino\Drupal\Debug\Cache\Event\FileBackendEvents;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class WatchModulesHooksImplementationsAction implements CompilerPassActionInterface, EventSubscriberActionInterface, ActionWithOptionsInterface
{
    use ValidateContainerDefinitionTrait;

    /**
     * @var string
     */
    private const RESOURCES_SERVICE_ID = 'ekino.drupal.debug.action.watch_modules_hooks_implementations.resources';

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
            DebugKernelEvents::AFTER_ATTACH_SYNTHETIC => 'setResources',
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
        $eventDispatcherDefinition = $this->validateContainerDefinitionClassImplements($container, 'event_dispatcher', EventDispatcherInterface::class);
        $this->validateContainerDefinitionClassImplements($container, 'kernel', HttpKernelInterface::class);

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

        $eventDispatcherDefinition
            ->addMethodCall('addListener', array(
                FileBackendEvents::ON_CACHE_NOT_FRESH,
                new Definition(LoadNewModuleFile::class, array(
                    new Reference('module_handler'),
                    new Reference('kernel'),
                )),
          ));
    }

    /**
     * @param AfterAttachSyntheticEvent $event
     */
    public function setResources(AfterAttachSyntheticEvent $event): void
    {
        $event->getContainer()->set(self::RESOURCES_SERVICE_ID, $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return WatchModulesHooksImplementationsOptions::class;
    }
}
