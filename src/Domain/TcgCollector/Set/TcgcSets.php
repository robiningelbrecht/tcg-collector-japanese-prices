<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector\Set;

use App\Infrastructure\ValueObject\Collection;

final class TcgcSets extends Collection
{
    public function getItemClassName(): string
    {
        return TcgcSet::class;
    }
}
