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

namespace Ekino\Drupal\Debug\Tests\Unit\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ThrowErrorsAsExceptionsOptionsTest extends TestCase
{
    public function testGetLevels(): void
    {
        $throwErrorsAsExceptionsOptions = new ThrowErrorsAsExceptionsOptions(42, null);

        $this->assertSame(42, $throwErrorsAsExceptionsOptions->getLevels());
    }

    /**
     * @dataProvider getLoggerProvider
     */
    public function testGetLogger(?LoggerInterface $logger): void
    {
        $throwErrorsAsExceptionsOptions = new ThrowErrorsAsExceptionsOptions(42, $logger);

        $this->assertSame($logger, $throwErrorsAsExceptionsOptions->getLogger());
    }

    public function getLoggerProvider(): array
    {
        return array(
            array(null),
            array($this->createMock(LoggerInterface::class)),
        );
    }

    public function testGetDefault(): void
    {
        $defaultsConfiguration = $this->createMock(DefaultsConfiguration::class);
        $defaultsConfiguration
            ->expects($this->atLeastOnce())
            ->method('getLogger')
            ->willReturn(null);

        $this->assertEquals(new ThrowErrorsAsExceptionsOptions(E_ALL & ~E_WARNING & ~E_USER_WARNING, null), ThrowErrorsAsExceptionsOptions::getDefault('/foo', $defaultsConfiguration));
    }
}
