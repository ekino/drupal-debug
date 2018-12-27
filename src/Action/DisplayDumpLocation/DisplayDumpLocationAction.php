<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Action\DisplayDumpLocation;

use Ekino\Drupal\Debug\Action\EventSubscriberActionInterface;
use Ekino\Drupal\Debug\Kernel\Event\DebugKernelEvents;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DisplayDumpLocationAction implements EventSubscriberActionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DebugKernelEvents::ON_KERNEL_INSTANTIATION => 'process',
        );
    }

    public function process()
    {
        if (!\class_exists(SourceContextProvider::class)) {
            return;
        }

        $cloner = new VarCloner();
        $dumper = \in_array(\PHP_SAPI, array('cli', 'phpdbg'), true) ? new CliDumper() : new HtmlDumper();

        VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
            (function () {
                list('name' => $name, 'file' => $file, 'line' => $line) = (new SourceContextProvider())->getContext();

                $attr = array();
                if ($this instanceof HtmlDumper) {
                    if (\is_string($file)) {
                        $attr = array(
                            'file' => $file,
                            'line' => $line,
                            'title' => $file,
                        );
                    }
                } else {
                    $name = $file;
                }

                $this->line = \sprintf('%s on line %s:', $this->style('meta', $name, $attr), $this->style('meta', $line));
                $this->dumpLine(0);
            })->bindTo($dumper, $dumper)();

            $dumper->dump($cloner->cloneVar($var));
        });
    }
}
