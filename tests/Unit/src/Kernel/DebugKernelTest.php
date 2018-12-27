<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel;

use Composer\Autoload\ClassLoader;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\OriginalDrupalKernel;
use Ekino\Drupal\Debug\Action\ActionManager;
use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterContainerInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterRequestPreHandleEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterSettingsInitializationEvent;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes\TestDebugKernel;
use Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes\TestDebugKernelInstantiation;
use Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes\TestOriginalDrupalKernel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class DebugKernelTest extends TestCase
{
    /**
     * @var string
     */
    const TEST_ORIGINAL_DRUPAL_KERNEL_CLASS_FILE_PATH = __DIR__.'/test_classes/DebugKernelTest_TestOriginalDrupalKernel.php';

    /**
     * @var string
     */
    const TEST_DEBUG_KERNEL_INSTANTIATION_FILE_PATH = __DIR__.'/test_classes/DebugKernelTest_TestDebugKernelInstantiation.php';

    /**
     * @var string
     */
    const TEST_DEBUG_KERNEL_FILE_PATH = __DIR__.'/test_classes/DebugKernelTest_TestDebugKernel.php';

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = true;

    /**
     * @var EventDispatcher|MockObject
     */
    private $eventDispatcher;

    /**
     * @var ActionManager|MockObject
     */
    private $actionManager;

    /**
     * @var TestDebugKernel
     */
    private $debugKernel;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        require self::TEST_ORIGINAL_DRUPAL_KERNEL_CLASS_FILE_PATH;

        \class_alias(TestOriginalDrupalKernel::class, OriginalDrupalKernel::class);

        require self::TEST_DEBUG_KERNEL_INSTANTIATION_FILE_PATH;

        require self::TEST_DEBUG_KERNEL_FILE_PATH;

        TestDebugKernelInstantiation::reset();

        $this->debugKernel = $this->getDebugKernel();
    }

    /**
     * @dataProvider instantiationProvider
     */
    public function testInstantiation($appRoot, OptionsStack $optionsStack = null)
    {
        new TestDebugKernelInstantiation('test', $this->createMock(ClassLoader::class), true, $appRoot, $optionsStack);

        $this->assertEquals(array(
            \is_string($appRoot) ? $appRoot : '/foo',
            $optionsStack instanceof OptionsStack ? $optionsStack : OptionsStack::create(),
            'addEventSubscriberActionsToEventDispatcher',
            'dispatch.ekino.drupal.debug.debug_kernel.on_kernel_instantiation',
            'bootEnvironment',
            'dispatch.ekino.drupal.debug.debug_kernel.after_environment_boot',
        ), TestDebugKernelInstantiation::$stack);
    }

    public function instantiationProvider()
    {
        return array(
            array(null, null),
            array(null, OptionsStack::create()),
            array('/bar', null),
            array('/bar', OptionsStack::create()),
        );
    }

    public function testBootWhenTheSettingsWereNotInitializedWithTheDedicatedDrupalKernelMethod()
    {
        $this->assertAfterSettingsInitialization(array(
            'boot',
        ));

        $this->assertAttributeSame(true, 'booted', $this->debugKernel);
    }

    public function testBoot()
    {
        $this->callProtectedMethod('initializeSettings', array($this->createMock(Request::class)));
        $this->callProtectedMethod('boot');

        $this->assertAttributeSame(true, 'booted', $this->debugKernel);
    }

    public function testPreHandle()
    {
        $this->setUpEnabledExtensions();

        $this->eventDispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with('ekino.drupal.debug.debug_kernel.after_request_pre_handle', new AfterRequestPreHandleEvent(new Container(), array('foo'), array('bar')));

        $this->debugKernel->preHandle($this->createMock(Request::class));
    }

    public function testGetKernelParameters()
    {
        $kernelParameters = $this->callProtectedMethod('getKernelParameters');

        $this->assertArrayHasKey('kernel.debug', $kernelParameters);
        $this->assertTrue($kernelParameters['kernel.debug']);
    }

    public function testInitializeContainer()
    {
        $this->setUpEnabledExtensions();

        $this->eventDispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with('ekino.drupal.debug.debug_kernel.after_container_initialization', new AfterContainerInitializationEvent(new Container(), array('foo'), array('bar')));

        $this->callProtectedMethod('initializeContainer');
    }

    public function testInitializeSettings()
    {
        $this->setUpEnabledExtensions();

        $this->assertAfterSettingsInitialization(array(
            'initializeSettings',
            array($this->createMock(Request::class)),
        ));

        $this->assertAttributeSame(true, 'settingsInitialized', $this->debugKernel);

        $this->assertAttributeSame(true, 'settingsWereInitializedWithTheDedicatedDrupalKernelMethod', $this->debugKernel);
    }

    public function testAttachSynthetic()
    {
        $this->setUpEnabledExtensions();

        $container = $this->createMock(ContainerInterface::class);

        $this->eventDispatcher
          ->expects($this->atLeastOnce())
          ->method('dispatch')
          ->with('ekino.drupal.debug.debug_kernel.after_attach_synthetic', new AfterAttachSyntheticEvent($container, array('foo'), array('bar')));

        $this->assertSame($container, $this->callProtectedMethod('attachSynthetic', array($container)));
    }

    public function testGetContainerBuilder()
    {
        $containerBuilder = new ContainerBuilder();

        $this->actionManager
            ->expects($this->atLeastOnce())
            ->method('addCompilerPassActionsToContainerBuilder')
            ->with($containerBuilder);

        $this->assertEquals($containerBuilder, $this->callProtectedMethod('getContainerBuilder'));
    }

    /**
     * @return TestDebugKernel
     */
    private function getDebugKernel()
    {
        $debugKernel = new TestDebugKernel('test', $this->createMock(ClassLoader::class));

        $propertiesToMock = array(
            'eventDispatcher' => $this->createMock(EventDispatcher::class),
            'actionManager' => $this->createMock(ActionManager::class),
        );

        foreach ($propertiesToMock as $property => $mock) {
            $this->{$property} = $mock;

            $refl = new \ReflectionProperty(DebugKernel::class, $property);
            $refl->setAccessible(true);
            $refl->setValue($debugKernel, $mock);
        }

        return $debugKernel;
    }

    private function setUpEnabledExtensions()
    {
        $properties = array(
            'enabledModules' => array('foo'),
            'enabledThemes' => array('bar'),
        );

        foreach ($properties as $property => $value) {
            $refl = new \ReflectionProperty(DebugKernel::class, $property);
            $refl->setAccessible(true);
            $refl->setValue($this->debugKernel, $value);
        }
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    private function callProtectedMethod($method, array $arguments = array())
    {
        $refl = new \ReflectionMethod($this->debugKernel, $method);
        $refl->setAccessible(true);

        return $refl->invokeArgs($this->debugKernel, $arguments);
    }

    /**
     * @param array $arguments
     */
    private function assertAfterSettingsInitialization(array $arguments)
    {
        $this->eventDispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with('ekino.drupal.debug.debug_kernel.after_settings_initialization', new AfterSettingsInitializationEvent(array('fcy'), array('ccc')));

        \call_user_func_array(array($this, 'callProtectedMethod'), $arguments);

        $this->assertAttributeSame(array('fcy'), 'enabledModules', $this->debugKernel);
        $this->assertAttributeSame(array('ccc'), 'enabledThemes', $this->debugKernel);
    }
}
