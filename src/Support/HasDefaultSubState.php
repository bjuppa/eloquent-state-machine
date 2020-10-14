<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;

trait HasDefaultSubState
{
    public static string $defaultStateClass;

    public function defaultEntry(Event $event): SimpleState
    {
        if (!static::$defaultStateClass) {
            throw new DomainException(get_class() . '::$defaultStateClass must be specified for default entry');
        }
        return $this->transitionToState($event, static::$defaultStateClass);
    }
}
