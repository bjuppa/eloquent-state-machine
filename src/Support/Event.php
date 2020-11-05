<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Throwable;

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

    public static function make(...$constructorArguments): Event
    {
        return new static(...$constructorArguments);
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

    public function dispatchToState(): ?SimpleState
    {
        try {
            return $this->dispatchToStateOrFail();
        } catch (UnhandledEventException $e) {
            return null;
        }
    }

    public function dispatchToStateOrFail(): SimpleState
    {
        if ($this->model->isDirty()) {
            throw new LogicException(
                get_class($this->model) . ' [' . $this->model->getKey() . ']'
                    . ' must not be dirty when dispatching '
                    . get_class($this)
                    . ' to state'
            );
        }

        try {
            return $this->model->transactionWithRefreshForUpdate(function () {
                return tap(
                    $this->model->getState()->dispatch($this),
                    function (State $destination) {
                        $this->assertCurrentState($destination);
                        $this->processSideEffects();
                    }
                );
            });
        } catch (Throwable $e) {
            $this->model->refresh();
            throw $e;
        }
    }

    public function assertCurrentState(State $state): void
    {
        $this->model->refresh();
        if (!$state->is($this->model->getState())) {
            throw new UnexpectedStateException(
                $state,
                $this->model->getState(),
                $this
            );
        }
    }
}
