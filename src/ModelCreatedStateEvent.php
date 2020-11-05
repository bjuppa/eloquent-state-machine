<?php

namespace Bjuppa\EloquentStateMachine;

use DomainException;
use LogicException;

/**
 * For transitioning new created models into an initial state, extend this class.
 *
 * In a handler for the "created" Eloquent event of your model,
 * statically call dispatch on your event class that extends this class.
 * @see https://laravel.com/docs/eloquent#events-using-closures
 */
abstract class ModelCreatedStateEvent extends StateEvent
{
    /**
     * Classname of the root state to perform default entry into.
     *
     * Your root state class should extend
     * @see \Bjuppa\EloquentStateMachine\RootState
     */
    protected string $rootStateClass;

    public function dispatchToStateOrFail(): SimpleState
    {
        if (!$this->model->wasRecentlyCreated) {
            throw new LogicException(get_class($this) . ' must be dispatched for a newly created model');
        }
        return $this->consummate($this->rootState()->defaultEntry($this));
    }

    protected function rootState(): RootState
    {
        if (!isset($this->rootStateClass)) {
            throw new DomainException(get_class($this) . '::$rootStateClass must be specified');
        }
        if (!is_a($this->rootStateClass, RootState::class, true)) {
            throw new DomainException(
                get_class($this) . '::$rootStateClass ' . $this->rootStateClass . ' is not a ' . RootState::class
            );
        }
        $rootStateClass = $this->rootStateClass;
        return new $rootStateClass($this->model);
    }
}
