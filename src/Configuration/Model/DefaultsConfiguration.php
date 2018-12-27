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
     * @param array $processedConfiguration
     */
    public function __construct(array $processedConfiguration)
    {
        parent::__construct($processedConfiguration);

        $this->logger = false;
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->processedConfiguration['cache_directory'];
    }

    /**
     * @return Logger|null
     */
    public function getLogger()
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
    public function getCharset()
    {
        return $this->processedConfiguration['charset'];
    }

    /**
     * @return string|null
     */
    public function getFileLinkFormat()
    {
        return $this->processedConfiguration['file_link_format'];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->processedConfiguration,
            null === $this->logger ? null : false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->processedConfiguration, $this->logger) = \unserialize($serialized);
    }
}
