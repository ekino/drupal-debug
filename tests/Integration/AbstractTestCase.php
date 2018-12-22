<?php

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
    const DEFAULT_CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var null|WebServerManager
     */
    private $webServerManager = null;

    /**
     * @var string[]
     */
    private static $modulesToInstall = array();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $filesystem = new Filesystem();

        $filesystem->remove(self::DRUPAL_FILES_DIRECTORY_PATH);
        $filesystem->mirror(self::REFERENCE_FILES_DIRECTORY_PATH, self::DRUPAL_FILES_DIRECTORY_PATH);

        $this->clearCache();

        foreach (self::$modulesToInstall as $moduleToInstall) {
            $this->installModule($moduleToInstall);
        }
    }

    protected function tearDown()
    {
        if ($this->webServerManager instanceof WebServerManager) {
            $this->webServerManager->quit();
        }

        $this->clearCache();
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        $fixturesModulesDirectoryPath = sprintf('%s/fixtures/modules', \dirname((new \ReflectionClass(static::class))->getFileName()));
        $filesystem = new Filesystem();

        if ($filesystem->exists($fixturesModulesDirectoryPath)) {
            self::$modulesToInstall = array_map(function (SplFileInfo $splFileInfo) {
                return $splFileInfo->getBasename();
            }, iterator_to_array(Finder::create()->directories()->depth(0)->in($fixturesModulesDirectoryPath)));

            $filesystem->symlink($fixturesModulesDirectoryPath, sprintf('%s/modules', self::DRUPAL_DIRECTORY_PATH));
        }
    }

    public function testInitialBehaviorWithDrupalKernel()
    {
        $this->webServerManager = new WebServerManager(self::DRUPAL_DIRECTORY_PATH, 'localhost', 9966);
        $this->webServerManager->start();

        $goutteClient = new GoutteClient();
        $goutteClient->setClient(new GuzzleClient(array(
            'base_uri' => 'http://localhost:9966',
        )));

        $this->doTestInitialBehaviorWithDrupalKernel($goutteClient);
    }

    public function testTargetedBehaviorWithDebugKernel()
    {
        $_ENV['DRUPAL_DEBUG_TESTS_FORCE_INITIALIZATION'] = '1';
        $_ENV[ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME] = self::DEFAULT_CONFIGURATION_FILE_PATH;
        $_ENV[ConfigurationManager::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME] = self::CACHE_DIRECTORY_PATH;

        $this->webServerManager = new WebServerManager(self::DRUPAL_DIRECTORY_PATH, 'localhost', 9967);
        $this->webServerManager->start();

        $goutteClient = new GoutteClient();
        $goutteClient->setClient(new GuzzleClient(array(
            'base_uri' => 'http://localhost:9967',
        )));

        $this->doTestTargetedBehaviorWithDebugKernel($goutteClient);
    }

    private function installModule($name)
    {
        $currentWorkingDirectory = getcwd();

        chdir(self::DRUPAL_DIRECTORY_PATH);

        $request = new Request();
        $classLoader = require 'autoload.php';

        $kernel = DrupalKernel::createFromRequest($request, $classLoader, 'test');
        $kernel->prepareLegacyRequest($request);
        $moduleInstaller = $kernel->getContainer()->get('module_installer');
        if (!$moduleInstaller instanceof ModuleInstallerInterface) {
            $this->markTestIncomplete('The module installer service is not the expected one.');
        }

        if (!$moduleInstaller->install(array($name))) {
            $this->markTestIncomplete(sprintf('The module "%s" could not be installed.', $name));
        }

        //$kernel->invalidateContainer();

        chdir($currentWorkingDirectory);
    }

    private function clearCache()
    {
        (new Filesystem())->remove(Finder::create()->in(self::CACHE_DIRECTORY_PATH));
    }

    abstract protected function doTestInitialBehaviorWithDrupalKernel(BrowserKitClient $client);

    abstract protected function doTestTargetedBehaviorWithDebugKernel(BrowserKitClient $client);
}
