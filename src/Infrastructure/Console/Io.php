<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

final class Io extends SymfonyStyle
{
    public function newOperation(string $message): void
    {
        $this->writeln(sprintf('=> %s', $message));
    }

    public function info(string|array $message): void
    {
        $this->writeln(sprintf('   <info>%s</>', $message));
    }

    public function warning(string|array $message): void
    {
        $this->writeln(sprintf('   <comment>%s</>', $message));
    }

    public function separator(): void
    {
        $this->writeln('');
        $this->writeln('<comment>=============================</>');
        $this->writeln('');
    }
}
