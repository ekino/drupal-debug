<?php

namespace Ekino\Drupal\Debug\Tests\Integration\DisplayDumpLocation;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;

class DisplayDumpLocationActionTest extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $this->assertSame("\"fcy\"\n", $this->getDumpText($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $this->assertSame("add_dump_die.module on line 4:\n\"fcy\"\n", $this->getDumpText($client));
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    private function getDumpText(Client $client)
    {
        return $client->request('GET', '/')->filter('pre[class="sf-dump"]')->text();
    }
}
