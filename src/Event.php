<?php

namespace Bjuppa\EloquentStateMachine;

use \Illuminate\Database\Eloquent\Model;

abstract class Event
{
    public $model;

    //TODO: Consider having a public $payload on events, for guidance.

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Manipulations to be done to model during transition.
     * Override this in your subclass to define "actions".
     *
     * Called when the transition is in the common superstate.
     * Throw any Exception to abort the transition.
     * @return void
     *
     * @throws \Throwable
     */
    public function processActions(): void
    {
        //
    }
}
