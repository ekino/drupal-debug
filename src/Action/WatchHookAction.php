<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Event\ContainerEvent;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Helper\CustomExtensionHelper;
use Ekino\Drupal\Debug\Helper\FileResourceHelper;
use Ekino\Drupal\Debug\NotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class WatchHookAction implements CompilerPassActionInterface, EventSubscriberActionInterface
{
    /**
     * @var string
     */
    const RESOURCES_SERVICE_ID = 'ekino.drupal.debug.action.watch_hook.resources';

    /**
     * @var string[]
     */
    const DEFAULT_MODULE_FILE_RESOURCE_MASKS = array(
        '%machine_name%.module',
    );

    /**
     * @var string[]
     */
    const DEFAULT_THEME_FILE_RESOURCE_MASKS = array(
        '%machine_name%.theme',
    );

    /**
     * @var string
     */
    private $cacheFilePath;

    /**
     * @var ResourceInterface[]
     */
    private $resources;

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
     * @param string $cacheFilePath
     * @param ResourceInterface[] $resources
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resources = $resources;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('module_handler')) {
            throw new NotSupportedException();
        }

        $definition = $container->getDefinition('module_handler');
        if (ModuleHandler::class !== $definition->getClass()) {
            throw new NotSupportedException();
        }

        $resourcesDefinition = new Definition();
        $resourcesDefinition->setSynthetic(true);
        $container->setDefinition(self::RESOURCES_SERVICE_ID, $resourcesDefinition);

        $definition->replaceArgument(2, new Definition(FileBackend::class, array(
            $this->cacheFilePath,
            new Reference(self::RESOURCES_SERVICE_ID),
        )));
    }

    /**
     * @param ContainerEvent $event
     */
    public function setResources(ContainerEvent $event)
    {
        $event->getContainer()->set(self::RESOURCES_SERVICE_ID, $this->resources);
    }

    /**
     * @param string $appRoot
     *
     * @return WatchHookAction
     */
    public static function getDefaultAction($appRoot)
    {
        $customExtensionHelper = new CustomExtensionHelper($appRoot);

        return new self(sprintf('%s/cache/hooks.php', $appRoot), self::getDefaultResources($customExtensionHelper->getCustomModules(), $customExtensionHelper->getCustomThemes()));
    }

    /**
     * @param CustomModule[] $customModules
     * @param CustomTheme[] $customThemes
     *
     * @return ResourceInterface[]
     */
    public static function getDefaultResources(array $customModules, array $customThemes)
    {
        $resources = array();

        $fileResourceHelper = new FileResourceHelper();

        /** @var CustomModule $customModule */
        foreach ($customModules as $customModule) {
            $resources = array_merge($resources, $fileResourceHelper->getFileResources($customModule->getRootPath(), self::DEFAULT_MODULE_FILE_RESOURCE_MASKS, array(
                '%machine_name%' => $customModule->getMachineName(),
            )));
        }

        /** @var CustomTheme $customTheme */
        foreach ($customThemes as $customTheme) {
            $resources = array_merge($resources, $fileResourceHelper->getFileResources($customTheme->getRootPath(), self::DEFAULT_THEME_FILE_RESOURCE_MASKS, array(
                '%machine_name%' => $customTheme->getMachineName(),
            )));
        }

        return $resources;
    }
}
