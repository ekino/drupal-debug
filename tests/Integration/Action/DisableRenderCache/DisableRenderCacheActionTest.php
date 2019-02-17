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

namespace Ekino\Drupal\Debug\Tests\Integration\Action\DisableRenderCache;

use Ekino\Drupal\Debug\Tests\Integration\Action\AbstractActionTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;

class DisableRenderCacheActionTest extends AbstractActionTestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const CONTROLLER_TEMPLATE_FILE_PATH = __DIR__.'/fixtures/ControllerTemplate.php';

    /**
     * @var string
     */
    private const MODULE_CONTROLLER_FILE_PATH = __DIR__.'/fixtures/modules/hit_render_cache/src/Controller/__FooController.php';

    /**
     * @var string|null
     */
    private static $controllerTemplateFileContent = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->writeControllerFile('');

        parent::setUp();

        $this->uninstallModules(array(
            'page_cache',
            'dynamic_page_cache',
        ));

        $this->deleteControllerFile(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteControllerFile(false);
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$controllerTemplateFileContent = self::getFileContent(self::CONTROLLER_TEMPLATE_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $results = $this->executeScenario($client);

        $this->assertContains('That is not what I meant to say at all', $results[0]);

        $this->assertContains('That is not what I meant to say at all', $results[1]);
        $this->assertNotContains('Is it the chorus yet?', $results[1]);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $results = $this->executeScenario($client);

        $this->assertContains('That is not what I meant to say at all', $results[0]);

        $this->assertContains('Is it the chorus yet?', $results[1]);
        $this->assertNotContains('That is not what I meant to say at all', $results[1]);
    }

    private function executeScenario(Client $client): array
    {
        $results = array();

        foreach (array(
            'That is not what I meant to say at all',
            'Is it the chorus yet?',
        ) as $markup) {
            $this->writeControllerFile($markup);
            $results[] = $client->request('GET', '/foo')->text();
        }

        return $results;
    }

    private function writeControllerFile(string $markup): void
    {
        if (!\is_string(self::$controllerTemplateFileContent)) {
            self::fail('The content should be a string.');
        }

        self::writeFile(self::MODULE_CONTROLLER_FILE_PATH, \strtr(self::$controllerTemplateFileContent, array(
            '%markup%' => $markup,
        )));
    }

    private function deleteControllerFile(bool $mandatory): void
    {
        self::deleteFile(self::MODULE_CONTROLLER_FILE_PATH, $mandatory);
    }
}
