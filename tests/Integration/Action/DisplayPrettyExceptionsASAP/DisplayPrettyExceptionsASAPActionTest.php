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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class DisplayPrettyExceptionsASAPActionTest extends AbstractActionTestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const FRONT_CONTROLLER_FILE_PATH = __DIR__.'/fixtures/index.php';

    /**
     * @var string
     */
    protected static $router = 'index_drupal_debug_display_pretty_exceptions_asap.php';

    /**
     * @var string|null
     */
    private static $targetFrontControllerFilePath = null;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$targetFrontControllerFilePath = \sprintf('%s/%s', self::DRUPAL_DIRECTORY_PATH, self::$router);

        self::deleteFile(self::$targetFrontControllerFilePath);

        self::copyFile(self::FRONT_CONTROLLER_FILE_PATH, self::$targetFrontControllerFilePath);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (!\is_string(self::$targetFrontControllerFilePath)) {
            self::fail('The path should be a string');
        }

        self::deleteFile(self::$targetFrontControllerFilePath, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $client->request('GET', '/');

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertSame('The website encountered an unexpected error. Please try again later.<br />', $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Whoops, looks like something went wrong.', $text);
        $this->assertContains('My custom exception message is great!', $text);
        $this->assertThat($text, $this->logicalOr(
            $this->stringContains('in throw_uncaught_exception.module line 5', false),
            $this->stringContains('in throw_uncaught_exception.module (line 5)', false)
        ));
    }
}
