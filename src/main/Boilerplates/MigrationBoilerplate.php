<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates;

use \Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SOkawa\Batch\Support\Boilerplates\Intermediate\SchemaParser;
use SOkawa\Batch\Support\Boilerplates\Intermediate\ScriptBuilder;

/**
 * Represents functionality to migrate datas from a database to the other one.
 *
 * @author Shingo OKAWA
 */
class MigrationBoilerplate extends ExportingBoilerplate
{
    /**
     * Enables functionality to parse RDBMS schema.
     */
    use SchemaParser;

    /**
     * Holds the connection identifier.
     * @var string
     */
    protected $connection;

    /**
     * Holds the dictionary instance.
     * @var Dictionary
     */
    protected $dictionary;

    /**
     * Holds the resulting rendered schema.
     * @var array
     */
    protected $schema = [];

    /**
     * Specifies if the conversion is neccessary or not.
     * @var bool
     */
    protected $prepared = false;

    /**
     * Constructor.
     *
     * @param string $connection the currently handling connection name.
     * @param string $dictionary the currently handling dictionary name.
     */
    public function __construct($connection, $dictionary='export.dictionary')
    {
        if (empty($connection)) {
            // TODO: implement appropriate exception.
            throw new Exception('no database specified in config/database.php');
        }
        $this->connection = $connection;
        $this->dictionary = new Dictionary($dictionary);
    }

    /**
     * {@inheritdoc}
     */
    public function dump()
    {
        if (!$this->prepared) {
            $this->prepare($this->connection);
        }
        $rendered = $this->render();
        $fileName = date('Y_m_d_His') . '_create_' . $this->connection . '_database';
        file_put_contents(config('exporter.migrations') . '{$fileName}.php', $rendered);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($connection=null)
    {
        if (!is_null($connection)) {
            $this->connection = $connection;
            $this->prepared = true;
        }

        $tables = $this->getTablesOf($this->connection);
        foreach ($tables as $key => $table) {
            if (in_array($table['table_name'], self::$ignoring)) {
                continue;
            }

            $destination = $this->dictionary->translate($table['table_name']);
            $up = new ScriptBuilder([
                'indent'  => 4,
                'padding' => 8
            ]);
            $up->append('Schema::create("' . $destination . '", function($' . 'table) {')->lineBreak()
               ->indent();

            $down = new ScriptBuilder([
                'indent'  => 4,
                'padding' => 8
            ]);
            $down->append('Schema::drop("' . $destination . '");');

            $schema = $this->getSchemaOf($table['table_name']);
            foreach ($schema as $clause) {
                $up->append($this->parse(
                    $clause,
                    $this->dictionary->getColumnsOf($table['table_name'])
                ))->lineBreak();
            }

            $indexes = $this->getIndexesOf($table['table_name']);
            if (!is_null($indexes) && count($indexes)) {
                foreach ($indexes as $index) {
                    $up->append('$' . 'table->index("' . $index['Key_name'] . '");')->lineBreak();
                }
            }

            $up->unindent()
               ->append('});')->lineBreak();

            $this->schema[$table['table_name']] = [
                'up'   => $up->build(),
                'down' => $down->build()
            ];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $up = new ScriptBuilder([
            'padding' => 8
        ]);
        $down = new ScriptBuilder([
            'padding' => 8
        ]);

        if (!is_null($this->schema) && count($this->schema)) {
            foreach ($this->schema as $name => $values) {
                if (in_array($name, self::$ignoring)) {
                    continue;
                }
                $up->append('/** Table: ' . $this->dictionary->translate($name) . ' */')->lineBreak()
                   ->append('{$values["up"]}', false)->lineBreak();
                $down->append('{$values["down"]}', false)->lineBreak();
            }
        }

        $template = file_get_contents(__DIR__ . '/templates/migration.txt');
        $template = str_replace('{{name}}', 'Create' . camel_case($this->connection) . 'Database', $template);
        $template = str_replace('{{up}}', $up->build(), $template);
        $template = str_replace('{{down}}', $down->build(), $template);
        return $template;
    }
}
