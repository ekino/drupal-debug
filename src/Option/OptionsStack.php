<?php

declare(strict_types=1);

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
    public static function create(array $options = array())
    {
        return new self($options);
    }

    /**
     * @param string $class
     *
     * @return OptionsInterface|null
     */
    public function get($class)
    {
        if (!isset($this->optionsStack[$class])) {
            return null;
        }

        return $this->optionsStack[$class];
    }

    /**
     * @param OptionsInterface $options
     */
    public function set(OptionsInterface $options)
    {
        $this->optionsStack[\get_class($options)] = $options;
    }
}
