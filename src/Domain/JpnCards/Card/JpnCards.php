<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Card;

use App\Domain\TcgCollector\Card\TcgcCard;
use App\Infrastructure\Exception\NotFound;
use App\Infrastructure\ValueObject\Collection;

final class JpnCards extends Collection
{
    public function getItemClassName(): string
    {
        return JpnCard::class;
    }

    public function findCorrespondingForTcgcCard(TcgcCard $tcgcCard): JpnCard
    {
        /** @var JpnCard $jpnCard */
        foreach ($this as $jpnCard) {
            if (!$jpnCard->matches($tcgcCard)) {
                continue;
            }

            return $jpnCard;
        }
        throw new NotFound('No matching JpnCard found for TcgcCard '.$tcgcCard->getCardId());
    }
}
