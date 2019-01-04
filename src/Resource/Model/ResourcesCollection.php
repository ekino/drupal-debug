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

namespace Ekino\Drupal\Debug\Resource\Model;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class ResourcesCollection implements \Countable, \Serializable
{
    /**
     * @var SelfCheckingResourceInterface[]
     */
    private $resources;

    /**
     * @param SelfCheckingResourceInterface[] $resources
     */
    public function __construct(array $resources = array())
    {
        $this->resources = $resources;
    }

    /**
     * @return SelfCheckingResourceInterface[]
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return \serialize($this->resources);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        $this->resources = \unserialize($serialized);
    }
}
