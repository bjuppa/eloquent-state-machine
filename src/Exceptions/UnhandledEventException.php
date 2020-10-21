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

        $message = 'Event ' . get_class($event) . ' could not be handled by state';

        if (isset($event->dispatchedTo)) {
            $message = implode(' ', [$message, get_class($event->dispatchedTo)]);
        }

        parent::__construct($message);
    }
}
