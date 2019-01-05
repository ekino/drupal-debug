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

namespace Ekino\Drupal\Debug\Tests\Unit\Composer\Helper\helper;

use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

// TODO : useless if https://github.com/composer/composer/pull/7869 is merged
class BufferIO extends ConsoleIO
{
    /**
     * @var StreamOutput
     */
    protected $output;

    public function __construct(array $userInputs = array())
    {
        $input = new StringInput('');
        $input->setStream($this->createStream($userInputs));

        $stream = \fopen('php://memory', 'rw');
        if (!\is_resource($stream)) {
            throw new \RuntimeException('The stream could not be opened.');
        }

        parent::__construct($input, new StreamOutput($stream), new HelperSet(array(
            new QuestionHelper(),
        )));
    }

    public function getOutput(): string
    {
        \fseek($this->output->getStream(), 0);

        $output = \stream_get_contents($this->output->getStream());
        if (!\is_string($output)) {
            throw new \RuntimeException('The stream content could not be gotten.');
        }

        $output = (string) \preg_replace_callback("{(?<=^|\n|\x08)(.+?)(\x08+)}", function (array $matches): string {
            $pre = \strip_tags($matches[1]);

            if (\strlen($pre) === \strlen($matches[2])) {
                return '';
            }

            return \rtrim($matches[1])."\n";
        }, $output);

        return $output;
    }

    /**
     * @param string[] $inputs
     *
     * @return resource
     */
    private function createStream(array $inputs)
    {
        $stream = \fopen('php://memory', 'r+', false);
        if (!\is_resource($stream)) {
            throw new \RuntimeException('The stream could not be opened.');
        }

        foreach ($inputs as $input) {
            \fwrite($stream, $input.PHP_EOL);
        }

        \rewind($stream);

        return $stream;
    }
}
