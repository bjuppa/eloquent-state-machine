<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use LogicException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class State
{
    /**
     * Model instance representing the extended state.
     */
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function defaultEntry(StateEvent $event): SimpleState
    {
        throw new LogicException(get_class($this) . ' does not support default entry');
    }

    /**
     * Handle event while staying in the current simple state.
     *
     * Evaluate guard conditions and manipulate $this->model in here.
     * Put any side effects into the event object for processing after the transition is completed.
     *
     * Return true if event has been handled to stop it from propagating.
     *
     * @throws \Throwable
     */
    protected abstract function handleInternal(StateEvent $event): bool;

    /**
     * Handle event and transition into another state.
     *
     * Evaluate guard conditions and return next state using $this->transitionToState() in here.
     *
     * Returning a new state will stop the event from propagating.
     *
     * @throws \Throwable
     */
    protected abstract function handle(StateEvent $event): ?SimpleState;

    /**
     * Transition from the current state to another state.
     *
     * @param $to Classname of the state to transition to.
     * @param $via Optional classname of a common higher superstate to exit up to before entering again.
     */
    protected function transitionToState(StateEvent $event, string $to, string $via = null): SimpleState
    {
        return (new Transition($this, $to, $via))->execute($event);
    }

    public function is(State $state): bool
    {
        return get_class($this) === get_class($state) && $this->model->is($state->model);
    }

    public function branch(): array
    {
        return [$this];
    }

    public function make(string $state): State
    {
        if (!is_a($state, State::class)) {
            throw new InvalidArgumentException(
                $state . ' is not a ' . State::class
            );
        }

        return new $state($this->model);
    }

    protected function dispatchInternal(StateEvent $event): bool
    {
        return $this->handleInternal($event);
    }

    protected function dispatchLocal(StateEvent $event): SimpleState
    {
        return tap($this->handle($event), function ($state) use ($event) {
            if (!$state) {
                throw new UnhandledEventException($event);
            }
        });
    }
}
