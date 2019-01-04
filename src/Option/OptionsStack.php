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

namespace Ekino\Drupal\Debug\Option;

class OptionsStack
{
    /**
     * @var OptionsInterface[]
     */
    private $optionsStack;

    /**
     * @param OptionsInterface[] $options
     */
    private function __construct(array $options)
    {
        $this->optionsStack = array();

        foreach ($options as $option) {
            $this->set($option);
        }
    }

    /**
     * @param OptionsInterface[] $options
     *
     * @return OptionsStack
     */
    public static function create(array $options = array()): self
    {
        return new self($options);
    }

    /**
     * @param string $class
     *
     * @return OptionsInterface|null
     */
    public function get(string $class): ?OptionsInterface
    {
        if (!isset($this->optionsStack[$class])) {
            return null;
        }

        return $this->optionsStack[$class];
    }

    /**
     * @param OptionsInterface $options
     */
    public function set(OptionsInterface $options): void
    {
        $this->optionsStack[\get_class($options)] = $options;
    }
}
