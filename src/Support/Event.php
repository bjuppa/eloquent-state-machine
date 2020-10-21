<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class Event
{
    protected Model $model;

    private array $actions = [];
    private array $sideEffects = [];

    public SimpleState $dispatchedTo;

    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->actions = [function () {
            $this->actions();
        }];
    }

    abstract protected function actions(): void;

    public function deferSideEffect(Closure $callback)
    {
        array_push($this->sideEffects, $callback);
    }

    public function processActions(): void
    {
        while (count($this->actions)) {
            array_shift($this->actions)();
        }
    }

    public function processSideEffects(): void
    {
        while (count($this->sideEffects)) {
            array_shift($this->sideEffects)();
        }
    }
}
