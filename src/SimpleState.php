<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\Dispatcher;
use Bjuppa\EloquentStateMachine\Support\SubState;

abstract class SimpleState extends SubState
{
    use Dispatcher;
}
