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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\DisableCSSAggregation;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Symfony\Component\BrowserKit\Client;

class DisableCSSAggregationActionTest extends AbstractActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->assertSame(1, $this->countStylesheetLinks($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertGreaterThan(10, $this->countStylesheetLinks($client));
    }

    /**
     * @return int
     */
    private function countStylesheetLinks(Client $client): int
    {
        return \iterator_count($client->request('GET', '/')->filterXPath('descendant-or-self::link[@rel="stylesheet"]'));
    }
}
