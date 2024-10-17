<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Card;

use App\Domain\TcgCollector\Card\TcgcCard;
use Money\Currency;
use Money\Money;

final readonly class JpnCard
{
    public function __construct(
        private int $cardId,
        private string $cardName,
        private int $cardSequenceNumber,
        private string $cardUrl,
        private array $prices,
    ) {
    }

    public function getCardId(): int
    {
        return $this->cardId;
    }

    public function getCardName(): string
    {
        return $this->cardName;
    }

    public function getCardSequenceNumber(): int
    {
        return $this->cardSequenceNumber;
    }

    public function getCardUrl(): string
    {
        return $this->cardUrl;
    }

    public function getPrice(): ?Money
    {
        if (0 === count($this->prices)) {
            return null;
        }

        // First try to find a price in JPY for "NM" card.
        if ($prices = array_filter($this->prices, fn (array $price) => 'JPY' === $price['priceCurrency'] && 'NM' === $price['condition'])) {
            return Money::JPY(array_values($prices)[0]['priceAmount']);
        }

        // If not found, use any price in JYP.
        if ($prices = array_filter($this->prices, fn (array $price) => 'JPY' === $price['priceCurrency'])) {
            return Money::JPY(array_values($prices)[0]['priceAmount']);
        }

        // If not found, use price in USD for "Ungraded" card.
        if ($prices = array_filter($this->prices, fn (array $price) => 'USD Cents' === $price['priceCurrency'] && 'Ungraded' === $price['condition'])) {
            return Money::USD(array_values($prices)[0]['priceAmount']);
        }

        // If not found, use first price.
        return new Money($prices[0]['priceAmount'], new Currency($prices[0]['priceCurrency']));
    }

    public function matches(TcgcCard $tcgcCard): bool
    {
        return $this->getCardUrl() === 'https://tcgcollector.com/cards/'.$tcgcCard->getCardId();
    }
}
