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
}
