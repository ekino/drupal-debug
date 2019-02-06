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

use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var string
     */
    protected const VENDOR_DIRECTORY_PATH = __DIR__.'/../../vendor';

    /**
     * @var string
     */
    public const DRUPAL_DIRECTORY_PATH = self::VENDOR_DIRECTORY_PATH.'/drupal';

    /**
     * @var string
     */
    private const CACHE_DIRECTORY_PATH = __DIR__.'/cache';

    /**
     * @var string
     */
    private const CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->clearCache();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->clearCache();
    }

    protected function useDebugKernel(): void
    {
        $_ENV['DRUPAL_DEBUG_TESTS_FORCE_INITIALIZATION'] = '1';
        $_ENV[ConfigurationManager::CONFIGURATION_CACHE_DIRECTORY_ENVIRONMENT_VARIABLE_NAME] = self::CACHE_DIRECTORY_PATH;
        $_ENV[ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME] = self::CONFIGURATION_FILE_PATH;
    }

    protected function clearCache(): void
    {
        (new Filesystem())->remove(Finder::create()->in(self::CACHE_DIRECTORY_PATH));
    }
}
