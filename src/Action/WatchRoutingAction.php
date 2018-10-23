<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Routing\RouteBuilderInterface;
use Ekino\Drupal\Debug\Event\ContainerEvent;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Helper\ConfigCacheHelper;
use Ekino\Drupal\Debug\Helper\CustomExtensionHelper;
use Ekino\Drupal\Debug\Helper\FileCacheHelper;
use Ekino\Drupal\Debug\Helper\FileResourceHelper;
use Ekino\Drupal\Debug\NotSupportedException;

class WatchRoutingAction implements EventSubscriberActionInterface
{
    /**
     * @var string[]
     */
    const DEFAULT_FILE_RESOURCE_MASKS = array(
        '%machine_name%.routing.yml',
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
            DebugKernelEvents::AFTER_REQUEST_PRE_HANDLE => 'process',
        );
    }

    /**
     * @param string $cacheFilePath
     * @param ResourceInterface[]
     */
    public function __construct($cacheFilePath, array $resources)
    {
        $this->cacheFilePath = $cacheFilePath;
        $this->resources = $resources;
    }

    /**
     * @param ContainerEvent $event
     */
    public function process(ContainerEvent $event)
    {
        $configCacheHelper = new ConfigCacheHelper($this->cacheFilePath, $this->resources);
        if ($configCacheHelper->isFresh()) {
            return;
        }

        $container = $event->getContainer();
        if (!$container->has('router.builder')) {
            throw new NotSupportedException();
        }

        $routerBuilder = $container->get('router.builder');
        if (!$routerBuilder instanceof RouteBuilderInterface) {
            throw new NotSupportedException();
        }

        $routerBuilder->rebuild();

        $configCacheHelper->write(array());
    }

    /**
     * @param string $appRoot
     *
     * @return WatchRoutingAction
     */
    public static function getDefaultAction($appRoot)
    {
        return new self(sprintf('%s/cache/routing.php', $appRoot), self::getDefaultResources((new CustomExtensionHelper($appRoot))->getCustomModules()));
    }

    /**
     * @param CustomModule[] $customModules
     *
     * @return ResourceInterface[]
     */
    public static function getDefaultResources(array $customModules)
    {
        $resources = array();

        $fileResourceHelper = new FileResourceHelper();

        /** @var CustomModule $customModule */
        foreach ($customModules as $customModule) {
            $resources = array_merge($resources, $fileResourceHelper->getFileResources($customModule->getRootPath(), self::DEFAULT_FILE_RESOURCE_MASKS, array(
                '%machine_name%' => $customModule->getMachineName(),
            )));
        }

        return $resources;
    }
}
