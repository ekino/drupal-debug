<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\WatchContainerDefinitions;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Cache\FileBackend;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Helper\SettingsHelper;
use Ekino\Drupal\Debug\Kernel\Event\AfterSettingsInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;

class WatchContainerDefinitionsAction implements EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var WatchContainerDefinitionsOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'process',
        );
    }

    /**
     * @param WatchContainerDefinitionsOptions $options
     */
    public function __construct(WatchContainerDefinitionsOptions $options)
    {
        $this->options = $options;
    }

    public function process(AfterSettingsInitializationEvent $event)
    {
        (new SettingsHelper())->override('[bootstrap_container_definition]', array(
            'services' => array(
                'cache.container' => array(
                    'class' => FileBackend::class,
                    'arguments' => array(
                        new FileCache($this->options->getCacheFilePath(), $this->options->getFilteredResourcesCollection($event->getEnabledModules(), $event->getEnabledThemes())),
                    ),
                ),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass()
    {
        return WatchContainerDefinitionsOptions::class;
    }
}
