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

use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;

final class TestEventSubscriberAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
    }
}
