<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;

trait HasDefaultSubState
{
    public static string $defaultStateClass;

    public function defaultEntry(StateEvent $event): SimpleState
    {
        if (!static::$defaultStateClass) {
            throw new DomainException(get_class($this) . '::$defaultStateClass must be specified to handle default entry into the composite state');
        }
        return $this->transitionToState($event, static::$defaultStateClass);
    }
}
