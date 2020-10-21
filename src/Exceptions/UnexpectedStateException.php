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

        $message = implode(' ', array_filter([
            isset($this->event->model)
                ? get_class($this->event->model) . ' [' . $this->event->model->getKey() . ']'
                : 'Model',
            'is in state',
            $this->actual,
            'and not expected state',
            $this->expected,
        ]));

        parent::__construct($message);
    }
}
