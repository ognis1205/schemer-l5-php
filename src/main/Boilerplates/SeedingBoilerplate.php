<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates;

use \Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SOkawa\Batch\Support\Boilerplates\Intermediate\RecordParser;
use SOkawa\Batch\Support\Boilerplates\Intermediate\ScriptBuilder;

/**
 * Represents functionality to seed datas from a database to the other one.
 *
 * @author Shingo OKAWA
 */
class SeedingBoilerplate extends ExportingBoilerplate
{
    /**
     * Enables functionality to parse RDBMS record.
     */
    use RecordParser;

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
     * Holds converted thunk.
     * @var string
     */
    protected $records;

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
        $fileName = date('Y_m_d_His') . camel_case($this->connection) . 'TableSeeder';
        file_put_contents(config('exporter.seeds') . '{$fileName}.php', $rendered);
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
        $run = new ScriptBuilder([
            'indent'  => 4,
            'padding' => 8
        ]);

        foreach ($tables as $key => $value) {
            if (in_array($value['table_name'], self::$ignoring)) {
                continue;
            }

            $destination = $this->dictionary->translate($table['table_name']);
            $records = $this->getDataOf($value['table_name']);
            $clause = new ScriptBuilder([
                'indent'  => 4,
                'padding' => 12
            ]);

            foreach ($records as $record) {
                $clause->append('[')->lineBreak()
                       ->indent();

                foreach ($record as $column => $data) {
                    $clause->append($this->parse(
                        $column,
                        $data,
                        $this->dictionary->getColumnsOf($table['table_name'])
                    ))->lineBreak();
                }

                if ($this->hasRecords($records, 1)) {
                    $clause->unindent()
                           ->append('],')->lineBreak();
                } else {
                    $clause->unindent()
                           ->append(']')->lineBreak();
                }
            }

            if ($this->hasRecords($records)) {
                $run->append('DB::table("' . $destination . '")->insert([')->lineBreak()
                    ->append($clause->build(), false)->lineBreak()
                    ->append(']);')->lineBreak()
                    ->lineBreak();
            }
        }

        $this->records = $run;
        return $this;
    }

    /**
     * Checks if the specified records exist more than the criterial number.
     *
     * @param array $records  the records array to be checked.
     * @param int   $criteria the criterial value to be checked.
     */
    private function hasRecords($records, $criteria=0)
    {
        return count($records) > $criteria;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $template = file_get_contents(__DIR__ . '/templates/seed.txt');
        $template = str_replace('{{className}}', camel_case($this->connection) . 'TableSeeder', $template);
        $template = str_replace('{{run}}', $this->records->build(), $template);
        return $template;
    }
}