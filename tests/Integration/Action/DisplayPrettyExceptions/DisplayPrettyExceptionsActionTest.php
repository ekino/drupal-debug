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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class DisplayPrettyExceptionsActionTest extends AbstractActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $client->request('GET', '/');

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertSame('The website encountered an unexpected error. Please try again later.', $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $text = $client->request('GET', '/')->text();

        $this->assertContains('Whoops, looks like something went wrong.', $text);
        $this->assertContains('This is an useless exception message.', $text);
        $this->assertThat($text, $this->logicalOr(
            $this->stringContains('in throw_uncaught_exception.module line 5', false),
            $this->stringContains('in throw_uncaught_exception.module (line 5)', false)
        ));
    }
}
