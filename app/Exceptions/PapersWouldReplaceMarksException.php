<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when splitting a subject into papers would discard single scores
 * that were already entered for it in this examination. The caller must
 * confirm (confirm_replace) before they are replaced by per-paper marks.
 */
class PapersWouldReplaceMarksException extends InvalidArgumentException
{
    public function __construct(public readonly int $count)
    {
        parent::__construct(
            "{$count} student(s) already have a single score for this subject in this examination. " .
            'Splitting it into papers replaces those scores: they will show as pending until each paper mark is entered.'
        );
    }
}
