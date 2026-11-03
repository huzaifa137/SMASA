<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by SchoolDeletionService when a school is deliberately NOT deleted
 * (it is still Active, or a delete is already running for it) and nothing has
 * been touched. Callers show the message as it is; it is never a server error.
 */
class SchoolDeletionRefusedException extends RuntimeException
{
}
