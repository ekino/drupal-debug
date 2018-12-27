<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Kernel;

use Drupal\Core\OriginalDrupalKernel;
use Ekino\Drupal\Debug\Action\ActionManager;
use Ekino\Drupal\Debug\Kernel\Event\AfterAttachSyntheticEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterContainerInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterRequestPreHandleEvent;
use Ekino\Drupal\Debug\Kernel\Event\AfterSettingsInitializationEvent;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Ekino\Drupal\Debug\Option\OptionsStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class DebugKernel extends OriginalDrupalKernel
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ActionManager
     */
    private $actionManager;

    /**
     * @var array
     */
    private $enabledModules;

    /**
     * @var array
     */
    private $enabledThemes;

    /**
     * @var bool
     */
    private $settingsWereInitializedWithTheDedicatedDrupalKernelMethod;

    /**
     * @param string            $environment
     * @param object            $class_loader
     * @param bool              $allow_dumping
     * @param string|null       $app_root
     * @param OptionsStack|null $optionsStack
     */
    public function __construct($environment, $class_loader, $allow_dumping = true, $app_root = null, OptionsStack $optionsStack = null)
    {
        $this->eventDispatcher = $this->getEventDispatcher();

        $appRoot = $app_root;
        if (!\is_string($appRoot)) {
            $appRoot = static::guessApplicationRoot();
        }

        $this->actionManager = $this->getActionManager($appRoot, $optionsStack instanceof OptionsStack ? $optionsStack : OptionsStack::create());

        $this->actionManager->addEventSubscriberActionsToEventDispatcher($this->eventDispatcher);

        $this->eventDispatcher->dispatch(DebugKernelEvents::ON_KERNEL_INSTANTIATION);

        static::bootEnvironment();

        $this->eventDispatcher->dispatch(DebugKernelEvents::AFTER_ENVIRONMENT_BOOT);

        $this->enabledModules = array();
        $this->enabledThemes = array();
        $this->settingsWereInitializedWithTheDedicatedDrupalKernelMethod = false;

        parent::__construct($environment, $class_loader, $allow_dumping, $appRoot);
    }

    /**
     * @return DebugKernel
     */
    public function boot()
    {
        // The kernel cannot be booted without settings.
        //
        // If the kernel is going to be booted, but that the
        // initializeSettings() method was never called, it means that the
        // settings were initialized in another way. If it is not the case, the
        // booting is going to fail anyway.
        //
        // Whatever... Since the settings were not initialized in the traditional
        // way, the things we want to do after the settings initialization
        // were not done. So we do them now.
        if (!$this->settingsWereInitializedWithTheDedicatedDrupalKernelMethod) {
            $this->afterSettingsInitialization();
        }

        return parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function preHandle(Request $request)
    {
        parent::preHandle($request);

        $this->eventDispatcher->dispatch(DebugKernelEvents::AFTER_REQUEST_PRE_HANDLE, new AfterRequestPreHandleEvent($this->container, $this->enabledModules, $this->enabledThemes));
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        return \array_merge(parent::getKernelParameters(), array(
            'kernel.debug' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        $container = parent::initializeContainer();

        $this->eventDispatcher->dispatch(DebugKernelEvents::AFTER_CONTAINER_INITIALIZATION, new AfterContainerInitializationEvent($container, $this->enabledModules, $this->enabledThemes));

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeSettings(Request $request)
    {
        parent::initializeSettings($request);

        $this->settingsWereInitializedWithTheDedicatedDrupalKernelMethod = true;

        $this->afterSettingsInitialization();
    }

    /**
     * {@inheritdoc}
     */
    protected function attachSynthetic(ContainerInterface $container)
    {
        $container = parent::attachSynthetic($container);

        $this->eventDispatcher->dispatch(DebugKernelEvents::AFTER_ATTACH_SYNTHETIC, new AfterAttachSyntheticEvent($container, $this->enabledModules, $this->enabledThemes));

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerBuilder()
    {
        $containerBuilder = parent::getContainerBuilder();

        $this->actionManager->addCompilerPassActionsToContainerBuilder($containerBuilder);

        return $containerBuilder;
    }

    /**
     * @return EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return new EventDispatcher();
    }

    /**
     * @param string       $appRoot
     * @param OptionsStack $optionsStack
     *
     * @return ActionManager
     */
    protected function getActionManager($appRoot, OptionsStack $optionsStack)
    {
        return new ActionManager($appRoot, $optionsStack);
    }

    private function afterSettingsInitialization()
    {
        $coreExtensionConfig = $this->getConfigStorage()->read('core.extension');
        if (isset($coreExtensionConfig['module'])) {
            $this->enabledModules = \array_keys($coreExtensionConfig['module']);
        }

        if (isset($coreExtensionConfig['theme'])) {
            $this->enabledThemes = \array_keys($coreExtensionConfig['theme']);
        }

        $this->eventDispatcher->dispatch(DebugKernelEvents::AFTER_SETTINGS_INITIALIZATION, new AfterSettingsInitializationEvent($this->enabledModules, $this->enabledThemes));
    }
}
