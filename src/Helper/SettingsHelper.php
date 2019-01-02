<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Helper;

use Drupal\Core\Site\Settings;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SettingsHelper
{
    /**
     * @param string $propertyPath
     * @param mixed  $value
     */
    public function override(string $propertyPath, $value): void
    {
        $settings = Settings::getInstance();

        $storage = &(function &() {
            return $this->storage;
        })->bindTo($settings, $settings)();

        PropertyAccess::createPropertyAccessor()->setValue($storage, $propertyPath, $value);
    }
}
