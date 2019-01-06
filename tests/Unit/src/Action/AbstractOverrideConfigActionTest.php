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

use Ekino\Drupal\Debug\Action\AbstractOverrideConfigAction;
use PHPUnit\Framework\TestCase;

class AbstractOverrideConfigActionTest extends TestCase
{
    /**
     * @var AbstractOverrideConfigActionTestClass
     */
    private $overrideConfigAction;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->overrideConfigAction = new AbstractOverrideConfigActionTestClass();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(array(
            'ekino.drupal.debug.debug_kernel.after_settings_initialization' => 'process',
        ), AbstractOverrideConfigActionTestClass::getSubscribedEvents());
    }

    /**
     * @dataProvider processProvider
     */
    public function testProcess(array $expected, array $baseConfig): void
    {
        global $config;

        $config = $baseConfig;

        $this->overrideConfigAction->process();

        $this->assertSame($expected, $config);
    }

    public function processProvider(): array
    {
        return array(
            array(
                array(
                    'foo' => array(
                        'bar' => 'fcy',
                    ),
                    'ccc' => false,
                ),
                array(),
            ),
            array(
                array(
                    'newt' => 'recalls',
                    'foo' => array(
                        'bar' => 'fcy',
                    ),
                    'ccc' => false,
                ),
                array(
                    'newt' => 'recalls',
                ),
            ),
            array(
                array(
                    'foo' => array(
                        'bar' => 'fcy',
                    ),
                    'ccc' => false,
                ),
                array(
                    'foo' => array(
                        'bar' => 'proto',
                    ),
                    'ccc' => true,
                ),
            ),
        );
    }
}

class AbstractOverrideConfigActionTestClass extends AbstractOverrideConfigAction
{
    /**
     * {@inheritdoc}
     */
    protected function getOverrides(): array
    {
        return array(
            '[foo][bar]' => 'fcy',
            '[ccc]' => false,
        );
    }
}
