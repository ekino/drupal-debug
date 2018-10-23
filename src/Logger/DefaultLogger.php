<?php

namespace Ekino\Drupal\Debug\Logger;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class DefaultLogger
{
    /**
     * @var string
     */
    const CHANNEL = 'drupal-debug';

    /**
     * @var LoggerInterface[]
     */
    private static $instances = array();

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param string $appRoot
     *
     * @return LoggerInterface
     */
    public static function get($appRoot)
    {
        if (!isset(self::$instances[$appRoot])) {
            self::$instances[$appRoot] = new Logger(self::CHANNEL, array(
                new StreamHandler(sprintf('%s/logs/%s.log', $appRoot, self::CHANNEL))
            ));
        }

        return self::$instances[$appRoot];
    }

    /**
     * @param string $appRoot
     * @param LoggerInterface $logger
     */
    public static function set($appRoot, LoggerInterface $logger)
    {
        self::$instances[$appRoot] = $logger;
    }
}
