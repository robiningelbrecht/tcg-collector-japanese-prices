<?php

declare(strict_types=1);

namespace App\Console;

use App\Domain\JpnCards\JpnCardsApi;
use App\Domain\Money\CurrencyApi;
use App\Domain\Money\MoneyFormatter;
use App\Domain\TcgCollector\TcgCollector;
use App\Domain\TcgCollector\TcgcRegion;
use App\Infrastructure\Console\Io;
use App\Infrastructure\Exception\NotFound;
use App\Infrastructure\Serialization\Json;
use League\Flysystem\FilesystemOperator;
use Money\Currency;
use Money\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
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
        private readonly FilesystemOperator $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'username');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new Io($input, $output);
        $username = $input->getArgument('username');

        $io->title('TCG Collector Japanese prices');

        $io->newOperation('Fetching market price for INTL cards...');
        $marketPriceIntl = $this->tcgCollector->getMarketPriceFor($username, TcgcRegion::INTERNATIONAL);

        $io->newOperation('Fetching Japanese TCG Collector sets...');
        $tcgcSets = $this->tcgCollector->getJapaneseSetsInProgress($username);

        $io->newOperation('Fetching JpnCards sets...');
        $jpnSets = $this->jpnCards->getSets();

        $io->newOperation('Fetching exchange rate JPY => USD...');
        $fromCurrency = new Currency('JPY');
        $toCurrency = new Currency('USD');
        $exchange = $this->currencyApi->getExchange($fromCurrency);

        $io->info(sprintf(
            '%s = %s',
            $this->moneyFormatter->formatAsCurrency(Money::JPY(100)),
            $this->moneyFormatter->formatAsCurrency($exchange->convert(Money::JPY(100), $toCurrency)),
        ));

        $io->separator();

        /* @var \App\Domain\TcgCollector\Set\TcgcSet $tcgcSet */
        $json = [];
        $totalCollectionValue = Money::USD(0);
        foreach ($tcgcSets as $tcgcSet) {
            $correspondingJpnSet = $jpnSets->findCorrespondingForTcgcSet($tcgcSet);
            $io->newOperation(sprintf('Processing set "%s (%s)"', $correspondingJpnSet->getSetName(), $correspondingJpnSet->getSetCode()));

            $cardsInCollectionForSet = $this->tcgCollector->getCardsInCollectionForSet(
                userName: $username,
                setId: $tcgcSet->getSetId()
            );
            $jpnCardsForSet = $this->jpnCards->getCardsForSet($correspondingJpnSet->getSetCode());

            /** @var \App\Domain\TcgCollector\Card\TcgcCard $card */
            $countCardsThatCouldBeMatched = 0;
            $countCardsThatCouldBeMatchedWithoutAPrice = 0;
            $countCardsThatCouldNotBeMatched = 0;

            $totalSetValue = Money::USD(0);
            foreach ($cardsInCollectionForSet as $card) {
                try {
                    $correspondingJpnCard = $jpnCardsForSet->findCorrespondingForTcgcCard($card);
                    ++$countCardsThatCouldBeMatched;

                    if (!$price = $correspondingJpnCard->getPrice()) {
                        // No prices found for card, skip.
                        ++$countCardsThatCouldBeMatchedWithoutAPrice;
                        continue;
                    }
                    if ($price->getCurrency()->equals($fromCurrency)) {
                        $price = $exchange->convert($price, $toCurrency);
                    }

                    $totalCardValueInCollection = $price->multiply($card->getCardCount());
                    $json['cards'][] = [
                        'cardId' => $card->getCardId(),
                        'cardName' => $correspondingJpnCard->getCardName(),
                        'cardNumber' => $correspondingJpnCard->getCardSequenceNumber(),
                        'cardPrice' => $price,
                        'cardCount' => $card->getCardCount(),
                        'totalCardValueInCollection' => $totalCardValueInCollection,
                        'setId' => $tcgcSet->getSetId(),
                        'setName' => $correspondingJpnSet->getSetName(),
                    ];
                    $totalSetValue = $totalSetValue->add($totalCardValueInCollection);
                } catch (NotFound) {
                    ++$countCardsThatCouldNotBeMatched;
                    $io->warning(sprintf('* No matching card found for %s', $card->getCardUrl()));
                }
            }
            $totalCollectionValue = $totalCollectionValue->add($totalSetValue);

            $io->info(sprintf('* %d card(s) processed', $countCardsThatCouldBeMatched));
            if ($countCardsThatCouldBeMatchedWithoutAPrice) {
                $io->warning(sprintf('* of which %d card(s) do not have a price', $countCardsThatCouldBeMatchedWithoutAPrice));
            }

            if ($countCardsThatCouldNotBeMatched) {
                $io->warning(sprintf('* %d card(s) could not be matched', $countCardsThatCouldNotBeMatched));
            }
            $io->info(sprintf('* Set has an estimated value of %s', $this->moneyFormatter->formatAsCurrency($totalSetValue)));
        }

        $json['totalCollectionValue'] = [
            'jp' => $totalCollectionValue,
            'intl' => $marketPriceIntl,
        ];

        $io->separator();

        $io->newOperation(sprintf(
            'Collection has an estimated value of %s',
            $this->moneyFormatter->formatAsCurrency($totalCollectionValue))
        );

        $this->filesystem->write('output/collection.json', Json::encode($json));
        $io->newOperation('Saved output in output/collection.json');

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
