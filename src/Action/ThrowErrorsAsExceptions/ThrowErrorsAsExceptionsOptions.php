<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Psr\Log\LoggerInterface;

class ThrowErrorsAsExceptionsOptions implements OptionsInterface
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
     * @param int                  $levels
     * @param LoggerInterface|null $logger
     */
    public function __construct($levels, LoggerInterface $logger = null)
    {
        $this->levels = $levels;
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return ThrowErrorsAsExceptionsOptions
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration)
    {
        return new self(E_ALL, $defaultsConfiguration->getLogger());
    }
}
