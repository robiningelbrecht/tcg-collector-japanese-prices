<?php

declare(strict_types=1);

namespace App\Domain\JpnCards;

use App\Domain\JpnCards\Card\JpnCard;
use App\Domain\JpnCards\Card\JpnCards;
use App\Domain\JpnCards\Set\JpnSet;
use App\Domain\JpnCards\Set\JpnSets;
use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final readonly class JpnCardsApi
{
    public function __construct(
        private Client $client,
    ) {
    }

    private function request(
        string $path,
        string $method = 'GET',
        array $options = []): array
    {
        $options = array_merge([
            'base_uri' => 'https://www.jpn-cards.com/v2/',
            RequestOptions::VERIFY => false,
        ], $options);

        $response = $this->client->request($method, $path, $options);

        return Json::decode($response->getBody()->getContents());
    }

    public function getSets(): JpnSets
    {
        return JpnSets::fromArray(array_map(
            fn (array $set) => new JpnSet(
                setId: $set['id'],
                setName: $set['name'],
                setCode: $set['set_code'],
                sourceUrl: $set['source_url'],
            ),
            $this->request('set/')
        ));
    }

    public function getCardsForSet(string $setCode): JpnCards
    {
        return JpnCards::fromArray(array_map(
            fn (array $card) => new JpnCard(
                cardId: $card['id'],
                cardName: $card['name'],
                cardSequenceNumber: $card['sequenceNumber'],
                cardUrl: $card['cardUrl'],
                prices: $card['prices'],
            ),
            $this->request('card/set_code='.$setCode)['data']
        ));
    }
}
