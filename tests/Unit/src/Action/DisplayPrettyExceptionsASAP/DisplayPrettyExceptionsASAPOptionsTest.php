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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions;
use PHPUnit\Framework\TestCase;

class DisplayPrettyExceptionsASAPOptionsTest extends TestCase
{
    /**
     * @dataProvider getCharsetProvider
     */
    public function testGetCharset(?string $charset): void
    {
        $displayPrettyExceptionsOptions = new DisplayPrettyExceptionsASAPOptions($charset, null);

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
        $displayPrettyExceptionsOptions = new DisplayPrettyExceptionsASAPOptions(null, $fileLinkFormat);

        $this->assertSame($fileLinkFormat, $displayPrettyExceptionsOptions->getFileLinkFormat());
    }

    public function getFileLinkFormatProvider(): array
    {
        return array(
            array(null),
            array('myide://open?url=file://%%f&line=%%l'),
        );
    }
}
