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

namespace Ekino\Drupal\Debug\Tests\Unit\Extension\Iterator;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator;
use Ekino\Drupal\Debug\Extension\Iterator\RecursiveCustomExtensionFilterIterator;
use PHPUnit\Framework\TestCase;

class RecursiveCustomExtensionFilterIteratorTest extends TestCase
{
    /**
     * @var RecursiveCustomExtensionFilterIterator
     */
    private $recursiveCustomExtensionFilterIterator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->recursiveCustomExtensionFilterIterator = new RecursiveCustomExtensionFilterIterator($this->createMock(\RecursiveDirectoryIterator::class));
    }

    public function testBlacklistContent(): void
    {
        $recursiveExtensionFilterIterator = new RecursiveExtensionFilterIterator($this->createMock(\RecursiveIterator::class));
        $refl = new \ReflectionProperty($recursiveExtensionFilterIterator, 'blacklist');
        $refl->setAccessible(true);

        $this->assertAttributeSame(\array_merge($refl->getValue($recursiveExtensionFilterIterator), array(
            'tests',
        )), 'blacklist', $this->recursiveCustomExtensionFilterIterator);
    }

    /**
     * @dataProvider acceptProvider
     */
    public function testAccept(bool $expected, ?string $filename = null, ?bool $isDir = null, ?string $pathname = null): void
    {
        $recursiveDirectoryIterator = $this->createMock(\RecursiveDirectoryIterator::class);
        $recursiveDirectoryIterator
            ->expects($this->atMost(2))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);

        $splFileInfo = null;
        if (\is_string($filename)) {
            $splFileInfo = $this->createMock(\SplFileInfo::class);
            $splFileInfo
                ->expects($this->atLeastOnce())
                ->method('getFilename')
                ->willReturn($filename);

            if (\is_bool($isDir)) {
                $recursiveDirectoryIterator
                    ->expects($this->atLeastOnce())
                    ->method('isDir')
                    ->willReturn($isDir);
            }

            if (\is_string($pathname)) {
                $splFileInfo
                    ->expects($this->atLeastOnce())
                    ->method('getPathname')
                    ->willReturn($pathname);
            }
        }

        $recursiveDirectoryIterator
            ->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($splFileInfo);

        $recursiveCustomExtensionFilterIterator = new RecursiveCustomExtensionFilterIterator($recursiveDirectoryIterator);
        $recursiveCustomExtensionFilterIterator->next();

        $this->assertSame($expected, $recursiveCustomExtensionFilterIterator->accept());
    }

    public function acceptProvider(): array
    {
        return array(
            array(false),
            array(false, '..'),
            array(false, '.'),
            array(true, 'module.info.yml', false),
            array(false, 'foo', false),
            array(true, 'module.info.yml', true),
            array(false, 'config', true, '/foo/bar/config'),
            array(true, 'config', true, '/foo/modules/config'),
            array(false, 'src', true),
        );
    }
}
