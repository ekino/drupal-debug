<?php

namespace Ekino\Drupal\Debug\Tests\Integration\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Symfony\Component\BrowserKit\Client;

class DisplayPrettyExceptionsActionTest extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client)
    {
        $client->request('GET', '/');

        $this->assertSame('The website encountered an unexpected error. Please try again later.', $client->getResponse()->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client)
    {
        $text = $client->request('GET', '/')->text();

        $this->assertContains('Whoops, looks like something went wrong.', $text);
        $this->assertContains('This is an useless exception message.', $text);
        $this->assertContains('in throw_uncaught_exception.module line 4', $text);
    }
}
