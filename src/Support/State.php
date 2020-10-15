<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class State
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public abstract function entry(Event $event): void;

    public function defaultEntry(Event $event): SimpleState
    {
        throw new UnhandledEventException($event);
    }

    protected abstract function handleInternal(Event $event): bool;

    protected abstract function handle(Event $event): ?SimpleState;

    protected function transitionToState(Event $event, string $to, string $via = null): SimpleState
    {
        return (new Transition($this, $to, $via))->execute($event);
    }

    protected function dispatchInternal(Event $event): bool
    {
        return $this->handleInternal($event);
    }

    protected function dispatchLocal(Event $event): SimpleState
    {
        return tap($this->handle($event), function ($state) use ($event) {
            if (!$state) {
                throw new UnhandledEventException($event);
            }
        });
    }

    public function is(State $state): bool
    {
        return get_class() === get_class($state) && $this->model->is($state->model);
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
