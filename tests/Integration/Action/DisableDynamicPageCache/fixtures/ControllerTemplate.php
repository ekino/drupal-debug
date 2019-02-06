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

namespace Drupal\hit_dynamic_page_cache\Controller;

use Drupal\Core\Controller\ControllerBase;

class __FooController extends ControllerBase
{
    public function action(): array
    {
        return array(
            '#markup' => '%markup%',
        );
    }
}
