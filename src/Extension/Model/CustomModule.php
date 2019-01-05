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

namespace Ekino\Drupal\Debug\Extension\Model;

use Drupal\Core\DependencyInjection\ContainerBuilder;

class CustomModule extends AbstractCustomExtension
{
    /**
     * @var string
     */
    private $camelCaseMachineName;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $rootPath, string $machineName)
    {
        parent::__construct($rootPath, $machineName);

        // The same camelize function is used in the Drupal kernel.
        $this->camelCaseMachineName = ContainerBuilder::camelize($machineName);
    }

    /**
     * @return string
     */
    public function getCamelCaseMachineName(): string
    {
        return $this->camelCaseMachineName;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return \serialize(array(
            $this->rootPath,
            $this->machineName,
            $this->camelCaseMachineName,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list($this->rootPath, $this->machineName, $this->camelCaseMachineName) = \unserialize($serialized);
    }
}
