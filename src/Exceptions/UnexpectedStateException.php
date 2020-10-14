<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use DomainException;

class UnexpectedStateException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Model is not in expected state.");
    }
}
