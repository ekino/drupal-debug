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

namespace Ekino\Drupal\Debug\Action\DisableDynamicPageCache;

use Ekino\Drupal\Debug\Action\AbstractDisableDrupalCacheAction;

class DisableDynamicPageCacheAction extends AbstractDisableDrupalCacheAction
{
    /**
     * {@inheritdoc}
     */
    protected function getBin(): string
    {
        return 'dynamic_page_cache';
    }
}
