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

namespace Ekino\Drupal\Debug\Tests\Unit\Configuration\Model;

use Composer\Autoload\ClassLoader;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Configuration\Model\SubstituteOriginalDrupalKernelConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class SubstituteOriginalDrupalKernelConfigurationTest extends TestCase
{
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
    private const VALID_AUTOLOAD_FILE_PATH = __DIR__.'/fixtures/valid_autoload.php';

    /**
     * @var string
     */
    private const INVALID_AUTOLOAD_FILE_PATH = __DIR__.'/fixtures/invalid_autoload.php';

    /**
     * @dataProvider isEnabledProvider
     */
    public function testIsEnabled(bool $expected, bool $enabled): void
    {
        $this->assertSame($expected, (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => $enabled,
        )))->isEnabled());
    }

    public function isEnabledProvider(): array
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }

    public function testGetClassLoaderWhenTheSubstitutionIsNotEnabled(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class loader getter should not be called if the original DrupalKernel substitution is disabled.');

        (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => false,
        )))->getClassLoader();
    }

    public function testGetClassLoaderWhenTheComposerAutoloadFileDoesNotReturnAValidComposerClassLoader(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The composer autoload.php file did not return a "Composer\Autoload\ClassLoader" instance.');

        (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => true,
            'composer_autoload_file_path' => self::INVALID_AUTOLOAD_FILE_PATH,
        )))->getClassLoader();
    }

    public function testGetClassLoader(): void
    {
        $substituteOriginalDrupalKernelConfiguration = new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => true,
            'composer_autoload_file_path' => self::VALID_AUTOLOAD_FILE_PATH,
        ));
        $classLoader = $substituteOriginalDrupalKernelConfiguration->getClassLoader();

        $this->assertEquals(new ClassLoader(), $classLoader);

        $this->assertSame($classLoader, $substituteOriginalDrupalKernelConfiguration->getClassLoader());
    }

    public function testGetCacheDirectoryWhenTheSubstitutionIsNotEnabled(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The cache directory getter should not be called if the original DrupalKernel substitution is disabled.');

        (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => false,
        )))->getCacheDirectory();
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetCacheDirectoryWhenItFallBacksOnItsDefaultValue(): void
    {
        $this->clearCache();

        \putenv(\sprintf('%s=%s', ConfigurationManager::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME, self::CACHE_DIRECTORY_PATH));
        \putenv(\sprintf('%s=%s', ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME, self::CONFIGURATION_FILE_PATH));

        ConfigurationManager::initialize();

        $this->assertSame(\sprintf('%s/fixtures/cache', __DIR__), (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => true,
        )))->getCacheDirectory());

        $this->clearCache();
    }

    public function testGetCacheDirectory(): void
    {
        $this->assertSame('fcy', (new SubstituteOriginalDrupalKernelConfiguration(array(
            'enabled' => true,
            'cache_directory' => 'fcy',
        )))->getCacheDirectory());
    }

    public function testSerialize(): void
    {
        $this->assertSame('a:2:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}i:1;N;}', (new SubstituteOriginalDrupalKernelConfiguration(array(
            'foo' => 'bar',
            'deep' => array(
                'is' => 'ok',
            ),
        )))->serialize());
    }

    public function testUnserialize(): void
    {
        $substituteOriginalDrupalKernelConfiguration = new SubstituteOriginalDrupalKernelConfiguration(array(
            'ccc' => 'fcy',
            'unrelated' => array(
                'does not' => 'matter',
            ),
        ));
        $substituteOriginalDrupalKernelConfiguration->unserialize('a:2:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}i:1;N;}');

        $this->assertAttributeSame(array(
            'foo' => 'bar',
            'deep' => array(
                'is' => 'ok',
            ),
        ), 'processedConfiguration', $substituteOriginalDrupalKernelConfiguration);
        $this->assertAttributeSame(null, 'classLoader', $substituteOriginalDrupalKernelConfiguration);
    }

    private function clearCache(): void
    {
        (new Filesystem())->remove(Finder::create()->in(self::CACHE_DIRECTORY_PATH));
    }
}
