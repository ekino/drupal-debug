<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Psr\Log\LoggerInterface;

class DisplayPrettyExceptionsOptions implements OptionsInterface
{
    /**
     * @var string|null
     */
    private $charset;

    /**
     * @var string|null
     */
    private $fileLinkFormat;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param string|null          $charset
     * @param string|null          $fileLinkFormat
     * @param LoggerInterface|null $logger
     */
    public function __construct($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
        $this->logger = $logger;
    }

    /**
     * @return string|null
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return string|null
     */
    public function getFileLinkFormat()
    {
        return $this->fileLinkFormat;
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
     * @return DisplayPrettyExceptionsOptions
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration)
    {
        return new self($defaultsConfiguration->getCharset(), $defaultsConfiguration->getFileLinkFormat(), $defaultsConfiguration->getLogger());
    }
}
