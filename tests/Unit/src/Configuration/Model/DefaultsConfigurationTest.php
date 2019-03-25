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

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use PHPUnit\Framework\TestCase;

class DefaultsConfigurationTest extends TestCase
{
    public function testGetCacheDirectoryPath(): void
    {
        $this->assertSame('/foo/cache', (new DefaultsConfiguration(array(
            'cache_directory_path' => '/foo/cache',
        )))->getCacheDirectoryPath());
    }

    public function testGetLogger(): void
    {
        $this->assertSame(array('foo'), (new DefaultsConfiguration(array(
            'logger' => array('foo'),
        )))->getLogger());
    }

    /**
     * @dataProvider getCharsetProvider
     */
    public function testGetCharset(?string $charset): void
    {
        $this->assertSame($charset, (new DefaultsConfiguration(array(
            'charset' => $charset,
        )))->getCharset());
    }

    public function getCharsetProvider(): array
    {
        return array(
            array(null),
            array('utf-8'),
        );
    }

    /**
     * @dataProvider getFileLinkFormatProvider
     */
    public function testGetFileLinkFormat(?string $fileLinkFormat): void
    {
        $this->assertSame($fileLinkFormat, (new DefaultsConfiguration(array(
            'file_link_format' => $fileLinkFormat,
        )))->getFileLinkFormat());
    }

    public function getFileLinkFormatProvider(): array
    {
        return array(
            array(null),
            array('myide://open?url=file://%%f&line=%%l'),
        );
    }

    public function testSerialize(): void
    {
        $this->assertSame('a:1:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}}', (new DefaultsConfiguration(array(
            'foo' => 'bar',
            'deep' => array(
                'is' => 'ok',
            ),
        )))->serialize());
    }

    public function testUnserialize(): void
    {
        $defaultsConfiguration = new DefaultsConfiguration(array());
        $defaultsConfiguration->unserialize('a:1:{i:0;a:2:{s:3:"ccc";s:3:"fcy";s:5:"array";a:1:{s:2:"is";b:0;}}}');

        $this->assertAttributeSame(array(
            'ccc' => 'fcy',
            'array' => array(
                'is' => false,
            ),
        ), 'processedConfiguration', $defaultsConfiguration);
    }
}
