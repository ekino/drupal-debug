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

use Symfony\Component\Debug\ErrorHandler as BaseErrorHandler;

// TODO : useless if https://github.com/symfony/symfony/pull/29869 is merged
class ErrorHandler extends BaseErrorHandler
{
    /**
     * {@inheritdoc}
     */
    public function handleError($type, $message, $file, $line): bool
    {
        $result = parent::handleError($type, $message, $file, $line);

        \set_error_handler(array(
            $this,
            __FUNCTION__,
        ));

        return $result;
    }
}
