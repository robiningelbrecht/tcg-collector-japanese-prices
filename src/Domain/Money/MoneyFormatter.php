<?php

declare(strict_types=1);

namespace App\Domain\Money;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

final readonly class MoneyFormatter
{
    private const string LOCALE = 'en_US';

    public function formatAsCurrency(Money $money): string
    {
        return (new IntlMoneyFormatter(
            new \NumberFormatter(self::LOCALE, \NumberFormatter::CURRENCY),
            new ISOCurrencies()
        ))->format($money);
    }
}
