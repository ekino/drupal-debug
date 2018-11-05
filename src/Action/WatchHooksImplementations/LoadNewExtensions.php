<?php

namespace Ekino\Drupal\Debug\Action\WatchHooksImplementations;

use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Cache\Event\CacheNotFreshEvent;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\CustomExtensionFileResource;

// WIP - not working
class LoadNewExtensions
{
    /**
     * @var ModuleHandler
     */
    private $moduleHandler;

    /**
     * @param ModuleHandler $moduleHandler
     */
    public function __construct(ModuleHandler $moduleHandler)
    {
        $this->moduleHandler = $moduleHandler;
    }

    /**
     * @param CacheNotFreshEvent $event
     */
    public function __invoke(CacheNotFreshEvent $event)
    {
        // NOT WORKING
        return;
        foreach ($event->getFileCache()->getCurrentResources() as $resource) {
            if (!$resource instanceof CustomExtensionFileResource) {
                continue;
            }

            if (!$resource->isNew()) {
                continue;
            }

            $customExtension = $resource->getCustomExtension();
            switch (get_class($customExtension)) {
                case CustomModule::class:
                    dump($resource);
                    dump($this->moduleHandler->moduleExists($customExtension->getMachineName())); die();
                    /** @var CustomModule $customExtension */
                    //$this->moduleHandler->getModule($customExtension->getMachineName())->load();

                    break;
                case CustomTheme::class:
            }
        }
    }
}
