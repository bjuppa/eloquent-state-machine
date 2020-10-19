<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\Event;
use Illuminate\Database\Eloquent\Model;

/**
 * Extend this class to describe an event that the state machine should handle.
 */
abstract class StateEvent extends Event
{
    /**
     * Subclass' constructor may receive and store any additional payload data needed during event handling.
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * Manipulates $this->model during transition.
     *
     * Actions will be executed when the transition is in the common superstate.
     *
     * Side-effects not directly manipulating the model, like queuing notifications,
     * should be put off to after the transition using
     * $this->deferSideEffect(function() { ... })
     *
     * @throws \Throwable
     */
    protected function actions(): void
    {
        //
    }
}
