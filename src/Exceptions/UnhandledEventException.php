<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\StateEvent;
use DomainException;

class UnhandledEventException extends DomainException
{
    public StateEvent $event;

    public function __construct(StateEvent $event)
    {
        $this->event = $event;

        parent::__construct("Event could not be handled by state.");
    }
}
