<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Unit\Resource\Model;

use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class ResourcesCollectionTest extends TestCase
{
    /**
     * @var string
     */
    const RESOURCE_1_FILE_PATH = __DIR__.'/fixtures/File1.php';

    /**
     * @var string
     */
    const RESOURCE_2_FILE_PATH = __DIR__.'/fixtures/File2.php';

    /**
     * @var MockObject[]|SelfCheckingResourceInterface[]
     */
    private $resources;

    /**
     * @var ResourcesCollection
     */
    private $resourcesCollection;

    /**
     * @var string
     */
    private $serializedResourcesCollection;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resources = array(
            new FileResource(self::RESOURCE_1_FILE_PATH),
            new FileResource(self::RESOURCE_2_FILE_PATH),
        );

        $this->resourcesCollection = new ResourcesCollection($this->resources);
        $resource1FilePathLength = \mb_strlen(self::RESOURCE_1_FILE_PATH);
        $resource2FilePathLength = \mb_strlen(self::RESOURCE_2_FILE_PATH);
        $this->serializedResourcesCollection = \sprintf('a:2:{i:0;C:46:"Symfony\Component\Config\Resource\FileResource":%s:{s:%s:"%s";}i:1;C:46:"Symfony\Component\Config\Resource\FileResource":%s:{s:%s:"%s";}}', ($resource1FilePathLength + \mb_strlen((string) $resource1FilePathLength) + 6), $resource1FilePathLength, self::RESOURCE_1_FILE_PATH, ($resource2FilePathLength + \mb_strlen((string) $resource2FilePathLength) + 6), $resource2FilePathLength, self::RESOURCE_2_FILE_PATH);
    }

    public function testAll()
    {
        $this->assertSame($this->resources, $this->resourcesCollection->all());
    }

    public function testCount()
    {
        $this->assertCount(2, $this->resourcesCollection);
    }

    public function testSerialize()
    {
        $this->assertSame($this->serializedResourcesCollection, $this->resourcesCollection->serialize());
    }

    public function testUnserialize()
    {
        $resourcesCollection = new ResourcesCollection(array());
        $resourcesCollection->unserialize($this->serializedResourcesCollection);

        $this->assertEquals($this->resourcesCollection, $resourcesCollection);
    }
}
