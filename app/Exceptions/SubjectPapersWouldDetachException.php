<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when removing standard papers from a subject would also detach them
 * from classes where they are attached (and drop their paper teachers). The
 * caller must confirm (confirm_detach) first.
 */
class SubjectPapersWouldDetachException extends InvalidArgumentException
{
    public function __construct(public readonly int $count)
    {
        parent::__construct(
            "The paper(s) you removed are attached to {$count} class-stream(s). " .
            'Removing them also detaches them there and clears their paper teachers. Examination papers and marks already entered are not touched.'
        );
    }
}
