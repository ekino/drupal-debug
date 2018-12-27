<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TestOriginalDrupalKernel
{
    /**
     * @var Container|null
     */
    protected $container;

    /**
     * @var bool
     */
    private $booted;

    /**
     * @var bool
     */
    private $settingsInitialized;

    public function __construct()
    {
        $this->container = null;
        $this->booted = false;
        $this->settingsInitialized = false;
    }

    /**
     * @return string
     */
    public static function guessApplicationRoot()
    {
        return '/foo';
    }

    /**
     * @param string|null $appRoot
     */
    public static function bootEnvironment($appRoot = null)
    {
    }

    public function boot()
    {
        $this->booted = true;
    }

    /**
     * @param Request $request
     */
    public function preHandle(Request $request)
    {
        $this->container = new Container();
    }

    /**
     * @return array
     */
    protected function getKernelParameters()
    {
        return array('foo');
    }

    /**
     * @return Container
     */
    protected function initializeContainer()
    {
        return new Container();
    }

    /**
     * @param Request $request
     */
    protected function initializeSettings(Request $request)
    {
        $this->settingsInitialized = true;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ContainerInterface
     */
    protected function attachSynthetic(ContainerInterface $container)
    {
        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder();
    }
}
