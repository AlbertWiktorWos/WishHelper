<?php

namespace App\Integration\Command;

use App\Service\CurrencyUpdater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:currency-integration',
    description: 'Fetch currencies from external API and update database',
)]
class CurrencyIntegrationCommand extends Command
{
    public function __construct(
        private CurrencyUpdater $currencyUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'codes',
            InputArgument::OPTIONAL,
            'Currency codes separated by comma (example: USD,EUR,PLN)'
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

            $io->note('Updating currencies: '.implode(', ', $codes));
        } else {
            $io->note('Updating all currencies');
        }

        try {
            $this->currencyUpdater->update($codes);

            $io->success('Currencies successfully updated');
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
