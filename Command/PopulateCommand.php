<?php

namespace Acts\SphinxRealTimeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Acts\SphinxRealTimeBundle\Manager\IndexManager;

/**
 * Populate the search index
 */
class PopulateCommand extends ContainerAwareCommand
{

    /** @var \Acts\SphinxRealTimeBundle\Manager\IndexManager */
    private $indexManager;

    /** @var \Acts\SphinxRealTimeBundle\Manager\ClientManager */
    private $clientManager;

    /** @var \Acts\SphinxRealTimeBundle\Provider\ProviderRegistry */
    private $providerRegistry;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('acts:sphinx:populate')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to repopulate')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not reset index before populating')
            ->setDescription('Populates search indexes from providers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->indexManager = $this->getContainer()->get('acts.sphinx_realtime.index_manager');
        $this->clientManager = $this->getContainer()->get('acts.sphinx_realtime.client_manager');
        $this->providerRegistry = $this->getContainer()->get('acts.sphinx_realtime.provider_registry');
        //$this->resetter = $this->getContainer()->get('foq_elastica.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index  = $input->getOption('index');
        $reset  = $input->getOption('no-reset') ? false : true;

        if (null !== $index) {
            $this->populateIndex($output, $index, $reset);
        }
        else {
            $indexes = array_keys($this->indexManager->getIndexes());

            foreach ($indexes as $index) {
                $this->populateIndex($output, $index, $reset);
            }
        }
    }

    private function populateIndex(OutputInterface $output, $index, $reset)
    {
        $index = $this->indexManager->getById($index);

        if ($reset) {
            //TODO - truncate command is not implemented until Sphinx 2.1
            //$output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
        }

        $provider = $this->providerRegistry->getIndexProvider($index->getId());

        $loggerClosure = function($message) use ($output, $index) {
            $output->writeln(sprintf('<info>Populating</info> %s, %s', $index->getId(), $message));
        };

        $provider->populate($loggerClosure);

        $output->writeln(sprintf('<info>Refreshing</info> <comment>%s</comment>', $index->getId()));
    }

}
