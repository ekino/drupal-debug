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

namespace Ekino\Drupal\Debug\Configuration\Model;

abstract class AbstractConfiguration implements \Serializable
{
    /**
     * @var array
     */
    protected $processedConfiguration;

    /**
     * @param array $processedConfiguration
     */
    public function __construct(array $processedConfiguration)
    {
        $this->processedConfiguration = $processedConfiguration;
    }
}
