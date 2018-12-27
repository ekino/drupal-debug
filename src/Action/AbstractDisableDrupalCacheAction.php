<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Ekino\Drupal\Debug\Helper\SettingsHelper;
use Ekino\Drupal\Debug\Kernel\Event\AfterContainerInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;

abstract class AbstractDisableDrupalCacheAction implements EventSubscriberActionInterface
{
    /**
     * @var string
     */
    const NULL_BACKEND_FACTORY_SERVICE_ID = 'ekino.drupal.debug.action.abstract_disable_cache.null_backend_factory';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'overrideSettings',
            DebugKernelEvents::AFTER_CONTAINER_INITIALIZATION => 'setNullBackend',
        );
    }

    public function overrideSettings()
    {
        (new SettingsHelper())->override(\sprintf('[cache][bins][%s]', $this->getBin()), self::NULL_BACKEND_FACTORY_SERVICE_ID);
    }

    /**
     * @param AfterContainerInitializationEvent $event
     */
    public function setNullBackend(AfterContainerInitializationEvent $event)
    {
        $container = $event->getContainer();
        if ($container->has(self::NULL_BACKEND_FACTORY_SERVICE_ID)) {
            return;
        }

        $container->set(self::NULL_BACKEND_FACTORY_SERVICE_ID, new NullBackendFactory());
    }

    /**
     * @return string
     */
    abstract protected function getBin();
}
