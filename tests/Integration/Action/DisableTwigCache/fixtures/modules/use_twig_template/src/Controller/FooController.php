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

namespace Drupal\use_twig_template\Controller;

use Drupal\Core\Controller\ControllerBase;

class FooController extends ControllerBase
{
    public function bar(): array
    {
        return array(
            '#theme' => '__partial',
        );
    }

    public function ccc(): array
    {
        return array(
            '#theme' => '__partial',
        );
    }
}
