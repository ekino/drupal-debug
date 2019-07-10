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

namespace Ekino\Drupal\Debug\Configuration\Model;

class ActionConfiguration extends AbstractConfiguration
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $processedConfiguration)
    {
        $this->enabled = $processedConfiguration['enabled'];

        unset($processedConfiguration['enabled']);

        parent::__construct($processedConfiguration);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getProcessedConfiguration(): array
    {
        return $this->processedConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return \serialize(array(
            $this->processedConfiguration,
            $this->enabled,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list($this->processedConfiguration, $this->enabled) = \unserialize($serialized);
    }
}
