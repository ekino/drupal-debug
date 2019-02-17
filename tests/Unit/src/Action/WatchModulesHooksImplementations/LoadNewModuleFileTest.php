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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\WatchModulesHooksImplementations;

use Drupal\Core\DrupalKernelInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandler;
use Ekino\Drupal\Debug\Action\WatchModulesHooksImplementations\LoadNewModuleFile;
use Ekino\Drupal\Debug\Cache\Event\CacheNotFreshEvent;
use Ekino\Drupal\Debug\Cache\FileCache;
use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class LoadNewModuleFileTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test(SelfCheckingResourceInterface $resource, ModuleHandler $moduleHandler, DrupalKernelInterface $drupalKernel): void
    {
        $fileCache = $this->createMock(FileCache::class);
        $fileCache
            ->expects($this->atLeastOnce())
            ->method('getCurrentResourcesCollection')
            ->willReturn(new ResourcesCollection(array($resource)));

        (new LoadNewModuleFile($moduleHandler, $drupalKernel))(new CacheNotFreshEvent($fileCache));
    }

    public function provider(): array
    {
        return array(
            array(new FileExistenceResource('/foo'), $this->getModuleHandler(), $this->getDrupalKernel()),
            array($this->getCustomExtensionFileResource(false), $this->getModuleHandler(), $this->getDrupalKernel()),
            array($this->getCustomExtensionFileResource(true, false), $this->getModuleHandler(), $this->getDrupalKernel()),
            array($this->getCustomExtensionFileResource(true, true, false), $this->getModuleHandler(), $this->getDrupalKernel()),
            array($this->getCustomExtensionFileResource(true, true, true), $this->getModuleHandler(true, false), $this->getDrupalKernel()),
            array($this->getCustomExtensionFileResource(true, true, true), $this->getModuleHandler(true, true), $this->getDrupalKernel(true)),
        );
    }

    private function getCustomExtensionFileResource(bool $isNew, ?bool $customExtensionIsAModule = null, ?bool $resourceFilePathIsThePointModule = null): MockObject
    {
        $customExtensionFileResource = $this->createMock(CustomExtensionFileResource::class);
        $customExtensionFileResource
            ->expects($this->atLeastOnce())
            ->method('isNew')
            ->willReturn($isNew);

        if (!$isNew) {
            return $customExtensionFileResource;
        }

        $customExtension = $customExtensionIsAModule ? new CustomModule('/foo', 'ccc') : $this->createMock(CustomTheme::class);
        $customExtensionFileResource
            ->expects($this->atLeastOnce())
            ->method('getCustomExtension')
            ->willReturn($customExtension);

        if (!$customExtensionIsAModule) {
            return $customExtensionFileResource;
        }

        $customExtensionFileResource
            ->expects($this->atLeastOnce())
            ->method('getFilePath')
            ->willReturn($resourceFilePathIsThePointModule ? 'ccc.module' : 'unrelated.php');

        return $customExtensionFileResource;
    }

    private function getModuleHandler(bool $shouldBeUsed = false, ?bool $extensionFileNameIsNull = null): MockObject
    {
        $extension = $this->createMock(Extension::class);
        $extension
            ->expects($shouldBeUsed ? $this->atLeastOnce() : $this->never())
            ->method('getExtensionFilename')
            ->willReturn($extensionFileNameIsNull ? null : 'foo');

        if ($extensionFileNameIsNull) {
            $extension
                ->expects($this->atLeastOnce())
                ->method('getName')
                ->willReturn('ccc');
            $extension
                ->expects($this->atLeastOnce())
                ->method('getPath')
                ->willReturn('bar');
            $extension
                ->expects($this->atLeastOnce())
                ->method('load');
        }

        $moduleHandler = $this->createMock(ModuleHandler::class);
        $moduleHandler
            ->expects($shouldBeUsed ? ($extensionFileNameIsNull ? $this->atLeast(2) : $this->atLeastOnce()) : $this->never())
            ->method('getModule')
            ->with('ccc')
            ->willReturn($extension);

        if ($extensionFileNameIsNull) {
            $moduleHandler
                ->expects($this->atLeastOnce())
                ->method('addModule')
                ->with('ccc', 'bar');
        }

        return $moduleHandler;
    }

    private function getDrupalKernel(bool $shouldBeUsed = false): MockObject
    {
        $drupalKernel = $this->createMock(DrupalKernelInterface::class);
        $drupalKernel
            ->expects($shouldBeUsed ? $this->atLeastOnce() : $this->never())
            ->method('invalidateContainer');

        return $drupalKernel;
    }
}
