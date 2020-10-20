<?php

namespace Bjuppa\EloquentStateMachine;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Use or extend this class to describe the final state, when the model has been deleted.
 */
class FinalState extends SimpleState
{
    public function __construct(Model $model)
    {
        if (isset(static::$superStateClass)) {
            parent::__construct($model);
        } else {
            $this->model = $model;
        }
    }

    final public function dispatch(StateEvent $event): SimpleState
    {
        throw new LogicException(get_class($this) . ' is a final state and can not handle events');
    }
}
