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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisableJSAggregation;

use Ekino\Drupal\Debug\Action\DisableJSAggregation\DisableJSAggregationAction;
use PHPUnit\Framework\TestCase;

class DisableJSAggregationActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_settings_initialization' => 'process',
        ), DisableJSAggregationAction::getSubscribedEvents());
    }
}
