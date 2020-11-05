<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Eloquent\HasState;
use Illuminate\Database\Eloquent\Model;

//TODO: make StateMachineModel a contract
abstract class StateMachineModel extends Model
{
    use HasState;

    abstract public function getState(): SimpleState;
}
