<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\State;
use Bjuppa\EloquentStateMachine\Support\HasDefaultSubState;

/**
 * Extend this class to describe the root state at the top level of the state machine.
 */
abstract class RootState extends State
{
    use HasDefaultSubState;

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
}
