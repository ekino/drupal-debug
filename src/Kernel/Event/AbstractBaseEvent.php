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

namespace Ekino\Drupal\Debug\Kernel\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractBaseEvent extends Event
{
    private $configurationChanged;

    public function __construct(bool $configurationChanged)
    {
        $this->configurationChanged = $configurationChanged;
    }

    public function doesConfigurationChanged(): bool
    {
        return $this->configurationChanged;
    }
}
