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

namespace Ekino\Drupal\Debug\Tests\Unit\Action;

use Ekino\Drupal\Debug\Action\AbstractOverrideTwigConfigAction;
use Ekino\Drupal\Debug\Exception\NotSupportedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AbstractOverrideTwigConfigActionTest extends TestCase
{
    /**
     * @var AbstractOverrideTwigConfigActionTestClass
     */
    private $overrideTwigConfigAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->overrideTwigConfigAction = new AbstractOverrideTwigConfigActionTestClass();
    }

    public function testProcessWhenTheTwigConfigParameterIsNotYetSet(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('The "twig.config" parameter should already be set in the container builder.');

        $this->overrideTwigConfigAction->process($this->getContainerBuilder(false));
    }

    /**
     * @dataProvider processProvider
     */
    public function testProcess(array $expected, array $baseConfig): void
    {
        $containerBuilder = $this->getContainerBuilder(true);
        $containerBuilder
            ->expects($this->atLeastOnce())
            ->method('getParameter')
            ->with('twig.config')
            ->willReturn($baseConfig);

        $containerBuilder
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with('twig.config', $expected);

        $this->overrideTwigConfigAction->process($containerBuilder);
    }

    public function processProvider(): array
    {
        return array(
            array(
                array(
                    'foo' => 'bar',
                    'fcy' => 'ccc',
                ),
                array(),
            ),
            array(
                array(
                    'ini' => 'tial',
                    'foo' => 'bar',
                    'fcy' => 'ccc',
                ),
                array(
                    'ini' => 'tial',
                ),
            ),
            array(
                array(
                    'fcy' => 'ccc',
                    'hold' => 'on',
                    'foo' => 'bar',
                ),
                array(
                    'fcy' => 'ssp',
                    'hold' => 'on',
                ),
            ),
        );
    }

    /**
     * @param bool $parameterExists
     *
     * @return ContainerBuilder|MockObject
     */
    private function getContainerBuilder(bool $parameterExists): ContainerBuilder
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder
            ->expects($this->atLeastOnce())
            ->method('hasParameter')
            ->with('twig.config')
            ->willReturn($parameterExists);

        return $containerBuilder;
    }
}

class AbstractOverrideTwigConfigActionTestClass extends AbstractOverrideTwigConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverrides(): array
    {
        return array(
            'foo' => 'bar',
            'fcy' => 'ccc',
        );
    }
}
