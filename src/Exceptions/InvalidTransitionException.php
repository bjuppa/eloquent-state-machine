<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use DomainException;

class InvalidTransitionException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Could not transition to desired state.");
    }
}
