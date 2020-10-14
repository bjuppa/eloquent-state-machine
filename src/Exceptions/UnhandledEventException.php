<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\Event;
use DomainException;

class UnhandledEventException extends DomainException
{
    public Event $event;

    public function __construct(Event $event)
    {
        parent::__construct("Event could not be handled by state.");

        $this->event = $event;
    }
}
