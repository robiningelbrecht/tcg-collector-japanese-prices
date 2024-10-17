<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Set;

use App\Domain\TcgCollector\Set\TcgcSet;

final readonly class JpnSet
{
    public function __construct(
        private int $setId,
        private string $setName,
        private string $setCode,
        private string $sourceUrl,
    ) {
    }

    public function getSetId(): int
    {
        return $this->setId;
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function getSetCode(): string
    {
        return $this->setCode;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function matches(TcgcSet $tcgcSet): bool
    {
        if ($this->getSourceUrl() === 'https://www.tcgcollector.com/cards/jp/'.$tcgcSet->getSetMachineName()) {
            return true;
        }

        return str_contains($this->getSourceUrl(), 'https://www.tcgcollector.com/sets/'.$tcgcSet->getSetId());
    }
}
