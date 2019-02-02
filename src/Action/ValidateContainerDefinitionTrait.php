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

namespace Ekino\Drupal\Debug\Action;

use Ekino\Drupal\Debug\Exception\NotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

trait ValidateContainerDefinitionTrait
{
    private function validateContainerDefinitionExists(ContainerBuilder $containerBuilder, string $name): Definition
    {
        if (!$containerBuilder->hasDefinition($name)) {
            throw new NotSupportedException(\sprintf('The "%s" service should already be set in the container.', $name));
        }

        return $containerBuilder->getDefinition($name);
    }

    private function validateContainerDefinitionClassIs(ContainerBuilder $containerBuilder, string $name, string $expectedClass): Definition
    {
        $definition = $this->validateContainerDefinitionExists($containerBuilder, $name);
        if ($expectedClass !== $definition->getClass()) {
            throw new NotSupportedException(\sprintf('The "%s" service class should be "%s".', $name, $expectedClass));
        }

        return $definition;
    }

    private function validateContainerDefinitionClassImplements(ContainerBuilder $containerBuilder, string $name, string $expectedClass): Definition
    {
        $definition = $this->validateContainerDefinitionExists($containerBuilder, $name);
        $definitionClass = $definition->getClass();
        if (!\is_string($definitionClass)) {
            throw new NotSupportedException(\sprintf('The "%s" service class should be a string.', $name));
        }

        if (!(new \ReflectionClass($definitionClass))->implementsInterface($expectedClass)) {
            throw new NotSupportedException(\sprintf('The "%s" service class should implement the "%s" interface.', $name, $expectedClass));
        }

        return $definition;
    }
}
