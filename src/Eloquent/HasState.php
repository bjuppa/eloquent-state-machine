<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use InvalidArgumentException;
use Throwable;

trait HasState
{
    use CanLockPessimistically;
    use SavesInTransaction;

    /**
     * Logic determining the state this model is currently in.
     *
     * Always implement this in your model class.
     *
     * Current state may be stored directly in a database column, but you are free to use
     * any logic involving the attributes and relationships of the current model.
     *
     * After determining the current state, call $this->makeState()
     * passing the desired classname and return it.
     *
     * @throws \Throwable
     */
    abstract public function getState(): SimpleState;

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
