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

namespace Ekino\Drupal\Debug\Composer\Helper;

use Composer\Composer;
use Composer\IO\IOInterface;
use Ekino\Drupal\Debug\ActionMetadata\ActionMetadataManager;
use Ekino\Drupal\Debug\Composer\Command\DumpReferenceConfigurationFileCommand;
use Ekino\Drupal\Debug\Configuration\ActionsConfiguration;
use Ekino\Drupal\Debug\Configuration\ConfigurationManager;
use Ekino\Drupal\Debug\Configuration\DefaultsConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration as DefaultsConfigurationModel;
use Ekino\Drupal\Debug\Configuration\SubstituteOriginalDrupalKernelConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
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

    private $configurationManager;

    /**
     * @param Composer    $composer
     * @param IOInterface $IO
     */
    public function __construct(Composer $composer, IOInterface $IO)
    {
        $this->composer = $composer;
        $this->IO = $IO;

        $this->configurationManager = ConfigurationManager::getInstance();
    }

    public function dumpReferenceConfigurationFile(): bool
    {
        $configurationFilePath = $this->configurationManager->getConfigurationFilePath();
        if ($configurationFilePathExists = $this->configurationManager->doesConfigurationFilePathExists()) {
            $this->IO->write(array(
                '<comment>An existing drupal-debug configuration file has been found at the following location:</comment>',
                \sprintf('<comment>--> "%s"</comment>', \realpath($configurationFilePath)),
                '',
            ));

            if (!$this->IO->askConfirmation('<question>Would you like to overwrite it?</question>')) {
                $this->IO->write(array(
                    '',
                    "<info>OK, let's keep it like this then!</info>",
                ));

                return true;
            }

            $this->IO->write('');
        }

        return $this->dumpConfigurationFile($configurationFilePath, $this->getReferenceConfigurationContent(), !$configurationFilePathExists);
    }

    // TODO: a real configuration update path
    public function warnAboutPotentialConfigurationChanges(): bool
    {
        if (!$this->configurationManager->doesConfigurationFilePathExists()) {
            return true;
        }

        $this->IO->write(array(
            '<comment>A custom drupal-debug configuration file has been found at the following location:</comment>',
            \sprintf('<comment>--> "%s"</comment>', \realpath($this->configurationManager->getConfigurationFilePath())),
            '',
            '<comment>The drupal-debug configuration might have change in the freshly updated code.</comment>',
            '',
            '<comment>If you encounter any problem after this update, it will surely be related to configuration. Please refer to the documentation and the release changelog to fix it.</comment>',
            '',
            \sprintf('<comment>You can alternatively dump the reference configuration file with the dedicated command "%s".</comment>', DumpReferenceConfigurationFileCommand::NAME),
        ));

        return true;
    }

    public function askForConfigurationFileDeletion(): bool
    {
        if (!$this->configurationManager->doesConfigurationFilePathExists()) {
            return true;
        }

        $this->IO->write(array(
            '<comment>The drupal-debug configuration file is going to be useless: it should be deleted.</comment>',
            '',
            '<info>It has been found at the following location:</info>',
            \sprintf('<info>--> "%s"</info>', \realpath($configurationFilePath = $this->configurationManager->getConfigurationFilePath())),
            '',
        ));

        if (!$this->IO->askConfirmation('Would you like to delete it?')) {
            $this->IO->write(array(
                '',
                "<info>OK, let's keep it!</info>",
            ));

            return true;
        }

        $this->IO->write('');

        if (!@\unlink($configurationFilePath)) {
            $this->IO->writeError('<error>The drupal-debug configuration file could not be deleted.</error>');

            return false;
        }

        $this->IO->write('<info>The drupal-debug configuration file has been successfully deleted.</info>');

        return true;
    }

    /**
     * @param bool $enabled
     */
    public function toggleOriginalDrupalKernelSubstitution(bool $enabled): bool
    {
        $configurationFilePath = $this->configurationManager->getConfigurationFilePath();

        if ($this->configurationManager->doesConfigurationFilePathExists()) {
            $configurationFileContent = $this->getCurrentConfigurationContent($configurationFilePath);
            if (\is_array($configurationFileContent) && isset($configurationFileContent['drupal-debug'])) {
                if (isset($configurationFileContent['drupal-debug']['substitute_original_drupal_kernel']) && \is_array($configurationFileContent['drupal-debug']['substitute_original_drupal_kernel'])) {
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

        return $this->dumpConfigurationFile($configurationFilePath, $configurationFileContent, true);
    }

    /**
     * @return array
     */
    private function getReferenceConfigurationContent(): array
    {
        return (new Parser())->parse((new YamlReferenceDumper())->dump(new class($this->configurationManager) implements ConfigurationInterface {
            private $configurationManager;

            public function __construct(ConfigurationManager $configurationManager)
            {
                $this->configurationManager = $configurationManager;
            }

            /**
             * {@inheritdoc}
             */
            public function getConfigTreeBuilder(): TreeBuilder
            {
                $treeBuilder = new TreeBuilder();
                /** @var ArrayNodeDefinition $rootNode */
                $rootNode = $treeBuilder->root(ConfigurationManager::ROOT_KEY);
                $rootNode
                    ->children()
                        ->append((new DefaultsConfiguration())->getArrayNodeDefinition(new TreeBuilder()))
                        ->append((new ActionsConfiguration((new ActionMetadataManager())->all(), $defaultsConfiguration = new DefaultsConfigurationModel($this->configurationManager->getProcessedDefaultsConfiguration(array()))))->getArrayNodeDefinition(new TreeBuilder()))
                        ->append((new SubstituteOriginalDrupalKernelConfiguration($defaultsConfiguration))->getArrayNodeDefinition(new TreeBuilder()))
                    ->end();

                return $treeBuilder;
            }
        }));
    }

    /**
     * @param string $configurationFilePath
     *
     * @return mixed
     */
    private function getCurrentConfigurationContent(string $configurationFilePath)
    {
        return (new Parser())->parseFile($configurationFilePath);
    }

    /**
     * @param string $configurationFilePath
     * @param array  $configurationFileContent
     * @param bool   $displayLocation
     */
    private function dumpConfigurationFile(string $configurationFilePath, array $configurationFileContent, bool $displayLocation): bool
    {
        try {
            (new Filesystem())->dumpFile($configurationFilePath, (new Dumper())->dump($configurationFileContent, 5));
        } catch (IOException $e) {
            $this->IO->writeError('<error>The drupal-debug configuration file could not be dumped.</error>');

            return false;
        }

        if ($displayLocation) {
            $this->IO->write(array(
                '<info>The drupal-debug configuration file has been successfully dumped at the following location:</info>',
                \sprintf('<info>--> "%s"</info>', \realpath($configurationFilePath)),
            ));
        } else {
            $this->IO->write('<info>The drupal-debug configuration file has been successfully dumped.</info>');
        }

        return true;
    }
}
