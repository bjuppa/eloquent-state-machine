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
     * Override this in your subclass to define "actions".
     *
     * Called when the transition is in the common superstate.
     * Throw any Exception to abort the transition.
     * @return void
     *
     * @throws \Throwable
     */
    protected function actions(): void
    {
        //
    }

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
