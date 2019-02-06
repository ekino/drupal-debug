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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class ThrowErrorsAsExceptionsActionTest extends AbstractActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->assertSame(200, $this->executeScenario($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertSame(500, $this->executeScenario($client));
    }

    private function executeScenario(Client $client): int
    {
        $client->request('GET', '/');

        /** @var Response $response */
        $response = $client->getResponse();

        return $response->getStatus();
    }
}
