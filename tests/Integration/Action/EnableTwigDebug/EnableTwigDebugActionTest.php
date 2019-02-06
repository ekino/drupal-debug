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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\EnableTwigDebug;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class EnableTwigDebugActionTest extends AbstractActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->assertNotContains('<!-- THEME DEBUG -->', $this->executeScenario($client));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertContains('<!-- THEME DEBUG -->', $this->executeScenario($client));
    }

    private function executeScenario(Client $client): string
    {
        $client->request('GET', '/');

        /** @var Response $response */
        $response = $client->getResponse();

        return $response->getContent();
    }
}
