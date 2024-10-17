<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Card;

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

    public function getPrices(): array
    {
        return $this->prices;
    }
}
