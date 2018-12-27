<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Option;

use Ekino\Drupal\Debug\Action\DisplayPrettyExceptions\DisplayPrettyExceptionsOptions;
use Ekino\Drupal\Debug\Action\DisplayPrettyExceptionsASAP\DisplayPrettyExceptionsASAPOptions;
use Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions\ThrowErrorsAsExceptionsOptions;
use Ekino\Drupal\Debug\Action\WatchContainerDefinitions\WatchContainerDefinitionsOptions;
use Ekino\Drupal\Debug\Action\WatchHooksImplementations\WatchHooksImplementationsOptions;
use Ekino\Drupal\Debug\Action\WatchRoutingDefinitions\WatchRoutingDefinitionsOptions;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Ekino\Drupal\Debug\Option\OptionsStackBuilder;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OptionsStackBuilderTest extends TestCase
{
    /**
     * @var OptionsStackBuilder
     */
    private $optionsStackBuilder;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->optionsStackBuilder = OptionsStackBuilder::create();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(OptionsStackBuilder::class, $this->optionsStackBuilder);
    }

    public function testGetOptionsStack()
    {
        $this->assertInstanceOf(OptionsStack::class, $this->optionsStackBuilder->getOptionsStack());
    }

    /**
     * @dataProvider setDisplayPrettyExceptionsOptionsProvider
     */
    public function testSetDisplayPrettyExceptionsOptions($charset, $fileLinkFormat, LoggerInterface $logger = null)
    {
        $this->doTestOptions('setDisplayPrettyExceptionsOptions', array(
            $charset,
            $fileLinkFormat,
            $logger,
        ), DisplayPrettyExceptionsOptions::class);
    }

    public function setDisplayPrettyExceptionsOptionsProvider()
    {
        return array(
          array(null, null, null),
          array('UTF-8', 'foo', $this->createMock(LoggerInterface::class)),
        );
    }

    /**
     * @dataProvider setDisplayPrettyExceptionsASAPOptionsProvider
     */
    public function testSetDisplayPrettyExceptionsASAPOptions($charset, $fileLinkFormat)
    {
        $this->doTestOptions('setDisplayPrettyExceptionsASAPOptions', array(
            $charset,
            $fileLinkFormat,
        ), DisplayPrettyExceptionsASAPOptions::class);
    }

    public function setDisplayPrettyExceptionsASAPOptionsProvider()
    {
        return array(
          array(null, null),
          array('UTF-8', 'foo'),
        );
    }

    /**
     * @dataProvider setThrowErrorsAsExceptionsOptionsProvider
     */
    public function testSetThrowErrorsAsExceptionsOptions($levels, LoggerInterface $logger = null)
    {
        $this->doTestOptions('setThrowErrorsAsExceptionsOptions', array(
            $levels,
            $logger,
        ), ThrowErrorsAsExceptionsOptions::class);
    }

    public function setThrowErrorsAsExceptionsOptionsProvider()
    {
        return array(
          array(E_ALL, null),
          array(E_ALL, $this->createMock(LoggerInterface::class)),
        );
    }

    public function testSetWatchContainerDefinitionsOptions()
    {
        $this->doTestWatchOptions('setWatchContainerDefinitionsOptions', WatchContainerDefinitionsOptions::class);
    }

    public function testSetWatchHooksImplementationsOptions()
    {
        $this->doTestWatchOptions('setWatchHooksImplementationsOptions', WatchHooksImplementationsOptions::class);
    }

    public function testSetWatchRoutingDefinitionsOptions()
    {
        $this->doTestWatchOptions('setWatchRoutingDefinitionsOptions', WatchRoutingDefinitionsOptions::class);
    }

    /**
     * @param string $method
     * @param string $class
     */
    private function doTestWatchOptions($method, $class)
    {
        $this->doTestOptions($method, array(
            '/foo',
            $this->createMock(ResourcesCollection::class),
        ), $class);
    }

    /**
     * @param string $method
     * @param array  $args
     * @param string $class
     */
    private function doTestOptions($method, array $args, $class)
    {
        $this->optionsStackBuilder->{$method}(...$args);

        $this->assertAttributeEquals(array(
            $class => new $class(...$args),
        ), 'options', $this->optionsStackBuilder);
    }
}
