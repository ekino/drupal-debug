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

namespace Ekino\Drupal\Debug\Tests\Integration;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\ProcessManager\WebServerManager;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var string
     */
    const DRUPAL_DIRECTORY_PATH = __DIR__.'/../../vendor/drupal';

    /**
     * @var string
     */
    const DRUPAL_FILES_DIRECTORY_PATH = self::DRUPAL_DIRECTORY_PATH.'/sites/default/files';

    /**
     * @var string
     */
    const REFERENCE_FILES_DIRECTORY_PATH = __DIR__.'/reference/files';

    /**
     * @var string
     */
    const CACHE_DIRECTORY_PATH = __DIR__.'/cache';

    /**
     * @var string
     */
    const CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var WebServerManager|null
     */
    private $webServerManager = null;

    /**
     * @var string[]
     */
    private static $modulesToInstall = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $filesystem = new Filesystem();

        $filesystem->remove(self::DRUPAL_FILES_DIRECTORY_PATH);
        $filesystem->mirror(self::REFERENCE_FILES_DIRECTORY_PATH, self::DRUPAL_FILES_DIRECTORY_PATH);

        $this->clearCache();

        if (!empty(self::$modulesToInstall)) {
            $this->installModules(self::$modulesToInstall);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        if ($this->webServerManager instanceof WebServerManager) {
            $this->webServerManager->quit();
        }

        $this->clearCache();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_stop_code_coverage(0);
        }

        $testCaseFilename = (new \ReflectionClass(static::class))->getFileName();
        if (!\is_string($testCaseFilename)) {
            self::markTestIncomplete('The test case filename could not be determined.');
        }

        $fixturesModulesDirectoryPath = \sprintf('%s/fixtures/modules', \dirname($testCaseFilename));
        $filesystem = new Filesystem();

        if ($filesystem->exists($fixturesModulesDirectoryPath)) {
            self::$modulesToInstall = \array_values(\array_map(function (SplFileInfo $splFileInfo) {
                return $splFileInfo->getBasename();
            }, \iterator_to_array(Finder::create()->directories()->depth(0)->in($fixturesModulesDirectoryPath))));

            $filesystem->symlink($fixturesModulesDirectoryPath, \sprintf('%s/modules', self::DRUPAL_DIRECTORY_PATH));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        }
    }

    public function testInitialBehaviorWithDrupalKernel(): void
    {
        $this->doTestInitialBehaviorWithDrupalKernel($this->getClient(9966));
    }

    public function testTargetedBehaviorWithDebugKernel(): void
    {
        $_ENV['DRUPAL_DEBUG_TESTS_FORCE_INITIALIZATION'] = '1';
        $_ENV[ConfigurationManager::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME] = self::CACHE_DIRECTORY_PATH;
        $_ENV[ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME] = self::CONFIGURATION_FILE_PATH;

        $this->doTestTargetedBehaviorWithDebugKernel($this->getClient(9967));
    }

    /**
     * @param string[] $names
     */
    private function installModules(array $names): void
    {
        $currentWorkingDirectory = \getcwd();
        if (!\is_string($currentWorkingDirectory)) {
            $this->fail('The current working directory could not be determined.');
        }

        \chdir(self::DRUPAL_DIRECTORY_PATH);

        $request = new Request();
        $classLoader = require 'autoload.php';

        $kernel = DrupalKernel::createFromRequest($request, $classLoader, 'test');
        $kernel->prepareLegacyRequest($request);
        /** @var ModuleInstallerInterface $moduleInstaller */
        $moduleInstaller = $kernel->getContainer()->get('module_installer');
        if (!$moduleInstaller instanceof ModuleInstallerInterface) {
            $this->fail('The module installer service is not the expected one.');
        }

        if (!$moduleInstaller->install($names)) {
            $this->fail(\sprintf('The module(s) "%s" could not be installed.', \implode(', ', $names)));
        }

        \chdir($currentWorkingDirectory);
    }

    private function clearCache(): void
    {
        (new Filesystem())->remove(Finder::create()->in(self::CACHE_DIRECTORY_PATH));
    }

    private function getClient(int $port): BrowserKitClient
    {
        if ($this->webServerManager instanceof WebServerManager) {
            $this->webServerManager->quit();
        }

        $this->webServerManager = new WebServerManager(self::DRUPAL_DIRECTORY_PATH, 'localhost', $port);
        $this->webServerManager->start();

        $goutteClient = new GoutteClient();
        $goutteClient->setClient(new GuzzleClient(array(
            'base_uri' => \sprintf('http://localhost:%s', $port),
        )));

        return $goutteClient;
    }

    abstract protected function doTestInitialBehaviorWithDrupalKernel(BrowserKitClient $client): void;

    abstract protected function doTestTargetedBehaviorWithDebugKernel(BrowserKitClient $client): void;
}
