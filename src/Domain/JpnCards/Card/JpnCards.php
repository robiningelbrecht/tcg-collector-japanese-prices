<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Card;

use App\Infrastructure\ValueObject\Collection;

final class JpnCards extends Collection
{
    public function getItemClassName(): string
    {
        return JpnCard::class;
    }
}
