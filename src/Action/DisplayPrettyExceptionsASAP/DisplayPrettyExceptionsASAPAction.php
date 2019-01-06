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

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\ExceptionHandler;

class DisplayPrettyExceptionsASAPAction implements EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var DisplayPrettyExceptionsASAPOptions
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            DebugKernelEvents::AFTER_ENVIRONMENT_BOOT => 'process',
        );
    }

    /**
     * @param DisplayPrettyExceptionsASAPOptions $options
     */
    public function __construct(DisplayPrettyExceptionsASAPOptions $options)
    {
        $this->options = $options;
    }

    public function process(): void
    {
        // TODO: https://github.com/symfony/symfony/pull/28954
        \set_exception_handler(function (\Throwable $exception): void {
            if (!$exception instanceof \Exception) {
                $exception = new FatalThrowableError($exception);
            }

            $exceptionHandler = new ExceptionHandler(true, $this->options->getCharset(), $this->options->getFileLinkFormat());
            $exceptionHandler->sendPhpResponse($exception);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return DisplayPrettyExceptionsASAPOptions::class;
    }
}
