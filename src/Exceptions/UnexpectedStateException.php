<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\Event;
use DomainException;

class UnexpectedStateException extends DomainException
{
    public string $expected;
    public string $actual;

    public Event $event;

    public function __construct($expected, $actual, Event $event = null)
    {
        $this->expected = is_string($expected) ? $expected : get_class($expected);
        $this->actual = is_string($actual) ? $actual : get_class($actual);
        $this->event = $event;

        parent::__construct("Model is not in expected state.");
    }
}
