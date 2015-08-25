<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates\Intermediate;

use \Exception;
use SOkawa\Batch\Support\Boilerplates\Dictionary;

/**
 * Specifies that assigned class uses SQL schema parsing functionality.
 *
 * @author Shingo OKAWA
 */
trait RecordParser
{
    /**
     * Translates specified line into internal context.
     *
     * @param  string   $key       the currently handling property key.
     * @param  string   $value     the currently handling property value.
     * @param  callable $translate the translation function.
     * @return string              the resulting PHP style array element.
     */
    public function parse(&$key, &$value, callable $translate=NULL)
    {
        if ($translate == NULL || !is_callable($translate)) {
            $translate = Dictionary::identity
        }
        return $this->_pairwise($translate($key), $value);
    }

    /**
     * Renders the specified key-value pair into the PHP style.
     *
     * @param  string $key   the currently handling property key.
     * @param  string $value the currently handling property value.
     * @return string        the resulting PHP style array element.
     */
    private function _pairwise(&$key, &$value)
    {
        $key   = addslashes($key);
        $value = addslashes($value);
        if (is_numeric($value)) {
            return '"{$key}" => {$value},';
        } elseif ($value == '') {
            return '"{$key}" => NULL,';
        } else {
            return '"{$key}" => "{$value}",';
        }
    }
}