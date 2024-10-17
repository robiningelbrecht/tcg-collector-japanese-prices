<?php

declare(strict_types=1);

namespace App\Domain\JpnCards\Set;

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
}
