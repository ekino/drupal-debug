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

use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsAction;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ErrorHandler;

class ThrowErrorsAsExceptionsActionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_environment_boot' => 'process',
        ), ThrowErrorsAsExceptionsAction::getSubscribedEvents());
    }

    /**
     * @dataProvider processProvider
     */
    public function testProcess(int $levels, ?LoggerInterface $logger): void
    {
        $throwErrorsAsExceptionsAction = new ThrowErrorsAsExceptionsAction(new ThrowErrorsAsExceptionsOptions($levels, $logger));

        \set_error_handler(null);

        $throwErrorsAsExceptionsAction->process();

        $callableErrorHandler = \set_error_handler(null);
        $this->assertInternalType('array', $callableErrorHandler);

        $errorHandler = \reset($callableErrorHandler);
        $this->assertInstanceOf(ErrorHandler::class, $errorHandler);

        /* @var ErrorHandler $errorHandler */
        // cf the throwAt method in the ErrorHandler class
        $this->assertAttributeSame(($levels | E_RECOVERABLE_ERROR | E_USER_ERROR) & ~E_USER_DEPRECATED & ~E_DEPRECATED, 'thrownErrors', $errorHandler);

        if ($logger instanceof LoggerInterface) {
            $this->assertAttributeSame($levels, 'loggedErrors', $errorHandler);
        }
    }

    public function processProvider(): array
    {
        return array(
            array(E_USER_ERROR, null),
            array(E_CORE_ERROR, $this->createMock(LoggerInterface::class)),
        );
    }

    public function testGetOptionsClass(): void
    {
        $this->assertSame('Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions', ThrowErrorsAsExceptionsAction::getOptionsClass());
    }
}
