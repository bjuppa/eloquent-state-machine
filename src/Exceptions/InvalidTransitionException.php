<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use RuntimeException;

class InvalidTransitionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Could not transition to desired state.");
    }
}
