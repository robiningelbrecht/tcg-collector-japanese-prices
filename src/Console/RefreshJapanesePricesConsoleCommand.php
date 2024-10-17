<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\JpnCards\JpnCardsApi;
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
        private readonly JpnCardsApi $jpnCards,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // var_dump($this->tcgCollector->getCardsInCollectionForSet('Frogfuhrer', 11575));

        // var_dump($this->jpnCards->getSets());

        var_dump($this->jpnCards->getCardsForSet('neo3'));

        return Command::SUCCESS;
    }
}
