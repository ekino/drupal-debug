<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Configuration\Model;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DefaultsConfiguration extends AbstractConfiguration
{
    /**
     * @var false|Logger|null
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $processedConfiguration)
    {
        parent::__construct($processedConfiguration);

        $this->logger = false;
    }

    /**
     * @return string
     */
    public function getCacheDirectory(): string
    {
        return $this->processedConfiguration['cache_directory'];
    }

    /**
     * @return Logger|null
     */
    public function getLogger(): ?Logger
    {
        if (false === $this->logger) {
            $loggerProcessedConfiguration = $this->processedConfiguration['logger'];

            $this->logger = $loggerProcessedConfiguration['enabled'] ? new Logger($loggerProcessedConfiguration['channel'], array(
                new StreamHandler($loggerProcessedConfiguration['file_path']),
            )) : null;
        }

        return $this->logger;
    }

    /**
     * @return string|null
     */
    public function getCharset(): ?string
    {
        return $this->processedConfiguration['charset'];
    }

    /**
     * @return string|null
     */
    public function getFileLinkFormat(): ?string
    {
        return $this->processedConfiguration['file_link_format'];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return \serialize(array(
            $this->processedConfiguration,
            null === $this->logger ? null : false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list($this->processedConfiguration, $this->logger) = \unserialize($serialized);
    }
}
