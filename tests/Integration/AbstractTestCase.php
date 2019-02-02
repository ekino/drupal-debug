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
use Drupal\Core\Extension\ThemeInstallerInterface;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
    public const DRUPAL_DIRECTORY_PATH = __DIR__.'/../../vendor/drupal';

    /**
     * @var string
     */
    public const DRUPAL_FILES_DIRECTORY_PATH = self::DRUPAL_DIRECTORY_PATH.'/sites/default/files';

    /**
     * @var string
     */
    public const REFERENCE_FILES_DIRECTORY_PATH = __DIR__.'/reference/files';

    /**
     * @var string
     */
    private const CACHE_DIRECTORY_PATH = __DIR__.'/cache';

    /**
     * @var string
     */
    private const CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var string
     */
    protected static $router = 'index.php';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var WebServerManager|null
     */
    private $webServerManager = null;

    /**
     * @var mixed[]
     */
    private static $extensionsToInstall = array(
        'modules' => array(),
        'themes' => array(),
    );

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $filesystem = new Filesystem();

        $filesystem->remove(self::DRUPAL_FILES_DIRECTORY_PATH);
        $filesystem->mirror(self::REFERENCE_FILES_DIRECTORY_PATH, self::DRUPAL_FILES_DIRECTORY_PATH);

        $this->clearCache();

        foreach (self::$extensionsToInstall as $type => $names) {
            if (!empty($names)) {
                $this->installExtensions($type, $names);
            }
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
        $testCaseFilename = (new \ReflectionClass(static::class))->getFileName();
        if (!\is_string($testCaseFilename)) {
            self::markTestIncomplete('The test case filename could not be determined.');
        }

        $filesystem = new Filesystem();

        foreach (\array_keys(self::$extensionsToInstall) as $extensionType) {
            $fixturesExtensionsDirectoryPath = \sprintf('%s/fixtures/%s', \dirname($testCaseFilename), $extensionType);

            if (!$filesystem->exists($fixturesExtensionsDirectoryPath)) {
                continue;
            }

            self::$extensionsToInstall[$extensionType] = \array_values(\array_map(function (SplFileInfo $splFileInfo) {
                return $splFileInfo->getBasename();
            }, \iterator_to_array(Finder::create()->directories()->depth(0)->in($fixturesExtensionsDirectoryPath))));

            $filesystem->symlink($fixturesExtensionsDirectoryPath, \sprintf('%s/%s', self::DRUPAL_DIRECTORY_PATH, $extensionType));
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
    protected function uninstallModules(array $names): void
    {
        $this->useExtensionInstaller('modules', 'uninstall', $names);
    }

    private function installExtensions(string $type, array $names): void
    {
        $this->useExtensionInstaller($type, 'install', $names);
    }

    private function useExtensionInstaller(string $type, string $action, array $names): void
    {
        $this->stopCodeCoverage();

        $currentWorkingDirectory = \getcwd();
        if (!\is_string($currentWorkingDirectory)) {
            self::fail('The current working directory could not be determined.');
        }

        \chdir(self::DRUPAL_DIRECTORY_PATH);

        $request = new Request();
        $classLoader = require 'autoload.php';

        $kernel = DrupalKernel::createFromRequest($request, $classLoader, 'test');
        $kernel->prepareLegacyRequest($request);

        switch ($type) {
            case 'modules':
                $this->useModuleInstaller($kernel->getContainer(), $action, $names);

                break;

            case 'themes':
                $this->useThemeInstaller($kernel->getContainer(), $action, $names);

                break;

            default:
                self::fail(\sprintf('The type "%s" is not supported.', $type));
        }

        \chdir($currentWorkingDirectory);

        $this->startCodeCoverage();
    }

    private function useModuleInstaller(ContainerInterface $container, string $action, array $names): void
    {
        if (!\in_array($action, array(
            'install',
            'uninstall',
        ))) {
            self::fail('The action "%s" is not supported.');
        }

        $moduleInstaller = $container->get('module_installer');
        if (!$moduleInstaller instanceof ModuleInstallerInterface) {
            self::fail('The module installer service is not the expected one.');
        }

        if (!$moduleInstaller->{$action}($names)) {
            self::fail(\sprintf('The module(s) "%s" could not be %sed.', \implode(', ', $names), $action));
        }
    }

    private function useThemeInstaller(ContainerInterface $container, string $action, array $names): void
    {
        if ('install' !== $action) {
            self::fail('The action "%s" is not supported.');
        }

        $themeInstaller = $container->get('theme_installer');
        if (!$themeInstaller instanceof ThemeInstallerInterface) {
            self::fail('The theme installer service is not the expected one.');
        }

        if (!$themeInstaller->install($names)) {
            self::fail(\sprintf('The theme(s) "%s" could not be installed.', \implode(', ', $names)));
        }
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

        $this->webServerManager = new WebServerManager(self::DRUPAL_DIRECTORY_PATH, 'localhost', $port, static::$router);
        $this->webServerManager->start();

        $goutteClient = new GoutteClient();
        $goutteClient->setClient(new GuzzleClient(array(
            'base_uri' => \sprintf('http://localhost:%s', $port),
        )));

        return $goutteClient;
    }

    private function stopCodeCoverage(): void
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_stop_code_coverage(0);
        }
    }

    private function startCodeCoverage(): void
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        }
    }

    abstract protected function doTestInitialBehaviorWithDrupalKernel(BrowserKitClient $client): void;

    abstract protected function doTestTargetedBehaviorWithDebugKernel(BrowserKitClient $client): void;
}
