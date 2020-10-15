<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\CanBeActiveState;
use Bjuppa\EloquentStateMachine\Support\SubState;

abstract class SimpleState extends SubState
{
    use CanBeActiveState;
}
