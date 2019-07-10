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

namespace Ekino\Drupal\Debug\Tests\Unit\Composer\Helper;

use Composer\Composer;
use Composer\IO\IOInterface;
use Ekino\Drupal\Debug\Composer\Helper\ManageConfigurationHelper;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Tests\Traits\FileHelperTrait;
use Ekino\Drupal\Debug\Tests\Unit\Composer\Helper\helper\BufferIO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class ManageConfigurationHelperTest extends TestCase
{
    use FileHelperTrait;

    /**
     * @var string
     */
    private const EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/drupal-debug.yml';

    /**
     * @var string
     */
    private const NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/__not-existing-drupal-debug.yml';

    /**
     * @var string
     */
    private const EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/not_writeable/drupal-debug.yml';

    /**
     * @var string
     */
    private const NOT_EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH = __DIR__.'/fixtures/not_writeable/__not-existing-drupal-debug.yml';

    /**
     * @var string
     */
    private const TOGGLE_SUBSTITUTION_CONFIGURATION_FILES_DIRECTORY_PATH = __DIR__.'/fixtures/toggle_substitution_cases';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        self::deleteFile(self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH, true);

        $this->assertFileIsWritable(self::EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH);
        $this->assertDirectoryIsWritable(\dirname(self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH));

        self::setFileNotWriteable(self::EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH);
        $this->assertFileNotIsWritable(self::EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH);

        $notExistingAndNotWriteableDirectoryPath = \dirname(self::NOT_EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH);
        self::setFileNotWriteable($notExistingAndNotWriteableDirectoryPath);
        $this->assertDirectoryNotIsWritable($notExistingAndNotWriteableDirectoryPath);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        self::deleteFile(self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH);
    }

    /**
     * @dataProvider dumpReferenceConfigurationFileProvider
     *
     * @runInSeparateProcess
     */
    public function testDumpReferenceConfigurationFile(bool $configurationFilePathExists, bool $configurationFileCanBeDumped, ?bool $overwriteExistingConfigurationFile = null): void
    {
        if ($configurationFilePathExists) {
            $configurationFilePath = $configurationFileCanBeDumped ? self::EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH : self::EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH;
        } else {
            $configurationFilePath = $configurationFileCanBeDumped ? self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH : self::NOT_EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH;
        }

        $this->setUpConfigurationFilePath($configurationFilePath);

        $userInputs = array();

        if ($configurationFilePathExists) {
            $expectedOutput = <<<EOF
An existing drupal-debug configuration file has been found at the following location:
--> "$configurationFilePath"

Would you like to overwrite it?

EOF;

            if ($overwriteExistingConfigurationFile) {
                $userInputs = array('yes');

                $expected = $configurationFileCanBeDumped;
                $expectedOutput .= $this->getDumpConfigurationFileExpectedOutput($configurationFileCanBeDumped, false, $configurationFilePath);
            } else {
                $userInputs = array('no');

                $expected = true;
                $expectedOutput .= <<<EOF
OK, let's keep it like this then!

EOF;
            }
        } else {
            $expected = $configurationFileCanBeDumped;
            $expectedOutput = $this->getDumpConfigurationFileExpectedOutput($configurationFileCanBeDumped, !$configurationFilePathExists, $configurationFilePath);
        }

        $IO = new BufferIO($userInputs);

        $this->assertSame($expected, $this->getManageConfigurationHelper($IO)->dumpReferenceConfigurationFile());

        if ($configurationFileCanBeDumped) {
            $this->assertFileExists($configurationFilePath);
        }

        $this->assertSame($expectedOutput, $IO->getOutput());
    }

    public function dumpReferenceConfigurationFileProvider(): array
    {
        return array(
            array(false, false),
            array(false, true),
            array(true, true, false),
            array(true, false, true),
            array(true, true, true),
        );
    }

    /**
     * @dataProvider warnAboutPotentialConfigurationChangesProvider
     *
     * @runInSeparateProcess
     */
    public function testWarnAboutPotentialConfigurationChanges(bool $configurationFilePathExists): void
    {
        $configurationFilePath = $configurationFilePathExists ? self::EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH : self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH;

        $this->setUpConfigurationFilePath($configurationFilePath);

        if ($configurationFilePathExists) {
            $expectedOutput = <<<EOF
A custom drupal-debug configuration file has been found at the following location:
--> "$configurationFilePath"

The drupal-debug configuration might have change in the freshly updated code.

If you encounter any problem after this update, it will surely be related to configuration. Please refer to the documentation and the release changelog to fix it.

You can alternatively dump the reference configuration file with the dedicated command "drupal-debug:dump-reference-configuration-file".

EOF;
        } else {
            $expectedOutput = '';
        }

        $IO = new BufferIO();

        $this->assertTrue($this->getManageConfigurationHelper($IO)->warnAboutPotentialConfigurationChanges());

        $this->assertSame($expectedOutput, $IO->getOutput());
    }

    public function warnAboutPotentialConfigurationChangesProvider(): array
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testAskForConfigurationFileDeletionWhenTheConfigurationFilePathDoesNotExist(): void
    {
        $this->setUpConfigurationFilePath(self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH);

        $IO = new BufferIO();

        $this->assertTrue($this->getManageConfigurationHelper($IO)->askForConfigurationFileDeletion());
        $this->assertSame('', $IO->getOutput());
    }

    /**
     * @dataProvider askForConfigurationFileDeletionProvider
     *
     * @runInSeparateProcess
     */
    public function testAskForConfigurationFileDeletion(bool $deleteExistingConfigurationFile, ?bool $configurationFileCanBeDeleted = null): void
    {
        if ($deleteExistingConfigurationFile) {
            if ($configurationFileCanBeDeleted) {
                $configurationFilePath = self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH;

                self::writeFile($configurationFilePath, 'foo:');
            } else {
                $configurationFilePath = self::EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH;
            }
        } else {
            $configurationFilePath = self::EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH;
        }

        $this->setUpConfigurationFilePath($configurationFilePath);

        $expectedOutput = <<<EOF
The drupal-debug configuration file is going to be useless: it should be deleted.

It has been found at the following location:
--> "$configurationFilePath"

Would you like to delete it?

EOF;

        if ($deleteExistingConfigurationFile) {
            $userInputs = array('yes');

            $expected = $configurationFileCanBeDeleted;

            if ($configurationFileCanBeDeleted) {
                $expectedOutput .= <<<EOF
The drupal-debug configuration file has been successfully deleted.

EOF;
            } else {
                $expectedOutput .= <<<EOF
The drupal-debug configuration file could not be deleted.

EOF;
            }
        } else {
            $userInputs = array('no');

            $expected = true;
            $expectedOutput .= <<<EOF
OK, let's keep it!

EOF;
        }

        $IO = new BufferIO($userInputs);

        $this->assertSame($expected, $this->getManageConfigurationHelper($IO)->askForConfigurationFileDeletion());

        if ($deleteExistingConfigurationFile && $configurationFileCanBeDeleted) {
            $this->assertFileNotExists($configurationFilePath);
        }

        $this->assertSame($expectedOutput, $IO->getOutput());
    }

    public function askForConfigurationFileDeletionProvider(): array
    {
        return array(
            array(false),
            array(true, false),
            array(true, true),
        );
    }

    /**
     * @dataProvider toggleOriginalDrupalKernelSubstitutionWhenTheConfigurationFilePathDoesNotExistProvider
     *
     * @runInSeparateProcess
     */
    public function testToggleOriginalDrupalKernelSubstitutionWhenTheConfigurationFilePathDoesNotExist(bool $enabled, bool $configurationFileCanBeDumped): void
    {
        $configurationFilePath = $configurationFileCanBeDumped ? self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH : self::NOT_EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH;

        $this->assertToggleOriginalDrupalKernelSubstitution($configurationFilePath, $enabled, $configurationFileCanBeDumped);
    }

    public function toggleOriginalDrupalKernelSubstitutionWhenTheConfigurationFilePathDoesNotExistProvider(): array
    {
        return array(
            array(false, false),
            array(false, true),
            array(true, true),
            array(true, true),
        );
    }

    /**
     * @dataProvider toggleOriginalDrupalKernelSubstitutionProvider
     *
     * @runInSeparateProcess
     */
    public function testToggleOriginalDrupalKernelSubstitution(bool $enabled, string $configurationTemplateFilePath, bool $configurationFileCanBeDumped): void
    {
        if ($configurationFileCanBeDumped) {
            $configurationFilePath = self::NOT_EXISTING_AND_WRITEABLE_CONFIGURATION_FILE_PATH;

            self::writeFile($configurationFilePath, self::getFileContent($configurationTemplateFilePath));
        } else {
            $configurationFilePath = $configurationTemplateFilePath;
        }

        $this->assertToggleOriginalDrupalKernelSubstitution($configurationFilePath, $enabled, $configurationFileCanBeDumped);
    }

    public function toggleOriginalDrupalKernelSubstitutionProvider(): \Generator
    {
        yield array(false, self::EXISTING_AND_NOT_WRITEABLE_CONFIGURATION_FILE_PATH, false);

        $finder = Finder::create()
            ->files()
            ->in(self::TOGGLE_SUBSTITUTION_CONFIGURATION_FILES_DIRECTORY_PATH);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $filePath = $file->getPathname();

            yield array(false, $filePath, true);
            yield array(true, $filePath, true);
        }
    }

    private function setUpConfigurationFilePath(string $configurationFilePath): void
    {
        \putenv(\sprintf('%s=%s', ConfigurationManager::CONFIGURATION_FILE_PATH_ENVIRONMENT_VARIABLE_NAME, $configurationFilePath));
    }

    private function getManageConfigurationHelper(IOInterface $IO): ManageConfigurationHelper
    {
        return new ManageConfigurationHelper($this->createMock(Composer::class), $IO);
    }

    private function getDumpConfigurationFileExpectedOutput(bool $configurationFileCanBeDumped, bool $shouldDisplayLocation, string $configurationFilePath): string
    {
        if (!$configurationFileCanBeDumped) {
            return "The drupal-debug configuration file could not be dumped.\n";
        }

        if (!$shouldDisplayLocation) {
            return "The drupal-debug configuration file has been successfully dumped.\n";
        }

        return <<<EOF
The drupal-debug configuration file has been successfully dumped at the following location:
--> "$configurationFilePath"

EOF;
    }

    private function assertToggleOriginalDrupalKernelSubstitution(string $configurationFilePath, bool $enabled, bool $configurationFileCanBeDumped): void
    {
        $this->setUpConfigurationFilePath($configurationFilePath);

        $IO = new BufferIO();

        $this->assertSame($configurationFileCanBeDumped, $this->getManageConfigurationHelper($IO)->toggleOriginalDrupalKernelSubstitution($enabled));

        $this->assertSame($this->getDumpConfigurationFileExpectedOutput($configurationFileCanBeDumped, true, $configurationFilePath), $IO->getOutput());

        if ($configurationFileCanBeDumped) {
            $this->assertArraySubset(array(
                'drupal-debug' => array(
                    'substitute_original_drupal_kernel' => array(
                        'enabled' => $enabled,
                    ),
                ),
            ), (new Parser())->parseFile($configurationFilePath));
        }
    }
}
