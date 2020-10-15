<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\SubState;
use Bjuppa\EloquentStateMachine\Support\HasDefaultSubState;

abstract class CompositeState extends SubState
{
    use HasDefaultSubState;
}
