<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates\Intermediate;

use \Exception;

/**
 * Represents rendering migration snipets' thunks.
 * This class'es instances are utilized for rendering intermediate migration
 * source codes.
 *
 * @author Shingo OKAWA
 */
class ScriptBuilder
{
    /**
     * Enables useful assertion utilities.
     */
    use Assertion;

    /**
     * Holds currently rendering thunk.
     * @var string
     */
    protected $thunk = '';

    /**
     * Holds current column number.
     * @var int
     */
    protected $column = 0;

    /**
     * Holds current indent level.
     * @var int
     */
    protected $level = 0;

    /**
     * Holds indentation.
     * @var string
     */
    protected $indentation;

    /**
     * Holds padding.
     * @var string
     */
    protected $padding;

    /**
     * Holds newline string.
     * @var string
     */
    protected $newline;

    /**
     * Holds default indentation width.
     * @var int
     */
    const DEFAULT_INDENT = 4;

    /**
     * Holds default padding width.
     * @var int
     */
    const DEFAULT_PADDING = 0;

    /**
     * Holds default newline string.
     * @var string
     */
    const DEFAULT_NEWLINE = '\n';

    /**
     * Constructor.
     *
     * @param array $config the configuration array.
     */
    public function __construct(array $config=['indent'=>NULL, 'padding'=>NULL, 'newline'=>NULL])
    {
        $this->indentation = $this->checkIfPositiveInt(isset($config['indent']) ? $config['indent'] : NULL) ?
            $config['indent'] : self::DEFAULT_INDENT;
        $this->indentation = str_repeat(' ', $this->indentation);

        $this->padding = $this->checkIfPositiveInt(isset($config['padding']) ? $config['padding'] : NULL) ?
            $config['padding'] : self::DEFAULT_PADDING;
        $this->padding = str_repeat(' ', $this->padding);

        $this->newline = ($this->checkIfString(isset($config['newline']) ? $config['newline'] : NULL)) ?
            $config['newline'] : self::DEFAULT_NEWLINE;
    }

    /**
     * Appends specified thunk into the currently handling intermediate thunk.
     *
     * @param string $thunk the thunk to be appended.
     */
    public function append($thunk, $align=true)
    {
        $this->thunk .= $this->align($thunk, !!$align);
        return $this;
    }

    /**
     * Aligns given thunk in accordance to the current context.
     *
     * @param string $thunk the assigned thunk snipet.
     * @param 
     */
    protected function align($thunk, $align)
    {
        if (!!$align) {
            return $this->padding . str_repeat($this->indentation, $this->level) . $thunk;
        }
        return $thunk;
    }

    /**
     * Appends newline string into the currently handling thunk.
     */
    public function lineBreak()
    {
        $this->thunk .= $this->newline;
        return $this;
    }

    /**
     * Incremets the currently handling indentation level.
     */
    public function indent()
    {
        $this->assert($this->level >= 0);
        $this->level += 1;
        return $this;
    }

    /**
     * Decrements the currently handling indentation level.
     */
    public function unindent()
    {
        $this->level -= 1;
        $this->assert($this->level >= 0);
        return $this;
    }

    /**
     * Returns resulting script.
     */
    public function build()
    {
        return $this->thunk;
    }
}