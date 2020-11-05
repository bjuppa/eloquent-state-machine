<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Eloquent\HasState;
use Illuminate\Database\Eloquent\Model;

abstract class StateMachineModel extends Model
{
    use HasState;

    abstract public function getState(): SimpleState;
}
