<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class State
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public abstract function entry(StateEvent $event): void;

    public function defaultEntry(StateEvent $event): SimpleState
    {
        throw new DomainException(get_class($this) . ' does not support default entry');
    }

    protected abstract function handleInternal(StateEvent $event): bool;

    protected abstract function handle(StateEvent $event): ?SimpleState;

    protected function transitionToState(StateEvent $event, string $to, string $via = null): SimpleState
    {
        return (new Transition($this, $to, $via))->execute($event);
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
}
