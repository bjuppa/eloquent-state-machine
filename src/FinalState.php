<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Illuminate\Database\Eloquent\Model;

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
        $event->dispatchedTo = $this;
        throw new UnhandledEventException($event);
    }
}
