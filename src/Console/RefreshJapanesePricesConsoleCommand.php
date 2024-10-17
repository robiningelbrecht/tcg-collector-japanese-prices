<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\JpnCards\JpnCardsApi;
use App\Domain\Money\CurrencyApi;
use App\Domain\Money\MoneyFormatter;
use App\Domain\TcgCollector\TcgCollector;
use App\Infrastructure\Exception\NotFound;
use Money\Currency;
use Money\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tcgc:refresh-japanese-prices', description: 'Refresh prices for Japanese cards in collection')]
final class RefreshJapanesePricesConsoleCommand extends Command implements SignalableCommandInterface
{
    public function __construct(
        private readonly TcgCollector $tcgCollector,
        private readonly JpnCardsApi $jpnCards,
        private readonly CurrencyApi $currencyApi,
        private readonly MoneyFormatter $moneyFormatter,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = 'Frogfuhrer';

        $output->writeln('=> Fetching Japanese TCG Collector sets...');
        $tcgcSets = $this->tcgCollector->getJapaneseSetsInProgress($username);

        $output->writeln('=> Fetching JpnCards sets...');
        $jpnSets = $this->jpnCards->getSets();

        $output->writeln('=> Fetching exchange rate JPY => USD...');
        $fromCurrency = new Currency('JPY');
        $toCurrency = new Currency('USD');
        $exchange = $this->currencyApi->getExchange($fromCurrency);

        $output->writeln(sprintf(
            '   <info>%s = %s</info>',
            $this->moneyFormatter->formatAsCurrency(Money::JPY(100)),
            $this->moneyFormatter->formatAsCurrency($exchange->convert(Money::JPY(100), $toCurrency)),
        ));

        $output->writeln('==========================================');

        /* @var \App\Domain\TcgCollector\Set\TcgcSet $tcgcSet */
        $totalCollectionValue = Money::USD(0);
        foreach ($tcgcSets as $tcgcSet) {
            $correspondingJpnSet = $jpnSets->findCorrespondingForTcgcSet($tcgcSet);
            $output->writeln(sprintf('=> Processing set "%s (%s)"', $correspondingJpnSet->getSetName(), $correspondingJpnSet->getSetCode()));

            $cardsInCollectionForSet = $this->tcgCollector->getCardsInCollectionForSet(
                userName: $username,
                setId: $tcgcSet->getSetId()
            );
            $jpnCardsForSet = $this->jpnCards->getCardsForSet($correspondingJpnSet->getSetCode());

            /** @var \App\Domain\TcgCollector\Card\TcgcCard $card */
            $countCardsThatCouldBeMatched = 0;
            $countCardsThatCouldNotBeMatchedWithoutAPrice = 0;
            $countCardsThatCouldNotBeMatched = 0;

            $totalSetValue = Money::USD(0);
            foreach ($cardsInCollectionForSet as $card) {
                try {
                    $correspondingJpnCard = $jpnCardsForSet->findCorrespondingForTcgcCard($card);
                    ++$countCardsThatCouldBeMatched;

                    if (!$price = $correspondingJpnCard->getPrice()) {
                        // No prices found for card, skip.
                        ++$countCardsThatCouldNotBeMatchedWithoutAPrice;
                        continue;
                    }
                    if ($price->getCurrency()->equals($fromCurrency)) {
                        $price = $exchange->convert($price, $toCurrency);
                    }
                    $totalSetValue = $totalSetValue->add($price->multiply($card->getCardCount()));
                } catch (NotFound) {
                    ++$countCardsThatCouldNotBeMatched;
                    $output->writeln(sprintf('   <comment>* No matching card found for %s</comment>', $card->getCardUrl()));
                }
            }
            $totalCollectionValue = $totalCollectionValue->add($totalSetValue);

            $output->writeln(sprintf('   <info>* %d card(s) processed</info>', $countCardsThatCouldBeMatched));
            if ($countCardsThatCouldNotBeMatchedWithoutAPrice) {
                $output->writeln(sprintf('   <comment>* of which %d card(s) do not have a price</comment>', $countCardsThatCouldNotBeMatchedWithoutAPrice));
            }

            if ($countCardsThatCouldNotBeMatched) {
                $output->writeln(sprintf(
                    '   <comment>* %d card(s) could not be matched</comment>',
                    $countCardsThatCouldNotBeMatched
                ));
            }
            $output->writeln(sprintf(
                '   <info>* Set has an estimated value of %s</info>',
                $this->moneyFormatter->formatAsCurrency($totalSetValue)
            ));
        }

        $output->writeln(sprintf(
            'Collection has an estimated value of %s',
            $this->moneyFormatter->formatAsCurrency($totalCollectionValue))
        );

        return Command::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGTERM, SIGINT];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        return 0;
    }
}
