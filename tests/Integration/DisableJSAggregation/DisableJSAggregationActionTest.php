<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Integration\DisableJSAggregation;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;

class DisableJSAggregationActionTest extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $this->assertSame(1, $this->countScripts($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $this->assertSame(2, $this->countScripts($client));
    }

    /**
     * @return int
     */
    private function countScripts(Client $client)
    {
        return \iterator_count($client->request('GET', '/')->filterXPath('descendant-or-self::script'));
    }
}
