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
    protected function setUp(): void
    {
        $this->optionsStackBuilder = OptionsStackBuilder::create();
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(OptionsStackBuilder::class, $this->optionsStackBuilder);
    }

    public function testGetOptionsStack(): void
    {
        $this->assertInstanceOf(OptionsStack::class, $this->optionsStackBuilder->getOptionsStack());
    }

    /**
     * @dataProvider setDisplayPrettyExceptionsOptionsProvider
     */
    public function testSetDisplayPrettyExceptionsOptions(?string $charset, ?string $fileLinkFormat, ?LoggerInterface $logger): void
    {
        $this->doTestOptions('setDisplayPrettyExceptionsOptions', array(
            $charset,
            $fileLinkFormat,
            $logger,
        ), DisplayPrettyExceptionsOptions::class);
    }

    public function setDisplayPrettyExceptionsOptionsProvider(): array
    {
        return array(
            array(null, null, null),
            array('UTF-8', 'foo', $this->createMock(LoggerInterface::class)),
        );
    }

    /**
     * @dataProvider setDisplayPrettyExceptionsASAPOptionsProvider
     */
    public function testSetDisplayPrettyExceptionsASAPOptions(?string $charset, ?string $fileLinkFormat): void
    {
        $this->doTestOptions('setDisplayPrettyExceptionsASAPOptions', array(
            $charset,
            $fileLinkFormat,
        ), DisplayPrettyExceptionsASAPOptions::class);
    }

    public function setDisplayPrettyExceptionsASAPOptionsProvider(): array
    {
        return array(
            array(null, null),
            array('UTF-8', 'foo'),
        );
    }

    /**
     * @dataProvider setThrowErrorsAsExceptionsOptionsProvider
     */
    public function testSetThrowErrorsAsExceptionsOptions(int $levels, ?LoggerInterface $logger): void
    {
        $this->doTestOptions('setThrowErrorsAsExceptionsOptions', array(
            $levels,
            $logger,
        ), ThrowErrorsAsExceptionsOptions::class);
    }

    public function setThrowErrorsAsExceptionsOptionsProvider(): array
    {
        return array(
            array(E_ALL, null),
            array(E_ALL, $this->createMock(LoggerInterface::class)),
        );
    }

    public function testSetWatchContainerDefinitionsOptions(): void
    {
        $this->doTestWatchOptions('setWatchContainerDefinitionsOptions', WatchContainerDefinitionsOptions::class);
    }

    public function testSetWatchHooksImplementationsOptions(): void
    {
        $this->doTestWatchOptions('setWatchHooksImplementationsOptions', WatchHooksImplementationsOptions::class);
    }

    public function testSetWatchRoutingDefinitionsOptions(): void
    {
        $this->doTestWatchOptions('setWatchRoutingDefinitionsOptions', WatchRoutingDefinitionsOptions::class);
    }

    /**
     * @param string $method
     * @param string $class
     */
    private function doTestWatchOptions(string $method, string $class): void
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
    private function doTestOptions(string $method, array $args, string $class): void
    {
        $this->optionsStackBuilder->{$method}(...$args);

        $this->assertAttributeEquals(array(
            $class => new $class(...$args),
        ), 'options', $this->optionsStackBuilder);
    }
}
