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

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Drupal\Core\Config\StorageInterface;
use Ekino\Drupal\Debug\Action\ActionRegistrar;
use Ekino\Drupal\Debug\ActionMetadata\ActionMetadataManager;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestConfigStorage implements StorageInterface
{
    /**
     * @param string $name
     *
     * @return array
     */
    public function read($name): array
    {
        if ('core.extension' !== $name) {
            throw new \InvalidArgumentException('The only expected config read is the "core.extension" one.');
        }

        return array(
          'module' => array(
            'fcy' => true,
          ),
          'theme' => array(
            'ccc' => true,
          ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name): bool
    {
        throw new NotImplementedException('The exists() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function readMultiple(array $names): array
    {
        throw new NotImplementedException('The readMultiple() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function write($name, array $data): bool
    {
        throw new NotImplementedException('The write() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name): bool
    {
        throw new NotImplementedException('The delete() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function rename($name, $new_name): bool
    {
        throw new NotImplementedException('The rename() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data): string
    {
        throw new NotImplementedException('The encode() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function decode($raw): array
    {
        throw new NotImplementedException('The decode() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function listAll($prefix = ''): array
    {
        throw new NotImplementedException('The listAll() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll($prefix = ''): bool
    {
        throw new NotImplementedException('The deleteAll() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function createCollection($collection): StorageInterface
    {
        throw new NotImplementedException('The createCollection() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCollectionNames(): array
    {
        throw new NotImplementedException('The getAllCollectionNames() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionName(): string
    {
        throw new NotImplementedException('The getCollectionName() method is not implemented.');
    }
}

class TestDebugKernelActionRegistrar extends ActionRegistrar
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addEventSubscriberActionsToEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
    }
}

class TestDebugKernel extends DebugKernel
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigStorage(): StorageInterface
    {
        return new TestConfigStorage();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionRegistrar(string $appRoot, ActionMetadataManager $actionMetadataManager, ConfigurationManager $configurationManager, OptionsStack $optionsStack): ActionRegistrar
    {
        return new TestDebugKernelActionRegistrar();
    }
}
