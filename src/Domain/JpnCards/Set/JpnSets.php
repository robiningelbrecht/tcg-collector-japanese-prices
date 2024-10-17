<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Set;

use App\Domain\TcgCollector\Set\TcgcSet;
use App\Infrastructure\Exception\NotFound;
use App\Infrastructure\ValueObject\Collection;

final class JpnSets extends Collection
{
    public function getItemClassName(): string
    {
        return JpnSet::class;
    }

    public function findCorrespondingForTcgcSet(TcgcSet $tcgcSet): JpnSet
    {
        /** @var JpnSet $jpnSet */
        foreach ($this as $jpnSet) {
            if (!$jpnSet->matches($tcgcSet)) {
                continue;
            }

            return $jpnSet;
        }

        throw new NotFound('No matching JpnSet found for TcgcSet '.$tcgcSet->getSetMachineName());
    }
}
