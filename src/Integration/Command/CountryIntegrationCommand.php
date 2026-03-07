<?php

namespace App\Integration\Command;

use App\Service\CountryUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:country-integration',
    description: 'Fetch countries from external API and update database',
)]
class CountryIntegrationCommand extends Command
{
    public function __construct(
        private CountryUpdater $countryUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'codes',
            InputArgument::OPTIONAL,
            'Currency codes separated by comma (example: PL,DE,US)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $codesArgument = $input->getArgument('codes');

        $codes = [];

        if ($codesArgument) {
            $codes = array_map(
                'trim',
                explode(',', $codesArgument)
            );

            $io->note('Updating countries: '.implode(', ', $codes));
        } else {
            $io->note('Updating all countries');
        }

        try {
            $this->countryUpdater->update($codes);

            $io->success('Countries successfully updated');
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
