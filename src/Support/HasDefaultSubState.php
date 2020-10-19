<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;

trait HasDefaultSubState
{
    /**
     * Classname of the substate to transition to if entering this composite state without a specific target substate.
     *
     * Needed only when there is some transition ending in this composite state.
     *
     * Can be either
     * @see \Bjuppa\EloquentStateMachine\SimpleState
     * or
     * @see \Bjuppa\EloquentStateMachine\CompositeState
     */
    public static string $defaultStateClass;

    /**
     * Perform default entry into this composite state.
     *
     * Override to run custom logic when entering this composite state without a specific target substate.
     *
     * Call $this->transitionToState() to transition into a substate and return the target state.
     *
     * @return SimpleState The target state, a substate of this composite state
     *
     * @throws \Throwable
     */
    public function defaultEntry(StateEvent $event): SimpleState
    {
        if (!static::$defaultStateClass) {
            throw new DomainException(get_class($this) . '::$defaultStateClass must be specified to handle default entry into the composite state');
        }
        return $this->transitionToState($event, static::$defaultStateClass);
    }
}
