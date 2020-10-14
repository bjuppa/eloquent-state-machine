<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\State;
use Bjuppa\EloquentStateMachine\Support\HasDefaultSubState;

abstract class RootState extends State
{
    use HasDefaultSubState;
}
