<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;

trait HasDefaultSubState
{
    /**
     * Classname of the substate to transition to if entering this composite state without a specific target substate.
     */
    public static string $defaultStateClass;

    /**
     * Override to run custom logic if entering this composite state without a specific target substate.
     *
     * Call $this->transitionToState() to transition into a substate and return the target state.
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
