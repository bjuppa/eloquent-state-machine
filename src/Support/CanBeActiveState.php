<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\SimpleState;

trait CanBeActiveState
{
    public function dispatch(Event $event): SimpleState
    {
        if ($this->dispatchInternal($event)) {
            return $this;
        }

        return $this->dispatchLocal($event);
    }
}
