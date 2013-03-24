<?php

namespace Acts\SphinxRealTimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Populate the search index
 */
class ConfigCommand extends ContainerAwareCommand
{

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('acts:sphinx:config')
            ->setDescription('Output sphinx config file with configuration provided')
        ;
    }


    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $builder \Acts\SphinxRealTimeBundle\Service\ConfigBuilder */
        $builder = $this->getContainer()->get('acts.sphinx_realtime.config_builder');
        $configs = $builder->build();

        $root = $this->getContainer()->getParameter('kernel.root_dir').'/config/';

        foreach ($configs as $id => $config) {
            $filename = $root.'sphinx.'.$id.'.cfg';
            file_put_contents($filename, $config);
            $output->writeln('Written configuration for Sphinx client "'.$id.'" to '.$filename);
        }
        $output->writeln('All Sphinx configuration generated - you may need to restart searchd');
    }

}
