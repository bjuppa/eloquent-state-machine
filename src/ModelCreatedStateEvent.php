<?php

namespace Bjuppa\EloquentStateMachine;

use DomainException;

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
        //TODO: throw exception unless model is recently created
        return tap(
            //TODO: should this process event's actions before entering state?
            $this->rootState()->defaultEntry($this),
            function ($state) {
                $this->assertCurrentState($state);
                $this->processSideEffects();
            }
        );
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
