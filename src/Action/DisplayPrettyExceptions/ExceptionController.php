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

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptions;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;

class ExceptionController
{
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @param ExceptionHandler $exceptionHandler
     */
    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @param FlattenException $exception
     *
     * @return Response
     */
    public function __invoke(FlattenException $exception): Response
    {
        return new Response($this->exceptionHandler->getHtml($exception), $exception->getStatusCode());
    }
}
