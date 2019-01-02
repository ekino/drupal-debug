<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Kernel\test_classes;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DrupalKernelInterface;
use Ekino\Drupal\Debug\Exception\NotImplementedException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestOriginalDrupalKernel implements DrupalKernelInterface
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
    public static function guessApplicationRoot(): string
    {
        return '/foo';
    }

    /**
     * @param string|null $appRoot
     */
    public static function bootEnvironment($appRoot = null): void
    {
    }

    public function boot(): DrupalKernelInterface
    {
        $this->booted = true;

        return $this;
    }

    /**
     * @param Request $request
     */
    public function preHandle(Request $request): void
    {
        $this->container = new Container();
    }

    /**
     * @return array
     */
    protected function getKernelParameters(): array
    {
        return array('foo');
    }

    /**
     * @return Container
     */
    protected function initializeContainer(): ContainerInterface
    {
        return new Container();
    }

    /**
     * @param Request $request
     */
    protected function initializeSettings(Request $request): void
    {
        $this->settingsInitialized = true;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ContainerInterface
     */
    protected function attachSynthetic(ContainerInterface $container): ContainerInterface
    {
        return $container;
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        throw new NotImplementedException('The setContainer() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown(): void
    {
        throw new NotImplementedException('The shutdown() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function discoverServiceProviders(): array
    {
        throw new NotImplementedException('The discoverServiceProviders() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders($origin): array
    {
        throw new NotImplementedException('The getServiceProviders() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        throw new NotImplementedException('The getContainer() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedContainerDefinition(): ?array
    {
        throw new NotImplementedException('The getCachedContainerDefinition() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function setSitePath($path): void
    {
        throw new NotImplementedException('The setSitePath() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getSitePath(): string
    {
        throw new NotImplementedException('The getSitePath() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAppRoot(): string
    {
        throw new NotImplementedException('The getAppRoot() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function updateModules(array $moduleList, array $moduleFilenames = array()): void
    {
        throw new NotImplementedException('The updateModules() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function rebuildContainer(): ContainerInterface
    {
        throw new NotImplementedException('The rebuildContainer() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateContainer(): void
    {
        throw new NotImplementedException('The invalidateContainer() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function prepareLegacyRequest(Request $request): DrupalKernelInterface
    {
        throw new NotImplementedException('The prepareLegacyRequest() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadLegacyIncludes(): void
    {
        throw new NotImplementedException('The loadLegacyIncludes() method is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true): Response
    {
        throw new NotImplementedException('The handle() method is not implemented.');
    }
}
