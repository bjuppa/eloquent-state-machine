<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class Event
{
    protected Model $model;

    private array $actions = [];
    private array $sideEffects = [];

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

    public function getActions(): array
    {
        return tap($this->actions, fn () => $this->actions = []);
    }

    public function getSideEffects(): array
    {
        return tap($this->sideEffects, fn () => $this->sideEffects = []);
    }
}
