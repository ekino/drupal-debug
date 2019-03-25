# Drupal Debug

[![Build Status](https://travis-ci.org/ekino/drupal-debug.svg?branch=master)](https://travis-ci.org/ekino/drupal-debug)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ekino/drupal-debug/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ekino/drupal-debug/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/ekino/drupal-debug/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ekino/drupal-debug/?branch=master)

------

This library provides an alternative Kernel for Drupal 8 to improve the Developer eXperience during the development process.

This Debug Kernel extends (but substitutes entirely by default) the original Drupal Kernel in order to alter some inner Drupal behaviors.
It is done for one reason: help you develop better and faster!

Once this library is installed, you will be able to experience a « debug mode » during the development process.
For example, you will not need to manually clear the cache anymore when you add or remove a custom service, a route or a module hook implementation.

# Requirements

This library requires that your Drupal project uses [Composer](https://getcomposer.org/). If it is not the case yet, you can check [this Composer template for Drupal projects](https://github.com/drupal-composer/drupal-project
) for example.

It has active support for the [latest released Drupal minor version](https://www.drupal.org/project/drupal/releases) only.

It has active support for the [currently supported versions of PHP](http://php.net/supported-versions.php) only.

# Installation

Require this library as a development dependency.

```
composer require ekino/drupal-debug --dev
```

It **MUST** only be installed as a development dependency. You do not want to use it in production!

# Actions

This library works with « actions ».
An action has an unique goal that improves your development experience with Drupal.

At the moment, all actions are enabled and mandatory but the plan is to make most of them optional, step by step.

Also, you cannot provide your own custom actions yet but this is planned for the future as well.

Here is the list of the current available actions and how they help you:

| Name                               | Description                                                                                                                                                            |
| ---------------------------------- | -----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Disable CSS Aggregation            | Disable CSS files aggregation                                                                                                                                          |
| Disable Dynamic Page Cache         | Disable [Drupal Dynamic Page Cache](https://www.drupal.org/docs/8/core/modules/dynamic-page-cache)                                                                     |
| Disable Internal Page Cache        | Disable [Drupal Internal Page Cache](https://www.drupal.org/docs/8/administering-a-drupal-8-site/internal-page-cache)                                                  |
| Disable JS Aggregation             | Disable JS files aggregation                                                                                                                                           |
| Disable Render Cache               | Disable [Drupal Render Cache](https://www.drupal.org/docs/8/api/render-api/cacheability-of-render-arrays)                                                              |
| Disable Twig Cache                 | Disable Twig Cache                                                                                                                                                     |
| Display Dump Location              | Display location when you use the `dump()` function of the Symfony VarDumper component                                                                                 |
| Display Pretty Exceptions          | Display a better looking exception page and log exceptions (active when the `Request` is handled by the Kernel and if the exceptions are caught)                       |
| Display Pretty Exceptions ASAP     | Display a better looking exception page (active as soon as the Kernel is instantiated)                                                                                 |
| Enable Debug Class Loader          | Enable the `DebugClassLoader` of the Symfony Debug component                                                                                                           |
| Enable Twig Debug                  | Enable Twig Debug mode                                                                                                                                                 |
| ~~Enable Twig Strict Variables~~   | ~~Enable Twig `strict_variables` option~~ (disabled at the moment because the [Drupal core is not ready](https://www.drupal.org/project/drupal/issues/2445705) at all) |
| Throw Errors As Exceptions         | Throw PHP errors as exceptions                                                                                                                                         |
| Watch Container Definitions        | Watch services definitions and service providers files to automatically invalidate the container definition                                                            |
| Watch Modules Hooks Implementations| Watch `.module` files to automatically refresh the modules hooks implementations                                                                                       |
| Watch Routing Definitions          | Watch routing definitions files to automatically rebuild the routes                                                                                                    |

And more to come!

# Configuration

Some actions are configurable with options that can be set in a dedicated configuration file.
However, this configuration file is not mandatory because every option has a default value that is resolved if it is not explicitly defined.

At the moment, those options are not configurable independently for each action (with this file) but this is of course planned.
What is configurable is actually the defaults values of reused options.
If you want to specify options for each actions, you have to [manually use the Debug Kernel](#manually-use-the-debug-kernel).

Here is the default configuration file content (i.e. the default resolved configuration if the configuration file does not exist, or if a key is not defined):
```yaml
# This is the drupal-debug configuration file.
drupal-debug:

    # The defaults values are common values that are reused by different actions.
    defaults:
        cache_directory_path: cache
        logger:
            enabled: true
            channel: drupal-debug
            file_path: logs/drupal-debug.log
        charset: null
        file_link_format: null

    # It is recommended to disable the original Drupal Kernel substitution to run your tests.
    # To programmatically toggle it, use the two dedicated composer commands.
    substitute_original_drupal_kernel:
        enabled: true
        composer_autoload_file_path: vendor/autoload.php

        # If not specified, it fall backs to the default cache directory path.
        cache_directory_path: null
```

By default, the location of this configuration file is the root of the project (the parent directory of the Composer vendor directory).
But it can be defined with the `DRUPAL_DEBUG_CONFIGURATION_FILE_PATH` environment variable.

For performance, the resolved configuration is cached.
By default, the location of this cache is the system temporary directory.
But it can be defined with the `DRUPAL_DEBUG_CONFIGURATION_CACHE_DIRECTORY_PATH` environment variable.

Here is the list of all the actions that have options :

#### Display Pretty Exceptions
* charset:  The charset of the exception page.
* fileLinkFormat: The file link format used to create links to your IDE.
* logger: A `LoggerInterface` instance to log exceptions.

#### Display Pretty Exceptions ASAP
* charset: The charset of the exception page.
* fileLinkFormat: The file link format used to create links to your IDE.

#### Throw Errors As Exceptions
* levels: Required. The bit field of E_* constants for thrown errors.
* logger: A `LoggerInterface` instance to log errors.

#### Watch Container Definitions
* cacheFilePath: Required. The location of the cached container definition file.
* resourcesCollection: Required. An `ResourcesCollection` instance (the resources to watch).

#### Watch Modules Hooks Implementations
* cacheFilePath: Required. The location of the cached modules hooks implementations file.
* resourcesCollection: Required. An `ResourcesCollection` instance (the resources to watch).

#### Watch Routing Definitions
* cacheFilePath: Required. The location of the cached routing file.
* resourcesCollection: Required. An `ResourcesCollection` instance (the resources to watch).

# Composer commands

Here is the list of the provided Composer commands that help you manage the configuration file:

| Command                                                           | Alias                         | Description                                                                     |
| ----------------------------------------------------------------- | ----------------------------- | ------------------------------------------------------------------------------- |
| composer drupal-debug:dump-reference-configuration-file           | None                          | Dump the reference configuration file                                           |
| composer drupal-debug:disable-original-drupal-kernel-substitution | composer drupal-debug:disable | Alter the configuration file to disable the original Drupal Kernel substitution |
| composer drupal-debug:enable-original-drupal-kernel-substitution  | composer drupal-debug:enable  | Alter the configuration file to enable the original Drupal Kernel substitution  |

# Original Drupal Kernel substitution

Substituting the original Drupal Kernel concretely means that every time the `DrupalKernel` class is used somewhere after the Composer autoload file has been required, it is this library `DebugKernel` class that is actually being used, despite appearances!

It is great because any third party libraries interacting with Drupal (such as `drush` for example) will automatically use the Debug Kernel.

However, it could lead to unwanted behaviors, especially during the WIP phase of this library.
This is why you **SHOULD NOT** use the original Drupal Kernel substitution when you run your tests for example. Keep it for the development part only!

# Manually use the Debug Kernel

It is possible to directly use the Debug Kernel in your front controller (typically the `index.php` file in your web exposed directory) to specify options for each actions.
It is also the solution to use the Debug Kernel with the original Drupal Kernel substitution disabled.

You simply need to use the `OptionsStackBuilder` helper class to build an `OptionsStack` instance and pass it to the `DebugKernel` constructor.

Manually setting an option in the options stack overrides the default value defined by the configuration (if it exists).

Here is an example usage:
```php
<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

use Ekino\Drupal\Debug\Kernel\DebugKernel;
use Ekino\Drupal\Debug\Option\OptionsStackBuilder;
use Ekino\Drupal\Debug\Resource\Model\ResourcesCollection;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;

$optionsStack = OptionsStackBuilder::create()
  ->setDisplayPrettyExceptionsOptions('utf-8', 'phpstorm://open?file=%%f&line=%%l', NULL)
  ->setWatchContainerDefinitionsOptions('/tmp/container_definition.php', new ResourcesCollection([
    new FileResource('/var/www/my_drupal_project/web/modules/custom_module/custom_module.services.yml'),
  ]))
  ->getOptionsStack();

$autoloader = require_once 'autoload.php';

$kernel = new DebugKernel('prod', $autoloader, TRUE, NULL, $optionsStack);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
```
