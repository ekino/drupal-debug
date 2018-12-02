<?php

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;

class DisplayPrettyExceptionsASAPOptions implements OptionsInterface
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
     * @param null|string $charset
     * @param null|string $fileLinkFormat
     */
    public function __construct($charset, $fileLinkFormat)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
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
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return DisplayPrettyExceptionsASAPOptions
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration)
    {
        return new self($defaultsConfiguration->getCharset(), $defaultsConfiguration->getFileLinkFormat());
    }
}
