<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates;

use \Exception;

/**
 * Represents conversion between source database and destination one.
 *
 * @author Shingo OKAWA
 */
class Dictionary
{
    /**
     * Holds the identity mapping.
     * @var array
     */
    public static function identity($key) {
        return $key;
    }

    /**
     * Holds the identity mapping.
     * @var array
     */
    private $path;

    /**
     * Constructor.
     *
     * @param string $path the string which represents dot-env path.
     */
    public function __construct($path='export.dictionary')
    {
        $this->path = $path;
    }

    /**
     * Translates table name into the corresponding destination table name.
     *
     * @param string $table the source table name.
     */
    public function translate($table)
    {
        return config($this->path . '.' . $table . '.destination') ?: $table;
    }

    /**
     * Returns columns' name mapping as a callable instance.
     *
     * @param string $table the source table name.
     */
    public function getColumnsOf($table)
    {
        $dictionary = config($this->path . '.' . $table . '.columns');
        if (!empty($dictionary)) {
            return function($key) use($dictionary) {
                return $dictionary[$key] ?: $key;
            }
        } else {
            return self::identity;
        }
    }
}