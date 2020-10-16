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
}
