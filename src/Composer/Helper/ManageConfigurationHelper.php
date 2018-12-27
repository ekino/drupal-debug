<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Composer\Helper;

use Composer\Composer;
use Composer\IO\IOInterface;
use Ekino\Drupal\Debug\Composer\Command\DumpReferenceConfigurationFileCommand;
use Ekino\Drupal\Debug\Configuration\Configuration;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class ManageConfigurationHelper
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @param Composer    $composer
     * @param IOInterface $IO
     */
    public function __construct(Composer $composer, IOInterface $IO)
    {
        $this->composer = $composer;
        $this->IO = $IO;
    }

    public function dumpReferenceConfigurationFile()
    {
        list($configurationFilePath, $configurationFilePathExists) = ConfigurationManager::getConfigurationFilePathInfo();
        if ($configurationFilePathExists) {
            $this->IO->write(array(
                '<comment>An existing drupal-debug configuration file has been found at the following location :</comment>',
                \sprintf('<comment>--> "%s"</comment>', \realpath($configurationFilePath)),
                '',
            ));

            if (!$this->IO->askConfirmation('<question>Would you like to overwrite it?</question> ')) {
                $this->IO->write(array(
                    '',
                    "<info>OK, let's keep it like this then!</info>",
                ));

                return;
            }

            $this->IO->write('');
        }

        $this->dumpConfigurationFile($configurationFilePath, $this->getReferenceConfigurationContent(), $configurationFilePathExists);
    }

    // TODO : a real configuration update path
    public function warnAboutPotentialConfigurationChanges()
    {
        list($configurationFilePath, $configurationFilePathExists) = ConfigurationManager::getConfigurationFilePathInfo();
        if (!$configurationFilePathExists) {
            return;
        }

        $this->IO->write(array(
            '<comment>A custom drupal-debug configuration file has been found at the following location :</comment>',
            \sprintf('<comment>--> "%s"</comment>', \realpath($configurationFilePath)),
            '',
            '<comment>The drupal-debug configuration might have change in the freshly updated code.</comment>',
            '',
            '<comment>If you encounter any problem after this update, it will surely be related to configuration. Please refer to the documentation and the release changelog to fix it.</comment>',
            '',
            \sprintf('<comment>You can alternatively dump the reference configuration file with the dedicated command "%s"</comment>', DumpReferenceConfigurationFileCommand::NAME),
        ));
    }

    public function askForConfigurationFileDeletion()
    {
        list($configurationFilePath, $configurationFilePathExists) = ConfigurationManager::getConfigurationFilePathInfo();
        if (!$configurationFilePathExists) {
            return;
        }

        $this->IO->write(array(
            '<comment>The drupal-debug configuration file is now useless : it should be deleted.</comment>',
            '',
            '<info>It has been found at the following location :</info>',
            \sprintf('<info>--> "%s"</info>', \realpath($configurationFilePath)),
            '',
        ));

        if (!$this->IO->askConfirmation('Would you like to delete it?')) {
            $this->IO->write(array(
                '',
                "<info>OK, let's keep it!</info>",
            ));

            return;
        }

        $this->IO->write('');

        if (!\unlink($configurationFilePath)) {
            $this->IO->writeError('<error>The file file could not be deleted.</error>');

            return;
        }

        $this->IO->write('<info>The file has been successfully deleted.</info>');
    }

    /**
     * @param bool $enabled
     */
    public function toggleOriginalDrupalKernelSubstitution($enabled)
    {
        list($configurationFilePath, $configurationFilePathExists) = ConfigurationManager::getConfigurationFilePathInfo();
        if ($configurationFilePathExists) {
            $configurationFileContent = $this->getCurrentConfigurationContent($configurationFilePath);
            if (\is_array($configurationFileContent) && isset($configurationFileContent['drupal-debug'])) {
                if (isset($configurationFileContent['drupal-debug']['substitute_original_drupal_kernel'])) {
                    $configurationFileContent['drupal-debug']['substitute_original_drupal_kernel']['enabled'] = $enabled;
                } else {
                    $configurationFileContent['drupal-debug']['substitute_original_drupal_kernel'] = array(
                        'enabled' => $enabled,
                    );
                }
            } else {
                $configurationFileContent = null;
            }
        }

        if (!isset($configurationFileContent)) {
            $configurationFileContent = array(
                'drupal-debug' => array(
                    'substitute_original_drupal_kernel' => array(
                        'enabled' => $enabled,
                    ),
                ),
            );
        }

        $this->dumpConfigurationFile($configurationFilePath, $configurationFileContent, $configurationFilePathExists);
    }

    /**
     * @return array
     */
    private function getReferenceConfigurationContent()
    {
        return (new Parser())->parse((new YamlReferenceDumper())->dump(new Configuration()));
    }

    /**
     * @param string $configurationFilePath
     *
     * @return mixed
     */
    private function getCurrentConfigurationContent($configurationFilePath)
    {
        return (new Parser())->parseFile($configurationFilePath);
    }

    /**
     * @param string $configurationFilePath
     * @param array  $configurationFileContent
     * @param bool   $displayLocation
     */
    private function dumpConfigurationFile($configurationFilePath, array $configurationFileContent, $displayLocation)
    {
        try {
            (new Filesystem())->dumpFile($configurationFilePath, (new Dumper())->dump($configurationFileContent, 4));
        } catch (IOException $e) {
            $this->IO->writeError('<error>The drupal-debug configuration file could not be dumped.</error>');

            return;
        }

        if ($displayLocation) {
            $this->IO->write(array(
                '<info>The drupal-debug configuration file has been successfully dumped at the following location :</info>',
                \sprintf('<info>--> "%s"</info>', \realpath($configurationFilePath)),
            ));
        } else {
            $this->IO->write('<info>The drupal-debug configuration file has been successfully dumped.</info>');
        }
    }
}
