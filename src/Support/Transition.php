<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\InvalidTransitionException;
use Bjuppa\EloquentStateMachine\SimpleState;
use Closure;

class Transition
{
    public string $from;
    public string $to;
    public ?string $via;

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

    public function execute(StateEvent $event): SimpleState
    {
        collect($this->exit)->each(fn (SubState $state) => $state->exit($event));

        $event->processActions();

        /* @var $destination State */
        $destination = collect($this->enter)->each(fn (SubState $state) => $state->entry($event))->last();

        while (!$destination instanceof SimpleState) {
            $destination = $destination->defaultEntry($event);
        }

        return $destination;
    }

    protected function buildPathVia(string $viaStateName)
    {
        $this->exit = collect($this->source->branch())
            ->takeUntil(fn (State $state) => get_class($state) === $viaStateName)
            ->toArray();

        if (count($this->exit) && !collect($this->exit)->last() instanceof SubState) {
            throw new InvalidTransitionException($this);
        }

        $this->enter = collect($this->source->make($this->to)->branch())
            ->takeUntil(fn (State $state) => get_class($state) === $viaStateName)
            ->reverse()->toArray();

        if (count($this->enter) && !collect($this->enter)->first() instanceof SubState) {
            throw new InvalidTransitionException($this);
        }
    }

    protected function buildPath()
    {
        $sourceBranch = collect($this->source->branch());
        $targetBranch = collect($this->source->make($this->to)->branch());

        $common = $sourceBranch->first(function ($source) use ($targetBranch) {
            return $targetBranch->contains(function ($target) use ($source) {
                return $target->is($source);
            });
        });

        if (!$common) {
            throw new InvalidTransitionException($this);
        }

        $this->buildPathVia(get_class($common));
    }
}
