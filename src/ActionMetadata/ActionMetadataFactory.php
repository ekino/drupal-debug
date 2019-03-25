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

namespace Ekino\Drupal\Debug\ActionMetadata;

use Ekino\Drupal\Debug\Action\ActionInterface;
use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionMetadata;
use Ekino\Drupal\Debug\ActionMetadata\Model\ActionWithOptionsMetadata;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ActionMetadataFactory
{
    public function create(string $class): ActionMetadata
    {
        $refl = new \ReflectionClass($class);

        if (!$refl->implementsInterface(ActionInterface::class)) {
            throw new InvalidConfigurationException(\sprintf('The "%s" class should implement the "%s" interface.', $class, ActionInterface::class));
        }

        return $refl->implementsInterface(ActionWithOptionsInterface::class) ?
            new ActionWithOptionsMetadata($refl, $class, $refl->getMethod('getOptionsClass')->invoke(null)) :
            new ActionMetadata($refl, $class);
    }
}
