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

namespace Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Action\ActionWithOptionsInterface;
use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ErrorHandler;

class ThrowErrorsAsExceptionsAction implements EventSubscriberActionInterface, ActionWithOptionsInterface
{
    /**
     * @var ThrowErrorsAsExceptionsOptions
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
     * @param ThrowErrorsAsExceptionsOptions $options
     */
    public function __construct(ThrowErrorsAsExceptionsOptions $options)
    {
        $this->options = $options;
    }

    public function process(): void
    {
        $errorHandler = ErrorHandler::register();

        $levels = $this->options->getLevels();
        $errorHandler->throwAt($levels, true);

        $logger = $this->options->getLogger();
        if ($logger instanceof LoggerInterface) {
            $errorHandler->setDefaultLogger($logger, $levels, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionsClass(): string
    {
        return ThrowErrorsAsExceptionsOptions::class;
    }
}
