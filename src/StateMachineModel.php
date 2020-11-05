<?php

namespace Bjuppa\EloquentStateMachine;

use Closure;

/**
 * Make your model implement this interface, and also use the HasState trait:
 * @see \Bjuppa\EloquentStateMachine\Eloquent\HasState
 */
interface StateMachineModel
{
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
     * @see \Bjuppa\EloquentStateMachine\Eloquent\HasState::makeState
     *
     * @throws \Throwable
     */
    public function getState(): SimpleState;

    /**
     * This method is already implemented for you through the HasState trait.
     * @see Bjuppa\EloquentStateMachine\Eloquent\CanLockPessimistically
     */
    public function transactionWithRefreshForUpdate(Closure $callback);

    // isDirty()
    // refresh()
    // getKey()
    // is()
}
