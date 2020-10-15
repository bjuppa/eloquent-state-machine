<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;

class Transition
{
    public string $from;
    public string $to;
    public string $via;

    protected array $exit = [];
    protected array $enter = [];

    public function __construct(State $from, string $to, string $via = null)
    {
        $this->from = get_class($from);
        $this->to = $to;
        $this->via = $via;

        // TODO: build the exit and enter states
    }

    public function execute(Event $event): SimpleState
    {
        collect($this->exit)->each(fn (SubState $state) => $state->exit($event));

        collect($event->getActions())->each(fn (Closure $callback) => $callback());

        /* @var $state State */
        $state = collect($this->enter)->each(fn (SubState $state) => $state->entry($event))->last();

        return $state instanceof SimpleState ? $state : $state->defaultEntry($event);
    }
}
