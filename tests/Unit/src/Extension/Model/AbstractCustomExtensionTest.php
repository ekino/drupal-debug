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

use Ekino\Drupal\Debug\Extension\Model\AbstractCustomExtension;
use PHPUnit\Framework\TestCase;

class AbstractCustomExtensionTest extends TestCase
{
    /**
     * @var string
     */
    const SERIALIZED_CUSTOM_EXTENSION = 'a:2:{i:0;s:4:"/foo";i:1;s:12:"machine_name";}';

    /**
     * @var AbstractCustomExtension
     */
    private $customExtension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->customExtension = new AbstractCustomExtensionTestClass('/foo', 'machine_name');
    }

    public function testGetRootPath(): void
    {
        $this->assertSame('/foo', $this->customExtension->getRootPath());
    }

    public function testGetMachineName(): void
    {
        $this->assertSame('machine_name', $this->customExtension->getMachineName());
    }

    public function testSerialize(): void
    {
        $this->assertSame(self::SERIALIZED_CUSTOM_EXTENSION, $this->customExtension->serialize());
    }

    public function testUnserialize(): void
    {
        $customExtension = new AbstractCustomExtensionTestClass('/bar', 'ccc');
        $customExtension->unserialize(self::SERIALIZED_CUSTOM_EXTENSION);

        $this->assertEquals($this->customExtension, $customExtension);
    }
}

class AbstractCustomExtensionTestClass extends AbstractCustomExtension
{
}
