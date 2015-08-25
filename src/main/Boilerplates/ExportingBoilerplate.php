<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates;

use \Exception;
use Illuminate\Support\Facades\DB;

/**
 * Represents functionality to export datas from a database to the other one.
 * Extends this class s.t. the extended class suffices more specific exporting requirements.
 *
 * @author Shingo OKAWA
 */
abstract class ExportingBoilerplate
{
    /**
     * Holds the ignoring tables.
     * @var array
     */
    public static $ignoring = [
        'migrations'
    ];

    /**
     * Holds the target remote host.
     * @var string
     */
    public static $remote;

    /**
     * Returns the all table names which is defined on the currently handling connection.
     *
     * @param  string $connection the idetifier of the handling connection.
     * @return array              the resulting string array consists of the resulting table names.
     */
    protected function getTablesOf($connection=NULL)
    {
        $database = $this->getConfigOf('database', $connection);
        return DB::connection($connection)->getPdo()->query(
            'SELECT table_name FROM information_schema.tables WHERE table_schema="' . $database . '"'
        )->fetchAll();
    }

    /**
     * Returns the all indexes which is defined on the specified table.
     *
     * @param  string $table      the currently handling table name.
     * @param  string $connection the idetifier of the handling connection.
     * @return array              the resulting array consists of the indexes which is defined on the
     *                            specified table.
     */
    public function getIndexesOf($table, $connection=NULL)
    {
        // Gets the PHP Data Object relating to the connection.
        return DB::connection($connection)->getPdo()->query(
            'SHOW index FROM ' . $table . ' WHERE key_name != "PRIMARY"'
        )->fetchAll();
    }

    /**
     * Returns the schema of the specified table.
     *
     * @param  string $table      the currently handling table name.
     * @param  string $connection the idetifier of the handling connection.
     * @return mixed              the resulting schema.
     */
    protected function getSchemaOf($table, $connection=NULL)
    {
        $database = $this->getConfigOf('database', $connection);
        return DB::connection($connection)->getPdo()->query(
            'SELECT ' . 
            $this->_csvify($this->selects) .
            ' FROM information_schema.columns WHERE table_schema="' . $database .
            '" AND table_name="' . $table . '"'
        )->fetchAll();
    }

    /**
     * Returns the all data of the specified table.
     *
     * @param  string $table      the currently handling table name.
     * @param  string $connection the idetifier of the handling connection.
     * @return mixed              the resulting data.
     */
    protected function getDataOf($table, $connection=NULL)
    {
        return DB::connection($connection)->table($table)
                                          ->get();
    }

    /**
     * Returns the configuration object of the specified connection.
     *
     * @param  string $key        the key name.
     * @param  string $connection the handling connection identifier.
     * @return mixed              the resulting configuration object.
     */
    protected function getConfigOf($key, $connection=NULL)
    {
        return DB::connection($connection)->getConfig($key);
    }

    /**
     * Ignores specified tables from the exporting task.
     *
     * @param array $tables the array consists of ignoring table names.
     */
    protected function ignore($tables)
    {
        self::$ignoring = array_merge(
            self::$ignoring,
            (array) $tables
        );
    }

    /**
     * Converts specified array into CSV formatted string.
     *
     * @param  array $array the array to be converted.
     * @return string       the resulting CSV formatted string.
     */
    private function _csvify(array $array) {
        $fd = fopen('php://temp', 'rw');
        fputcsv($fd, $array);
        rewind($fd);

        $csv = stream_get_contents($fd);
        fclose($fd);

        return str_replace('"', '', $csv);
    }

    /**
     * Dumps datas out to the binded file.
     *
     * @return mixed this depends on the specific implementations.
     */
    abstract public function dump();

    /**
     * Converts the database to a appropriate format.
     *
     * @param  string $connection the handling connection identifier.
     * @return mixed              this depends on the specific implementations.
     */
    abstract public function prepare($connection=null);

    /**
     * Renders the converted thunk into the template.
     *
     * @return mixed this depends on the specific implementations.
     */
    abstract public function render();
}