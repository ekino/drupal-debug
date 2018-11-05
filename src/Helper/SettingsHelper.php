<?php

namespace Ekino\Drupal\Debug\Helper;

use Drupal\Core\Site\Settings;
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
