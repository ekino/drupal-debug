<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Integration\DisableCSSAggregation;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;

class DisableCSSAggregationActionTest extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $this->assertSame(1, $this->countStylesheetLinks($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $this->assertGreaterThan(10, $this->countStylesheetLinks($client));
    }

    /**
     * @return int
     */
    private function countStylesheetLinks(Client $client)
    {
        return \iterator_count($client->request('GET', '/')->filterXPath('descendant-or-self::link[@rel="stylesheet"]'));
    }
}
