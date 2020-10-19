<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use Bjuppa\EloquentStateMachine\StateEvent;
use LogicException;

class UnexpectedStateException extends LogicException
{
    public string $expected;
    public string $actual;

    public StateEvent $event;

    public function __construct($expected, $actual, StateEvent $event = null)
    {
        $this->expected = is_string($expected) ? $expected : get_class($expected);
        $this->actual = is_string($actual) ? $actual : get_class($actual);
        $this->event = $event;

        parent::__construct("Model is not in expected state.");
    }
}
