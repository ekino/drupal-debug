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

namespace Ekino\Drupal\Debug\ActionMetadata\Model;

class ActionWithOptionsMetadata extends ActionMetadata
{
    private $optionsClass;

    public function __construct(\ReflectionClass $reflectionClass, string $shortName, string $optionsClass)
    {
        parent::__construct($reflectionClass, $shortName);

        $this->optionsClass = $optionsClass;
    }

    public function getOptionsClass(): string
    {
        return $this->optionsClass;
    }
}
