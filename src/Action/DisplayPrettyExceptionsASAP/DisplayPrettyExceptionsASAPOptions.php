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

use Ekino\Drupal\Debug\Configuration\CharsetConfigurationTrait;
use Ekino\Drupal\Debug\Configuration\FileLinkFormatConfigurationTrait;
use Ekino\Drupal\Debug\Configuration\Model\ActionConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class DisplayPrettyExceptionsASAPOptions implements OptionsInterface
{
    use CharsetConfigurationTrait;
    use FileLinkFormatConfigurationTrait;

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

    public static function addConfiguration(NodeBuilder $nodeBuilder, DefaultsConfiguration $defaultsConfiguration): void
    {
        self::addCharsetConfigurationNodeFromDefaultsConfiguration($nodeBuilder, $defaultsConfiguration);
        self::addFileLinkFormatConfigurationNodeFromDefaultsConfiguration($nodeBuilder, $defaultsConfiguration);
    }

    public static function getOptions(string $appRoot, ActionConfiguration $actionConfiguration): OptionsInterface
    {
        return new self(self::getConfiguredCharset($actionConfiguration), self::getConfiguredFileLinkFormat($actionConfiguration));
    }
}
