<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;

class DisplayPrettyExceptionsASAPOptions implements OptionsInterface
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
     * @param string|null $charset
     * @param string|null $fileLinkFormat
     */
    public function __construct($charset, $fileLinkFormat)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
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
