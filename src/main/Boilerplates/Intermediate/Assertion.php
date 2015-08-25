<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Boilerplates\Intermediate;

use \Exception;

/**
 * Specifies that assigned class uses asserions.
 *
 * @author Shingo OKAWA
 */
trait Assertion
{
    /**
     * Returns true if the given candidate is positive integer.
     *
     * @param  mixed $candidate the candidate to be checked.
     * @return true if the specified candidate is positive integer.
     */
    public function checkIfPositiveInt($candidate)
    {
        return (!is_null($candidate) && is_int($candidate) && $candidate > 0);
    }

    /**
     * Returns true if the given candidate is string.
     *
     * @param  mixed $candidate the candidate to be checked.
     * @return true if the specified candidate is string.
     */
    public function checkIfString($candidate)
    {
        return (!is_null($candidate) && is_string($candidate));
    }

    /**
     * Checks if the specified expression is supposed to be true.
     *
     * @param mixed  $expression the expression to be checked.
     * @param string $message    the assertion message.
     */
    public function assert($expression, $message='')
    {
        if (!$expression) {
            throw new Exception($message);
        }
    }
}