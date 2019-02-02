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

namespace Ekino\Drupal\Debug\Tests\Integration\WatchRoutingDefinitions;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class WatchRoutingDefinitionsActionTest extends AbstractTestCase
{
    use FileHelperTrait;

    const ROUTING_TEMPLATE_FILE_PATH = __DIR__.'/fixtures/routing_template.yml';

    const MODULE_ROUTING_FILE_PATH = __DIR__.'/fixtures/modules/use_custom_route/use_custom_route.routing.yml';

    const ROUTE_PATH_1 = '/foo/bar';

    const ROUTE_PATH_2 = '/ccc/fcy';

    private static $routingTemplateFileContent = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteRoutingFile();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::deleteFile(self::MODULE_ROUTING_FILE_PATH);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$routingTemplateFileContent = self::getFileContent(self::ROUTING_TEMPLATE_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_1);
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_2);

        $this->writeRoutingFile(self::ROUTE_PATH_1);
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_1);

        $this->writeRoutingFile(self::ROUTE_PATH_2);
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_2);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_1);
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_2);

        $this->writeRoutingFile(self::ROUTE_PATH_1);
        $this->assertRoutePathFound($client, self::ROUTE_PATH_1);

        $this->writeRoutingFile(self::ROUTE_PATH_2);
        $this->assertRoutePathFound($client, self::ROUTE_PATH_2);

        $this->deleteRoutingFile();
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_2);
        $this->assertRoutePathNotFound($client, self::ROUTE_PATH_1);
    }

    private function assertRoutePathFound(Client $client, string $routePath): void
    {
        $this->assertSame(200, $this->callRoutePathAndGetResponseStatus($client, $routePath));
    }

    private function assertRoutePathNotFound(Client $client, string $routePath): void
    {
        $this->assertSame(404, $this->callRoutePathAndGetResponseStatus($client, $routePath));
    }

    private function callRoutePathAndGetResponseStatus(Client $client, string $routePath): int
    {
        $client->request('GET', $routePath);
        /** @var Response $response */
        $response = $client->getResponse();

        return $response->getStatus();
    }

    private function writeRoutingFile(string $routePath): void
    {
        $touch = \is_file(self::MODULE_ROUTING_FILE_PATH);

        self::writeFile(self::MODULE_ROUTING_FILE_PATH, \strtr(self::$routingTemplateFileContent, array(
            '%path%' => $routePath,
        )));

        if ($touch) {
            self::touchFile(self::MODULE_ROUTING_FILE_PATH, Carbon::now()->addSecond()->getTimestamp());
        }
    }

    private function deleteRoutingFile(): void
    {
        self::deleteFile(self::MODULE_ROUTING_FILE_PATH, true);
    }
}
