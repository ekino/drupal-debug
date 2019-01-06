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

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\ExceptionController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;

class ExceptionControllerTest extends TestCase
{
    public function testInvoke(): void
    {
        $exception = FlattenException::create(new \Exception(), 503);

        $exceptionHandler = $this->createMock(ExceptionHandler::class);
        $exceptionHandler
            ->expects($this->atLeastOnce())
            ->method('getHtml')
            ->with($exception)
            ->willReturn('foo');

        $this->assertEquals(new Response('foo', 503), (new ExceptionController($exceptionHandler))->__invoke($exception));
    }
}
