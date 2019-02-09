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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DefaultsConfigurationTest extends TestCase
{
    public function testGetCacheDirectory(): void
    {
        $this->assertSame('/foo/cache', (new DefaultsConfiguration(array(
            'cache_directory' => '/foo/cache',
        )))->getCacheDirectory());
    }

    /**
     * @dataProvider getLoggerProvider
     */
    public function testGetLogger(?Logger $expected, array $loggerProcessedConfiguration): void
    {
        $defaultsConfiguration = new DefaultsConfiguration(array(
            'logger' => $loggerProcessedConfiguration,
        ));

        $logger = $defaultsConfiguration->getLogger();

        $this->assertEquals($expected, $logger);
        $this->assertSame($logger, $defaultsConfiguration->getLogger());
    }

    public function getLoggerProvider(): array
    {
        return array(
            array(
                null,
                array(
                    'enabled' => false,
                ),
            ),
            array(
                new Logger('my-custom-channel', array(
                    new StreamHandler('/foo/bar/fcy.log'),
                )),
                array(
                    'enabled' => true,
                    'channel' => 'my-custom-channel',
                    'file_path' => '/foo/bar/fcy.log',
                ),
            ),
        );
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

    /**
     * @dataProvider serializeProvider
     */
    public function testSerialize(string $expected, $loggerValue): void
    {
        $defaultsConfiguration = new DefaultsConfiguration(array(
            'foo' => 'bar',
            'deep' => array(
                'is' => 'ok',
            ),
        ));

        $refl = new \ReflectionProperty($defaultsConfiguration, 'logger');
        $refl->setAccessible(true);
        $refl->setValue($defaultsConfiguration, $loggerValue);

        $this->assertSame($expected, $defaultsConfiguration->serialize());
    }

    public function serializeProvider(): array
    {
        return array(
            array('a:2:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}i:1;N;}', null),
            array('a:2:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}i:1;b:0;}', false),
            array('a:2:{i:0;a:2:{s:3:"foo";s:3:"bar";s:4:"deep";a:1:{s:2:"is";s:2:"ok";}}i:1;b:0;}', $this->createMock(Logger::class)),
        );
    }

    /**
     * @dataProvider unserializeProvider
     */
    public function testUnserialize(array $expectedProcessedConfiguration, $expectedLogger, string $serialized): void
    {
        $defaultsConfiguration = new DefaultsConfiguration(array());
        $defaultsConfiguration->unserialize($serialized);

        $this->assertAttributeSame($expectedProcessedConfiguration, 'processedConfiguration', $defaultsConfiguration);
        $this->assertAttributeSame($expectedLogger, 'logger', $defaultsConfiguration);
    }

    public function unserializeProvider(): array
    {
        return array(
            array(
                array(
                    'ccc' => 'fcy',
                    'array' => array(
                        'is' => false,
                    ),
                ),
                null,
                'a:2:{i:0;a:2:{s:3:"ccc";s:3:"fcy";s:5:"array";a:1:{s:2:"is";b:0;}}i:1;N;}',
            ),
            array(
                array(),
                false,
                'a:2:{i:0;a:0:{}i:1;b:0;}',
            ),
        );
    }
}
