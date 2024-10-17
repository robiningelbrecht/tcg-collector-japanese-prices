<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\TcgCollector\TcgCollector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tcgc:refresh-japanese-prices', description: 'Refresh prices for Japanese cards in collection')]
final class RefreshJapanesePricesConsoleCommand extends Command
{
    public function __construct(
        private readonly TcgCollector $tcgCollector,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        var_dump($this->tcgCollector->getCardsInCollectionForSet('Frogfuhrer', 11575));

        return Command::SUCCESS;
    }
}
