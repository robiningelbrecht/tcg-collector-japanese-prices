<?php

declare(strict_types=1);

namespace App\Domain\Money;

use Money\Money;

final readonly class JsonSerializableMoney implements \JsonSerializable
{
    public function __construct(
        private Money $money,
    ) {
    }

    public function jsonSerialize(): array
    {
        $json = $this->money->jsonSerialize();
        $json['amount'] = (int) $json['amount'];

        return $json;
    }
}
