<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Option;

use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Ekino\Drupal\Debug\Option\OptionsStack;
use PHPUnit\Framework\TestCase;

class OptionsStackTest extends TestCase
{
    /**
     * @var TestOptions
     */
    private $options;

    /**
     * @var OptionsStack
     */
    private $optionsStack;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->options = new TestOptions();
        $this->optionsStack = OptionsStack::create();
    }

    public function testCreateWithoutOptions()
    {
        $this->assertAttributeSame(array(), 'optionsStack', $this->optionsStack);
    }

    public function testCreateWithOptions()
    {
        $optionsStack = OptionsStack::create(array(
            $this->options,
        ));

        $this->assertAttributeSame(array(
            TestOptions::class => $this->options,
        ), 'optionsStack', $optionsStack);
    }

    public function testGetWithAnUnknownClass()
    {
        $this->assertNull($this->optionsStack->get('A\Foo\Options'));
    }

    public function testGetWithAKnownClass()
    {
        $optionsStack = OptionsStack::create(array(
            $this->options,
            $this->createMock(OptionsInterface::class),
        ));

        $this->assertSame($this->options, $optionsStack->get(TestOptions::class));
    }

    public function testSet()
    {
        $this->optionsStack->set($this->options);

        $this->assertAttributeSame(array(
          TestOptions::class => $this->options,
        ), 'optionsStack', $this->optionsStack);
    }

    public function testSetWithTheSameOptionsClassTwice()
    {
        $this->optionsStack->set($this->options);

        $otherOptionsOfSameClass = new TestOptions();
        $this->optionsStack->set($otherOptionsOfSameClass);

        $this->assertAttributeSame(array(
            \get_class($otherOptionsOfSameClass) => $otherOptionsOfSameClass,
        ), 'optionsStack', $this->optionsStack);
    }
}

class TestOptions implements OptionsInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getDefault($appRoot, DefaultsConfiguration $defaultsConfiguration)
    {
    }
}
