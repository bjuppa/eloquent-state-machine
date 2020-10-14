<?php

namespace Bjuppa\EloquentStateMachine;

use \Illuminate\Database\Eloquent\Model;
use Closure;

abstract class Event
{
    protected Model $model;

    private array $sideEffects = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Manipulations to be done to model during transition.
     *
     * Declare this in Event subclasses to define "actions" that should be
     * executed when the transition is in the common superstate.
     *
     * Throw any Exception to abort the transition.
     *
     * Side-effects not directly manipulating the model, like queuing notifications,
     * should be put off to after the transition using
     * $this->deferSideEffect(function() { ... })
     *
     * @return void
     *
     * @throws \Throwable
     */
    abstract protected function actions(): void;

    public function deferSideEffect(Closure $callback)
    {
        array_push($this->sideEffects, $callback);
    }

    public function getActions(): array
    {
        return [function () {
            $this->actions();
        }];
    }

    public function getSideEffects(): array
    {
        return $this->sideEffects;
    }
}
