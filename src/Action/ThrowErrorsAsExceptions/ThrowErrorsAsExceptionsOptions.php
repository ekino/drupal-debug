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

namespace Ekino\Drupal\Debug\Action\ThrowErrorsAsExceptions;

use Ekino\Drupal\Debug\Configuration\LoggerConfigurationTrait;
use Ekino\Drupal\Debug\Configuration\Model\ActionConfiguration;
use Ekino\Drupal\Debug\Configuration\Model\DefaultsConfiguration;
use Ekino\Drupal\Debug\Option\OptionsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class ThrowErrorsAsExceptionsOptions implements OptionsInterface
{
    use LoggerConfigurationTrait;

    /**
     * @var int
     */
    private $levels;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param int                  $levels
     * @param LoggerInterface|null $logger
     */
    public function __construct(int $levels, ?LoggerInterface $logger)
    {
        $this->levels = $levels;
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function getLevels(): int
    {
        return $this->levels;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public static function addConfiguration(NodeBuilder $nodeBuilder, DefaultsConfiguration $defaultsConfiguration): void
    {
        $nodeBuilder
            ->integerNode('levels')
                ->isRequired()
                ->defaultValue(E_ALL & ~E_WARNING & ~E_USER_WARNING)
            ->end();

        self::addLoggerConfigurationNodeFromDefaultsConfiguration($nodeBuilder, $defaultsConfiguration);
    }

    public static function getOptions(string $appRoot, ActionConfiguration $actionConfiguration): OptionsInterface
    {
        $processedConfiguration = $actionConfiguration->getProcessedConfiguration();

        //todo : evaluate les const E_* passé en string dans la conf ?

        return new self($processedConfiguration['levels'], self::getConfiguredLogger($actionConfiguration));
    }
}
