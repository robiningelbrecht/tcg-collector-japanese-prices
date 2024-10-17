<?php

declare(strict_types=1);

namespace App\Domain\Money;

use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;

final readonly class CurrencyApi
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
            'base_uri' => 'https://cdn.jsdelivr.net/npm/@fawazahmed0/',
            RequestOptions::VERIFY => false,
        ], $options);

        $response = $this->client->request($method, $path, $options);

        return Json::decode($response->getBody()->getContents());
    }

    public function getExchange(Currency $currency): Converter
    {
        $response = $this->request(sprintf('currency-api@latest/v1/currencies/%s.min.json', strtolower((string) $currency)));

        $exchangeRates = [];
        foreach ($response[strtolower((string) $currency)] as $currencyIsoCode => $rate) {
            $exchangeRates[strtoupper($currencyIsoCode)] = (string) $rate;
        }

        return new Converter(new ISOCurrencies(), new FixedExchange([
            (string) $currency => $exchangeRates,
        ]));
    }
}
