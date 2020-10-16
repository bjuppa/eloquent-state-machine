<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\SimpleState;

trait CanBeActiveState
{
    public function dispatch(StateEvent $event): SimpleState
    {
        if ($this->dispatchInternal($event)) {
            return $this;
        }

        return $this->dispatchLocal($event);
    }
}
