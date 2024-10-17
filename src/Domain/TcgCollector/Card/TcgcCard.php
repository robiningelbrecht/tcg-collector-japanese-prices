<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector\Card;

final readonly class TcgcCard
{
    public function __construct(
        private int $cardId,
        private int $cardCount,
    ) {
    }

    public function getCardCount(): int
    {
        return $this->cardCount;
    }

    public function getCardId(): int
    {
        return $this->cardId;
    }

    public function getCardUrl(): string
    {
        return 'https://www.tcgcollector.com/cards/'.$this->getCardId();
    }
}
