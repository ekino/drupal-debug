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

namespace Ekino\Drupal\Debug\Tests\Unit\Resource;

use Carbon\Carbon;
use Ekino\Drupal\Debug\Extension\Model\AbstractCustomExtension;
use Ekino\Drupal\Debug\Extension\Model\CustomTheme;
use Ekino\Drupal\Debug\Resource\Model\CustomExtensionFileResource;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Ekino\Drupal\Debug\Resource\ResourcesFreshnessChecker;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ResourcesFreshnessCheckerTest extends TestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    const EXISTING_FILE_PATH = __DIR__.'/fixtures/__existing.meta';

    /**
     * @var string
     */
    const NOT_EXISTING_FILE_PATH = __DIR__.'/fixtures/__not_existing.meta';

    /**
     * @var string
     */
    const CANNOT_BE_READ_FILE_PATH = __DIR__.'/fixtures/__cannot_be_read.php';

    /**
     * @var string
     */
    const RESOURCE_1_FILE_PATH = __DIR__.'/fixtures/File1.php';

    /**
     * @var string
     */
    const RESOURCE_2_FILE_PATH = __DIR__.'/fixtures/File2.php';

    /**
     * @var string
     */
    const RESOURCE_3_FILE_PATH = __DIR__.'/fixtures/File3.php';

    /**
     * @var string
     */
    const UNEXPECTED_CONTENT_FILE_PATH = __DIR__.'/fixtures/unexpected_content.meta';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        (new Filesystem())->dumpFile(self::EXISTING_FILE_PATH, \serialize(new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, self::getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_2_FILE_PATH, self::getCustomExtension()),
        ))));

        if (!\is_file(self::EXISTING_FILE_PATH)) {
            self::markTestIncomplete(\sprintf('File "%s" could not be created.', self::EXISTING_FILE_PATH));
        }

        $this->resetResourcesModificationTime();

        self::deleteFile(self::NOT_EXISTING_FILE_PATH, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->deleteFile(self::EXISTING_FILE_PATH);
        $this->deleteFile(self::NOT_EXISTING_FILE_PATH);
        $this->deleteFile(self::CANNOT_BE_READ_FILE_PATH);
    }

    public function testGetCurrentResourcesCollectionWhenThereIsNone(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::NOT_EXISTING_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->assertEquals(new ResourcesCollection(), $resourcesFreshnessChecker->getCurrentResourcesCollection());
    }

    public function testGetCurrentResourcesCollectionWhenTheFileCannotBeRead(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile(self::CANNOT_BE_READ_FILE_PATH, '');
        $filesystem->chmod(self::CANNOT_BE_READ_FILE_PATH, 0000);

        $this->assertFileNotIsReadable(self::CANNOT_BE_READ_FILE_PATH);

        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::CANNOT_BE_READ_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The current resources serialized content could not be read.');

        $resourcesFreshnessChecker->getCurrentResourcesCollection();
    }

    public function testGetCurrentResourcesCollectionWhenTheUnserializedContentIsNotTheExpectedOne(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::UNEXPECTED_CONTENT_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The current resources unserialized content class should be "Ekino\Drupal\Debug\Resource\Model\ResourcesCollection".');

        $resourcesFreshnessChecker->getCurrentResourcesCollection();
    }

    public function testGetCurrentResourcesCollection(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->assertEquals(new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, $this->getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_2_FILE_PATH, $this->getCustomExtension()),
        )), $resourcesFreshnessChecker->getCurrentResourcesCollection());
    }

    public function testIsFreshWhenTheFileDoesNotExists(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::NOT_EXISTING_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->assertFalse($resourcesFreshnessChecker->isFresh());
    }

    public function testIsFreshWhenResourcesCountIsDifferent(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, $this->createMock(ResourcesCollection::class));

        $this->assertFalse($resourcesFreshnessChecker->isFresh());
    }

    public function testIsFreshWhenResourcesAreDifferent(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, $this->getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_3_FILE_PATH, $this->getCustomExtension()),
        )));

        $this->assertFalse($resourcesFreshnessChecker->isFresh());
    }

    public function testIsFreshWhenResourcesWereNotModified(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, $this->getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_2_FILE_PATH, $this->getCustomExtension()),
        )));

        $this->assertTrue($resourcesFreshnessChecker->isFresh());
    }

    public function testIsFreshWhenResourcesWereModified(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, $this->getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_2_FILE_PATH, $this->getCustomExtension()),
        )));

        $this->touchFile(self::RESOURCE_1_FILE_PATH, Carbon::now()->addSecond()->getTimestamp());

        $this->assertFalse($resourcesFreshnessChecker->isFresh());
    }

    public function testIsFreshWhenResourcesWereNotModifiedButAreNotInTheSameOrder(): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker(self::EXISTING_FILE_PATH, new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_2_FILE_PATH, $this->getCustomExtension()),
            new CustomExtensionFileResource(self::RESOURCE_1_FILE_PATH, $this->getCustomExtension()),
        )));

        $this->assertTrue($resourcesFreshnessChecker->isFresh());
    }

    /**
     * @dataProvider commitProvider
     */
    public function testCommitWithExistingFile(string $filePath): void
    {
        $resourcesFreshnessChecker = new ResourcesFreshnessChecker($filePath, new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_3_FILE_PATH, $this->getCustomExtension()),
        )));
        $resourcesFreshnessChecker->commit();

        $resourcesFreshnessChecker = new ResourcesFreshnessChecker($filePath, $this->createMock(ResourcesCollection::class));

        $this->assertEquals(new ResourcesCollection(array(
            new CustomExtensionFileResource(self::RESOURCE_3_FILE_PATH, $this->getCustomExtension()),
        )), $resourcesFreshnessChecker->getCurrentResourcesCollection());
    }

    public function commitProvider(): array
    {
        return array(
            array(self::EXISTING_FILE_PATH),
            array(self::NOT_EXISTING_FILE_PATH),
        );
    }

    private function resetResourcesModificationTime(): void
    {
        $resourcesFilePaths = array(
            self::RESOURCE_1_FILE_PATH,
            self::RESOURCE_2_FILE_PATH,
            self::RESOURCE_3_FILE_PATH,
        );

        $nowTs = Carbon::now()->getTimestamp();
        foreach ($resourcesFilePaths as $resourceFilePath) {
            self::touchFile($resourceFilePath, $nowTs);
        }
    }

    /**
     * @return CustomTheme
     */
    private function getCustomExtension(): AbstractCustomExtension
    {
        return new CustomTheme('/foo', 'bar');
    }
}
