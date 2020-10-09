<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use RuntimeException;

class UnhandledEventException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Event could not be handled by state.");
    }
}
