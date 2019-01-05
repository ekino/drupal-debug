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

namespace Ekino\Drupal\Debug\Tests\Unit\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Ekino\Drupal\Debug\Composer\Helper\ManageConfigurationHelper;
use Ekino\Drupal\Debug\Composer\Plugin\ManageConfigurationPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManageConfigurationPluginTest extends TestCase
{
    /**
     * @var MockObject|ManageConfigurationHelper
     */
    private $manageConfigurationHelper;

    /**
     * @var ManageConfigurationPlugin
     */
    private $manageConfigurationPlugin;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->manageConfigurationHelper = $this->createMock(ManageConfigurationHelper::class);

        $this->manageConfigurationPlugin = new ManageConfigurationPlugin();

        $refl = new \ReflectionProperty($this->manageConfigurationPlugin, 'manageConfigurationHelper');
        $refl->setAccessible(true);
        $refl->setValue($this->manageConfigurationPlugin, $this->manageConfigurationHelper);
    }

    public function testActivate(): void
    {
        $composer = $this->createMock(Composer::class);
        $io = $this->createMock(IOInterface::class);

        $manageConfigurationPlugin = new ManageConfigurationPlugin();
        $manageConfigurationPlugin->activate($composer, $io);

        $this->assertAttributeEquals(new ManageConfigurationHelper($composer, $io), 'manageConfigurationHelper', $manageConfigurationPlugin);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'post-package-update' => 'onPostPackageUpdate',
            'pre-package-uninstall' => 'onPrePackageUninstall',
        ), ManageConfigurationPlugin::getSubscribedEvents());
    }

    /**
     * @dataProvider packageEventsProvider
     */
    public function testOnPostPackageUpdate(bool $shouldProcess, PackageEvent $packageEvent): void
    {
        $this->manageConfigurationHelper
            ->expects($shouldProcess ? $this->atLeastOnce() : $this->never())
            ->method('warnAboutPotentialConfigurationChanges');

        $this->manageConfigurationPlugin->onPostPackageUpdate($packageEvent);
    }

    /**
     * @dataProvider packageEventsProvider
     */
    public function testOnPrePackageUninstall(bool $shouldProcess, PackageEvent $packageEvent): void
    {
        $this->manageConfigurationHelper
          ->expects($shouldProcess ? $this->atLeastOnce() : $this->never())
          ->method('askForConfigurationFileDeletion');

        $this->manageConfigurationPlugin->onPrePackageUninstall($packageEvent);
    }

    public function packageEventsProvider(): array
    {
        $shouldProcessPackage = $this->createMock(PackageInterface::class);
        $shouldProcessPackage
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('ekino/drupal-debug');

        $shouldNotProcessPackage = $this->createMock(PackageInterface::class);
        $shouldNotProcessPackage
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('unrelated/package');

        return array(
            array(false, $this->getPackageEvent(true, null)),
            array(false, $this->getPackageEvent(false, new InstallOperation($this->createMock(PackageInterface::class)))),
            array(false, $this->getPackageEvent(false, new UpdateOperation($this->createMock(PackageInterface::class), $shouldNotProcessPackage))),
            array(false, $this->getPackageEvent(false, new UninstallOperation($shouldNotProcessPackage))),
            array(true, $this->getPackageEvent(false, new UpdateOperation($this->createMock(PackageInterface::class), $shouldProcessPackage))),
            array(true, $this->getPackageEvent(false, new UninstallOperation($shouldProcessPackage))),
        );
    }

    private function getPackageEvent(bool $isPropagationStopped, ?OperationInterface $operation): PackageEvent
    {
        $packageEvent = $this->createMock(PackageEvent::class);
        $packageEvent
            ->expects($this->atLeastOnce())
            ->method('isPropagationStopped')
            ->willReturn($isPropagationStopped);

        if ($operation instanceof OperationInterface) {
            $packageEvent
                ->expects($this->atLeastOnce())
                ->method('getOperation')
                ->willReturn($operation);
        }

        return $packageEvent;
    }
}
