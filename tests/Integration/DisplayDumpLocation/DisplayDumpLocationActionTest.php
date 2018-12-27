<?php

declare(strict_types=1);

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
        $this->assertThat($this->getDumpText($client), $this->logicalOr(
            $this->identicalTo("add_dump_die.module on line 4:\n\"fcy\"\n"),
            $this->identicalTo("\"fcy\"\n")
        ));
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    private function getDumpText(Client $client)
    {
        return $client->request('GET', '/')->filterXPath("descendant-or-self::pre[@class = 'sf-dump']")->text();
    }
}
