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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DisplayPrettyExceptionsOptionsTest extends TestCase
{
    /**
     * @dataProvider getCharsetProvider
     */
    public function testGetCharset(?string $charset): void
    {
        $displayPrettyExceptionsOptions = new DisplayPrettyExceptionsOptions($charset, null, null);

        $this->assertSame($charset, $displayPrettyExceptionsOptions->getCharset());
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
        $displayPrettyExceptionsOptions = new DisplayPrettyExceptionsOptions(null, $fileLinkFormat, null);

        $this->assertSame($fileLinkFormat, $displayPrettyExceptionsOptions->getFileLinkFormat());
    }

    public function getFileLinkFormatProvider(): array
    {
        return array(
            array(null),
            array('myide://open?url=file://%%f&line=%%l'),
        );
    }

    /**
     * @dataProvider getLoggerProvider
     */
    public function testGetLogger(?LoggerInterface $logger): void
    {
        $displayPrettyExceptionsOptions = new DisplayPrettyExceptionsOptions(null, null, $logger);

        $this->assertSame($logger, $displayPrettyExceptionsOptions->getLogger());
    }

    public function getLoggerProvider(): array
    {
        return array(
            array(null),
            array($this->createMock(LoggerInterface::class)),
        );
    }
}
