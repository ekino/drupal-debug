<?php

namespace Ekino\Drupal\Debug\Helper;

use Drupal\Core\Site\Settings;
use Ekino\Drupal\Debug\Action\EnhanceClassLoaderAction;
use Ekino\Drupal\Debug\Action\EnhanceContainerAction;
use Ekino\Drupal\Debug\Action\EnhanceDumpAction;
use Ekino\Drupal\Debug\Action\EnhanceExceptionPageAction;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class SettingsHelper
{
    /**
     * @param string $propertyPath
     * @param mixed $value
     */
    public function override($propertyPath, $value)
    {
        $settings = Settings::getInstance();

        $storage = &(function &() {
            return $this->storage;
        })->bindTo($settings, $settings)();

        (new PropertyAccessor())->setValue($storage, $propertyPath, $value);
    }
}
