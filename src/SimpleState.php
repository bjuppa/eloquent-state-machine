<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\CanBeActiveState;
use Bjuppa\EloquentStateMachine\Support\SubState;

/**
 * Extend this class to describe a leaf state in the state hierarchy.
 */
abstract class SimpleState extends SubState
{
    use CanBeActiveState;
}
