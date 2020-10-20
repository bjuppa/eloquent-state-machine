<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\SubState;
use Bjuppa\EloquentStateMachine\Support\HasDefaultSubState;

/**
 * Extend this class do describe a state containing substates.
 */
abstract class CompositeState extends SubState
{
    use HasDefaultSubState;

    public static string $superStateClass;

    public static string $defaultStateClass;

    protected function handleInternal(StateEvent $event): bool
    {
        //
        return false;
    }

    protected function handle(StateEvent $event): ?SimpleState
    {
        //
        return null;
    }

    public function entry(StateEvent $event): void
    {
        //
    }

    public function exit(StateEvent $event): void
    {
        //
    }
}
