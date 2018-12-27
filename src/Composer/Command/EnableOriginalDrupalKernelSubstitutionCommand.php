<?php

declare(strict_types=1);

namespace Ekino\Drupal\Debug\Composer\Command;

use Composer\Command\BaseCommand;
use Ekino\Drupal\Debug\Composer\Helper\ManageConfigurationHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableOriginalDrupalKernelSubstitutionCommand extends BaseCommand
{
    /**
     * @var string
     */
    const NAME = 'drupal-debug:enable-original-drupal-kernel-substitution';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new ManageConfigurationHelper($this->getComposer(), $this->getIO()))->toggleOriginalDrupalKernelSubstitution(true);
    }
}
