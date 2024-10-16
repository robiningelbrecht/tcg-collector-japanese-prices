<?php

require __DIR__.'/../vendor/autoload.php';

use App\Infrastructure\DependencyInjection\ContainerFactory;
use App\Infrastructure\Environment\Settings;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;

$container = ContainerFactory::create();

return DependencyFactory::fromConnection(
    new ConfigurationArray($container->get(Settings::class)->get('doctrine.migrations')),
    new ExistingConnection($container->get(Connection::class))
);
