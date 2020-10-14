<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\State;
use Bjuppa\EloquentStateMachine\Support\SuperState;

abstract class RootState extends State
{
    use SuperState;
}
