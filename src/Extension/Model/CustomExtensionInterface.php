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

namespace Ekino\Drupal\Debug\Extension\Model;

interface CustomExtensionInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getRootPath(): string;

    /**
     * @return string
     */
    public function getMachineName(): string;
}
