<?php

use App\Infrastructure\Environment\Settings;

return [
    'doctrine' => [
        // Enables or disables Doctrine metadata caching
        // for either performance or convenience during development.
        'dev_mode' => 'dev',
        // Path where Doctrine will cache the processed metadata
        // when 'dev_mode' is false.
        'cache_dir' => Settings::getAppRoot().'/var/cache/doctrine',
        // List of paths where Doctrine will search for metadata.
        // Metadata can be either YML/XML files or PHP classes annotated
        // with comments or PHP8 attributes.
        'metadata_dirs' => [
            Settings::getAppRoot().'/src/Domain',
            Settings::getAppRoot().'/src/Infrastructure',
        ],
        // The parameters Doctrine needs to connect to your database.
        // These parameters depend on the driver (for instance the 'pdo_sqlite' driver
        // needs a 'path' parameter and doesn't use most of the ones shown in this example).
        // Refer to the Doctrine documentation to see the full list
        // of valid parameters: https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/configuration.html
        'connections' => [
            'default' => [
                'driver' => 'pdo_sqlite',
                'path' => Settings::getAppRoot().'/database/tcg-collector.db',
            ],
        ],
        'migrations' => [
            'table_storage' => [
                'table_name' => 'doctrine_migration_versions',
                'version_column_name' => 'version',
                'version_column_length' => 1024,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],
            'migrations_paths' => [
                'App\Migrations' => Settings::getAppRoot().'/migrations',
            ],
            'all_or_nothing' => true,
            'transactional' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'none',
            'connection' => null,
            'em' => null,
        ],
    ],
];
