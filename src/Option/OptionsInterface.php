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

namespace Ekino\Drupal\Debug\Option;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;

interface OptionsInterface
{
    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return self
     */
    public static function getDefault(string $appRoot, DefaultsConfiguration $defaultsConfiguration): self;
}
