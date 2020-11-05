<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\Support\State;
use InvalidArgumentException;

trait HasState
{
    use CanLockPessimistically;
    use SavesInTransaction;

    /**
     * Generate a state object bound to this model.
     */
    protected function makeState(string $classname): State
    {
        if (!is_a($classname, State::class, true)) {
            throw new InvalidArgumentException(
                $classname . ' is not a ' . State::class
            );
        }
        return new $classname($this);
    }
}
