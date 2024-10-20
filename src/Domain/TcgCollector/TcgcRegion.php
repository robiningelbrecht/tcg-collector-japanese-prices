<?php

declare(strict_types=1);

namespace App\Domain\TcgCollector;

enum TcgcRegion: string
{
    case ALL = 'dashboard';
    case INTERNATIONAL = 'dashboard/intl';
    case JAPAN = 'dashboard/jp';
}
