<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Integration\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class DisplayPrettyExceptionsActionTest extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $client->request('GET', '/');

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertSame('The website encountered an unexpected error. Please try again later.', $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $text = $client->request('GET', '/')->text();

        $this->assertContains('Whoops, looks like something went wrong.', $text);
        $this->assertContains('This is an useless exception message.', $text);
        $this->assertThat($text, $this->logicalOr(
            $this->stringContains('in throw_uncaught_exception.module line 4', false),
            $this->stringContains('in throw_uncaught_exception.module (line 4)', false)
        ));
    }
}
