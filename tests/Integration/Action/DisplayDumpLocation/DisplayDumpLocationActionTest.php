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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\DisplayDumpLocation;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Symfony\Component\BrowserKit\Client;

class DisplayDumpLocationActionTest extends AbstractActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->assertSame("\"fcy\"\n", $this->getDumpText($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertThat($this->getDumpText($client), $this->logicalOr(
            $this->identicalTo("add_dump_die.module on line 5:\n\"fcy\"\n"),
            $this->identicalTo("\"fcy\"\n")
        ));
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    private function getDumpText(Client $client): string
    {
        return $client->request('GET', '/')->filterXPath('descendant-or-self::pre[@class="sf-dump"]')->text();
    }
}
