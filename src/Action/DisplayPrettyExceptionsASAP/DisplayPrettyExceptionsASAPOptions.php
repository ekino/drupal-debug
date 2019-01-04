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
    public function __construct(?string $charset, ?string $fileLinkFormat)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
    }

    /**
     * @return string|null
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * @return string|null
     */
    public function getFileLinkFormat(): ?string
    {
        return $this->fileLinkFormat;
    }

    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return DisplayPrettyExceptionsASAPOptions
     */
    public static function getDefault(string $appRoot, DefaultsConfiguration $defaultsConfiguration): OptionsInterface
    {
        return new self($defaultsConfiguration->getCharset(), $defaultsConfiguration->getFileLinkFormat());
    }
}
