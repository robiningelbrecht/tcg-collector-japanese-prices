<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector\Card;

use App\Infrastructure\ValueObject\Collection;

final class TcgcCards extends Collection
{
    public function getItemClassName(): string
    {
        return TcgcCard::class;
    }
}
