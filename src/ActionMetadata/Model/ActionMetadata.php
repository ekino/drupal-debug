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

class ActionMetadata
{
    private $reflectionClass;

    private $shortName;

    public function __construct(\ReflectionClass $reflectionClass, string $shortName)
    {
        $this->reflectionClass = $reflectionClass;
        $this->shortName = $shortName;
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }
}
