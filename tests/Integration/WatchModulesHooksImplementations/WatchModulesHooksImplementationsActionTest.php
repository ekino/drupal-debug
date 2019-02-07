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

namespace Ekino\Drupal\Debug\Tests\Integration\WatchModulesHooksImplementations;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Tests\Integration\AbstractTestCase;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Symfony\Component\BrowserKit\Client;

class WatchModulesHooksImplementationsActionTest extends AbstractTestCase
{
    use FileHelperTrait;

    private const POINT_MODULE_FILE_PATH = __DIR__.'/fixtures/implement_hook.module';

    private const MODULE_HOOK_TO_ADD_FILE_PATH = __DIR__.'/fixtures/module_hook_to_add.php';

    private const MODULE_POINT_MODULE_FILE_PATH = __DIR__.'/fixtures/modules/implement_hook/implement_hook.module';

    /**
     * @var string|null
     */
    private static $moduleHookToAddFileContent = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deletePointModuleFile(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deletePointModuleFile(false);
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$moduleHookToAddFileContent = self::getFileContent(self::MODULE_HOOK_TO_ADD_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestInitialBehaviorWithDrupalKernel(Client $client): void
    {
        $client->request('GET', '/');

        $this->addPointModuleFile();
        $this->assertNotContains('Find a subtle way out', $client->request('GET', '/')->text());

        $this->addHookToPointModuleFile();
        $text = $client->request('GET', '/')->text();
        $this->assertNotContains('Find a subtle way out', $text);
        $this->assertNotContains("I've been trying to stay out", $text);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTestTargetedBehaviorWithDebugKernel(Client $client): void
    {
        $client->request('GET', '/');

        $this->addPointModuleFile();
        $this->assertContains('Find a subtle way out', $client->request('GET', '/')->text());

        $this->addHookToPointModuleFile();
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Find a subtle way out', $text);
        $this->assertContains("I've been trying to stay out", $text);

        $this->addPointModuleFile();
        $text = $client->request('GET', '/')->text();
        $this->assertContains('Find a subtle way out', $text);
        $this->assertNotContains("I've been trying to stay out", $text);

        $this->deletePointModuleFile(true);
        $text = $client->request('GET', '/')->text();
        $this->assertNotContains('Find a subtle way out', $text);
        $this->assertNotContains("I've been trying to stay out", $text);
    }

    private function addPointModuleFile(): void
    {
        $touch = \is_file(self::POINT_MODULE_FILE_PATH);

        self::copyFile(self::POINT_MODULE_FILE_PATH, self::MODULE_POINT_MODULE_FILE_PATH);

        if ($touch) {
            self::touchFile(self::MODULE_POINT_MODULE_FILE_PATH, Carbon::now()->addSecond()->getTimestamp());
        }
    }

    private function addHookToPointModuleFile(): void
    {
        if (!\is_string(self::$moduleHookToAddFileContent)) {
            self::fail('The content should be a string.');
        }

        self::writeFile(self::MODULE_POINT_MODULE_FILE_PATH, self::$moduleHookToAddFileContent, true);

        self::touchFile(self::MODULE_POINT_MODULE_FILE_PATH, Carbon::now()->addSecond()->getTimestamp());
    }

    private function deletePointModuleFile(bool $mandatory): void
    {
        self::deleteFile(self::MODULE_POINT_MODULE_FILE_PATH, $mandatory);
    }
}
