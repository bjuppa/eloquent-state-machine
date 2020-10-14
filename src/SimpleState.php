<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\SubState;

abstract class SimpleState extends SubState
{
    public function dispatch(Event $event): SimpleState
    {
        if ($this->dispatchInternal($event)) {
            return $this;
        }

        return $this->dispatchLocal($event);
    }
}
