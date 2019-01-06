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

namespace Ekino\Drupal\Debug\Tests\Unit\Extension;

use Ekino\Drupal\Debug\Extension\CustomExtensionDiscovery;
use Ekino\Drupal\Debug\Extension\Model\AbstractCustomExtension;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use PHPUnit\Framework\TestCase;

class CustomExtensionDiscoveryTest extends TestCase
{
    /**
     * @var string
     */
    private const CUSTOM_EXTENSIONS_DIRECTORY_PATH = __DIR__.'/fixtures/custom_extensions';

    /**
     * @var CustomExtensionDiscovery
     */
    private $customExtensionDiscovery;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->customExtensionDiscovery = new CustomExtensionDiscovery(self::CUSTOM_EXTENSIONS_DIRECTORY_PATH);
    }

    public function testGetCustomModules(): void
    {
        $this->assertArrayContainsOnlyExpectedCustomExtensions(array(
            $this->getExpectedCustomModule('modules', 'module'),
            $this->getExpectedCustomModule('modules/sub', 'sub'),
            $this->getExpectedCustomModule('modules/sub/sub/sub/we/need/to_go_deeper', 'my_module'),
            $this->getExpectedCustomModule('sites/all/modules', 'foo'),
            $this->getExpectedCustomModule('sites/all/modules/sub', 'bar'),
            $this->getExpectedCustomModule('sites/all/modules/sub/sub/sub', 'fcy'),
        ), $this->customExtensionDiscovery->getCustomModules());
    }

    public function testGetCustomThemes(): void
    {
        $this->assertArrayContainsOnlyExpectedCustomExtensions(array(
            $this->getExpectedCustomTheme('themes', 'ccc'),
            $this->getExpectedCustomTheme('themes/sub', 'foo'),
            $this->getExpectedCustomTheme('themes/sub/sub/sub', 'fcy'),
        ), $this->customExtensionDiscovery->getCustomThemes());
    }

    private function getExpectedCustomModule(string $directoryPath, string $machineName): CustomModule
    {
        return new CustomModule(\sprintf('%s/fixtures/custom_extensions/%s', __DIR__, $directoryPath), $machineName);
    }

    private function getExpectedCustomTheme(string $directoryPath, string $machineName): CustomTheme
    {
        return new CustomTheme(\sprintf('%s/fixtures/custom_extensions/%s', __DIR__, $directoryPath), $machineName);
    }

    /**
     * @param AbstractCustomExtension[] $customExtensions
     * @param array                     $array
     */
    private function assertArrayContainsOnlyExpectedCustomExtensions(array $customExtensions, array $array): void
    {
        $this->assertCount(\count($customExtensions), $array);

        foreach ($customExtensions as $customExtension) {
            $this->assertContains($customExtension, $array, '', false, false);
        }
    }
}
