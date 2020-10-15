<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\Exceptions\InvalidTransitionException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;

class Transition
{
    public string $from;
    public string $to;
    public string $via;

    protected State $source;

    protected array $exit = [];
    protected array $enter = [];

    public function __construct(State $source, string $to, string $via = null)
    {
        $this->source = $source;

        $this->from = get_class($source);
        $this->to = $to;
        $this->via = $via;

        if ($via) {
            $this->buildPathVia($via);
        } else {
            $this->buildPath();
        }
    }

    public function execute(Event $event): SimpleState
    {
        collect($this->exit)->each(fn (SubState $state) => $state->exit($event));

        collect($event->getActions())->each(fn (Closure $callback) => $callback());

        /* @var $destination State */
        $destination = collect($this->enter)->each(fn (SubState $state) => $state->entry($event))->last();

        return $destination instanceof SimpleState ? $destination : $destination->defaultEntry($event);
    }

    protected function buildPathVia(string $viaStateName)
    {
        $this->exit = collect($this->source->branch())
            ->takeUntil(fn (State $state) => get_class($state) === $viaStateName)
            ->toArray();

        if (!collect($this->exit)->last() instanceof SubState) {
            throw new InvalidTransitionException($this);
        }

        $this->enter = collect($this->source->make($this->to)->branch())
            ->takeUntil(fn (State $state) => get_class($state) === $viaStateName)
            ->reverse()->toArray();

        if (!collect($this->exit)->first() instanceof SubState) {
            throw new InvalidTransitionException($this);
        }
    }

    protected function buildPath()
    {
    }
}
