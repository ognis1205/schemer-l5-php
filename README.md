# Schemer Package Enables You agile DB migrations

Schemer is a package to extend Laravel 5's basic fucntionality of DB migration.
All functionalities of this package are provided as CLI, hence, once you configure
the DB connections of your application, just run migration commands, then you can
get migration scripts written in PHP, implemented based on "Schema" and "DB"
facades of Laravel5.

## Installation

Install the package via composer, or autoloading it configuring your composer.json:

    $ composer require sokawa/schemer

## Usage

First, register the schemer commands on your own console application, in default,
you can find the registry on "App\Console\Kernel" module, then add configuration
as follows:

    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \SOkawa\Batch\Support\Commands\Migrate::class,
        \SOkawa\Batch\Support\Commands\Seed::class
    ];

Second, write your configuration file "config/exporter.php" for the several migration
settings:

    <?php
    return [
        'migrations' : 'relative_path_to_the_directory_of_migration_files',
        'seeds'      : 'relative_path_to_the_directory_of_seeding_files',
        'dictionary' : [
            'source_table1' : [
                'destination' : 'translated_table_name',
                'columns'     : [
                    'source_column11' : 'translated_column_name11',
                    :
                ]
            ],
                :
                :
            'source_tablen' : [
                    :
                    :
            ]
        ]
    ];

The "dictionary" entry is optional, if the "dictionary" entry is found, then the generating
migration code will be translated in accordance with the defined context.

Finally, now you can be good to go, run the following artisan commands:

    $ php artisan export:migrate
    $ php artisan export:seed