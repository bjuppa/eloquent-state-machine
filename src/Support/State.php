<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Illuminate\Database\Eloquent\Model;

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
    }

    protected function dispatchInternal(Event $event): bool
    {
        return $this->handleInternal($event);
    }

    protected abstract function dispatchLocal(Event $event): SimpleState;
}
