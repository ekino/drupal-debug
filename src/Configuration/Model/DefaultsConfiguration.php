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

class DefaultsConfiguration extends AbstractConfiguration
{
    /**
     * @return string
     */
    public function getCacheDirectoryPath(): string
    {
        return $this->processedConfiguration['cache_directory_path'];
    }

    public function getLogger(): array
    {
        return $this->processedConfiguration['logger'];
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
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list($this->processedConfiguration) = \unserialize($serialized);
    }
}
