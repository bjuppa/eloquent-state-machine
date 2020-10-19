<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\Support\Transition;
use LogicException;

class InvalidTransitionException extends LogicException
{
    public Transition $transition;

    public function __construct(Transition $transition)
    {
        $this->transition = $transition;

        parent::__construct("Could not find path between source and target states");
    }
}
