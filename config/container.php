<?php

use App\Console\RefreshJapanesePricesConsoleCommand;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandBus;
use App\Infrastructure\Environment\Settings;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use olvlvl\ComposerAttributeCollector\Attributes;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

return [
    Settings::class => DI\factory([Settings::class, 'load']),
    Application::class => function (ContainerInterface $container) {
        $application = new Application();
        $application->add($container->get(RefreshJapanesePricesConsoleCommand::class));

        return $application;
    },
    CommandBus::class => function (ContainerInterface $container) {
        $commandBus = new CommandBus();

        foreach (Attributes::findTargetClasses(AsCommandHandler::class) as $target) {
            $commandBus->subscribeCommandHandler($container->get($target->name));
        }

        return $commandBus;
    },
    // Doctrine Dbal.
    Connection::class => function (Settings $settings): Connection {
        return DriverManager::getConnection($settings->get('doctrine.connections.default'));
    },
    FilesystemOperator::class => DI\create(Filesystem::class)->constructor(new LocalFilesystemAdapter(
        Settings::getAppRoot()
    )),
];
