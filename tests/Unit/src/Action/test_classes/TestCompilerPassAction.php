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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\test_classes;

use Ekino\Drupal\Debug\Action\CompilerPassActionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TestCompilerPassAction implements CompilerPassActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
