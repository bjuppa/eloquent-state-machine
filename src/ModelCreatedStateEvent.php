<?php

namespace Bjuppa\EloquentStateMachine;

class ModelCreatedStateEvent extends StateEvent
{
    /**
     * Extend this class and return an instance from initialTransitionEvent()
     * in your model if you need any special payload to be sent along with
     * newly created models when the state machine does the first
     * transition to the initial state.
     */
}
