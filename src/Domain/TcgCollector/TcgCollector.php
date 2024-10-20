<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

use App\Domain\TcgCollector\Card\TcgcCard;
use App\Domain\TcgCollector\Card\TcgcCards;
use App\Domain\TcgCollector\Set\TcgcSet;
use App\Domain\TcgCollector\Set\TcgcSets;
use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Money\Money;

final readonly class TcgCollector
{
    public function __construct(
        private Client $client,
    ) {
    }

    private function request(
        string $path,
        string $method = 'GET',
        array $options = []): string
    {
        $options = array_merge([
            'base_uri' => 'https://www.tcgcollector.com/',
            RequestOptions::VERIFY => false,
        ], $options);

        $response = $this->client->request($method, $path, $options);

        return $response->getBody()->getContents();
    }

    public function getMarketPriceFor(string $userName, TcgcRegion $region): Money
    {
        $response = $this->request(
            $region->value,
            'GET',
            [
                RequestOptions::QUERY => [
                    'viewUser' => $userName,
                ],
            ]
        );

        // Suppress any faulty HTML errors.
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($response);

        $x = new \DOMXPath($dom);

        $marketPriceNode = $x->query("//div[@id='dashboard-cards']/div/div[@class='dashboard-card-text']")[3]
            ?? throw new \RuntimeException('Unable to determine market price for '.$region->name);

        $marketPriceIntl = str_replace('$', '', trim($marketPriceNode->textContent));

        return Money::USD((string) ($marketPriceIntl * 100));
    }

    public function getJapaneseSetsInProgress(string $userName): TcgcSets
    {
        $response = $this->request(
            'sets/jp',
            'GET',
            [
                RequestOptions::QUERY => [
                    'viewUser' => $userName,
                    'setSource' => 'cardCollectionInProgress',
                ],
            ]
        );

        $regex = sprintf(
            '/<a href="\/sets\/(?<setId>[\d]+)\/(?<setMachineName>.*)\?viewUser=%s"[\s]*class="set-logo-grid-item-set-logo-container"/U',
            $userName
        );
        if (!preg_match_all($regex, $response, $matches)) {
            throw new \RuntimeException('No sets in progress found, check if the regex needs updating.');
        }

        $sets = TcgcSets::empty();
        foreach ($matches['setId'] as $key => $setId) {
            $sets->add(new TcgcSet(
                setId: (int) $setId,
                setMachineName: $matches['setMachineName'][$key]
            ));
        }

        return $sets;
    }

    public function getCardsInCollectionForSet(string $userName, int $setId): TcgcCards
    {
        $response = $this->request(
            'sets/'.$setId,
            'GET',
            [
                RequestOptions::QUERY => [
                    'viewUser' => $userName,
                    'cardSource' => 'inCardCollection',
                ],
            ]
        );

        $regex = '/window.tcgcollector[\s]*=[\s]*{[\s]*appState:(?<appState>.*),[\s]*}/mi';
        if (!preg_match($regex, $response, $match)) {
            throw new \RuntimeException('AppState could not be determined, check if the regex needs updating.');
        }

        $appState = Json::decode($match['appState']);
        $cardsInCollection = TcgcCards::empty();
        foreach ($appState['cardIdToCardCollectionCardDtoMap'] ?? [] as $cardId => $map) {
            $cardsInCollection->add(new TcgcCard(
                cardId: (int) $cardId,
                cardCount: array_sum(array_map(
                    fn (array $entry) => $entry['cardCount'],
                    $map['entries'] ?? [],
                ))
            ));
        }

        return $cardsInCollection;
    }
}
