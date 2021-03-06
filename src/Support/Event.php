<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;
use Bjuppa\EloquentStateMachine\StateMachineModel as Model;
use LogicException;
use Throwable;

abstract class Event
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
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

    public static function make(...$construct): Event
    {
        return new static(...$construct);
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
                return $this->consummate($this->model->getState()->dispatch($this));
            });
        } catch (Throwable $e) {
            $this->model->unsetRelations();
            $this->model->refresh();
            throw $e;
        }
    }

    protected function consummate(State $state): State
    {
        $this->processActions();
        $this->assertCurrentState($state);
        $this->processSideEffects();

        return $state;
    }

    public function assertCurrentState(State $state): void
    {
        $this->model->unsetRelations();
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
