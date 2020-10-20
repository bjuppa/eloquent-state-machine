<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\CanBeActiveState;
use Bjuppa\EloquentStateMachine\Support\SubState;

/**
 * Extend this class to describe a leaf state in the state hierarchy.
 */
abstract class SimpleState extends SubState
{
    use CanBeActiveState;

    public static string $superStateClass;

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
