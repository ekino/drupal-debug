<?php

namespace Ekino\Drupal\Debug\Action;

use Drupal\Core\Cache\NullBackendFactory;
use Ekino\Drupal\Debug\Event\ContainerEvent;
use Ekino\Drupal\Debug\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Helper\SettingsHelper;

abstract class AbstractDisableDrupalCacheAction implements EventSubscriberActionInterface
{
    /**
     * @var string
     */
    const NULL_BACKEND_FACTORY_SERVICE_ID = 'ekino.drupal.debug.action.abstract_disable_cache.null_backend_factory';

    /**
     * @var NullBackendFactory|null
     */
    private $nullBackendFactory;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION => 'overrideSettings',
            DebugKernelEvents::AFTER_CONTAINER_INITIALIZATION => 'setNullBackend'
        );
    }

    public function overrideSettings()
    {
        (new SettingsHelper())->override(sprintf('[cache][bins][%s]', $this->getBin()), self::NULL_BACKEND_FACTORY_SERVICE_ID);
    }

    /**
     * @param ContainerEvent $event
     */
    public function setNullBackend(ContainerEvent $event)
    {
        if (!$this->nullBackendFactory instanceof NullBackendFactory) {
            $this->nullBackendFactory = new NullBackendFactory();
        }

        $event->getContainer()->set(self::NULL_BACKEND_FACTORY_SERVICE_ID, $this->nullBackendFactory);
    }

    /**
     * @return string
     */
    abstract protected function getBin();
}
