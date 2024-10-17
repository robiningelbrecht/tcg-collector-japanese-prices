<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector\Set;

final readonly class TcgcSet
{
    public function __construct(
        public int $setId,
        public string $setMachineName,
    ) {
    }

    public function getSetId(): int
    {
        return $this->setId;
    }

    public function getSetMachineName(): string
    {
        return $this->setMachineName;
    }
}
