<?php

namespace Bjuppa\EloquentStateMachine;

use LogicException;

/**
 * Use or extend this class to describe the final state, when the model has been deleted.
 */
class FinalState extends SimpleState
{
    final public function dispatch(StateEvent $event): SimpleState
    {
        throw new LogicException(get_class($this) . ' is a final state and can not handle events');
    }
}
