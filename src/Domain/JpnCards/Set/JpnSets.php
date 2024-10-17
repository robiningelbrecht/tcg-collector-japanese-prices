<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Set;

use App\Infrastructure\ValueObject\Collection;

final class JpnSets extends Collection
{
    public function getItemClassName(): string
    {
        return JpnSet::class;
    }
}
