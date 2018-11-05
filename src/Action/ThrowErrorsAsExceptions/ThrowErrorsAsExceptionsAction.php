<?php

namespace Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Logger\DefaultLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\ErrorHandler;

class ThrowErrorsAsExceptionsAction implements EventSubscriberActionInterface
{
    /**
     * @var int
     */
    private $levels;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::AFTER_ENVIRONMENT_BOOT => 'process'
        );
    }

    /**
     * @param int $levels
     * @param LoggerInterface|null $logger
     */
    public function __construct($levels, LoggerInterface $logger = null)
    {
        $this->levels = $levels;
        $this->logger = $logger;
    }

    public function process()
    {
        $errorHandler = ErrorHandler::register();
        $errorHandler->throwAt($this->levels, true);

        if ($this->logger instanceof LoggerInterface) {
            $errorHandler->setDefaultLogger($this->logger, $this->levels, true);
        }
    }

    /**
     * @param string $appRoot
     *
     * @return ThrowErrorsAsExceptionsAction
     *
     * @throws \Exception
     */
    public static function getDefaultAction($appRoot)
    {
        return new self(E_ALL, DefaultLogger::get($appRoot));
    }
}
