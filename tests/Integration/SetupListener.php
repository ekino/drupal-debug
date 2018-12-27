<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Tests\Integration;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class SetupListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if (!$this->supports($suite)) {
            return;
        }

        $drupalDirectoryPath = \realpath(AbstractTestCase::DRUPAL_DIRECTORY_PATH);
        if (!\is_string($drupalDirectoryPath)) {
            throw new \RuntimeException('The Drupal directory path was not found.');
        }

        $defaultSitesDirectoryPath = \sprintf('%s/sites/default', AbstractTestCase::DRUPAL_DIRECTORY_PATH);
        $settingsFilePath = \sprintf('%s/settings.php', $defaultSitesDirectoryPath);

        $filesystem = new Filesystem();
        if ($filesystem->exists($settingsFilePath)) {
            $filesystem->chmod($defaultSitesDirectoryPath, 0777);

            $filesystem->chmod($settingsFilePath, 0777);
            $filesystem->remove($settingsFilePath);

            $filesystem->remove(AbstractTestCase::DRUPAL_FILES_DIRECTORY_PATH);
        }

        $phpBinary = (new PhpExecutableFinder())->find();
        if (!\is_string($phpBinary)) {
            throw new \RuntimeException('The PHP binary was not found.');
        }

        $drupalInstallationsCount = 0;

        $process = new Process(\sprintf('%s %s/core/scripts/drupal install minimal', $phpBinary, $drupalDirectoryPath));
        $process->mustRun(function ($type, $output) use (&$drupalInstallationsCount) {
            if (\is_int(\strpos($output, 'Congratulations, you installed Drupal!'))) {
                ++$drupalInstallationsCount;
            }
        });

        // We cannot use the exit code because the Drupal command returns 0
        // even if the installation failed. So we rely on the success message
        // that is actually outputted 2 times if everything goes well :drupal:
        if (2 !== $drupalInstallationsCount) {
            throw new \RuntimeException('The Drupal installation failed.');
        }

        $filesystem->chmod($defaultSitesDirectoryPath, 0777);
        $filesystem->chmod($settingsFilePath, 0777);

        if ($filesystem->exists(AbstractTestCase::REFERENCE_FILES_DIRECTORY_PATH)) {
            $filesystem->remove(AbstractTestCase::REFERENCE_FILES_DIRECTORY_PATH);
        }

        $filesystem->rename(AbstractTestCase::DRUPAL_FILES_DIRECTORY_PATH, AbstractTestCase::REFERENCE_FILES_DIRECTORY_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (!$this->supports($suite)) {
            return;
        }
    }

    /**
     * @param TestSuite $suite
     *
     * @return bool
     */
    private function supports(TestSuite $suite)
    {
        return 'integration' === $suite->getName();
    }
}
