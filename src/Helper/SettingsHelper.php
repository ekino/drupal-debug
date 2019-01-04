<?php

declare(strict_types=1);

/*
 * This file is part of the ekino Drupal Debug project.
 *
 * (c) ekino
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
