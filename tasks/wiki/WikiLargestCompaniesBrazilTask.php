<?php

namespace Wiki;

use App\Services\WikiLargestCompaniesService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;

class WikiLargestCompaniesBrazilTask extends Command
{
    use LockableTrait;

    private WikiLargestCompaniesService $wikiService;

    public function __construct(?string $name = null)
    {
        parent::__construct();
        $this->wikiService = new WikiLargestCompaniesService();
    }

    protected function configure()
    {
        $this->setName("wiki:import-largest-companies-brazil")
            ->setDescription("Run the import");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock("wiki:import-largest-companies-brazil")) {
            $output->writeln('<error>Task is already being executed by another process.</error>');
            return 0;
        }

        try {
            $this->wikiService->importData();

            $output->writeln('<info>---- Import executed successfully.</info>');
            return 1;
        } catch (\Exception $e) {
            $output->writeln("<error>---- {$e->getMessage()} => Linha: {$e->getLine()}</error>");
            return 0;
        }
    }
}
