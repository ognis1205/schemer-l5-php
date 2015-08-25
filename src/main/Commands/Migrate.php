<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use SOkawa\Batch\Support\Boilerplates\ExportingBoilerplate;
use SOkawa\Batch\Support\Boilerplates\MigrationBoilerplate;

/**
 * Represents 'migrate' command.
 *
 * @author Shingo OKAWA
 */
class Migrate extends Export
{
    /**
     * Holds the command name.
     * @var string
     */
    protected $name = "export:migrate";

    /**
     * Holds the command description.
     * @var string
     */
    protected $description = "Generate Migrator implementations from your database.";

    /**
     * Constructor.
     *
     * @param ExportingManager $manager the currently handling manager.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Executes defined task when the assigned command is called.
     */
    public function fire()
    {
        $this->comment("preparing the seeder for database {$this->getDatabase()}.");
        $ignoring = $this->option("ignore");
        $boilerplate = new MigrationBoilerplate(
            $this->option("connection")
        );

        if (empty($ignore)) {
            $boilerplate->prepare()->dump();
        } else {
            $tables = explode(",", str_replace(" ", "", $ignoring));
            $boilerplate->ignore($tables)->prepare()->dump();
            foreach (ExportingBoilerplate::$ignoring as $table) {
                $this->comment("ignoring the {$table} table.");
            }
        }

        $formatter = $this->getHelperSet()->get("formatter");
        $message   = [
            "success!",
            "databse migrate class generated in: {$output}."
        ];
        $block     = $formatter->formatBlock(
            $message,
            "info",
            true
        );
        $this->line($block);
    }

    /**
     * Returns the resulting file name.
     *
     * @return string the resulting fully qualified file name.
     */
    protected function getOptions()
    {
        return [
            [
                "ignore",
                "i",
                InputOption::VALUE_OPTIONAL,
                "ignore tables to export, must be formatted in CSV",
                null
            ],
            [
                "connection",
                "c",
                InputOption::VALUE_OPTIONAL,
                "specify the exporting source connection.",
                null
            ]
        ];
    }
}