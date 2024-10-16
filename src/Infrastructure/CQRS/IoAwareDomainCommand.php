<?php

namespace App\Infrastructure\CQRS;

use Symfony\Component\Console\Style\SymfonyStyle;

interface IoAwareDomainCommand
{
    public function getIo(): SymfonyStyle;
}
