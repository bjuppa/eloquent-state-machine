<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\Support\Transition;
use DomainException;

class InvalidTransitionException extends DomainException
{
    public Transition $transition;

    public function __construct(Transition $transition)
    {
        $this->transition = $transition;

        parent::__construct("Could not transition to desired state.");
    }
}
