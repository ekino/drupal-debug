<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractOverrideConfigAction implements EventSubscriberActionInterface
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
        global $config;

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->getOverrides() as $propertyPath => $value) {
            $propertyAccessor->setValue($config, $propertyPath, $value);
        }
    }

    /**
     * @return array
     */
    abstract protected function getOverrides();
}
