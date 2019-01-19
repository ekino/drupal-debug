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

namespace Ekino\Drupal\Debug\Tests\Integration\DisableDynamicPageCache;

use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;

class DisableDynamicPageCacheActionTest extends AbstractTestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const CONTROLLER_TEMPLATE_FILE_PATH = __DIR__.'/fixtures/ControllerTemplate.php';

    /**
     * @var string
     */
    private const MODULE_CONTROLLER_FILE_PATH = __DIR__.'/fixtures/modules/hit_dynamic_page_cache/src/Controller/__FooController.php';

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

        $this->assertContains('Pristine, untraced by the world outside you', $results[0]);

        $this->assertContains('Pristine, untraced by the world outside you', $results[1]);
        $this->assertNotContains('Anyways, I will never get real', $results[1]);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $results = $this->executeScenario($client);

        $this->assertContains('Pristine, untraced by the world outside you', $results[0]);

        $this->assertContains('Anyways, I will never get real', $results[1]);
        $this->assertNotContains('Pristine, untraced by the world outside you', $results[1]);
    }

    private function executeScenario(Client $client): array
    {
        $results = array();

        foreach (array(
            'Pristine, untraced by the world outside you',
            'Anyways, I will never get real',
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
