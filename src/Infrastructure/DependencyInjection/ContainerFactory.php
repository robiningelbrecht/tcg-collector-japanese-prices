<?php

namespace App\Infrastructure\DependencyInjection;

use App\Infrastructure\Environment\Settings;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    public static function create(): ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(Settings::getAppRoot().'/config/container.php');

        return $builder->build();
    }
}
