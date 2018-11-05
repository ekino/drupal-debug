<?php

namespace Ekino\Drupal\Debug\Action\WatchContainerDefinitions;

use Ekino\Drupal\Debug\Action\AbstractFileBackendDependantAction;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Helper\SettingsHelper;

class WatchContainerDefinitionsAction extends AbstractFileBackendDependantAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    public function process()
    {
        (new SettingsHelper())->override('[bootstrap_container_definition]', array(
            'services' => array(
                'cache.container' => array(
                    'class' => FileBackend::class,
                    'arguments' => array(
                        new FileCache($this->cacheFilePath, $this->resources),
                    ),
                ),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultModuleFileResourceMasks()
    {
        return array(
            '%machine_name%.services.yml',
            '%camel_case_machine_name%ServiceProvider.php',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected static function getDefaultCacheFileName()
    {
        return 'container_definition.php';
    }
}
