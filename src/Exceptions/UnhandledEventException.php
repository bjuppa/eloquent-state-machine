<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\StateEvent;
use LogicException;

class UnhandledEventException extends LogicException
{
    public StateEvent $event;

    public function __construct(StateEvent $event)
    {
        $this->event = $event;

        $message = 'Event ' . get_class($this->event) . ' could not be handled in state';

        if (isset($this->event->dispatchedTo)) {
            $message = implode(' ', [$message, get_class($this->event->dispatchedTo)]);
        }

        parent::__construct($message);
    }
}
