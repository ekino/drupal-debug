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

namespace Ekino\Drupal\Debug\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class LoggerStack
{
    /**
     * @var Logger[]
     */
    private static $instances = array();

    public static function getInstance(string $channel, string $filePath): Logger
    {
        if (!isset(self::$instances[$key = $channel.$filePath])) {
            self::$instances[$key] = new Logger($channel, array(
                new StreamHandler($filePath),
            ));
        }

        return self::$instances[$key];
    }
}
