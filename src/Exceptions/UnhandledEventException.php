<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use DomainException;

class UnhandledEventException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Event could not be handled by state.");
    }
}
