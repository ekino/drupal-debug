<?php

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptions;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Psr\Log\LoggerInterface;

class DisplayPrettyExceptionsOptions implements OptionsInterface
{
    /**
     * @var null|string
     */
    private $charset;

    /**
     * @var null|string
     */
    private $fileLinkFormat;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @param null|string          $charset
     * @param null|string          $fileLinkFormat
     * @param null|LoggerInterface $logger
     */
    public function __construct($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
        $this->logger = $logger;
    }

    /**
     * @return null|string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return null|string
     */
    public function getFileLinkFormat()
    {
        return $this->fileLinkFormat;
    }

    /**
     * @return null|LoggerInterface
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
