<?php

namespace Bjuppa\EloquentStateMachine;

/**
 * Extend this class and have initialTransitionEvent() in your model return an instance.
 *
 * This is useful if you need any special payload to be sent along with
 * newly created models when the state machine does the first
 * transition to the initial state.
 */
class ModelCreatedStateEvent extends StateEvent
{
}
