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
    public function __construct(?string $charset, ?string $fileLinkFormat, ?LoggerInterface $logger)
    {
        $this->charset = $charset;
        $this->fileLinkFormat = $fileLinkFormat;
        $this->logger = $logger;
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
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string                $appRoot
     * @param DefaultsConfiguration $defaultsConfiguration
     *
     * @return DisplayPrettyExceptionsOptions
     */
    public static function getDefault(string $appRoot, DefaultsConfiguration $defaultsConfiguration): OptionsInterface
    {
        return new self($defaultsConfiguration->getCharset(), $defaultsConfiguration->getFileLinkFormat(), $defaultsConfiguration->getLogger());
    }
}
