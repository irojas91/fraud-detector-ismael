<?php

namespace App\Infrastructure\Command;

use App\Application\UseCase\DetectFraudUseCase;
use App\Infrastructure\Source\FileSource;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:detect-fraud', description: 'Detects suspicious readings.')]
class DetectFraudCommand extends Command
{
    public function __construct(private DetectFraudUseCase $useCase)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:detect-fraud')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the input file')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (table or csv)', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $format = $input->getOption('format');
        $source = new FileSource($file);

        if (!file_exists($source->path)) {
            $output->writeln(sprintf('<error>File not found: %s</error>', $source->path));
            return Command::FAILURE;
        }

        $suspiciousReadings = $this->useCase->execute($source);

        if ($format === 'csv') {
            $output->writeln('Client,Month,Suspicious,Median');
            foreach ($suspiciousReadings as $reading) {
                $output->writeln(sprintf('%s,%s,%s,%s', $reading->clientId, $reading->month, $reading->suspiciousValue, $reading->median));
            }
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Client', 'Month', 'Suspicious', 'Median']);
        foreach ($suspiciousReadings as $reading) {
            $table->addRow([$reading->clientId, $reading->month, $reading->suspiciousValue, $reading->median]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
