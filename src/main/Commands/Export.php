<?php
/*
 * Copyright (c) Shingo OKAWA <shingo.okawa.n.a@gmail.com>
 */
namespace SOkawa\Batch\Support\Commands;

use Illuminate\Console\Command;

/**
 * Represents base generating command.
 * Extends this class s.t. the extended class suffices more specific CLI requirements.
 *
 * @author Shingo OKAWA
 */
class Export extends Command
{
    /**
     * Get the database name from the config/database.php file.
     *
     * @return string
     */
    protected function getDatabase()
    {
        $default  = config('database.default');
        $database = config('database.connections.' . $default);
        return $database['database'];
    }

    /**
     * Returns Symfony style block message.
     *
     * @param  string $title   the message's title.
     * @param  string $message the message's body.
     * @param  string $style   the rendering style.
     * @return string          the resulting message.
     */
    protected function formatBlock($title, $message, $style="info")
    {
        $formatter = $this->getHelperSet()->get("formatter");
        $context   = array($title, $message);
        $formatted = $formatter->formarBlock(
            $context,
            $style,
            true
        );
        return $this->line($formatted);
    }

    /**
     * Returns Symfony style section message.
     *
     * @param  string $title   the message's title.
     * @param  string $message the message's body.
     * @return string          the resulting message.
     */
    protected function formatSection($title, $message)
    {
        $formatter = $this->getHelperSet()->get("formatter");
        $formatted = $formatter->formarSection(
            $title,
            $message
        );
        return $this->line($formatted);
    }
}