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

namespace Ekino\Drupal\Debug\Tests\Unit\Extension\Model;

use Ekino\Drupal\Debug\Extension\Model\CustomModule;
use PHPUnit\Framework\TestCase;

class CustomModuleTest extends TestCase
{
    /**
     * @var string
     */
    const SERIALIZED_CUSTOM_MODULE = 'a:3:{i:0;s:4:"/foo";i:1;s:12:"machine_name";i:2;s:11:"MachineName";}';

    /**
     * @var CustomModule
     */
    private $customModule;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->customModule = new CustomModule('/foo', 'machine_name');
    }

    public function testGetCamelCaseMachineName(): void
    {
        $this->assertSame('MachineName', $this->customModule->getCamelCaseMachineName());
    }

    public function testSerialize(): void
    {
        $this->assertSame(self::SERIALIZED_CUSTOM_MODULE, $this->customModule->serialize());
    }

    public function testUnserialize(): void
    {
        $customModule = new CustomModule('/bar', 'ccc');
        $customModule->unserialize(self::SERIALIZED_CUSTOM_MODULE);

        $this->assertEquals($this->customModule, $customModule);
    }
}
