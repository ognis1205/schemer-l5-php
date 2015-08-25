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
trait SchemaParser
{
    /**
     * Holds the predefined SELECT directives.
     * @var array
     */
    protected $selects = [
        'column_name     as name',
        'column_type     as type',
        'is_nullable     as nullable',
        'column_key      as ckey',
        'column_default  as cdefault',
        'extra           as extra',
        'data_type       as data_type'
    ];

    /**
     * Translates specified line into internal context.
     *
     * @param  mixed    $line      the currently handling schema line.
     * @param  callable $translate the translation function.
     * @return array
     */
    public function parse(&$line, callable $translate=Dictionary::identity)
    {
        $context = $this->_contextize($line);

        switch ($context['type']) {
        case 'int':
            $context['method'] = 'integer';
            break;
        case 'smallint':
            $context['method'] = 'smallInteger';
            break;
        case 'bigint':
            $context['method'] = 'bigInteger';
            break;
        case 'char':
            $context['method'] = 'string';
            $context['numbers'] = ', ' . substr(
                $line['type'],
                $context['parenthesis'] + 1,
                -1
            );
            break;
        case 'varchar':
            $context['method'] = 'string';
            $context['numbers'] = ', ' . substr(
                $line['type'],
                $context['parenthesis'] + 1,
                -1
            );
            break;
        case 'float':
            $context['method'] = 'float';
            break;
        case 'double':
            $context['method'] = 'double';
            $context['numbers'] = ', ' . substr(
                $line['type'],
                $context['parenthesis'] + 1,
                -1
            );
            break;
        case 'decimal':
            $context['method'] = 'decimal';
            $context['numbers'] = ', ' . substr(
                $line['type'],
                $context['parenthesis'] + 1,
                -1
            );
            break;
        case 'tinyint':
            $context['method'] = 'boolean';
            break;
        case 'date':
            $context['method'] = 'date';
            break;
        case 'timestamp':
            $context['method'] = 'timestamp';
            break;
        case 'datetime':
            $context['method'] = 'dateTime';
            break;
        case 'longtext':
            $context['method'] = 'longText';
            break;
        case 'mediumtext':
            $context['method'] = 'mediumText';
            break;
        case 'text':
            $context['method'] = 'text';
            break;
        case 'longblob':
            $context['method'] = 'binary';
            break;
        case 'blob':
            $context['method'] = 'binary';
            break;
        case 'enum':
            $context['method'] = 'enum';
            $options = substr(
                $values->type,
                $paren + 1,
                -1
            );
            $context['numbers'] = ', array(' . $options . ')';
            break;
        }

        if ($line['ckey'] == 'PRIMARY') {
            $context['method'] = 'increments';
        }

        return $this->_clausify($context, $translate);
    }

    /**
     * Returns phpized source code snipet.
     *
     * @param mixed    $context
     * @param callable $translate the translation function.
     */
    private function _clausify($context, callable $translate=Dictionary::identity)
    {
        $rendered = '$' . 'table->' . $context['method'];
        $rendered .= '(';
        $rendered .= '"' . $translate($context['name']) . '"';
        $rendered .= $context['numbers'];
        $rendered .= ')';
        $rendered .= $context['nullable'];
        $rendered .= $context['default'];
        $rendered .= $context['unsigned'];
        $rendered .= ';';
        return $rendered;
    }

    /**
     * Extracts context from the currently handling schema line.
     *
     * @param  object $line the curremtly handling schema line.
     * @return array        the resulting context
     */
    private function _contextize(&$line)
    {
        $parenthesis = strpos($line['type'], '(');
        $unsigned    = strpos($line['type'], 'unsigned');
        return [
            'method'      => '',
            'parenthesis' => $parenthesis,
            'name'        => $line['name'],
            'type'        => $parenthesis > -1 ?
                             substr($line['type'], 0, $parenthesis) : $line['type'],
            'numbers'     => '',
            'nullable'    => $line['nullable'] == 'NO' ?
                             '' : '->nullable()',
            'default'     => empty($line['cdefault']) ?
                             '' : '->default("' . $line['cdefault'] . '}")',
            'unsigned'    => $unsigned === false ? '' : '->unsigned()'
        ];
    }
}